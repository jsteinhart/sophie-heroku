<?php
class Expfront_AjaxController extends Symbic_Controller_Action
{
	public $lastContactSet = false;

	public $participantId = null;
	public $sessionId = null;

	public function init()
	{
		$session = new Zend_Session_Namespace('expfront');
		if (isset($session->participantId))
		{
			$this->participantId = $session->participantId;
		}
		Zend_Session::writeClose();
	}

	public function preDispatch()
	{
		$this->_helper->layout->setLayout('frontend');

		if (!isset ($this->participantId) || is_null($this->participantId))
		{
			if ($this->getRequest()->isXmlHttpRequest())
			{
				$this->_error('Not logged in');
				return;
			}
			else
			{
				$this->_helper->flashMessenger('No user logged in');
				$this->_helper->getHelper('Redirector')->gotoSimple('index', 'login', 'expfront');
				return;
			}
		}
	}

	private function setLastContact($checkAlreadySet = true)
	{
		if (isset($this->participantId) && (!$checkAlreadySet || !$this->lastContactSet))
		{
			$time = microtime(true);
			$db = Zend_Registry :: get('db');
			$db->update('sophie_session_participant', array (
				'lastContact' => $time), 'id = ' . $this->participantId);
			$this->lastContactSet = true;
		}
	}
	
	public function postDispatch()
	{
		$this->setLastContact();
	}

	public function servertimeAction()
	{
		$this->_helper->json(array('servertime'=>time(), 'servermillitime' => round(microtime( true ) * 1000, 0)));
	}

	public function stepsyncAction()
	{
		$db = Zend_Registry :: get('db');

		$participantParts = $db->fetchRow('SELECT httpSession, sessionId FROM sophie_session_participant WHERE id = ' . $db->quote($this->participantId));
		if (!$participantParts)
		{
			$this->_error('Participant does not exists or does not exist any more');
			return;
		}

		if ($participantParts['httpSession'] != Zend_Session::getId())
		{
			$this->_error('Session expired due to newer login.');
			return;
		}

		$this->sessionId = $participantParts['sessionId'];

		// preparations as in StepController -> indexAction
		// participant:
		$participant = $db->fetchRow('SELECT * FROM sophie_session_participant WHERE id = ' . $db->quote($this->participantId));

		// session:
		$session = $db->fetchRow('SELECT * FROM sophie_session WHERE id = ' . $db->quote($participant['sessionId']));
		// step:
		$step = $db->fetchRow('SELECT * FROM sophie_treatment_step WHERE id = ' . $db->quote($participant['stepId']));
		// stepgroup:
		$stepgroup = $db->fetchRow('SELECT * FROM sophie_treatment_stepgroup WHERE id = ' . $db->quote($step['stepgroupId']));

		//////////////////////////////////////////////////////////////////////////
		// init steptype controller
		//////////////////////////////////////////////////////////////////////////

		$context = new Sophie_Context();
		$context->setParticipant($participant);
		$context->setSession($session);
		$context->setStep($step);
		$context->setStepgroup($stepgroup);
		
		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		try
		{
			$steptype = $steptypeFactory->get($step['steptypeSystemName']);
		}
		catch (Exception $e)
		{
			// TODO: add logging
			// TODO: handle this with a dummy steptype?
			$this->_error('Steptype could not be initialized');
			return;
		}
		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$context->setSteptype($steptype);

		//////////////////////////////////////////////////////////////////////////
		// check context checksum
		//////////////////////////////////////////////////////////////////////////

		$contextChecksum = $this->_getParam('contextChecksum', null);
		if (is_null($contextChecksum))
		{
			$this->_error('No context checksum passed');
			return;
		}

		// the ajax request has been sent for a step the participant is no more in
		if ($contextChecksum !== $context->getChecksum())
		{
			$this->setLastContact();
			$this->_helper->json(array('nextStep'=>'1'));
			//$this->_error('Participant is no more in this step');
			return;
		}

		//////////////////////////////////////////////////////////////////////////
		// run sync
		//////////////////////////////////////////////////////////////////////////

		$lastResult = $this->_getParam('lastResult', 'null');
		try
		{
			$lastResult = Zend_Json::decode($lastResult, true);
		}
		catch (Exception $e)
		{
			$lastResult = null;
		}

		if ($lastResult === NULL)
		{
			$newResult = $steptype->ajaxSync();
			$this->setLastContact();
			$this->_helper->json($newResult);
			return;
		}

		if (!is_array($lastResult) || sizeof($lastResult) == 0)
		{
			$lastResult = null;
		}

		$config = Zend_Registry::get('config');
		$syncLoopLimit = (int)$config['systemConfig']['sophie']['expfront']['ajaxStepsyncLoopLimit'];
		if (empty($syncLoopLimit))
		{
			$syncLoopLimit = 1;
		}

		$syncLoopSleep = (int)$config['systemConfig']['sophie']['expfront']['ajaxStepsyncLoopSleep'];
		if (empty($syncLoopSleep))
		{
			$syncLoopSleep = 250;
		}

		$syncLoop = 1;
		$ajaxStepsyncSetLastContactLast = 0;
		$ajaxStepsyncSetLastContactInterval = (int)$config['systemConfig']['sophie']['expfront']['ajaxStepsyncSetLastContactInterval'];
		if (empty($ajaxStepsyncSetLastContactInterval))
		{
			$ajaxStepsyncSetLastContactInterval = 5000;
		}

		while($syncLoop <= $syncLoopLimit)
		{
			if ($syncLoop > 1)
			{
				usleep($syncLoopSleep * 1000);
				$ajaxStepsyncSetLastContactLast += $syncLoopSleep;
				
				if ($context->isUpToDate() === false)
				{
					$this->setLastContact();
					$this->_helper->json(array('nextStep'=>'1'));
					return;
				}
			}

			$newResult = $steptype->ajaxSync();

			// break if lastResult differs from newResult, ignoring time
			foreach ($newResult as $resKey => $resVal)
			{
				if ($resKey != 'time' && (!array_key_exists($resKey, $lastResult) || $lastResult[$resKey] != $resVal))
				{
					break 2;
				}
			}

			// update lastContact only every ajaxStepsyncSetLastContactInterval ms
			if ($ajaxStepsyncSetLastContactLast > $ajaxStepsyncSetLastContactInterval)
			{
				$this->setLastContact(false);
				$syncSetLastContactLast = 0;
			}
			$syncLoop++;
		}
		
		$this->_helper->json($newResult);
	}

	public function processAction()
	{
		$db = Zend_Registry :: get('db');

		$participantParts = $db->fetchRow('SELECT httpSession, sessionId FROM sophie_session_participant WHERE id = ' . $db->quote($this->participantId));
		if (!$participantParts)
		{
			$this->_error('Participant does not exists or does not exist any more');
			return;
		}

		if ($participantParts['httpSession'] != Zend_Session::getId())
		{
			$this->_error('Session expired due to newer login.');
			return;
		}

		$this->sessionId = $participantParts['sessionId'];


		// get participant
		$participant = $db->fetchRow('SELECT * FROM sophie_session_participant WHERE id = ' . $db->quote($this->participantId));
		if (!$participant)
		{
			$this->_error('Participant does not exists or does not exist any more');
			return;
		}

		$session = $db->fetchRow('SELECT * FROM sophie_session WHERE id = ' . $db->quote($participant['sessionId']));
		if (!$session)
		{
			$this->_error('Session does not exists or does not exist any more');
			return;
		}

		// check session state
		if ($session['state'] != 'running')
		{
			Sophie_Db_Session_Log :: log($session['id'], 'Ajax Request from participant ' . $participant['label'] . ' but session state is ' . $session['state'], 'notice');
			$this->_forward('sessionstate');
			return;
		}

		// check state of participant
		if ($participant['state'] != 'started')
		{
			$this->_forward('participantstate');
			return;
		}

		$step = $db->fetchRow('SELECT * FROM sophie_treatment_step WHERE id = ' . $db->quote($participant['stepId']));
		if (!$step)
		{
			$this->_error('Step not found');
			return;
		}

		$stepgroup = $db->fetchRow('SELECT * FROM sophie_treatment_stepgroup WHERE id = ' . $db->quote($step['stepgroupId']));
		if (!$stepgroup)
		{
			$this->_error('Stepgroup not found');
			return;
		}

		//////////////////////////////////////////////////////////////////////////
		// init steptype controller
		//////////////////////////////////////////////////////////////////////////
		$context = new Sophie_Context();
		$context->setParticipant($participant);
		$context->setSession($session);
		$context->setStep($step);
		$context->setStepgroup($stepgroup);
		$context->setController($this);

		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		try
		{
			$steptype = $steptypeFactory->get($step['steptypeSystemName']);
		}
		catch (Exception $e)
		{
			Sophie_Db_Session_Log :: log($session['id'], 'Steptype ' . $step['steptypeSystemName'] . ' could not be initialized', 'error');
			$this->_error('Steptype could not be initialized');
			return;
		}

		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$context->setSteptype($steptype);

		//////////////////////////////////////////////////////////////////////////
		// check context checksum
		//////////////////////////////////////////////////////////////////////////

		$contextChecksum = $this->_getParam('contextChecksum', null);
		if (is_null($contextChecksum))
		{
			$this->_error('No context checksum passed');
			return;
		}

		// the ajax request has been sent for a step the participant is no more in
		if ($contextChecksum !== $context->getChecksum())
		{
			$this->setLastContact();
			$this->_helper->json(array('nextStep'=>'1'));
//			$this->_error('Participant is no more in this step');
			return;
		}

		//////////////////////////////////////////////////////////////////////////
		// execute ajax process action
		//////////////////////////////////////////////////////////////////////////

		$steptype->init();

		if (!$steptype->preAjaxProcess())
		{
			$this->setLastContact();
			$this->_helper->json(array('error'=>'Pre ajax process returned false.'));
			return;
		}

		Sophie_Eval_Error_Handler :: $context = $context;
		Sophie_Eval_Error_Handler :: $script = 'Ajax Process';
		set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

		$response = $steptype->ajaxProcess();

		restore_error_handler();

		//$steptype->postAjaxProcess(& $response);

		$this->setLastContact();
		$this->_helper->json($response);
	}

	public function sessionstateAction()
	{
		$this->_error('sessionstate ' . $this->session->state);
	}

	public function participantstateAction()
	{
		$this->_error('participantstate ' . $this->participant->state);
	}

}