<?php

class Sfwsysadmin_UsergroupController extends Symbic_Controller_Action
{

	private $userSession;
	private $moduleConfig;
	private $userModel;
	private $module;

	public function init()
	{
		$this->userSession	 = $this->getUserSession();
		$this->moduleConfig	 = $this->getModuleConfig();
		$this->module		 = $this->getModule();
		if (!$this->module->isAllowed('usergroup'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}
		$this->userModel = $this->module->getUserModel();
	}

	/**
	 * Get all users from the db and return it to the view
	 */
	public function indexAction()
	{
		if (!$this->module->isAllowed('usergroup'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->view->usergroups = $this->userModel->fetchAllUsergroupsOrderByColumn();

		$this->view->breadcrumbs = array(
			array(
				'url'	 => $this->view->url(array(
					'controller'	 => 'index',
					'action'	 => 'index')),
				'title'	 => 'Administration',
				'small'	 => 'Home:',
				'name'	 => 'Administration'
			),
			array(
				'title'	 => 'User Groups',
				'small'	 => 'User Groups:',
				'name'	 => 'Overview'
			)
		);
	}

	/**
	 * Add a new user to the db
	 */
	public function addAction()
	{
		if (!$this->module->isAllowed('usergroup'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$form = $this->getForm('Usergroup_Add');

		$userIdsElement = $form->getElement('userIds');
		$userIdsElement->setMultiOptions($this->userModel->getUserSelect());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if ($this->userModel->groupNameExists($values['name']))
				{
					$nameElement = $form->getElement('name');
					$nameElement->addError('Usergroup name already exists');
				}
				else
				{
					if (!isset($values['userIds']) || !is_array($values['userIds']))
					{
						$userIds = array();
					}
					else
					{
						$userIds = $values['userIds'];
					}

					$data		 = array(
						'name' => $values['name']);
					$usergroupId	 = $this->userModel->insertUsergroup($data);

					$this->userModel->setUsergroupUsersById($usergroupId, $userIds);

					$this->_helper->flashMessenger('Usergroup added');

					// TODO: forward to user message to tell users that they have been added to a usergroup
					$this->_helper->getHelper('Redirector')->gotoRoute(array(
						'module'	 => 'sfwsysadmin',
						'controller'	 => 'usergroup',
						'action'	 => 'index'
						), 'default', true);
					return;
				}
			}
		}

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
			array(
				'url'	 => $this->view->url(array(
					'controller'	 => 'index',
					'action'	 => 'index')),
				'title'	 => 'Administration',
				'small'	 => 'Home:',
				'name'	 => 'Administration'
			),
			array(
				'url'	 => $this->view->url(array(
					'controller'	 => 'usergroup',
					'action'	 => 'index')),
				'title'	 => 'User Groups',
				'small'	 => 'User Groups:',
				'name'	 => 'Overview'
			),
			array(
				'title'	 => 'Add Usergroup',
				'small'	 => 'Usergroup:',
				'name'	 => 'Add Usergroup'
			)
		);
	}

	/**
	 * To edit a usergroup
	 */
	public function editAction()
	{
		if (!$this->module->isAllowed('usergroup'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$usergroupId = $this->_getParam('usergroupId', null);
		if (is_null($usergroupId))
		{
			$this->_error('Missing parameter');
			return;
		}

		$usergroup = $this->userModel->fetchUsergroupById($usergroupId);
		if ($usergroup === false)
		{
			$this->_error('Usergroup does not exist');
			return;
		}

		$form = $this->getForm('Usergroup_Edit');
		$form->setDefaults($usergroup);

		$userIdsElement = $form->getElement('userIds');
		$userIdsElement->setMultiOptions($this->userModel->getUserSelect());
		$userIdsElement->setValue(array());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if ($values['name'] != $usergroup['name'] && $this->userModel->groupNameExists($values['name']))
				{
					$nameElement = $form->getElement('name');
					$nameElement->addError('Usergroup name already exists');
				}
				else
				{
					if (!isset($values['userIds']) || !is_array($values['userIds']))
					{
						$userIds = array();
					}
					else
					{
						$userIds = $values['userIds'];
					}

					$data = array(
						'name' => $values['name']);
					$this->userModel->updateUsergroupById($data, $usergroup['id']);

					$this->userModel->setUsergroupUsersById($usergroup['id'], $userIds);

					$this->_helper->flashMessenger('Changes to usergroup saved');

					$this->_helper->getHelper('Redirector')->gotoRoute(array(
						'action' => 'index'
					));
					return;
				}
			}
		}

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
			array(
				'url'	 => $this->view->url(array(
					'controller'	 => 'index',
					'action'	 => 'index')),
				'title'	 => 'Administration',
				'small'	 => 'Home:',
				'name'	 => 'Administration'
			),
			array(
				'url'	 => $this->view->url(array(
					'controller'	 => 'usergroup',
					'action'	 => 'index')),
				'title'	 => 'User Groups',
				'small'	 => 'User Groups:',
				'name'	 => 'Overview'
			),
			array(
				'title'	 => 'Edit Usergroup',
				'small'	 => 'Usergroup:',
				'name'	 => 'Edit Usergroup'
			)
		);
	}

	/**
	 * Action to delete a usergroup
	 */
	public function deleteAction()
	{
		if (!$this->module->isAllowed('usergroup'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$usergroupId = $this->getParam('usergroupId', null);
		if (is_null($usergroupId))
		{
			$this->_error('Missing parameter');
			return;
		}

		// check if user exists
		$usergroup = $this->userModel->fetchUsergroupById($usergroupId);

		if ($usergroup === false)
		{
			$this->_error('Usergroup does not exist');
			return;
		}

		$this->userModel->deleteUsergroupById($usergroup['id']);
		$this->_helper->json(array(
			'message' => 'Usergroup deleted'));
	}

}
