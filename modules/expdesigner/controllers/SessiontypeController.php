<?php
class Expdesigner_SessiontypeController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $typeLabel = null;

	private $experiment = null;
	private $treatment = null;
	private $sessiontype = null;

	public function preDispatch()
	{
		$this->sessiontypeId = $this->_getParam('sessiontypeId', null);
		if (!empty($this->sessiontypeId))
		{
			$this->sessiontype = Sophie_Db_Treatment_Sessiontype :: getInstance()->find($this->sessiontypeId)->current();

			if (is_null($this->sessiontype))
			{
				$this->_error('Selected sessiontypeId does not exist or does not belong to selected treatment!');
				return;
			}

			$this->treatmentId = $this->sessiontype['treatmentId'];
		}
		else
		{
			$this->treatmentId = $this->_getParam('treatmentId', null);
		}

		if (empty ($this->treatmentId))
		{
			$this->_error('Paramater treatmentId missing!');
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
		
		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentSessiontypesTab'
			)
		);

	}

	public function addAction()
	{
		$form = $this->getForm('Sessiontype_Add');
		$form->setAction($this->view->url());

		$nameElement = $form->getElement('name');
		$nameValidator = new Sophie_Validate_Treatment_Sessiontype_Name();
		$nameValidator->treatmentId = $this->treatment->id;
		$nameValidator->setUniqueCheck(true);
		$nameElement->addValidator($nameValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$data = array (
					'treatmentId' => $this->treatment->id,
					'name' => $values['name'],
					'participantMgmt' => $values['participantMgmt'],
					'size' => $values['size'],
					'groupStructureLabel' => 'G'
				);
				$id = Sophie_Db_Treatment_Sessiontype :: getInstance()->insert($data);

				$this->_helper->flashMessenger('New sessiontype added');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'sessiontype',
					'action' => 'edit',
					'treatmentId' => $this->treatment->id,
					'sessiontypeId' => $id
				), 'default', true);
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Add Sessiontype',
			'small' => 'Sessiontype:',
			'name' => 'Add Type'
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
	}

	public function editAction()
	{
		if (is_null($this->sessiontype))
		{
			$this->_error('Missing sessiontypeId parameter');
			return;
		}

		$form = $this->getForm('Sessiontype_Edit');
		$form->setAction($this->view->url());

		$nameElement = $form->getElement('name');
		$nameValidator = new Sophie_Validate_Treatment_Sessiontype_Name();
		$nameValidator->treatmentId = $this->treatment->id;
		$nameValidator->sessiontypeId = $this->sessiontype->id;
		$nameValidator->setUniqueCheck(true);
		$nameElement->addValidator($nameValidator, true);

		$sessiontypeModel = Sophie_Db_Treatment_Sessiontype :: getInstance();
		$sessiontypeDisassembled = $sessiontypeModel->fetchDisassembledRow($this->sessiontype->id);
		$form->setDefaults($sessiontypeDisassembled);

		if ($this->sessiontype->participantMgmt == 'static')
		{
			if (!$sessiontypeModel->checkGroupDefinition($this->sessiontype->id, $sessiontypeDisassembled['groupDefinition']))
			{
				$this->view->groupDefinitionError = $sessiontypeModel->lastGroupDefinitionError;
				$this->view->groupDefinitionCanBeRepaired = $sessiontypeModel->groupDefinitionCanBeRepaired;
			}
		}

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$data = array(
					'name' => $values['name'],
					'participantMgmt' => $values['participantMgmt'],
					'size' => $values['size']
				);
				$where = $sessiontypeModel->getAdapter()->quoteInto('id = ?', $sessiontypeDisassembled['id']);
				$sessiontypeModel->update($data, $where);

				$this->_helper->flashMessenger('Changes in sessiontype saved');
				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment->id
				)) . '#tab_treatmentSessiontypesTab');
				return;
			}
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Edit Sessiontype',
			'small' => 'Sessiontype:',
			'name' => $sessiontypeDisassembled['name']
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->sessiontype = $sessiontypeDisassembled;
	}

	public function groupingAction()
	{
		if (is_null($this->sessiontype))
		{
			$this->_error('Missing sessiontypeId parameter');
			return;
		}

		$sessiontypeModel = Sophie_Db_Treatment_Sessiontype :: getInstance();
		$sessiontypeDisassembled = $sessiontypeModel->fetchDisassembledRow($this->sessiontype->id);

		$repair = $this->_getParam('repair', null);
		if ($repair == 'forcereset')
		{
			if ($sessiontypeModel->resetGroupDefinition($this->sessiontype->id))
			{
				$this->_helper->FlashMessenger('Grouping reset.');
				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'sessiontype',
					'action' => 'grouping',
					'treatmentId' => $this->treatment->id,
					'sessiontypeId' => $this->sessiontype->id
				), 'default', true);
				return;
			}
		}

		if (!$sessiontypeModel->checkGroupDefinition($this->sessiontype->id, $sessiontypeDisassembled['groupDefinition']))
		{
			$this->view->groupDefinitionError = $sessiontypeModel->lastGroupDefinitionError;
			$this->view->groupDefinitionCanBeRepaired = $sessiontypeModel->groupDefinitionCanBeRepaired;

			$repairOk = false;
			if ($repair == 'repair' && $sessiontypeModel->groupDefinitionCanBeRepaired)
			{
				if ($sessiontypeModel->repairGroupDefinition($this->sessiontype->id))
				{
					$this->_helper->FlashMessenger('Grouping repaired.');
					$repairOk = true;
				}
			}
			elseif ($repair == 'reset')
			{
				if ($sessiontypeModel->resetGroupDefinition($this->sessiontype->id))
				{
					$this->_helper->FlashMessenger('Grouping reset.');
					$repairOk = true;
				}
			}
			if ($repairOk)
			{
				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'sessiontype',
					'action' => 'grouping',
					'treatmentId' => $this->treatment->id,
					'sessiontypeId' => $this->sessiontype->id
				), 'default', true);
				return;
			}
		}

		if ($this->getRequest()->isPost())
		{
			$groupDefinitionJson = $this->_getParam('groupDefinitionJson', null);
			if (!is_null($groupDefinitionJson))
			{
				$table = Sophie_Db_Treatment_Sessiontype :: getInstance();
				$data = array(
					'groupDefinitionJson' => $groupDefinitionJson
				);
				$where = $table->getAdapter()->quoteInto('id = ?', $sessiontypeDisassembled['id']);
				$table->update($data, $where);

				$this->view->message = 'Grouping saved.';
			}
			else
			{
				$this->view->message = 'Missing parameter: groupDefinitionJson';
			}
			$this->_helper->json($this->view->getVars());
			return;
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Edit Sessiontype',
			'small' => 'Sessiontype:',
			'name' => $sessiontypeDisassembled['name']
		);

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->sessiontype = $sessiontypeDisassembled;
	}

	public function deleteAction()
	{
		if (is_null($this->sessiontype))
		{
			$this->_error('Missing sessiontypeId parameter');
			return;
		}

		$this->sessiontype->state = 'deleted';
		$this->sessiontype->save();

		$this->_helper->json(array (
			'message' => 'Sessiontype deleted'
		));
	}
}