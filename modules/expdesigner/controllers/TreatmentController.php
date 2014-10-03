<?php
class Expdesigner_TreatmentController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;

	private $experiment = null;
	private $treatment = null;

	private $maxSupportedFormatVersion = '1.0.8';
	private $minSupportedFormatVersion = '1.0.0';

	public function preDispatch()
	{
		$this->treatmentId = $this->_getParam('treatmentId', null);
		if (!is_null($this->treatmentId))
		{
			// if treatmentId given: use it to get treatment and experiment
			$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
			if (is_null($this->treatment))
			{
				$this->_error('Selected treatment does not exist!');
				return;
			}
			$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
			$this->experimentId = $this->experiment->id;

		}
		else
		{
			// otherwise get experimentId and experiment
			$this->experimentId = $this->_getParam('experimentId', null);
			$this->experiment = Sophie_Db_Experiment :: getInstance()->find($this->experimentId)->current();
			if (is_null($this->experiment))
			{
				$this->_error('Selected experiment does not exist!');
				return;
			}
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied');
			return;
		}

		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),


		);
		if ($this->treatment)
		{
			$this->view->breadcrumbs['treatment'] = array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name
			);
		}
	}

	public function indexAction()
	{
		$experimentId = $this->_getParam('experimentId', 0);
		if (is_null($experimentId) || $experimentId == 0)
		{
			$this->_error('Missing experimentId parameter');
			return;
		}

		$this->experiment = Sophie_Db_Experiment :: getInstance()->find($experimentId)->current();
		if (is_null($this->experiment))
		{
			$this->_error('Selected experiment does not exist!');
			return;
		}
		$this->view->experiment = $this->experiment->toArray();

		$this->view->treatments = Sophie_Db_Treatment :: getInstance()->fetchAllJoinCountings($this->experiment->id);
	}

	public function addAction()
	{
		$experimentId = $this->_getParam('experimentId', 0);
		if (is_null($experimentId) || $experimentId == 0)
		{
			$this->_error('Missing experimentId parameter');
			return;
		}

		$this->experiment = Sophie_Db_Experiment :: getInstance()->find($experimentId)->current();
		if (is_null($this->experiment))
		{
			$this->_error('Selected experiment does not exist!');
			return;
		}

		$form = $this->getForm('Treatment_Add');
		$form->setAction($this->view->url());

		if ($this->getRequest()->isPost())
		{

			if ($form->isValid($_POST))
			{
				$config = Zend_Registry::get('config');

				$values = $form->getValues();

				$data = array (
					'experimentId' => $this->experiment->id,
					'name' => $values['name'],
					'payoffScript' => '$payoff = 0;' . "\n" . '$moneyPayoff = $payoff;',
					'secondaryPayoffScript' => '$payoff = 0;' . "\n" . '$moneyPayoff = $payoff;',
					'layoutTheme' => $config['systemConfig']['sophie']['expfront']['defaultLayoutTheme'],
				);
				$id = Sophie_Db_Treatment :: getInstance()->insert($data);

				Sophie_Db_Treatment_Stepgroup :: getInstance()->insert(array (
					'treatmentId' => $id,
					'label' => 'main',
					'name' => 'Main',
					'position' => 1,
					'loop' => 1,
					'active' => 1
				));
				Sophie_Db_Treatment_Type :: getInstance()->insert(array (
					'treatmentId' => $id,
					'label' => 'P',
					'name' => 'Default Player'
				));
				Sophie_Db_Treatment_Group_Structure :: getInstance()->insert(array (
					'treatmentId' => $id,
					'label' => 'G',
					'name' => 'Default Group Structure',
					'structure' => array('P'=>array('min'=>1, 'max'=>1))
				));

				$newTreatment = Sophie_Db_Treatment :: getInstance()->find($id)->current();
				Sophie_Db_Treatment_Log :: getInstance()->log($id, 'Treatment created: ' . print_r($newTreatment->toArray(), true));

				$this->_helper->flashMessenger('New treatment added');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $id
				), 'default', true);
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Add Treatment',
			'small' => 'Treatment:',
			'name' => 'Add'
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
	}

	public function editAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$eventManager = Zend_Registry :: get('Zend_EventManager');
		$treatmentEav = Sophie_Db_Treatment_Eav :: getInstance();

		$formValues = $this->treatment->toArray();
		$screens = Sophie_Db_Treatment_Screen :: getInstance()->find($treatmentId)->current();
		if (!is_null($screens))
		{
			$formValues['useCustomThemes'] = true;
			$formValues = array_merge($formValues, $screens->toArray());
		}
		$eav = $treatmentEav->getAll($treatmentId);
		if (count($eav))
		{
			$formValues = array_merge($formValues, $eav);
		}

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');

		$form = $this->getForm('Treatment_Edit');
		$eventManager->trigger('sophie_expdesigner_treatment_edit_form', null, array('form' => $form));
		$form->setAction($this->view->url());
		$form->setDefaults($formValues);

		$layoutThemeElement = $form->getSubForm('layoutTab')->getElement('layoutTheme');
		$layoutThemeOptions = array (
			'' => 'System Default',
		);
		$themePath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes';
		foreach (scandir($themePath) as $theme)
		{
			if ($theme == '.' || $theme == '..')
			{
				continue;
			}
			$layoutThemeOptions[$theme] = $theme;
		}
		$layoutThemeElement->setMultiOptions($layoutThemeOptions);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = array();
				foreach ($form->getSubForms() as $subForm)
				{
					$values = array_merge($values, $subForm->getValues());
				}

				// $treatmentValues will contain only those values which are saved in sophie_treatment.
				// Other values will be saved in sophie_treatment_eav.
				$treatmentValues = array_intersect_key($values, array(
					'id' => true,
					'experimentId' => true,
					'name' => true,
					'description' => true,
					'state' => true,
					'layoutTheme' => true,
					'layoutDesign' => true,
					'css' => true,
					'payoffScript' => true,
					'payoffRetrivalMethod' => true,
					'secondaryPayoffScript' => true,
					'secondaryPayoffRetrivalMethod' => true,
					'defaultLocale' => true,
					'loggingEnabled' => true,
					'setupScript' => true,
				));

				$oldValues = $this->treatment->toArray();
				$this->treatment->setFromArray($treatmentValues);
				$this->treatment->save();

				$customThemeValues = array_intersect_key($values, array(
					'createdHtml' => true,
					'finishedHtml' => true,
					'pausedHtml' => true,
					'archivedHtml' => true,
					'excludedHtml' => true
				));

				$treatmentScreen = Sophie_Db_Treatment_Screen :: getInstance();
				if (!empty($values['useCustomThemes']))
				{
					$customThemeValues['treatmentId'] = $treatmentId;
					$treatmentScreen->replace($customThemeValues);
				}
				else
				{
					$treatmentScreen->delete($treatmentScreen->getAdapter()->quoteInto('treatmentId = ?', $treatmentId));
				}

				// Save other values in sophie_treatment_eav:
				/*
				foreach ($values as $name => $value)
				{
					if (isset($treatmentValues[$name]) || isset($customThemeValues[$name]) || $name === 'useCustomThemes' || $name === 'submit')
					{
						// Value already saved in sophie_treatment!
						continue;
					}
					$treatmentEav->replace(array(
						'treatmentId' => $treatmentId,
						'name' => $name,
						'value' => $value,
					));
				}
				*/

				$eventManager->trigger('sophie_expdesigner_treatment_edit_save', null, array('treatmentId' => $treatmentId, 'values' => $values));

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Changes to treatment saved: ' . print_r($oldValues, true) . ' => ' . print_r($this->treatment->toArray(), true));

				$this->_helper->flashMessenger('Saved changes to treatment');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment->id
				), 'default', true);
				return;
			}
		}

		$payoffForm = $form->getSubForm('payoffTab');

		$payoffScriptElement = $payoffForm->getElement('payoffScript');
		$payoffScriptElement->setAttrib('onchange','expdesigner.updateTreatmentPayoffScriptSanitizerResults(' . $this->treatment->id . ', ' . $payoffScriptElement->getJsInstance() . '.getValue(), \'payoffScriptSanitizerMessages\', \'php\')');

		$secondaryPayoffScriptElement = $payoffForm->getElement('secondaryPayoffScript');
		$secondaryPayoffScriptElement->setAttrib('onchange','expdesigner.updateTreatmentPayoffScriptSanitizerResults(' . $this->treatment->id . ', ' . $secondaryPayoffScriptElement->getJsInstance() . '.getValue(), \'secondaryPayoffScriptSanitizerMessages\', \'php\')');

		$setupForm = $form->getSubForm('setupTab');

		$setupScriptElement = $setupForm->getElement('setupScript');
		$setupScriptElement->setAttrib('onchange','expdesigner.updateTreatmentSetupScriptSanitizerResults(' . $this->treatment->id . ', ' . $setupScriptElement->getJsInstance() . '.getValue(), \'setupScriptSanitizerMessages\', \'php\')');

		$this->view->treatment = $this->treatment->toArray();
		$this->view->experiment = $this->experiment->toArray();
		$this->view->form = $form;
	}

	public function detailsAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}
		$this->view->treatment = $this->treatment->toArray();

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->view->experiment = $this->experiment->toArray();

		// Check if there is a new or running session:
		$sessionSelect = Sophie_Db_Session::getInstance()->select();
		$sessionSelect->where('treatmentId = ?', $this->treatmentId);
		$sessionSelect->where('state IN (?)', array('new', 'running'));
		$sessionSelectResult = $sessionSelect->query();
		$this->view->hasRunningSessions = (sizeof($sessionSelectResult->fetchAll()) > 0);

		// Check if all used steptypes are installed
		$db = Zend_registry::get('db');
		$steptypeSelect = $db->select()->distinct();
		$steptypeSelect->from(array('step'=>Sophie_Db_Treatment_Step::getInstance()->_name), array('steptypeSystemName'));
		$steptypeSelect->joinLeft(array('steptype'=>Sophie_Db_Steptype::getInstance()->_name), 'step.steptypeSystemName = steptype.systemName', array('name'));
		$steptypeSelect->joinLeft(array('stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name), 'step.stepgroupId = stepgroup.id', array());
		$steptypeSelect->where('(steptype.name IS NULL OR steptype.isAbstract = 1 OR steptype.isInstalled = 0 OR steptype.isActive = 0 OR steptype.isBroken = 1)');
		$steptypeSelect->where('stepgroup.treatmentId = ?', $this->treatmentId);
		$steptypeSelectResult = $steptypeSelect->query()->fetchAll();
		$this->view->brokenSteptypes = $steptypeSelectResult;
	}

	public function detailsstructureAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}
		$this->view->treatment = $this->treatment->toArray();

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->view->experiment = $this->experiment->toArray();

		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'));
		$this->view->stepgroups = $this->stepgroups->toArray();

		$this->steps = array ();
		$this->view->steps = array ();

		$stepDbModel = Sophie_Db_Treatment_Step :: getInstance();

		foreach ($this->stepgroups as $stepgroup)
		{
			$steps = $stepDbModel->fetchAllForStructureDetailsByStepgroupId($stepgroup->id);
			$this->steps[$stepgroup->id] = $steps;
			$this->view->steps[$stepgroup->id] = $this->steps[$stepgroup->id];
		}

		$this->_helper->layout->disableLayout();
	}

	public function detailssessiontypesAction()
		{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
			{
			$this->_error('Missing parameter');
			return;
			}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}
		$this->view->treatment = $this->treatment->toArray();

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->view->experiment = $this->experiment->toArray();

		$this->view->sessiontypes = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Sessiontype', null, Sophie_Db_Treatment_Sessiontype :: getInstance()->select()->where('state <> "deleted"')->order('name'))->toArray();
		$this->_helper->layout->disableLayout();
	}

	public function modifystructureAction()
	{
		$itemType = $this->_getParam('itemType', null);
		$itemAction = $this->_getParam('itemAction', null);
		$itemId = $this->_getParam('itemId', null);

		if (is_null($this->treatment) || is_null($itemType) || is_null($itemAction) || is_null($itemId))
		{
			$this->_error('Missing parameter');
			return;
		}

		$message = null;

		switch ($itemType)
		{

			case 'stepgroup' :
				$stepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->find($itemId)->current();
				if (is_null($stepgroup))
				{
					$this->_error('Selected stepgroup does not exist!');
					return;
				}
				if ($stepgroup->treatmentId != $this->treatment->id)
				{
					$this->_error('Selected stepgroup does not belong to treatment!');
					return;
				}

				switch ($itemAction)
				{
					case 'moveUp' :
						if ($stepgroup->position == 1)
						{
							$this->_error('Selected stepgroup is already in first position!');
							return;
						}
						Sophie_Db_Treatment_Stepgroup :: getInstance()->moveUp($stepgroup->id);
						$message = 'Moved stepgroup up';
						break;

					case 'moveDown' :
						$maxPosition = sizeof($this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup'));
						if ($stepgroup->position == $maxPosition)
						{
							$this->_error('Selected stepgroup is already at last position!');
							return;
						}
						Sophie_Db_Treatment_Stepgroup :: getInstance()->moveDown($stepgroup->id);
						$message = 'Moved stepgroup down';
						break;

					case 'moveTo' :
						$targetPosition = $this->_getParam('targetPosition', null);
						if (is_null($targetPosition))
						{
							$this->_error('Incomplete parameter list submitted');
							return;
						}

						if (!is_numeric($targetPosition))
						{
							$this->_error('Target position has to be an integer value');
							return;
						}

						$targetPosition = (int)$targetPosition;
						if ($targetPosition < 1)
						{
							$this->_error('Target position has to be at least 1');
							return;
						}

						if (Sophie_Db_Treatment_Stepgroup::getInstance()->moveToPosition($stepgroup->id, $targetPosition))
						{
							$message = 'Stepgroup moved sucessfully';
						}
						else
						{
							$this->_error('Moving stepgroup failed');
							return;
						}
						break;

					case 'setActive' :
						$stepgroup->active = 1;
						$stepgroup->save();
						$message = 'Stepgroup activated';
						break;

					case 'setInactive' :
						$stepgroup->active = 0;
						$stepgroup->save();
						$message = 'Stepgroup deactivated';
						break;

					default :
						$this->_error('Unkown action for item passed');
						break;
				}
				break;

			case 'step' :

				$step = Sophie_Db_Treatment_Step :: getInstance()->find($itemId)->current();
				if (is_null($step))
				{
					$this->_error('Selected step does not exist!');
					return;
				}

				$stepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->find($step->stepgroupId)->current();
				if (is_null($stepgroup))
				{
					$this->_error('Stepgroup for selected step does not exist!');
					return;
				}

				if ($stepgroup->treatmentId != $this->treatment->id)
				{
					$this->_error('Selected stepgroup does not belong to treatment!');
					return;
				}

				switch ($itemAction)
				{
					case 'moveUp' :
						if ($step->position == 1)
						{
							$this->_error('Selected step is already in first position!');
							return;
						}
						Sophie_Db_Treatment_Step :: getInstance()->moveUp($step->id);
						$message = 'Moved step up';
						break;

					case 'moveDown' :
						$maxPosition = sizeof($stepgroup->findDependentRowset('Sophie_Db_Treatment_Step'));
						if ($step->position == $maxPosition)
						{
							$this->_error('Selected step is already at last position!');
							return;
						}
						Sophie_Db_Treatment_Step :: getInstance()->moveDown($step->id);
						$message = 'Moved step down';
						break;

					case 'moveToTop' :
						if ($step->position == 1)
						{
							$this->_error('Selected step is already in first position!');
							return;
						}
						Sophie_Db_Treatment_Step :: getInstance()->moveToPosition($step->id, 1);
						$message = 'Moved step to top';
						break;

					case 'moveToBottom' :
						$maxPosition = sizeof($stepgroup->findDependentRowset('Sophie_Db_Treatment_Step'));
						if ($step->position == $maxPosition)
						{
							$this->_error('Selected step is already at last position!');
							return;
						}
						Sophie_Db_Treatment_Step :: getInstance()->moveToPosition($step->id, $maxPosition);
						$message = 'Moved step to bottom';
						break;

					case 'moveToNextStepgroup' :
						$stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
						if ($stepgroup->position == count($stepgroups))
						{
							$this->_error('Selected step is already in the last stepgroup!');
							return;
						}

						$maxPosition = sizeof($stepgroup->findDependentRowset('Sophie_Db_Treatment_Step'));
						if ($maxPosition != $step->position)
						{
							Sophie_Db_Treatment_Step :: getInstance()->moveToPosition($step->id, $maxPosition);
						}

						$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPosition($this->treatment->id, null, $stepgroup->position + 1);
						$newStepgroupSteps = $newStepgroup->findDependentRowset('Sophie_Db_Treatment_Step');

						$step->stepgroupId = $newStepgroup->id;
						$step->position = count($newStepgroupSteps) + 1;
						$step->save();

						$message = 'Moved Step to next Stepgroup';
						break;

					case 'moveToPreviousStepgroup' :
						if ($stepgroup->position == 1)
						{
							$this->_error('Selected step is already in the first stepgroup!');
							return;
						}

						$maxPosition = sizeof($stepgroup->findDependentRowset('Sophie_Db_Treatment_Step'));
						if ($maxPosition != $step->position)
						{
							Sophie_Db_Treatment_Step :: getInstance()->moveToPosition($step->id, $maxPosition);
						}

						$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->fetchRowByTreatmentPosition($this->treatment->id, null, $stepgroup->position - 1);
						$newStepgroupSteps = $newStepgroup->findDependentRowset('Sophie_Db_Treatment_Step');

						$step->stepgroupId = $newStepgroup->id;
						$step->position = count($newStepgroupSteps) + 1;
						$step->save();

						$message = 'Moved step to previous stepgroup';
						break;

					case 'moveTo' :
						$targetStepgroupId = $this->_getParam('targetStepgroupId', null);
						$targetPosition = $this->_getParam('targetPosition', null);
						if (is_null($targetPosition) || is_null($targetStepgroupId))
						{
							$this->_error('Incomplete parameter list submitted');
							return;
						}

						$targetStepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->find($targetStepgroupId);
						if (is_null($targetStepgroup))
						{
							$this->_error('Target stepgroup does not exist');
							return;
						}

						if ($targetStepgroup->current()->treatmentId != $this->treatment->id)
						{
							$this->_error('Target stepgroup does not belong to treatment!');
							return;
						}

						if (!is_numeric($targetPosition))
						{
							$this->_error('Target position has to be an integer value');
							return;
						}

						$targetPosition = (int)$targetPosition;
						if ($targetPosition < 1)
						{
							$this->_error('Target position has to be at least 1');
							return;
						}

						if (Sophie_Db_Treatment_Step :: getInstance()->moveToPosition($step->id, $targetPosition, $targetStepgroupId))
						{
							$message = 'Step moved sucessfully';
						}
						else
						{
							$this->_error('Moving step failed');
							return;
						}
						break;

					case 'setActive' :
						$step->active = 1;
						$step->save();
						$message = 'Step activated';
						break;

					case 'setInactive' :
						$step->active = 0;
						$step->save();
						$message = 'Step deactivated';
						break;

					default :
						$this->_error('Unknown action for item passed');
						break;
				}
				break;

			default :
				$this->_error('Unknown item type passed');
				break;
		}

		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Changes to treatment structure: ' . $message);

		$response = array (
			'message' => $message
		);
		$this->_helper->json($response);
	}

	public function searchstructureAction()
	{
		$form = $this->getForm('Treatment_Searchstructure');
		$form->setAction($this->view->url() . '#results');

		// get steptypes and attributes used in treatment
		$stepModel = Sophie_Db_Treatment_Step::getInstance();
		$steptypes = $stepModel->fetchUsedSteptypes($this->treatmentId);
		$form->setSteptypes($steptypes);
		$eavModel = Sophie_Db_Treatment_Step_Eav::getInstance();
		$attributes = $eavModel->fetchUsedAttributes($this->treatmentId);
		$form->setAttributes($attributes);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$this->view->query = $values['query'];
				$this->view->results = $eavModel->searchStructure($this->treatmentId, $values['query'], $values['steptypes'], $values['attribs']);
			}
		}

		$this->view->form = $form;
		$this->view->treatment = $this->treatment->toArray();

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Search Structure',
			'small' => 'Structure:',
			'name' => 'Search'
		);
	}

	public function deleteAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$oldValues = $this->treatment->toArray();
		$this->treatment->state = 'deleted';
		$this->treatment->save();

		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Treatment deleted: ' . print_r($oldValues, 1));

		$this->_helper->json(array (
			'message' => 'Treatment deleted'
		));
	}

	public function importAction()
	{
		$experimentId = $this->_getParam('experimentId', 0);
		if (is_null($experimentId) || $experimentId == 0)
		{
			$this->_error('Missing experimentId parameter');
			return;
		}

		$experiment = Sophie_Db_Experiment :: getInstance()->find($experimentId)->current();
		if (is_null($experiment))
		{
			$this->_error('Selected experiment does not exist!');
			return;
		}

		$source = $this->_getParam('source', 'file');
		if ($source != 'file' && $source != 'url')
		{
			$source = 'file';
		}

		$form = $this->getForm('Treatment_Import_' . ucfirst($source));

		if ($source == 'url')
		{
			$treatmentContentUrl = $form->getElement('treatmentContentUrl');
			// TODO: load URLs from configured repositories
			$treatmentContentUrl->setMultiOptions(
				array(
					'' => '',
					'http://www.sophie.uos.de/docs/tutorials/UG.current.sophie' => 'http://www.sophie.uos.de/docs/tutorials/UG.current.sophie',
				));
		}

		if ($this->getRequest()->isPost() && $form->isValid($_POST))
		{
			$values = $form->getValues();

			// handle file source
			if ($source == 'file')
			{
				$contentFileElement = $form->getElement('contentFile');
				if (! $contentFileElement->receive() )
				{
					$this->_error('Receiving file failed');
					return;
				}

				$contentFile = $contentFileElement->getFileName();

				$treatmentDefinition = file_get_contents($contentFile);
				$treatmentDefinition = utf8_encode($treatmentDefinition);

				try {

					$treatmentDefinition = Zend_Json :: decode($treatmentDefinition);

					//Check format
					$adminMode = (boolean)$this->_getParam('adminMode', false);

					$testChecksum = (Symbic_User_Session::getInstance()->hasRight('admin') || !isset($values['noChecksumTest']) || !$values['noChecksumTest']);

					Sophie_Service_CheckImportFile :: check_header_fields($treatmentDefinition, $testChecksum);

					if ($treatmentDefinition['header']['formatVersion'] != '' && version_compare($treatmentDefinition['header']['formatVersion'], $this->maxSupportedFormatVersion, '>'))
					{
						throw new Exception('SoPHIE treatment file version ' . $treatmentDefinition['header']['formatVersion'] . ' is higher than ' . $this->maxSupportedFormatVersion . ' supported by this installation. Please update your system to support newer file version.');
					}

					if ($treatmentDefinition['header']['formatVersion'] != '' && version_compare($treatmentDefinition['header']['formatVersion'], $this->minSupportedFormatVersion, '<'))
					{
						throw new Exception('SoPHIE treatment file version ' . $treatmentDefinition['header']['formatVersion'] . ' is lower than ' . $this->minSupportedFormatVersion . ' supported by this installation. Please update your system to support newer file version.');
					}

					$treatmentId = Sophie_Service_Treatment :: getInstance()->fromArray($this->experiment->id, $treatmentDefinition);

					if ($treatmentId)
					{
						$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
						if ($values['name'] != '')
						{
							$treatment->name = $values['name'];
							$treatment->save();
						}

						//TreatmentLog --> Treatment imported
						$newTreatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
						Sophie_Db_Treatment_Log :: getInstance()->log($treatmentId, 'Treatment imported: ' . print_r($newTreatment->toArray(), true));

						$this->_helper->flashMessenger('Treatment has been imported');

						$this->_helper->getHelper('Redirector')->gotoRoute(array (
							'module' => 'expdesigner',
							'controller' => 'treatment',
							'action' => 'details',
							'treatmentId' => $treatmentId
						), 'default', true);

						return;
					}
					else
					{
						$this->_helper->flashMessenger('Importing treatment has failed');
					}

				}
				catch (Exception $e)
				{
					$this->_helper->flashMessenger('Importing treatment has failed: ' . $e->getMessage());
				}
			}

			// handle file
			else
			{
				// handle url form
				$errorElement = $form->getElement('treatmentContentUrl');

				$uri = Zend_Uri_Http::fromString($values['treatmentContentUrl']);
				if (!$uri->valid())
				{
					$errorElement->addError('Download failed');
				}
				else
				{
					$client = new Zend_Http_Client($uri);
					$response = $client->request();

					if ($response->isError())
					{
						$errorElement->addError('Download failed');
					}
					else
					{

						$treatmentDefinition = $response->getRawBody();
						$treatmentDefinition = utf8_encode($treatmentDefinition);

						$importFailed = false;
						try
						{
							$treatmentDefinition = Zend_Json :: decode($treatmentDefinition);
						}
						catch (Exception $e)
						{
							$this->_helper->flashMessenger('Decoding treatment file has failed: ' . $e->getMessage());
							$importFailed = true;
						}

						if (!$importFailed)
						{
							//Check format
							$adminMode = (boolean)$this->_getParam('adminMode', false);
							$systemSession = new Zend_Session_Namespace('system');

							$testChecksum = (Symbic_User_Session::getInstance()->hasRight('admin') || !isset($values['noChecksumTest']) || !$values['noChecksumTest']);

							try
							{
								Sophie_Service_CheckImportFile :: check_header_fields($treatmentDefinition, $testChecksum);
							}
							catch (Exception $e)
							{
								$this->_helper->flashMessenger('Checking treatment file failed: ' . $e->getMessage());
								$importFailed = true;
							}

							if (!$importFailed)
							{
								if ($treatmentDefinition['header']['formatVersion'] != '' && version_compare($treatmentDefinition['header']['formatVersion'], $this->maxSupportedFormatVersion, '>'))
								{
									$errorElement->addError('SoPHIE treatment file version ' . $treatmentDefinition['header']['formatVersion'] . ' is higher than ' . $this->maxSupportedFormatVersion . ' supported by this installation. Please update your system to support newer file version.');
								}
								elseif ($treatmentDefinition['header']['formatVersion'] != '' && version_compare($treatmentDefinition['header']['formatVersion'], $this->minSupportedFormatVersion, '<'))
								{
									$errorElement->addError('SoPHIE treatment file version ' . $treatmentDefinition['header']['formatVersion'] . ' is lower than ' . $this->minSupportedFormatVersion . ' supported by this installation. Please update your system to support newer file version.');
								}
								else
								{
									try
									{
										$treatmentId = Sophie_Service_Treatment :: getInstance()->fromArray($this->experiment->id, $treatmentDefinition);
									}
									catch (Exception $e)
									{
										$errorElement->addError('Importing treatment has failed: ' . $e->getMessage());
										$importFailed = true;
									}


									if (!$importFailed)
									{
										if (empty($treatmentId))
										{
											$errorElement->addError('Imported treatment cannot be found');
										}
										else
										{
											$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
											if ($values['nameUrl'] != '')
											{
												$treatment->name = $values['nameUrl'];
												$treatment->save();
											}

											//TreatmentLog --> Treatment imported
											$newTreatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
											Sophie_Db_Treatment_Log :: getInstance()->log($treatmentId, 'Treatment imported: ' . print_r($newTreatment->toArray(), true));

											$this->_helper->flashMessenger('Treatment has been imported');

											$this->_helper->getHelper('Redirector')->gotoRoute(array (
												'module' => 'expdesigner',
												'controller' => 'treatment',
												'action' => 'details',
												'treatmentId' => $treatmentId
											), 'default', true);

											return;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$this->view->source = $source;
		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
	}

	public function exportAction()
	{
		$this->view->treatment = $this->treatment->toArray();
		$name = $this->view->treatment['name'];
		$name = preg_replace('/[^-a-z0-9]/i', '-', $name);
		$name = preg_replace('/--+/i', '-', $name);
		$name = trim($name, '-');

		$treatmentDefinition = Sophie_Service_Treatment :: getInstance()->toArray($this->treatmentId);
		$treatmentDefinition = Zend_Json::encode($treatmentDefinition);

		$this->getResponse()->setHeader('Content-Type', 'text/sophie');
		$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=' . $name . '.sophie');
		$this->getResponse()->appendBody($treatmentDefinition);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

	public function copyAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');

		$form = $this->getForm('Treatment_Copy');
		$form->setAction($this->view->url());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{

				$values = $form->getValues();

				$treatmentServiceModel = Sophie_Service_Treatment:: getInstance();
				$treatmentDefinition = $treatmentServiceModel->toArray($this->treatment->id);
				$treatmentId = $treatmentServiceModel->fromArray($this->treatment->experimentId, $treatmentDefinition);

				if ($treatmentId)
				{
					$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
					if (!empty($values['name']))
					{
						$treatment->name = $values['name'];
						$treatment->save();
					}

					$this->_helper->flashMessenger('Treatment copy saved as ' . $treatment->name);

					$this->_helper->getHelper('Redirector')->gotoRoute(array (
						'module' => 'expdesigner',
						'controller' => 'treatment',
						'action' => 'details',
						'treatmentId' => $treatmentId
					), 'default', true);
					return;
				}
				else
				{
					$this->_helper->flashMessenger('Copy treatment failed');
				}
			}
		}

		$this->view->form = $form;
		$this->view->treatment = $this->treatment->toArray();
		$this->view->experiment = $this->experiment->toArray();
	}

	public function previewAction()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if (is_null($treatmentId) || $treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$stepgroupId = $this->_getParam('stepgroupId', null);
		$stepgroupSelect = Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position');
		if (!is_null($stepgroupId))
		{
			$stepgroupSelect->where('id = ?' , $stepgroupId);
		}
		$structureStepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, $stepgroupSelect);

		if (!is_null($stepgroupId))
		{
			if (sizeof($structureStepgroups) != 1)
			{
				$this->_error('Stepgroup not found');
				return;
			}
		}


		$treatmentStructure = array();
		foreach ($structureStepgroups as $structureStepgroup) {
			$structureSteps = $structureStepgroup->findDependentRowset('Sophie_Db_Treatment_Step', null, Sophie_Db_Treatment_Step::getInstance()->select()->order('position'));
			foreach ($structureSteps as $structureStep) {
				$structureStepDescription = $structureStepgroup->position . '.' . $structureStep->position . ' : ' . $structureStep->name;
				$treatmentStructure[$structureStep->id] = $structureStepDescription;
			}
		}

		if (!is_null($stepgroupId))
		{
			$this->view->stepgroup = $structureStepgroups[0]->toArray();
		}

		$this->view->treatment = $this->treatment->toArray();
		$this->view->treatmentStructure = $treatmentStructure;
	}

	public function checkpayoffscriptcodeAction()
	{
		$code = $this->_getParam('code', '');

		$errors = array();
		$notices = array();

		if ($code != '')
		{
			$code = '<?php ' . $code;

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

	public function checksetupscriptcodeAction()
	{
		$code = $this->_getParam('code', '');

		$errors = array();
		$notices = array();

		if ($code != '')
		{
			$code = '<?php ' . $code;

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
}