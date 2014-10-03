<?php
class Expdesigner_StepController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $stepgroupId = null;
	private $stepId = null;

	private $experiment = null;
	private $treatment = null;
	private $stepgroup = null;
	private $step = null;
	
	public function preDispatch()
	{
		$this->stepId = $this->_getParam('stepId', null);
		if (!is_null($this->stepId))
		{
			$this->step = Sophie_Db_Treatment_Step :: getInstance()->find($this->stepId)->current();
			if (is_null($this->step))
			{
				$this->_error('Selected step does not exist!');
				return;
			}
			$this->stepgroupId = $this->step->stepgroupId;
		}
		else
		{
			$this->stepgroupId = $this->_getParam('stepgroupId', null);
		}

		if (empty ($this->stepId) && empty ($this->stepgroupId))
		{
			$this->_error('Paramater stepId or stepgroupId missing!');
			return;
		}

		$this->stepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->find($this->stepgroupId)->current();
		if (is_null($this->stepgroup))
		{
			$this->_error('Selected stepgroup does not exist!');
			return;
		}
		$this->treatmentId = $this->stepgroup->treatmentId;
		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist!');
			return;
		}
		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->experimentId = $this->experiment->id;

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied.');
			return;
		}

		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentStructureTab'
			),
			'stepgroup' => array (
				'id' => $this->stepgroup->id,
				'treatmentId' => $this->treatment->id,
				'name' => $this->stepgroup->name
			)
		);
		if ($this->step)
		{
			$this->view->breadcrumbs['step'] = array (
				'id' => $this->step->id,
				'name' => $this->step->name
			);
		}
	}

	public function indexAction()
	{
		$this->_forward('edit');
	}

	public function addAction()
	{
		$this->view->stepgroup = $this->stepgroup->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->experiment = $this->experiment->toArray();

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Step',
			'small' => 'Step:',
			'name' => 'Add Step'
		);
	}

	public function add2Action()
	{
		$steptypeModel = new Sophie_Db_Steptype();

		if ($this->_hasParam('steptypeId'))
		{
			$steptypeId = $this->_getParam('steptypeId');
			$steptype = $steptypeModel->find($steptypeId)->current();
			$this->view->steptype = $steptype->toArray();
		}

		$this->view->stepgroup = $this->stepgroup->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->experiment = $this->experiment->toArray();

		$this->_helper->layout->disableLayout();

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Step',
			'small' => 'Step:',
			'name' => 'Add Step'
		);
	}

	public function add3Action()
	{

		$steptypeModel = new Sophie_Db_Steptype();

		if (!$this->_hasParam('steptypeId'))
		{
			$this->_helper->flashMessenger('No steptype parameter given');
			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'expdesigner',
				'controller' => 'step',
				'action' => 'add',
				'stepgroupId' => $this->stepgroup['id']
			), 'default', true);
			return;
		}

		$steptypeId = $this->_getParam('steptypeId');
		$steptype = $steptypeModel->find($steptypeId)->current();
		if (is_null($steptype))
		{
			$this->_helper->flashMessenger('Selected steptype does not exist');
			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'expdesigner',
				'controller' => 'step',
				'action' => 'add',
				'stepgroupId' => $this->stepgroup['id']
			), 'default', true);
			return;
		}

		if ($steptype->isActive == 0)
		{
			$this->_helper->flashMessenger('Selected steptype is not active');
			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'expdesigner',
				'controller' => 'step',
				'action' => 'add',
				'stepgroupId' => $this->stepgroup['id']
			), 'default', true);
			return;
		}

		$form = $this->getForm('Step_Add3');
		$form->setAction($this->view->url());

		$labelElement = $form->getElement('label');
		$labelValidator = new \Sophie_Validate_Treatment_Step_Label();
		$labelValidator->setUniqueCheck(true);
		$labelValidator->treatmentId = $this->treatmentId;
		$labelElement->addValidator($labelValidator, true);

		$form->setDefaults(array (
			'stepgroupId' => $this->stepgroup['id'],
			'steptypeSystemName' => $steptype->systemName,
		));

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{

				$values = $form->getValues();
				
				// auto generate label:
				if (empty($values['label']))
				{
					$values['label'] = preg_replace('/[^a-z0-9_]/i', '', $values['name']);
				}

				$internalLabelValidator = new \Sophie_Validate_Treatment_Step_Label();
				$internalLabelValidator->setUniqueCheck(true);
				$internalLabelValidator->treatmentId = $this->treatmentId;
				if ($internalLabelValidator->isValid($values['label']) == false)
				{
					$uniqid = preg_replace('/[^a-z0-9_]/i', '', uniqid('', true));
					$values['label'] = substr($values['label'], 0, 254 - strlen($uniqid)) . '_' . $uniqid;
				}

				$data = array (
					'name' => $values['name'],
					'label' => $values['label'],
					'steptypeSystemName' => $steptype->systemName,
					'stepgroupId' => $this->stepgroup['id']
				);
				
				
				$stepModel = Sophie_Db_Treatment_Step::getInstance();
				
				$position = $this->getParam('position', null);
				$stepId = $this->getParam('stepId', null);
				
				if (!empty($position) && $position === '1')
				{
					$data['position'] = 1;
				}
				elseif (!empty($stepId))
				{					
					$positionStep = $stepModel->find($stepId)->current();
					if (!is_null($positionStep) && $positionStep->stepgroupId === $this->stepgroup->id)
					{
						$data['position'] = $positionStep->position + 1;
					}
				}
								
				$id = $stepModel->insertPosition($data);
				$this->step = Sophie_Db_Treatment_Step :: getInstance()->find($id)->current();

				$context = new Sophie_Context();
				$context->setPersonContextLevel('none');
				$context->setTreatment($this->treatment->toArray());
				$context->setStepgroup($this->stepgroup->toArray());
				$context->setStep($this->step->toArray());

				$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
				try
				{
					$steptype = $steptypeFactory->get($steptype->systemName);
				}
				catch (Exception $e)
				{
					die('Steptype could not be initialized');
				}

				$steptype->setController($this);
				$steptype->setView($this->view);
				$steptype->setContext($context);
				$context->setSteptype($steptype);

				$steptype->adminSetDefaultValues();

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Step added: ' . print_r($this->step->toArray(), true));

				$this->_helper->flashMessenger('Step added');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'step',
					'action' => 'edit',
					'stepId' => $id
				), 'default', true);
				return;
			}
			else
			{
			}
		}

		$this->view->steptype = $steptype->toArray();
		$this->view->stepgroup = $this->stepgroup->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->experiment = $this->experiment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Step',
			'small' => 'Step:',
			'name' => 'Add ' . $steptype->name . ' (' . $steptype->version . ')'
		);
	}

	public function editAction()
	{
	    if ($this->step == null || empty ($this->step))
	    {
	      $this->_error('Step missing!');
	      return;
	    }

		$context = new Sophie_Context();
		$context->setPersonContextLevel('none');
		$context->setTreatment($this->treatment->toArray());
		$context->setStepgroup($this->stepgroup->toArray());
		$context->setStep($this->step->toArray());

		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		try
		{
			$steptype = $steptypeFactory->get($this->step->steptypeSystemName);
		}
		catch (Exception $e)
		{
			die('Steptype could not be initialized');
		}

		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$context->setSteptype($steptype);

		if ($this->getRequest()->isPost())
		{
			if ($steptype->adminIsValid($_POST))
			{
				$steptype->adminProcess();

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Step changed: ' . print_r($this->step->toArray(), true) . 'Values: ' .  print_r($steptype->getAttributeValues(),true));

				$this->_helper->flashMessenger('Changes to Step saved');

				if (isset($_POST['saveAndReturn']))
				{
					$this->_helper->getHelper('Redirector')->gotoRoute(array (
						'module' => 'expdesigner',
						'controller' => 'treatment',
						'action' => 'details',
						'treatmentId' => $this->treatment['id']
					), 'default', true);
					return;
				}
				else
				{
					$url = $this->_helper->url->url(array (
						'module' => 'expdesigner',
						'controller' => 'step',
						'action' => 'edit',
						'stepId' => $this->step['id']
					), 'default', true);
					$anchor = $this->_getParam('__tabAnchor', null);
					if (!empty($anchor)) {
						$url .= '#' . $anchor;
					}
					$this->_helper->redirector->setPrependBase('')->gotoUrl($url);
					return;
				}
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->stepgroup = $this->stepgroup->toArray();
		$this->view->step = $this->step->toArray();

		$this->view->steptype = $steptype;
	}

	public function copyAction()
	{
		if (is_null($this->step))
		{
			$this->_error('Paramater stepId missing!');
			return;
		}

		$stepModel = Sophie_Db_Treatment_Step::getInstance();
		$newId = $stepModel->copyById($this->step->id);

		//Log
		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Copied step with id ' . $this->step->id . ' to step with id ' . $newId);

		$this->_helper->json(array('message' => 'Step copied'));
	}

	public function setAction()
	{
		$name = $this->_getParam('name', null);
		$value = $this->_getParam('value', null);
		
		if (is_null($name))
		{
			$this->_error('Parameter missing');
			return;
		}

		$error = null;
		switch ($name)
		{
			case 'name':
				if (empty($value))
				{
					$error = 'Value cannot be empty';
				}
				else
				{
					$this->step->name = $value;
					$this->step->save();
					$message = 'Change successful';
				}
				break;

			case 'step_types':
				if (empty($value))
				{
					$value = array();
				}
				elseif (!is_array($value))
				{
					$value = array($value);
				}

				$stepModel = Sophie_Db_Treatment_Step::getInstance();
				Sophie_Db_Treatment_Step_Type::getInstance()->setByStep($this->step->id, $value);
				$message = 'Change successful';
				break;

			default:
				$error = 'Unknown attribute name';
		}

		if (!is_null($error))
		{
			$this->getResponse()->setHttpResponseCode(500);
			$this->_helper->json(array('error' => $error));
			return;
		}
		
		$this->_helper->json(array('message' => 'Change successful'));
	}

	public function deleteAction()
	{
		$oldValues = $this->step->toArray();
		Sophie_Db_Treatment_Step :: getInstance()->deletePosition($this->step->id);

		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Step deleted: ' . print_r($oldValues, true));

		$this->_helper->json(array('message' => 'Step deleted'));
	}

	public function checkcodeAction()
	{
		$code = $this->_getParam('code', '');
		$type = $this->_getParam('type', '');

		$errors = array();
		$notices = array();

		if ($code != '')
		{
			if ($type == 'html')
			{
				$code = '?>' . $code;
			}
			else
			{
				$code = '<?php ' . $code;
			}

			$validate = new Sophie_Validate_PHPCode();
			
			$valid = $validate->isValid($code);
			
			if ($valid !== true)
			{
				$errors = $validate->getMessages();
			}
		}

		$return = array();
		$return['errors'] = $errors;
		$return['notices'] = $notices;
		$this->_helper->json($return);
	}
	
	public function previewnavigationAction()
	{
		$jumpToStep = $this->getParam('jumpToStep', null);
		if (!is_null($jumpToStep))
		{
			if ($jumpToStep == 'next')
			{
				$jumpToStep = $this->step->id;
				
				$nextStep = Sophie_Db_Treatment_Step::getInstance()->fetchRowByStepgroupIdPosition( $this->stepgroup->id, $this->step->position + 1 );
				if ($nextStep)
				{
					$jumpToStep = $nextStep->id;
				}
				else
				{
					$nextStepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->fetchRowByTreatmentPosition( $this->stepgroup->treatmentId, null, $this->stepgroup->position + 1);
					if ($nextStepgroup)
					{
						$nextStep = Sophie_Db_Treatment_Step::getInstance()->fetchRowByStepgroupIdPosition( $nextStepgroup->id, 1 );
						if ($nextStep)
						{
							$jumpToStep = $nextStep->id;
						}
					}
				}
			}
			
			elseif ($jumpToStep == 'previous')
			{
				if ($this->step->position > 1)
				{
					$newStep = Sophie_Db_Treatment_Step::getInstance()->fetchRowByStepgroupIdPosition( $this->stepgroup->id, $this->step->position - 1 );
					$jumpToStep = $newStep->id;
				}
				elseif ($this->stepgroup->position > 1)
				{
					$newStepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->fetchRowByTreatmentPosition( $this->stepgroup->treatmentId, $this->stepgroup->position - 1 );
					$newStep = Sophie_Db_Treatment_Step::getInstance()->fetchLastRowByStepgroupId( $newStepgroup->id );
					$jumpToStep = $newStep->id;
				}
				else
				{
					$jumpToStep = $this->step->id;
				}
			}
			
			//die('newstep' . print_r($jumpToStep, true));
			$this->getHelper('redirector')->gotoSimple('previewnavigation', 'step', 'expdesigner', array('stepId' => $jumpToStep));
			return;
		}

		$scanState = 'previous';
		$nextStepNext = false;
		$previousStep = null;
		$nextStep = null;
		
        $treatmentStructure = array();
        $structureStepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));
        foreach ($structureStepgroups as $structureStepgroup) {
            $structureSteps = $structureStepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
            foreach ($structureSteps as $structureStep) {
				if ($scanState == 'previous')
				{
					if ($structureStep->id == $this->step->id)
					{
						$scanState = 'current';
					}
					else
					{
						$previousStep = $structureStep->id;
					}
				}
				elseif ($scanState == 'current')
				{
					$nextStep = $structureStep->id;
				}
				$structureStepDescription = $structureStepgroup->position . '.' . $structureStep->position . ' : ' . $structureStep->name;
				/*if (!in_array($structureStep->steptypeSystemName, $this->steptypesWithPreviewFeature))
				{
					$structureStepDescription .= ' (No preview)';
				}*/
                $treatmentStructure[$structureStep->id] = $structureStepDescription;
            }
        }

		$this->view->step = $this->step;
		$this->view->nextStep = $nextStep;
		$this->view->previousStep = $previousStep;
		$this->view->treatmentStructure = $treatmentStructure;
	}
	
	public function previewAction()
	{
		// get parameters: testing sessionId, participantId
			
		//////////////////////////////////////////////////////////////////////////
		// init steptype controller
		//////////////////////////////////////////////////////////////////////////

		$participant = array(
				'sessionId' => null,
				'label' => 'P.1',
				'number' => '1',
				'typeLabel' => 'P',
				'stepgroupLabel' => $this->stepgroup->label,
				'stepgroupLoop' => 1,
				'stepId' => $this->step->id
			);
		$session = array(
				'id' => null,
				'treatmentId' => $this->stepgroup->treatmentId,
				'name' => 'Preview Session',
			);
		$step = $this->step->toArray();
		$stepgroup = $this->stepgroup->toArray();
		
		$context = new Sophie_Context();
		$context->setPreviewMode(true);
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
			throw new Exception('Steptype can not be initialized', null, $e);
			return;
		}

		$steptype->setController($this);
		$steptype->setView($this->view);
		$steptype->setContext($context);
		$steptype->setFrontUrl('/expdesigner/step/preview/stepId/' . $this->step->id);
		$context->setSteptype($steptype);

		//////////////////////////////////////////////////////////////////////////
		// render step
		//////////////////////////////////////////////////////////////////////////

		$this->_helper->layout->setLayout('frontend');
		$this->_helper->viewRenderer->setNoRender(true);
		$steptype->preRender();

		// init error handler
		Sophie_Eval_Error_Handler :: $context = $context;
		Sophie_Eval_Error_Handler :: $script = 'Preview';
		Sophie_Eval_Error_Handler :: $logToSession = false;
		set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));

		try
		{
			$renderResult = $steptype->renderComposite();
		}
		catch (Exception $e)
		{
			$this->_forward('previewerror');
			return;
		}

		// restore error handler
		restore_error_handler();

		$this->getResponse()->appendBody($renderResult);
		$steptype->postRender();
		
		/* print_r(Sophie_Api_Log :: getDummyStorage()); */
	}
	
	public function previewerrorAction()
	{
		$this->_helper->layout->setLayout('previewerror');
	}

}