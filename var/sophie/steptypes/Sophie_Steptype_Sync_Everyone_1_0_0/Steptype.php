<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Sync_Abstract_1_0_0');

class Sophie_Steptype_Sync_Everyone_1_0_0_Steptype extends Sophie_Steptype_Sync_Abstract_1_0_0_Steptype
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
		// TODO: add option to run as admin process or as a participant process
		
		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		$stepLabel = $context->getStepLabel();

		$syncApi = $context->getApi('sync');
		if ($syncApi->checkEveryone())
		{		
			$syncScript = $this->getAttributeRuntimeValue('syncScript');
			if (!empty($syncScript))
			{
				$sandbox = new Sophie_Script_Sandbox();
				$sandbox->setContext($this->getContext());
				$sandbox->setLocalVars($this->getContext()->getStdApis());

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
					Sophie_Db_Session_Log :: log($sessionId, 'Sync script output for step ' . $stepLabel . ': ' . $sandboxOutputShort, null, $sandboxOutput);
				}

				if ($return === false)
				{
					Sophie_Db_Session_Log::log($sessionId, 'Sync script for step ' . $stepLabel . ' returned false, will not set sync', 'debug');
					return;
				}
			}

			Sophie_Db_Session_Log :: log($sessionId, 'Set sync step ' . $stepLabel);

			$processApi = $this->getContext()->getApi('process');
			$processApi->transferEveryoneToNextStep();
		}
	}
}