<?php
class Expadmin_OptionsController extends Symbic_Controller_Action
{
	private $popup = 0;

	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0)
		{
			$this->_error('Missing parameter sessionId');
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

		$popup = $this->_getParam('popup', false);
		if ($popup)
		{
			$this->_helper->layout->setLayout('popup');
			$this->popup = 1;
		}
	}

	public function timerAction()
	{
		$stepId = $this->_getParam('stepId', null);
		if (is_null($stepId))
		{
			$this->_error('Missing parameter stepId');
			return;
		}

		$stepModel = Sophie_Db_Treatment_Step :: getInstance();
		$step = $stepModel->find($stepId)->current();

		if (is_null($step))
		{
			$this->_error('Invalid step');
			return;
		}
		
		$stepgroup = $step->findParentRow('Sophie_Db_Treatment_Stepgroup');
		if (is_null($stepgroup))
		{
			$this->_error('Invalid stepgroup');
			return;
		}

		$stepgroupLoop = $this->_getParam('stepgroupLoop', null);
		if (is_null($stepgroupLoop) || $stepgroupLoop <= 0)
		{
			$this->_error('Missing or invalid parameter stepgroupLoop');
			return;
		}

		$context = new Sophie_Context();
		$context->setPersonContextLevel('none');
		$context->setSession($this->session->toArray());
		$context->setStep($step->toArray());
		$context->setStepgroup($stepgroup->toArray());
		$context->setStepgroupLoop($stepgroupLoop);

		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		$steptype = $steptypeFactory->get($step->steptypeSystemName);
		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$context->setSteptype($steptype);

		$timerApi = $context->getApi('timer');

		if (!$timerApi->isEnabled())
		{
			$this->_error('Step has no active timer.');
			return;
		}

		$timerContext = $timerApi->getTimerContext();

		if ($timerContext === 'E')
		{
			$timerStartTime = $timerApi->getStartTime();
			$timerDuration = $timerApi->getDuration();
			$timerState = $timerApi->getState();
			$timerEndTime = $timerApi->getEndTime();
			$timerCountdown = $timerApi->getCountdownDuration();
		}		
		else
		{
			$timerStartTime = array();
			$timerDuration = array();
			$timerState = array();
			$timerEndTime = array();
			$timerCountdown = array();

			if ($timerContext === 'G')
			{
				$groupLabels = $context->getApi('group')->getGroupLabels();
				foreach ($groupLabels as $groupLabel)
				{
					$groupContext = clone $context;
					$groupContext->setPersonContextLevel('group');
					$groupContext->setGroupLabel($groupLabel);

					$groupTimerApi = $groupContext->getApi('timer');
					
					$timerStartTime[$groupLabel] = $groupTimerApi->getStartTime();
					$timerDuration[$groupLabel] = $groupTimerApi->getDuration();
					$timerState[$groupLabel] = $groupTimerApi->getState();
					$timerEndTime[$groupLabel] = $groupTimerApi->getEndTime();
					$timerCountdown[$groupLabel] = $groupTimerApi->getCountdownDuration();
				}
				$this->view->contextLabels = $groupLabels;
			}
			elseif ($timerContext === 'P')
			{
				$participantLabels = $context->getApi('participant')->getParticipantLabels();
				foreach ($participantLabels as $participantLabel)
				{
					$participantContext = clone $context;
					$participantContext->setPersonContextLevel('participant');
					$participantContext->setParticipantLabel($participantLabel);

					$participantTimerApi = $participantContext->getApi('timer');

					$timerStartTime[$participantLabel] = $participantTimerApi->getStartTime();
					$timerDuration[$participantLabel] = $participantTimerApi->getDuration();
					$timerState[$participantLabel] = $participantTimerApi->getState();
					$timerEndTime[$participantLabel] = $participantTimerApi->getEndTime();
					$timerCountdown[$participantLabel] = $participantTimerApi->getCountdownDuration();
				}
				$this->view->contextLabels = $participantLabels;
			}
		}

		$this->view->session = $this->session->toArray();
		$this->view->step = $step->toArray();
		$this->view->stepgroupLoop = $stepgroupLoop;
		
		$this->view->timerContext = $timerContext;
		$this->view->timerStart = $timerApi->getTimerStart();
		$this->view->timerStartTime = $timerStartTime;
		$this->view->timerDuration = $timerDuration;
		$this->view->timerCountdown = $timerCountdown;
		$this->view->timerState = $timerState;
		$this->view->timerEndTime = $timerEndTime;
		
		$details = $this->_getParam('details', '0');
		if ($details === '1')
		{
			$this->render('timerDetails');
			$this->_helper->layout->disableLayout();
		}
	}

	public function timersetAction()
	{
		$stepId = $this->_getParam('stepId', null);
		if (is_null($stepId))
		{
			$this->_error('Missing parameter stepId');
			return;
		}

		$stepModel = Sophie_Db_Treatment_Step :: getInstance();
		$step = $stepModel->find($stepId)->current();

		if (is_null($step))
		{
			$this->_error('Invalid step');
			return;
		}
		
		$stepgroup = $step->findParentRow('Sophie_Db_Treatment_Stepgroup');
		if (is_null($stepgroup))
		{
			$this->_error('Invalid stepgroup');
			return;
		}

		$stepgroupLoop = $this->_getParam('stepgroupLoop', null);
		if (is_null($stepgroupLoop) || $stepgroupLoop <= 0)
		{
			$this->_error('Missing or invalid parameter stepgroupLoop');
			return;
		}

		$context = new Sophie_Context();
		$context->setPersonContextLevel('none');
		$context->setSession($this->session->toArray());
		$context->setStep($step->toArray());
		$context->setStepgroup($stepgroup->toArray());
		$context->setStepgroupLoop($stepgroupLoop);

		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		$steptype = $steptypeFactory->get($step->steptypeSystemName);
		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$context->setSteptype($steptype);

		$timerApi = $context->getApi('timer');

		if (!$timerApi->isEnabled())
		{
			$this->_error('Step has no active timer.');
			return;
		}

		$timerContext = $timerApi->getTimerContext();

		$requestContext = $this->_getParam('context', null);

		if ($timerContext !== $requestContext)
		{
			$this->_error('Context does not match configured timer context');
			return;
		}
		
		if ($timerContext === 'E')
		{
			$timerApi->start();
			$this->_helper->json(array('message' => 'Timer started'));
			return;
		}

		$requestContextLabel = $this->_getParam('contextLabel', null);
		if (is_null($requestContextLabel))
		{
			$this->_error('No context label passed');
			return;
		}
		
		if ($timerContext === 'G')
		{
			if ($requestContextLabel === '*')
			{
				$groupLabels = $context->getApi('group')->getGroupLabels();
				foreach ($groupLabels as $groupLabel)
				{
					$groupContext = clone $context;
					$groupContext->setPersonContextLevel('group');
					$groupContext->setGroupLabel($groupLabel);

					$groupTimerApi = $groupContext->getApi('timer');
					$groupTimerApi->start();
				}
				$this->_helper->json(array('message' => 'Timer started'));
				return;
			}
			else
			{
				$groupContext = clone $context;
				$groupContext->setPersonContextLevel('group');
				$groupContext->setGroupLabel($requestContextLabel);

				$groupTimerApi = $groupContext->getApi('timer');
				$groupTimerApi->start();

				$this->_helper->json(array('message' => 'Timer started'));
				return;
			}
		}
		
		if ($timerContext === 'P')
		{
			if ($requestContextLabel === '*')
			{
				$participantLabels = $context->getApi('participant')->getParticipantLabels();
				foreach ($participantLabels as $participantLabel)
				{
					$participantContext = clone $context;
					$participantContext->setPersonContextLevel('participant');
					$participantContext->setParticipantLabel($participantLabel);

					$participantTimerApi = $participantContext->getApi('timer');
					$participantTimerApi->start();
				}
				$this->_helper->json(array('message' => 'Timer started'));
				return;
			}
			else
			{
				$participantContext = clone $context;
				$participantContext->setPersonContextLevel('participant');
				$participantContext->setParticipantLabel($requestContextLabel);

				$participantTimerApi = $participantContext->getApi('timer');
				$participantTimerApi->start();

				$this->_helper->json(array('message' => 'Timer started'));
				return;
			}
		}
		
		$this->_error('Invalid timer context');
	}

	public function adminsyncAction()
	{
		// get context parameters:
		$stepId = $this->_getParam('stepId', null);
		if (is_null($stepId))
		{
			$this->_error('Missing parameter stepId');
			return;
		}
		$stepModel = Sophie_Db_Treatment_Step :: getInstance();
		$stepgroupLabel = $stepModel->fetchStepgroupLabelByStepId($stepId);
		if (is_null($stepgroupLabel))
		{
			$this->_error('Invalid stepgroup for selected stepId');
			return;
		}
		$stepgroupLoop = $this->_getParam('stepgroupLoop', 0);

		// init system variable names:
		$varSyncRun = '__stepsyncRun_' . $stepId;
		$varSync = '__stepsync_' . $stepId;
		$varSyncTimestamp = '__stepsync_' . $stepId . '_timestamp';

		// get information about set variables:
		$variableModel = Sophie_Db_Session_Variable :: getInstance();
		$this->view->varSyncRun = $variableModel->fetchValueByNameAndContext($varSyncRun, $this->session->id, null, null, $stepgroupLabel, $stepgroupLoop);
		$this->view->varSync = $variableModel->fetchValueByNameAndContext($varSync, $this->session->id, null, null, $stepgroupLabel, $stepgroupLoop);
		$this->view->varSyncTimestamp = $variableModel->fetchValueByNameAndContext($varSyncTimestamp, $this->session->id, null, null, $stepgroupLabel, $stepgroupLoop);

		// get default values from step eav
		$stepEavModel = Sophie_Db_Treatment_Step_Eav :: getInstance();
		$this->view->adminSyncNote = $stepEavModel->get($stepId, 'adminSyncNote');

		// parse form and set vars

		$form = $this->getForm('Options_Adminsync');
		$form->setAction($this->view->url(array(
			'sessionId' => $this->session->id,
			'stepId' => $stepId,
			'stepgroupLoop' => $stepgroupLoop,
			'popup' => $this->popup
		)));

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$variableModel->setValueByNameAndContext($varSyncRun, 'sync', $this->session->id, null, null, $stepgroupLabel, $stepgroupLoop);
				$variableModel->setValueByNameAndContext($varSyncTimestamp, time(), $this->session->id, null, null, $stepgroupLabel, $stepgroupLoop);

				Sophie_Db_Session_Log :: getInstance()->log($this->session->id, 'Admin sync set');
				$this->_helper->flashMessenger('Admin sync set');

				$this->_helper->redirector->setPrependBase('')->gotoUrl($this->view->url(array (
					'sessionId' => $this->session->id, 'stepId' => $stepId, 'stepgroupLoop' => $stepgroupLoop, 'popup' => $this->popup
				)));
			return;

			}
		}
		$this->view->form = $form;
	}
}