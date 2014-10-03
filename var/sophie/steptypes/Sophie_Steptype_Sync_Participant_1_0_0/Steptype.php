<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Sync_Abstract_1_0_0');

class Sophie_Steptype_Sync_Participant_1_0_0_Steptype extends Sophie_Steptype_Sync_Abstract_1_0_0_Steptype
{

	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		// TODO: add steptype attribute configuration
		return $config;
	}

	public function runAdminProcess()
	{
		$sessionId = $this->getContext()->getSessionId();
		$stepId = $this->getContext()->getStepId();
		$stepLabel = $this->getContext()->getStepLabel();
		$stepgroupLabel = $this->getContext()->getStepgroupLabel();
		$stepgroupLoop = $this->getContext()->getStepgroupLoop();

		// TODO: add an option for participantState = 'new', 'started', 'finished', 'excluded' 
		$inState = array(
			//'new',
			'started',
			//'finished',
			//'excluded'
		);

		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from( array('p' => Sophie_Db_Session_Participant::getInstance()->_name), array('label'));
		$select->where('p.sessionId = ?', $sessionId);
		$select->where('p.stepId = ' . $db->quote($stepId));
		$select->where('p.stepgroupLabel = ' . $db->quote($stepgroupLabel));
		$select->where('p.stepgroupLoop = ' . $db->quote($stepgroupLoop));
		$select->where('NOT p.stepId IS NULL');
		$select->where('p.state IN (' . $db->quote($inState) . ')');

		$participants = $select->query()->fetchAll();

		foreach ($participants as $participant)
		{			
			$participantContext = clone $this->getContext();
			$participantContext->setPersonContextLevel('participant');
			$participantContext->setParticipantLabel($participant['label']);

			if ($participantContext->isUpToDate())
			{
				$syncScript = $this->getAttributeRuntimeValue('syncScript');
				if (!empty($syncScript))
				{
					$sandbox = new Sophie_Script_Sandbox();
					$sandbox->setContext($participantContext);
					$sandbox->setLocalVars($participantContext->getStdApis());

					$sandbox->setThrowOriginalException(true);
					$return = $sandbox->run($syncScript);

					$sandboxOutput = $sandbox->getEvalOutput();
					$sandbox->clearEvalOutput();
					if ($sandboxOutput != '')
					{
						if (strlen($sandboxOutput) > 100)
						{
							$sandboxOutputShort = substr($sandboxOutput, 0, 90) . '...';
						}
						else
						{
							$sandboxOutputShort = $sandboxOutput;
							$sandboxOutput = null;
						}
						Sophie_Db_Session_Log::log($sessionId, 'Sync script output for step ' . $stepLabel . ' and participant ' . $participant['label'] . ': ' . $sandboxOutputShort, null, $sandboxOutput);
					}

					if ($return === false)
					{
						Sophie_Db_Session_Log::log($sessionId, 'Sync script for step ' . $stepLabel . ' and participant ' . $participant['label'] . ' returned false, will not set sync', 'debug');
						continue;
					}
				}

				Sophie_Db_Session_Log::log($sessionId, 'Set sync step ' . $stepLabel . ' for participant ' . $participant['label']);

				$processApi = $participantContext->getApi('process');
				$processApi->transferParticipantToNextStep();
			}
			else
			{
				Sophie_Db_Session_Log::log($sessionId, 'Processing sync step deferred because participant context has changed ' . $stepLabel . ' for participant ' . $participant['label']);
			}
		}
	}
}