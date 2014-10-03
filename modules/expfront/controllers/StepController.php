<?php
class Expfront_StepController extends Symbic_Controller_Action
{
	public $_session = null;
	public $lastContactSet = false;

	public $participantId = null;
	public $sessionId = null;

	private function setTreatmentLayout($treatmentId)
	{
		$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($treatment))
		{
			return;
		}

		if ($treatment->css != '')
		{
			$this->view->headStyle()->appendStyle($treatment->css, array('media'=>'all'));
		}

		if ($treatment->layoutTheme != '')
		{
			$layoutPath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $treatment->layoutTheme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts';
			if (!is_dir($layoutPath))
			{
				throw new Exception('Selected theme does not exist: ' . $treatment->layoutTheme);
			}
			$this->_helper->layout->setLayoutPath($layoutPath);
		}
		
		if ($treatment->layoutDesign != '')
		{
			$this->_helper->layout->setLayout($treatment->layoutDesign);
		}

		$this->view->participantLabel = 'setToHideLogin';
	}

	public function init()
	{
		$this->_session = new Zend_Session_Namespace('expfront');
	}

	public function preDispatch()
	{
		$config = Zend_Registry :: get('config');

		$defaultLayoutTheme = $config['systemConfig']['sophie']['expfront']['defaultLayoutTheme'];
		$defaultLayoutDesign = $config['systemConfig']['sophie']['expfront']['defaultLayoutDesign'];

		$layoutPath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $defaultLayoutTheme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts';

		$layoutHelper = $this->getHelper('layout')->getLayoutInstance();
		$layoutHelper->setLayoutPath($layoutPath);
		$layoutHelper->setLayout($defaultLayoutDesign);

		if (!isset ($this->_session->participantId))
		{
			if ($this->getRequest()->isXmlHttpRequest())
			{
				$this->_error('Not logged in');
			}
			else
			{
				$this->_helper->flashMessenger('No user logged in');
				$this->_helper->getHelper('Redirector')->gotoSimple('index', 'login', 'expfront');
				return;
			}
		}
	}

	public function postDispatch()
	{
		if (isset ($this->_session->participantId))
		{
			$db = Zend_Registry :: get('db');
			// set last contact time
			if (!$this->lastContactSet)
			{
				$db->update('sophie_session_participant', array ('lastContact' => microtime(true)), 'id = ' . $this->_session->participantId . ' AND (lastContact IS NULL OR lastContact < ' . microtime(true) . ')');
				$this->lastContactSet = true;
			}
		}
	}

	/*
	 * process normal step actions here
	 */
	public function indexAction()
	{
		$db = Zend_Registry :: get('db');
		$cache = Zend_Registry :: get('cache');

		// loop over steps to process
		$renderStep = false;
		$stepControllerLoop = 0;

		do
		{
			// TODO: detect user abort and close loop cleanly: problem only possible, when data is send to the client, see flush()

			// get participant
			$participant = $db->fetchRow('SELECT * FROM sophie_session_participant WHERE id = ' . $db->quote($this->_session->participantId));

			if ($participant === false)
			{
				$this->_session->unsetAll();
				$this->_error('Participant does not exists or does not exist any more');
				return;
			}

			if ($participant['httpSession'] != Zend_Session::getId())
			{
				$this->_session->unsetAll();
				$this->_error('Session expired due to newer login.');
				return;
			}

			$session = $db->fetchRow('SELECT * FROM sophie_session WHERE id = ' . $db->quote($participant['sessionId']));

			if ($session === false)
			{
				$this->_session->unsetAll();
				$this->_error('Session does not exists or does not exist any more');
				return;
			}

			// TODO: check maximum runtime to prevent timeouts and show waiting reload page if the loop takes too log
			if ($stepControllerLoop > 20)
			{
				// TODO: Implement showing a waiting reload page here to offload further handling to the next request
				$this->setTreatmentLayout($session['treatmentId']);
				$this->render('processwait');
				return;
			}

			$stepControllerLoop++;
			// check session state
			if ($session['state'] != 'running')
			{
				Sophie_Db_Session_Log :: log($session['id'], 'Request from participant ' . $participant['label'] . ' but session state is ' . $session['state'], 'notice');
				$this->_forward('sessionstate');
				return;
			}

			// find first stepgroup and step to process
			if ($participant['state'] == 'new')
			{
				$processService = Sophie_Service_Session_Process :: getInstance();
				try
				{
					$participant = $processService->initializeParticipant($session['id'], $participant['label']);
				}
				catch (Exception $e)
				{
					$this->_session->unsetAll();
					$this->setTreatmentLayout($session['treatmentId']);
					$this->_error($e->getMessage());
					return;
				}
				$this->lastContactSet = true;
			}

			// check state of participant
			if ($participant['state'] != 'started')
			{
				$this->_forward('participantstate');
				return;
			}

			$cacheTreatment = $session['cacheTreatment'] === '1';
			$cachePrefix = 'sophie_sessionCache_' . $session['id'] . '_';

			// get step
			$step = false;
			if ($cacheTreatment)
			{
				$cacheKey = $cachePrefix . 'treatmentStep_' . $participant['stepId'];
				$step = $cache->load($cacheKey);
			}

			if ($step === false)
			{
				$step = $db->fetchRow('SELECT * FROM sophie_treatment_step WHERE id = ' . $db->quote($participant['stepId']));
			}

			if ($step === false)
			{
				Sophie_Db_Session_Log :: log($session['id'], 'Step for participant ' . $participant['label'] . ' does not exist', 'error');
				$this->_session->unsetAll();
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Participant could not be initialized. Step does not exists');
				return;
			}
			
			if ($cacheTreatment)
			{
				$cache->save($step, $cacheKey);
			}

			// get stepgroup
			$stepgroup = false;
			if ($cacheTreatment)
			{
				$cacheKey = $cachePrefix . 'treatmentStepgroup_' . $step['stepgroupId'];
				$stepgroup = $cache->load($cacheKey);
			}

			if ($stepgroup === false)
			{
				$stepgroup = $db->fetchRow('SELECT * FROM sophie_treatment_stepgroup WHERE id = ' . $db->quote($step['stepgroupId']));
			}

			if ($stepgroup === false)
			{
				Sophie_Db_Session_Log :: log($session['id'], 'Stepgroup for participant ' . $participant['label'] . ' does not exist', 'error');
				$this->_session->unsetAll();
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Stepgroup for participant does not exist');
				return;
			}

			if ($cacheTreatment)
			{
				$cache->save($stepgroup, $cacheKey);
			}

			// get treatment
			$treatment = false;
			if ($cacheTreatment)
			{
				$cacheKey = $cachePrefix . 'treatment_' . $stepgroup['treatmentId'];
				$treatment = $cache->load($cacheKey);
			}

			if ($treatment === false)
			{
				$treatment = $db->fetchRow('SELECT * FROM sophie_treatment WHERE id = ' . $db->quote($stepgroup['treatmentId']));
			}

			if ($treatment === false)
			{
				$this->_error('Treatment for participant does not exist');
				return;
			}

			if ($cacheTreatment)
			{
				$cache->save($treatment, $cacheKey);
			}

			//////////////////////////////////////////////////////////////////////////
			// init SoPHIE context
			//////////////////////////////////////////////////////////////////////////
			$context = new Sophie_Context();
			$context->setParticipant($participant);
			$context->setSession($session);
			$context->setStep($step);
			$context->setStepgroup($stepgroup);
			$context->setTreatment($treatment);
			$context->setController($this);

			// skip inactive steps
			if ($step['active'] !== '1')
			{
				$context->getApi('process')->transferParticipantToNextStep();
				continue;
			}

			//////////////////////////////////////////////////////////////////////////
			// init error handler
			//////////////////////////////////////////////////////////////////////////
			Sophie_Eval_Error_Handler :: $context = $context;
			set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

			///////////////////////////////////////////////////////
			// check stepgroup/stepgroupLoop run condition
			///////////////////////////////////////////////////////
			if ($stepgroup['runConditionScript'] != '')
			{
				Sophie_Eval_Error_Handler :: $script = 'Check Stepgroup Run Conditions';
				try
				{
					$scriptSandbox = $context->getScriptSandbox();
					$scriptReturn = $scriptSandbox->run($stepgroup['runConditionScript']);
					if (!is_null($scriptReturn))
					{
						$runThisStepgroup = $scriptReturn;
					}
					else
					{
						$runThisStepgroup = true;
					}

					$evalOutput = $scriptSandbox->getEvalOutput();
					$evalOutput = trim($evalOutput);
					if ($evalOutput != '')
					{
						if (strlen($evalOutput) > 100)
						{
							$evalOutputShort = substr($evalOutput, 0, 90) . '...';
						}
						else
						{
							$evalOutputShort = $evalOutput;
							$evalOutput = null;
						}
						Sophie_Db_Session_Log :: log($this->getContext()->getSessionId(), 'runConditionScript: ' . $evalOutputShort, 'debug', $evalOutput);
					}
					$scriptSandbox->clearEvalOutput();

				}
				catch (Exception $e)
				{
					// TODO: handle this error in a more usable way
					Sophie_Db_Session_Log :: log($participant['sessionId'], 'Check Stepgroup Run Condition Script failed with exception', 'error', print_r($e, true));
					
					// TODO: forward to an error page and reload after some time
					$this->setTreatmentLayout($session['treatmentId']);
					$this->_error('Check Stepgroup Run Condition Script failed');
					return;
				}

				// return true means -> run this step
				if ($runThisStepgroup === 'skipStepgroup')
				{
					$context->getApi('process')->transferParticipantToNextStepgroup();
					restore_error_handler();
					continue;
				}
				elseif ($runThisStepgroup === 'skipStepgroupLoop')
				{
					$context->getApi('process')->transferParticipantToNextStepgroupLoop();
					restore_error_handler();
					continue;
				}
				elseif (!(boolean)$runThisStepgroup)
				{
					if ($stepgroup['runConditionFalse'] === 'skipStepgroup')
					{
						$context->getApi('process')->transferParticipantToNextStepgroup();
					}
					// case: skipStepgroupLoop
					else
					{
						$context->getApi('process')->transferParticipantToNextStepgroupLoop();
					}
					restore_error_handler();
					continue;
				}
			}

			///////////////////////////////////////////////////////
			// init steptype controller
			///////////////////////////////////////////////////////
			Sophie_Eval_Error_Handler :: $script = 'Creating Step Instance';
			$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
			try
			{
				$steptype = $steptypeFactory->get($step['steptypeSystemName']);
			}
			catch (Exception $e)
			{
				Sophie_Db_Session_Log :: log($session['id'], 'Steptype ' . $step['steptypeSystemName'] . ' could not be initialized', 'error');
				restore_error_handler();
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Steptype could not be initialized');
				return;
			}

			$steptype->setController($this);
			$steptype->setView($this->view);
			$steptype->setContext($context);
			$context->setSteptype($steptype);

			///////////////////////////////////////////////////////
			// check step run condition
			///////////////////////////////////////////////////////
			Sophie_Eval_Error_Handler :: $script = 'Check Step Run Conditions';
			try
			{
				$runThisStep = $steptype->checkRunCondition();
			}
			catch (Exception $e)
			{
				Sophie_Db_Session_Log :: log($participant['sessionId'], 'Check Step Run Condition Script failed with exception', 'error', print_r($e, true));
				
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Check Step Run Condition Script failed');
				return;
			}
			
			// return true means -> run this step
			if (!$runThisStep)
			{
				$context->getApi('process')->transferParticipantToNextStep();
				restore_error_handler();
				continue;
			}

			///////////////////////////////////////////////////////
			// init step
			///////////////////////////////////////////////////////
			Sophie_Eval_Error_Handler :: $script = 'Init Step';
			try
			{
				$steptype->init();
			}
			catch (Exception $e)
			{
				Sophie_Db_Session_Log :: log($participant['sessionId'], 'Initializing step failed!', 'error', print_r($e, true));
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Initializing step failed');
				return;
			}

			///////////////////////////////////////////////////////
			// run step
			///////////////////////////////////////////////////////
			Sophie_Eval_Error_Handler :: $script = 'Run Step';
			try
			{
				$stepRunResult = $steptype->run();
			}
			catch (Exception $e)
			{
				Sophie_Db_Session_Log :: log($participant['sessionId'], 'Running step failed!', 'error', print_r($e, true));
				$this->setTreatmentLayout($session['treatmentId']);
				$this->_error('Running step failed');
				return;
			}

			restore_error_handler();
		}
		while (!isset($stepRunResult) || $stepRunResult !== true);

		$this->_helper->viewRenderer->setNoRender(true);
	}

	public function sessionstateAction()
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->find($this->_session->participantId)->current();
		if (is_null($participant))
		{
			$this->_session->unsetAll();
			$this->_error('Participant does not exist.');
			return;
		}

		$session = $participant->findParentRow('Sophie_Db_Session');

		switch ($session->state)
		{
			case 'running' :
				$this->_forward('index');
				return;
				break;

			case 'created' :
				$this->_helper->viewRenderer->setRender('session-created');
				break;

			case 'paused' :
				$this->_helper->viewRenderer->setRender('session-paused');
				break;

			case 'finished' :
				$this->_session->unsetAll();
				$this->_helper->viewRenderer->setRender('session-finished');
				break;

			case 'archived' :
				$this->_session->unsetAll();
				$this->_helper->viewRenderer->setRender('session-archived');
				break;

			case 'deleted' :
				$this->_session->unsetAll();
				$this->_helper->viewRenderer->setRender('session-deleted');
				break;

			default :
				throw new Exception('Session state unknown.');
				break;
		}

		// check if there are custom screens for created/finished/paused/archived
		$screens = Sophie_Db_Treatment_Screen :: getInstance()->find($session->treatmentId)->current();
		if (!is_null($screens))
		{
			switch ($session->state)
			{
				case 'created' :
					$this->view->customHtml = $screens->createdHtml;
					$this->_helper->viewRenderer->setRender('session-custom');
					break;

				case 'paused' :
					$this->view->customHtml = $screens->pausedHtml;
					$this->_helper->viewRenderer->setRender('session-custom');
					break;

				case 'finished' :
					$this->view->customHtml = $screens->finishedHtml;
					$this->_helper->viewRenderer->setRender('session-custom');
					break;

				case 'archived' :
					$this->view->customHtml = $screens->archivedHtml;
					$this->_helper->viewRenderer->setRender('session-custom');
					break;
			}
		}

		$this->setTreatmentLayout($session->treatmentId);
		$this->view->session = $session->toArray();
		$this->view->participant = $participant->toArray();
	}

	public function participantstateAction()
	{
		$participant = Sophie_Db_Session_Participant :: getInstance()->find($this->_session->participantId)->current();
		if (is_null($participant))
		{
			$this->_session->unsetAll();
			$this->_error('Participant does not exists');
			return;
		}

		$session = $participant->findParentRow('Sophie_Db_Session');

		$eventManager = Zend_Registry :: get('Zend_EventManager');
		$eventManager->trigger('sophie_expfront_participantstate_' . $participant->state, null, array('participant' => $participant, 'session' => $session));

		switch ($participant->state)
		{
			case 'new' :
			case 'started' :
				$this->_forward('index');
				return;
				break;

			case 'excluded' :
				$this->_helper->viewRenderer->setRender('participant-excluded');
				break;

			case 'finished' :
				$this->_session->unsetAll();
				$this->_helper->viewRenderer->setRender('participant-finished');
				break;

			default :
				throw new Exception('Participant state unknown.');
				break;
		}
		
		// check if there are custom screens for created/finished/paused/excluded
		$screens = Sophie_Db_Treatment_Screen :: getInstance()->find($session->treatmentId)->current();
		if (!is_null($screens))
		{
			switch ($participant->state)
			{
				case 'created' :
					$this->view->customHtml = $screens->createdHtml;
					$this->_helper->viewRenderer->setRender('participant-custom');
					break;

				case 'paused' :
					$this->view->customHtml = $screens->pausedHtml;
					$this->_helper->viewRenderer->setRender('participant-custom');
					break;

				case 'finished' :
					$this->view->customHtml = $screens->finishedHtml;
					$this->_helper->viewRenderer->setRender('participant-custom');
					break;

				case 'excluded' :
					$this->view->customHtml = $screens->excludedHtml;
					$this->_helper->viewRenderer->setRender('participant-custom');
					break;
			}
		}
		
		$this->setTreatmentLayout($session->treatmentId);
		$this->view->session = $session->toArray();
		$this->view->participant = $participant->toArray();
	}
}