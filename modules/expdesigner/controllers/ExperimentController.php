<?php
class Expdesigner_ExperimentController extends Symbic_Controller_Action
{
	private $experimentId;
	private $experiment;
	private $userId;

	public function init()
	{
		$this->userId = Symbic_User_Session::getInstance()->getId();
	}

	public function preDispatch()
	{
		$this->experimentId = $this->_getParam('experimentId', null);
		if (!is_null($this->experimentId))
		{
			// if experimentId given: use it to get experiment
			$this->experiment = Sophie_Db_Experiment :: getInstance()->find($this->experimentId)->current();
			if (is_null($this->experiment))
			{
				$this->_error('Selected experiment does not exist!');
				return;
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
				)
			);

		}
		else
		{
			$this->view->breadcrumbs = array(
				'home' => 'expdesigner'
			);
		}
	}

	public function indexAction()
	{
		$experimentModel = Sophie_Db_Experiment :: getInstance();
		$db = $experimentModel->getAdapter();
		$overviewSelect = $experimentModel->getOverviewSelect();

		// allow admin to see everything
		$adminMode = (boolean)$this->_getParam('adminMode', false);
		$adminRight = Symbic_User_Session::getInstance()->hasRight('admin');

		if ($adminMode && $adminRight)
		{
			$overviewSelect->order(array('experiment.name'));
		}
		else
		{
			System_Acl::getInstance()->addSelectAcl($overviewSelect, 'experiment');
			$overviewSelect->order(array('experiment.name', 'acl.rule'));
		}
		$overviewSelect->group(array('experiment.id'));

		$this->view->experiments = $overviewSelect->query()->fetchAll();
		$this->view->adminMode = $adminMode;
		$this->view->adminRight = $adminRight;
	}

	public function addAction()
	{
		$form = $this->getForm('Experiment_Add');
		$form->setAction($this->view->url());
		$form->setDefaults(array('ownerId'=>$this->userId));

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				$values = $form->getValues();

				if (!isset($values['userAccess']))
				{
					$values['userAccess'] = array();
				}
				$userAccess = $values['userAccess'];
				unset($values['userAccess']);
				if (!is_array($userAccess))
				{
					$userAccess = array();
				}

				if (!isset($values['usergroupAccess']))
				{
					$values['usergroupAccess'] = array();
				}
				$usergroupAccess = $values['usergroupAccess'];
				unset($values['usergroupAccess']);
				if (!is_array($usergroupAccess))
				{
					$usergroupAccess = array();
				}

				$id = Sophie_Db_Experiment :: getInstance()->insert(array (
					'name' => $values['name'],
					'description' => $values['description'],
					'ownerId'=>$values['ownerId']
				));

				//Avoid lock out if logged in user!=owner or not in access list
				if($values['ownerId'] != $this->userId && !in_array($this->userId, $userAccess))
				{
					$userAccess[] = $this->userId;
				}

				$acl = System_Acl::getInstance();
				$acl->setAccessForRoles('experiment', $id, 'user', $userAccess);
				$acl->setAccessForRoles('experiment', $id, 'usergroup', $usergroupAccess);

				$this->_helper->flashMessenger('New experiment added');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'index',
					'experimentId' => $id
				), 'default', true);
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array(
			'title' => 'Add Experiment',
			'small' => 'Experiment:',
			'name' => 'Add'
		);

		$this->view->form = $form;
	}

	public function editAction()
	{
		$form = $this->getForm('Experiment_Edit');
		$form->setAction($this->view->url());

		if (is_null($this->experimentId))
		{
			$this->_error('Missing experimentId parameter');
			return;
		}

		$formData = $this->experiment->toArray();
		$formData['experimentId'] = $this->experiment->id;
		$form->setDefaults($formData);

		$acl = System_Acl::getInstance();

		$userAccess = $acl->getAccessForRoleClass('experiment', $this->experimentId, 'user');
		if ($userAccessElement = $form->getElement('userAccess'))
		{
			$userAccessElement->setValue($userAccess);
		}

		$usergroupAccess = $acl->getAccessForRoleClass('experiment', $this->experimentId, 'usergroup');
		if ($usergroupAccessElement = $form->getElement('usergroupAccess'))
		{
			$usergroupAccessElement->setValue($usergroupAccess);
		}

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				$values = $form->getValues();

				$userAccess = (isset($values['userAccess'])) ? $values['userAccess'] : array($this->userId => $this->userId);
				unset($values['userAccess']);
				$usergroupAccess = (isset($values['usergroupAccess'])) ? $values['usergroupAccess'] : array();
				unset($values['usergroupAccess']);

				$this->experiment->setFromArray($values);
				$this->experiment->save();

				$acl->setAccessForRoles('experiment', $this->experimentId, 'user', $userAccess);
				$acl->setAccessForRoles('experiment', $this->experimentId, 'usergroup', $usergroupAccess);

				$this->_helper->flashMessenger('Changes to experiment saved');
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->form = $form;
	}

	public function importAction()
	{
		$form = $this->getForm('Experiment_Import');
		$form->setAction($this->view->url());

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {

				$values = $form->getValues();

				$id = Sophie_Db_Experiment :: getInstance()->insert(array (
					'name' => $values['name'],
					'ownerId'=>$this->userId
				));
				Sophie_Db_Experiment :: getInstance()->import(
					$id,
					$values['content']
				);

				$this->_helper->flashMessenger('Experiment has been imported');
				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'index',
					'experimentId' => $id
				), 'default', true);
				return;

			} else {
			}
		}

		$this->view->breadcrumbs['__own'] = array(
			'title' => 'Import Experiment',
			'small' => 'Experiment:',
			'name' => 'Import'
		);

		$this->view->form = $form;
	}

	public function deleteAction()
	{
		if (is_null($this->experiment))
		{
			$this->_error('Selected experiment does not exist!');
			return;
		}

		// TODO: super admin can delete all
		// TODO: additional admins can delete too

		if ($this->experiment->ownerId != $this->userId)
		{
			$this->_error('You are not allowed to delete the selected experiment!');
			return;
		}

/*
		$count = sizeof($this->experiment->findDependentRowset('Sophie_Db_Treatment'));
		if ($count > 0)
		{
			$this->_error('Selected experiment has treatments!');
			return;
		}
		$this->experiment->delete(); */
		$this->experiment->state = 'deleted';
		$this->experiment->save();

		$this->_helper->json(array('message' => 'Experiment deleted'));
	}

	public function exportAction()
	{
		if (is_null($this->experiment))
		{
			$this->_error('Missing experimentId parameter');
			return;
		}

		$experimentDefinition = array('header' => array('name'=>$this->experiment->name), 'treatments'=>array());

		$treatments = Sophie_Db_Treatment :: getInstance()->fetchAll('experimentId = ' . $this->experiment->id);
		foreach ($treatments as $treatment)
		{
			$experimentDefinition['treatments'][] = Sophie_Service_Treatment :: getInstance()->toArray($treatment->id);
		}
		$experimentDefinition = Zend_Json::encode($experimentDefinition);

		$this->getResponse()->setHeader('Content-Type', 'text/sophie');
		$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=experiment' . $this->experiment->id . '.sophie');
		$this->getResponse()->appendBody($experimentDefinition);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

}