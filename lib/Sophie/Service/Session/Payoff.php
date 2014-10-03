<?php
class Sophie_Service_Session_Payoff
{
	// FUNCTIONS
	public function calculate($sessionId)
	{
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			throw new Exception('Invalid Session Id.');
		}
		$treatment = $session->findParentRow('Sophie_Db_Treatment');

		$participants = Sophie_Db_Session_Participant :: getInstance()->fetchAll(Sophie_Db_Session_Participant :: getInstance()->select()->where('sessionId = ?', $session->id));

		$payoffs = array();
		$moneyPayoffs = array();
		$moneyPayouts = array();

		$secondaryPayoffs = array();
		$secondaryMoneyPayoffs = array();
		$secondaryMoneyPayouts = array();

		foreach ($participants as $participant)
		{
			$context = new Sophie_Context();
			$context->setProcessContextLevel('treatment');
			$context->setParticipant($participant);
			$context->setSession($session->toArray());

			if ($treatment->payoffRetrivalMethod == 'payoffVarSum') {
				$variableTable = Sophie_Db_Session_Variable::getInstance();
				$variableDb = $variableTable->getAdapter();
				$payoffVariables = $variableTable->fetchAllByNameAndContext('payoff', true, $session->id, array('pe', 'ps', 'psl'), null, null, 'participantLabel = ' . $variableDb->quote($participant->label));

				$payoff = 0;
				foreach ($payoffVariables as $payoffVariable)
				{
					if (!isset($payoffVariable['value']))
					{
						Sophie_Db_Session_Log :: log($session->id, 'Payoff variable value is not set for participant ' . $participant->label, 'error', print_r($payoffVariable, true));
					}
					else if (!is_numeric($payoffVariable['value']))
					{
						Sophie_Db_Session_Log :: log($session->id, 'Payoff variable value is not numeric (but ' . gettype($payoffVariable['value']) . ') for participant ' . $participant->label, 'error', print_r($payoffVariable, true));
					}
					else if (!empty($payoffVariable['value']))
					{
						$payoff += $payoffVariable['value'];
					}
				}
				$moneyPayoff = $payoff;
			} elseif ($treatment->payoffRetrivalMethod == 'payoffSumVar') {
				$payoff = $context->getApi('variable')->getPE('payoffSum');
				$moneyPayoff = $payoff;
			} // use payoffScript
			else {
				if ($treatment->payoffScript == '') {
					die('No payoff script defined');
				}
				$sandbox = new Sophie_Script_Sandbox();
				$sandbox->setContext($context);
				$sandbox->setLocalVars($context->getStdApis());

				$payoffScript = '$payoff = 0; $moneyPayoff = 0;' . "\n";
				$payoffScript .= $treatment->payoffScript;
				$payoffScript .= "\n\n";
				$payoffScript .= '$payoffReturnArray = array();' . "\n";
				$payoffScript .= '$payoffReturnArray[\'payoff\'] = $payoff;' . "\n";
				$payoffScript .= '$payoffReturnArray[\'moneyPayoff\'] = $moneyPayoff;' . "\n";
				$payoffScript .= 'return $payoffReturnArray;' . "\n";

				$payoffReturnArray = $sandbox->run($payoffScript);
				$payoff = $payoffReturnArray['payoff'];
				$moneyPayoff = $payoffReturnArray['moneyPayoff'];
			}

			if ($payoff === null)
			{
				$payoff = 0;
			}
			if ($moneyPayoff === null)
			{
				$moneyPayoff = 0;
			}

			if (!is_numeric($payoff))
			{
				Sophie_Db_Session_Log :: log($session->id, '$payoff value ("' . $payoff . '") is not numeric (but ' . gettype($payoff) . ') for participant ' . $participant->label, 'error');
				$payoff = 0;
			}
			if (!is_numeric($moneyPayoff))
			{
				Sophie_Db_Session_Log :: log($session->id, '$moneyPayoff value ("' . $moneyPayoff . '") is not numeric (but ' . gettype($moneyPayoff) . ') for participant ' . $participant->label, 'error');
				$moneyPayoff = 0;
			}

			$payoffs[$participant->label] = $payoff;
			$moneyPayoffs[$participant->label] = $moneyPayoff;
			$moneyPayouts[$participant->label] = ceil($moneyPayoff * 10) / 10;


			if ($treatment->secondaryPayoffRetrivalMethod != 'inactive') {
				if ($treatment->secondaryPayoffRetrivalMethod == 'payoff2VarSum') {
					$variableTable = Sophie_Db_Session_Variable::getInstance();
					$variableDb = $variableTable->getAdapter();
					$payoffVariables2 = $variableTable->fetchAllByNameAndContext('payoff2', true, $session->id, array('pe', 'ps', 'psl'), null, null, 'participantLabel = ' . $variableDb->quote($participant->label));

					$payoff2 = 0;
					foreach ($payoffVariables2 as $payoffVariable2) {
						if (!empty($payoffVariable2['value'])) {
							$payoff2 += $payoffVariable2['value'];
						}
					}
					$moneyPayoff2 = $payoff2;
				} elseif ($treatment->secondaryPayoffRetrivalMethod == 'payoffSum2Var') {
					$payoff2 = $context->getApi('variable')->getPE('payoffSum2');
					$moneyPayoff2 = $payoff2;
				} // use payoffScript
				else {
					if ($treatment->secondaryPayoffScript == '') {
						die('No payoff script defined');
					}
					$sandbox = new Sophie_Script_Sandbox();
					$sandbox->setContext($context);
					$sandbox->setLocalVars($context->getStdApis());

					$payoffScript = '$payoff = 0; $moneyPayoff = 0;' . "\n";
					$payoffScript .= $treatment->secondaryPayoffScript;
					$payoffScript .= "\n\n";
					$payoffScript .= '$payoffReturnArray = array();' . "\n";
					$payoffScript .= '$payoffReturnArray[\'payoff\'] = $payoff;' . "\n";
					$payoffScript .= '$payoffReturnArray[\'moneyPayoff\'] = $moneyPayoff;' . "\n";
					$payoffScript .= 'return $payoffReturnArray;' . "\n";

					$payoffReturnArray2 = $sandbox->run($payoffScript);
					$payoff2 = $payoffReturnArray2['payoff'];
					$moneyPayoff2 = $payoffReturnArray2['moneyPayoff'];
				}

				$secondaryPayoffs[$participant->label] = $payoff2;
				$secondaryMoneyPayoffs[$participant->label] = $moneyPayoff2;
				$secondaryMoneyPayouts[$participant->label] = ceil($moneyPayoff2 * 10) / 10;
			}
		}
		return array(
			'payoffs' => $payoffs,
			'moneyPayoffs' => $moneyPayoffs,
			'moneyPayouts' => $moneyPayouts,

			'secondaryPayoffs' => $secondaryPayoffs,
			'secondaryMoneyPayoffs' => $secondaryMoneyPayoffs,
			'secondaryMoneyPayouts' => $secondaryMoneyPayouts,
		);
	}
}