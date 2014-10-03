<?php
class Expadmin_ProcessController extends Symbic_Controller_Action
{
	private $session = null;
	private $adminProcessWarningTime = 10;

	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if (is_null($sessionId) || $sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $this->session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}
	}

	private function _log($logLine, $type = "notice")
	{
		Sophie_Db_Session_Log :: log($this->session->id, $logLine, $type);
	}

	public function indexAction()
	{
		$adminProcessState = $this->_getParam('adminProcessState', 'stopped');

		if ($this->session->state == 'running' && $adminProcessState == 'running')
		{
			try
			{
				$this->_runAdminAction();
			}
			catch (Exception $e)
			{
				$this->_log('Admin process exception: ' . print_r($e, 1), 'error');
			}
		}

		$responseContent = array ();

		$responseContent['session'] = array ();
		$responseContent['session']['state'] = $this->session->state;
		$responseContent['session']['lastAdminProcess'] = $this->session->lastAdminProcess;

		if ($this->session->state == 'archived' || $this->session->state == 'deleted')
		{
			$this->_helper->json($responseContent);
			return;
		}

		$participantModel = Sophie_Db_Session_Participant :: getInstance();
		$responseContent['sessionParticipants'] = $participantModel->fetchSessionOverview($this->session->id);

		$responseContent['logs'] = array ();
		// possible types: error, warning, notice, info, debug

		$logModel = Sophie_Db_Session_Log::getInstance();
		$logWhere = 'sessionId = ' . $this->session->id . ' AND type != "event"';
		$lastLogId = (int) $this->_getParam('lastLogId', 0);
		if ($lastLogId > 0)
		{
			$logWhere .= ' AND id > ' . $lastLogId;
		}

		$logs = $logModel->fetchAll($logWhere, 'microtime DESC', 25);
		
		$responseContent['logs'] = $logs->toArray();
		$responseContent['logs'] = array_reverse($responseContent['logs']);

		$responseContent['participants'] = $participantModel->fetchAll('sessionId = ' . $this->session->id)->toArray();

		$this->_helper->json($responseContent);
	}

	private function _runAdminAction()
	{
		$db = Zend_Registry :: get('db');
		$select = $db->select();

		// get distinct contexts with active participants
		$select->from(array (
		'session_participant' => Sophie_Db_Session_Participant :: getInstance()->_name), array (
			'stepgroupLabel' => 'session_participant.stepgroupLabel',
			'stepgroupLoop' => 'session_participant.stepgroupLoop',
			'stepId' => 'session_participant.stepId')
		);

		$select->joinLeft(array (
		'treatment_step' => Sophie_Db_Treatment_Step :: getInstance()->_name), 'treatment_step.id = session_participant.stepId', array (
			'steptypeSystemName' => 'steptypeSystemName'
		));

		$select->where('session_participant.stepId IS NOT NULL');
		$select->where('session_participant.sessionId = ?', $this->session->id);

		$select->group(array (
			'session_participant.stepgroupLabel',
			'session_participant.stepgroupLoop',
			'session_participant.stepId'
		));

		$activeSteps = $select->query()->fetchAll();

		$steptypeObjects = array ();

		foreach ($activeSteps as $activeStep)
		{

			$context = new Sophie_Context();
			$context->setPersonContextLevel('none');
			$context->setSession($this->session->toArray());
			$context->setStepId($activeStep['stepId']);
			$context->setStepgroupLabel($activeStep['stepgroupLabel']);
			$context->setStepgroupLoop($activeStep['stepgroupLoop']);

			try
			{
				$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
				$steptype = $steptypeFactory->get($activeStep['steptypeSystemName']);
				$steptype->setController($this);
				$steptype->setView($this->view);
				$steptype->setContext($context);
				$context->setSteptype($steptype);

				try
				{
					Sophie_Eval_Error_Handler :: $context = $context;
					Sophie_Eval_Error_Handler :: $script = 'Admin Action';
					set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

					// START TRANSACTION;
					$context->beginTransaction();

					$steptype->runAdminProcessTimer();
					$steptype->runAdminProcess();

					$db->commit();

					restore_error_handler();
				}
				catch (Exception $e)
				{
					// TODO: differentiate db connection, timeout or deadlock from application error
					$db->rollBack();
					$this->_log('Admin process caught exception while running: stepId ' . $activeStep['stepId'] . ' - ' . print_r($e, true), 'error');
				}

			}
			catch (Exception $e)
			{
				$this->_log('Admin process caught exception while initializing steptype: stepId ' . $activeStep['stepId'] . ' - ' . print_r($e, true), 'error');
			}

		}
		$this->session->lastAdminProcess = new Zend_Db_Expr('now()');
		$this->session->save();
	}
}