<?php
class Expdesigner_StepgroupController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $stepgroupId = null;

	private $experiment = null;
	private $treatment = null;
	private $stepgroup = null;

	public function preDispatch()
	{
		$this->stepgroupId = $this->_getParam('stepgroupId', null);
		if ($this->stepgroupId)
		{
			$this->stepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->find($this->stepgroupId)->current();
			if (is_null($this->stepgroup))
			{
				$this->_error('Selected stepgroup does not exist!');
				return;
			}
			$this->treatmentId = $this->stepgroup->treatmentId;
		}
		else
		{
			$this->treatmentId = $this->_getParam('treatmentId', null);
		}

		if (empty($this->stepgroupId) && empty($this->treatmentId))
		{
			$this->_error('Paramater stepgroupId or treatmentId missing!');
			return;
		}

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

		$this->view->breadcrumbs = array(
			'home' => 'expdesigner',
			'experiment' => array(
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array(
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentStructureTab'
			)
		);
		if ($this->stepgroup) {
			$this->view->breadcrumbs['stepgroup'] = array(
				'treatmentId' => $this->treatment->id,
				'id' => $this->stepgroup->id,
				'name' => $this->stepgroup->name
			);
		}
	}

	public function indexAction()
	{
		$this->_forward('edit');
	}

	public function addAction()
	{
		$form = $this->getForm('Stepgroup_Add');
		$form->setAction($this->view->url());
		$form->setDefaults(array (
			'treatmentId' => $this->treatment->id
		));

		$labelElement = $form->getElement('label');
		$stepgroupLabelValidator = new Sophie_Validate_Treatment_Stepgroup_Label();
		$stepgroupLabelValidator->treatmentId = $this->treatment->id;
		$stepgroupLabelValidator->setUniqueCheck(true);
		$labelElement->addValidator($stepgroupLabelValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$id = Sophie_Db_Treatment_Stepgroup :: getInstance()->insertPosition(array (
					'name' => $values['name'],
					'label' => $values['label'],
					'loop' => $values['loop'],
					'grouping' => $values['grouping'],
					'treatmentId' => $this->treatment->id,
				));

				$newStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance()->find($id)->current();
				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Stepgroup added: ' . print_r($newStepgroup->toArray(), true));

				$this->_helper->flashMessenger('New Stepgroup added');

				$url = $this->view->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true) . '#stepgroup' . (int)$id;
				$this->_helper->getHelper('Redirector')->gotoUrl($url);
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Stepgroup',
			'small' => 'Stepgroup:',
			'name' => 'Add Stepgroup'
		);
	}

	public function editAction()
	{
		$stepgroupId = $this->stepgroupId;

		$form = $this->getForm('Stepgroup_Edit');
		$form->setAction($this->view->url());
		$formData = $this->stepgroup->toArray();
		$formData['treatmentId'] = $this->treatment->id;
		$formData['stepgroupId'] = $this->stepgroup->id;
		$form->setDefaults($formData);

		$stepgroupLabelValidator = new Sophie_Validate_Treatment_Stepgroup_Label();
		$stepgroupLabelValidator->treatmentId = $this->treatment->id;
		$stepgroupLabelValidator->stepgroupId = $this->stepgroup->id;
		$stepgroupLabelValidator->setUniqueCheck(true);

		$labelElement = $form->getElement('label');
		$labelElement->addValidator($stepgroupLabelValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$oldValues = $this->stepgroup->toArray();
				$this->stepgroup->setFromArray($values);
				$this->stepgroup->save();

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Saved changes to Stepgroup: ' . print_r($oldValues, true) . ' => ' .  print_r($this->stepgroup->toArray(), true));

				$this->_helper->flashMessenger('Saved changes to Stepgroup');

				$url = $this->view->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true) . '#stepgroup' . (int)$this->stepgroup->id;
				$this->_helper->getHelper('Redirector')->gotoUrl($url);
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment;
		$this->view->stepgroup = $this->stepgroup->toArray();
		$this->view->form = $form;
	}

	public function setAction()
	{
		$name = $this->_getParam('name', null);
		$value = $this->_getParam('value', null);

		if (is_null($name) || is_null($value))
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
					$this->stepgroup->name = $value;
					$this->stepgroup->save();
					$message = 'Change successful';
				}
				break;

			case 'label':
				if (empty($value))
				{
					$error = 'Value cannot be empty';
				}
				// check whether label is unique
				else
				{
					$this->stepgroup->label = $value;
					$this->stepgroup->save();
					$message = 'Change successful';
				}
				break;

			case 'loops':
				if (empty($value))
				{
					$error = 'Value cannot be empty';
				}
				elseif (!is_numeric($value))
				{
					$error = 'Value has to be numeric';
				}
				else
				{
					$value = (int)$value;
					if ($value < 1 && $value != -1)
					{
						$error = 'Value has to be greater than 0 or equal to -1';
					}
					else
					{
						$this->stepgroup->loop = $value;
						$this->stepgroup->save();
						$message = 'Change successful';
					}
				}
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

	public function copyAction()
	{
		if (is_null($this->stepgroup))
		{
			$this->_error('Paramater stepgroupId missing!');
			return;
		}

		$stepgroupModel = Sophie_Db_Treatment_Stepgroup::getInstance();
		$newId = $stepgroupModel->copyById($this->stepgroup->id);

		//Log
		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Copied stepgroup with id ' . $this->stepgroup->id . ' to stepgroup with id ' . $newId);

		$this->_helper->json(array('message' => 'Stepgroup copied'));

	}

	public function deleteAction()
	{
		$stepgroupId = $this->stepgroupId;
		$oldValues = $this->stepgroup->toArray();

		if (Sophie_Db_Treatment_Stepgroup :: getInstance()->deletePosition($this->stepgroup->id))
		{
			Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Stepgroup deleted: ' . print_r($oldValues, true));
			$this->_helper->json(array('message' => 'Stepgroup deleted'));
		}
		else
		{
			$this->_helper->json(array('error' => 'Stepgroup not deleted'));
		}
	}

}