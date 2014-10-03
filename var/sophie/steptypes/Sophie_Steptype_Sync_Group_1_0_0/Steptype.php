<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Sync_Abstract_1_0_0');

class Sophie_Steptype_Sync_Group_1_0_0_Steptype extends Sophie_Steptype_Sync_Abstract_1_0_0_Steptype
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

		$sessionId = $this->getContext()->getSessionId();
		$stepId = $this->getContext()->getStepId();
		$stepLabel = $this->getContext()->getStepLabel();
		$stepgroupLabel = $this->getContext()->getStepgroupLabel();
		$stepgroupLoop = $this->getContext()->getStepgroupLoop();

		$stepgroup = $this->getContext()->getStepgroup();
		if ($stepgroup['grouping'] == 'inactive')
		{
			Sophie_Db_Session_Log::log($this->getContext()->getSessionId(), 'Group sync step is in a stepgroup without grouping', 'error');
			return;
		}

		// TODO: add an option for participantState = 'new', 'started', 'finished', 'excluded' 
		$inState = array(
			//'new',
			'started',
			//'finished',
			//'excluded'
		);

		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from( array('p' => Sophie_Db_Session_Participant::getInstance()->_name), array('num'=>new Zend_Db_Expr('count(*)')));
		$select->joinLeft(array('g' => Sophie_Db_Session_Participant_Group::getInstance()->_name), 'p.sessionId = g.sessionId AND p.label = g.participantLabel AND p.stepgroupLabel = g.stepgroupLabel AND p.stepgroupLoop = g.stepgroupLoop', array('groupLabel'));
		$select->where('p.sessionId = ?', $sessionId);
		$select->where('p.stepId = ' . $db->quote($stepId) . ' AND p.stepgroupLabel = ' . $db->quote($stepgroupLabel) . ' AND p.stepgroupLoop = ' . $db->quote($stepgroupLoop) . ' AND NOT p.stepId IS NULL AND p.state IN (' . $db->quote($inState) . ')');
		$select->group(array('p.sessionId', 'p.stepgroupLabel', 'p.stepgroupLoop', 'p.stepId', 'g.groupLabel'));

		$groups = $select->query()->fetchAll();

		foreach ($groups as $group)
		{
			if (empty($group['groupLabel']))
			{
				Sophie_Db_Session_Log::log($this->getContext()->getSessionId(), 'sync step ' . $this->getContext()->getStepId() . ': found empty group label', 'error');
				continue;
			}
			$groupContext = clone $this->getContext();
			$groupContext->setPersonContextLevel('group');
			$groupContext->setGroupLabel($group['groupLabel']);

			$syncApi = $groupContext->getApi('sync');
			if ($syncApi->checkGroup())
			{
				$syncScript = $this->getAttributeRuntimeValue('syncScript');
				if (!empty($syncScript))
				{
					$sandbox = new Sophie_Script_Sandbox();
					$sandbox->setContext($groupContext);
					$sandbox->setLocalVars($groupContext->getStdApis());

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
						Sophie_Db_Session_Log::log($sessionId, 'Sync script output for step ' . $stepLabel . ' and group ' . $group['groupLabel'] . ': ' . $sandboxOutputShort, null, $sandboxOutput);
					}

					if ($return === false)
					{
						Sophie_Db_Session_Log::log($sessionId, 'Sync script for step ' . $stepLabel . ' and group ' . $group['groupLabel'] . ' returned false, will not set sync', 'debug');
						continue;
					}
				}

				Sophie_Db_Session_Log::log($sessionId, 'Set sync step ' . $stepLabel . ' and group ' . $group['groupLabel']);

				$processApi = $groupContext->getApi('process');
				$processApi->transferGroupToNextStep();
			}
		}
	}
}