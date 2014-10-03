<?php
class Sfwsysadmin_UserController extends Symbic_Controller_Action
{
	private $userSession;
	private $moduleConfig;
	private $userModel;
	private $module;

	public function init()
	{
		$this->userSession = $this->getUserSession();
		$this->moduleConfig = $this->getModuleConfig();
		$this->module = $this->getModule();
		if (!$this->module->isAllowed('user'))
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
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$result = $this->userModel->fetchAllUsersOrderByColumn('name');
		$this->view->result = $result;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array (
					'title' => 'Users',
					'small' => 'Users:',
					'name' => 'Overview'
				)
			);
	}

	/**
	 * Add a new user to the db
	 */
	public function addAction()
	{
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$form = $this->getForm('User_Add');

		// TODO: implement a default selection for roles and groups
		// $form->setDefaults(array('role'=>'user', 'groups' => array(...)));

		$roleElement = $form->getElement('role');
		$roleElement->setMultiOptions($this->userModel->getRoleSelect());

		$usergroupSelect = $this->userModel->getUsergroupSelect();
		if (sizeof($usergroupSelect) > 0)
		{
			$usergroupsElement = $form->getElement('usergroups');
			$usergroupsElement->setMultiOptions($usergroupSelect);
		}
		else
		{
			$form->removeElement('usergroups');
		}

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if ($this->userModel->userLoginExists($values['login']))
				{
					$loginElement = $form->getElement('login');
					$loginElement->addError('Login already exists');
				}
				elseif($values['sendMessage'] == '1' && $values['email'] == '')
				{
					$sendMessageElement = $form->getElement('sendMessage');
					$sendMessageElement->addError('Email address required');
				}
				else
				{
					// TODO: catch exception for duplicate key etc.
					try {
						$userId = $this->userModel->insertUser(
							array(
								'name' => $values['name'],
								'login' => $values['login'],
								'password' => $values['password'],
								'email' => $values['email'],
								'role' => $values['role'],
								'active' => $values['active'],
							)
						);
					}
					catch (Exception $e)
					{
						throw new Exception('Adding user failed', null, $e);
					}

					if(!isset($values['usergroups']) || !is_array($values['usergroups']))
					{
						$values['usergroups'] = array();
					}
					$this->userModel->setUserUsergroupsById($userId, $values['usergroups']);

					$this->_helper->flashMessenger('User added');

					// Send email to new user
					if ($values['sendMessage'] == '1')
					{
						// get new user email text and forward to
						$message = $this->userModel->getNewUserMessageById($userId, $values['password'], $this);
						$forwardToUserMessageParams = array(
								'forwardToUserMessage' => true,
								'messageSubject' => $message['subject'],
								'messageBodyText' => $message['bodyText'],
								'messageRecipientToUserIds' => array($userId)
							);

						$this->_forward('message', null, null, $forwardToUserMessageParams);
						return;
					}

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
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array (
					'url' => $this->view->url(array('controller' => 'user', 'action' => 'index')),
					'title' => 'Users',
					'small' => 'Users:',
					'name' => 'Overview'
				),
				array (
					'title' => 'Add User',
					'small' => 'User:',
					'name' => 'Add User'
				)
			);
	}

	public function editAction()
	{
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$userId = $this->_getParam('userId', null);
		if (is_null($userId))
		{
			$this->_error('Missing parameter');
			return;
		}

		// check if user exists
		$user = $this->userModel->fetchUserById($userId);
		if ($user === false)
		{
			$this->_error('User does not exist');
			return;
		}

		$form = $this->getForm('User_Edit');

		$formData = $user;
		unset($formData['password']);
		$form->setDefaults($formData);

		$roleElement = $form->getElement('role');
		$roleElement->setMultiOptions($this->userModel->getRoleSelect());

		$usergroupSelect = $this->userModel->getUsergroupSelect();
		if (sizeof($usergroupSelect) > 0)
		{
			$usergroupsElement = $form->getElement('usergroups');
			$usergroupsElement->setMultiOptions($usergroupSelect);
			$usergroupsElement->setValue($this->userModel->getUserUsergroupUsergroupIdByUserId($userId));
		}
		else
		{
			$form->removeElement('usergroups');
		}

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if ($user['login'] != $values['login'] && $this->userModel->userLoginExists($values['login']))
				{
					$loginElement = $form->getElement('login');
					$loginElement->addError('Cannot change login to ' . $values['login'] . ' because it already exists.');
					$loginElement->setValue($user['login']);
				}
				else
				{
					$data = array(
							'name' => $values['name'],
							'login' => $values['login'],
							'email' => $values['email'],
							'role' => $values['role'],
							'active' => $values['active'],
						);

					if ($values['password'] != '')
					{
						$data['password'] = $values['password'];
					}

					$this->userModel->updateUserById($data, $userId);

					if(!isset($values['usergroups']) || !is_array($values['usergroups']))
					{
						$values['usergroups'] = array();
					}
					$this->userModel->setUserUsergroupsById($userId, $values['usergroups']);

					$this->_helper->flashMessenger('User saved');

					// Send email to new user
					if ($values['sendMessage'] == '1')
					{
						// get new user email text and forward to
						if (empty($values['password']))
						{
							$values['password'] = '';
						}
						$message = $this->userModel->getUserUpdateMessageById($userId, $values['password'], $this);
						$forwardToUserMessageParams = array(
								'forwardToUserMessage' => true,
								'messageSubject' => $message['subject'],
								'messageBodyText' => $message['bodyText'],
								'messageRecipientToUserIds' => array($user['id'])
							);

						$this->_forward('message', null, null, $forwardToUserMessageParams);
						return;
					}

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
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array (
					'url' => $this->view->url(array('controller' => 'user', 'action' => 'index')),
					'title' => 'Users',
					'small' => 'Users:',
					'name' => 'Overview'
				),
				array (
					'title' => 'Edit User',
					'small' => 'User:',
					'name' => 'Edit User'
				)
			);
	}

	/**
	 * Action to delete a user
	 */
	public function deleteAction()
	{
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$userId = $this->getParam('userId', null);
		if (is_null($userId))
		{
			$this->_error('Missing parameter');
			return;
		}

		if ($this->userSession->getId() == $userId)
		{
			$this->_error('You can not delete your own account');
			return;
		}

		// check if user exists
		$user = $this->userModel->fetchUserById($userId);

		if ($user === false)
		{
			$this->_error('User does not exist');
			return;
		}

		$this->userModel->deleteUserById($user['id']);

		$this->_helper->json(array('message' => 'User ' . $user['login'] . ' deleted'));
	}

	/**
	 * Activate/Deactivate a user
	 */
	public function setactiveAction()
	{
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$userId = $this->getParam('userId', null);
		if (is_null($userId))
		{
			$this->_error('Missing parameter');
			return;
		}

		if ($this->userSession->getId() == $userId)
		{
			$this->_error('You can not change your own account state');
			return;
		}

		// check if user exists
		$user = $this->userModel->fetchUserById($userId);

		if ($user === false)
		{
			$this->_error('User does not exist');
			return;
		}

		$active = $this->getParam('active', 1);
		if ($active != 0)
		{
			$this->userModel->activateUserById($userId);
			$this->_helper->json(array('message' => 'User ' . $user['login'] . ' activated'));
		}
		else
		{
			$this->userModel->deactivateUserById($userId);
			$this->_helper->json(array('message' => 'User ' . $user['login'] . ' deactivated'));
		}
	}

	public function messageAction()
	{
		if (!$this->module->isAllowed('user'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$moduleConfig = $this->getModuleConfig();
		if (isset($moduleConfig['userMessage']) && is_array($moduleConfig['userMessage']))
		{
			$userMessageConfig = $moduleConfig['userMessage'];
		}
		else
		{
			$userMessageConfig = array();
		}

		$form = $this->getForm('User_Message');
		$form->setAction($this->view->url(array('action'=>'message', 'controller' => 'user', 'module' => 'sfwsysadmin')));

		$userOptions = array ();
		$users = $this->userModel->fetchAllUsersOrderByColumn('name');
		foreach ($users as $user)
		{
			if (!empty($user['email']))
			{
				if (isset($userMessageConfig['showInactiveUserRecipients']) && $userMessageConfig['showInactiveUserRecipients'] === '0' && $user['active'] === '0')
				{
					continue;
				}

				$desc = $user['email'];
				if (!empty($user['name']))
				{
					$desc = $user['name'] . ' <' . $desc . '>';
				}

				if ($user['active'] === '0')
				{
					$desc .= ' (inactive)';
				}

				$userOptions[$user['id']] = $desc;
			}
		}
		$recipientToUserIdsElement = $form->getElement('recipientToUserIds');
		$recipientToUserIdsElement->setMultiOptions($userOptions);

		$forwardToUserMessage = $this->getParam('forwardToUserMessage', false);

		$formDefaults = array();
		$formDefaults['recipientToUserIds'] = array();
		$formDefaults['subject'] = '';
		$formDefaults['bodyText'] = '';
		$formDefaults['bodyTextFooter'] = '';

		if ($forwardToUserMessage === true)
		{
			// inject message subject and bodyText
			$messageSubject = $this->getParam('messageSubject', null);
			if (!empty($messageSubject))
			{
				$formDefaults['subject'] = $messageSubject;
			}

			$messageBodyText = $this->getParam('messageBodyText', null);
			if (!empty($messageBodyText))
			{
				$formDefaults['bodyText'] = $messageBodyText;
			}

			// inject recipientToUserIds
			$messageRecipientToUserIds = $this->getParam('messageRecipientToUserIds', array());

			$formDefaults['recipientToUserIds'] = array_merge($formDefaults['recipientToUserIds'], $messageRecipientToUserIds);
		}

		if (!empty($userMessageConfig['subjectDefault']) && empty($formDefaults['subject']))
		{
			$formDefaults['subject'] = $userMessageConfig['subjectDefault'];
		}

		if (!empty($userMessageConfig['subjectPrefix']))
		{
			$formDefaults['subject'] = $userMessageConfig['subjectPrefix'] . $formDefaults['subject'];
		}

		if (!empty($userMessageConfig['bodyTextFooterDefault']) && empty($formDefaults['bodyTextFooter']))
		{
			$formDefaults['bodyTextFooter'] = $userMessageConfig['bodyTextFooterDefault'];
		}

		if (isset($userMessageConfig['defaultCopyToSenderUser']) && $userMessageConfig['defaultCopyToSenderUser'] === '1')
		{
			$formDefaults['recipientToUserIds'][] = $this->userSession->getId();
		}

		if (!empty($userMessageConfig['defaultSenderName']))
		{
			$formDefaults['senderName'] = $userMessageConfig['defaultSenderName'];
		}

		if (!empty($userMessageConfig['defaultSenderEmail']))
		{
			$formDefaults['senderEmail'] = $userMessageConfig['defaultSenderEmail'];
		}

		$form->setDefaults($formDefaults);

		if ($this->getRequest()->isPost() && !$forwardToUserMessage)
		{
			if ($form->isValid($_REQUEST))
			{
				$values = $form->getValues();

				$results = array();
				$errors = array();

				$userMessageModel = $this->getModel('User_Message');

				foreach ($values['recipientToUserIds'] as $userId)
				{
					$user = $this->userModel->fetchUserById($userId);
					if ($user === false)
					{
						$errors[] = 'User with id ' . $userId . ' not found';
						continue;
					}

					if ($user['email'] == '')
					{
						$errors[] = 'User ' . $user['name'] . ' has no email';
						continue;
					}

					$userLabel = $user['email'];
					if (!empty($user['name']))
					{
						$userLabel = $user['name'] . ' <' . $userLabel . '>';;
					}

					try
					{
						$messageReplacements = array(
							'systemLoginUrl' => $this->view->baseUrl() . $this->view->url(array ('login' => $user['login']), 'login'),
							'userName' => $user['name'],
							'userLogin' => $user['login'],
							'userEmail' => $user['email'],
						);

						$config = Zend_Registry::get('config');
						if (isset($config['systemConfig']['admin']) && !empty($config['systemConfig']['admin']['name']))
						{
							$messageReplacements['systemAdminName'] = $config['systemConfig']['admin']['name'];
						}

						if (isset($config['systemConfig']['admin']) && !empty($config['systemConfig']['admin']['email']))
						{
							$messageReplacements['systemAdminEmail'] = $config['systemConfig']['admin']['email'];
						}

						$userMessageModel->sendMessageToUser($values['senderName'], $values['senderEmail'], $user, $values['subject'], $values['bodyText'], $values['bodyTextFooter'], $messageReplacements);

						$results[] = 'Message sent to user ' . $userLabel;
					}
					catch(Exception $e)
					{
						$errors[] = 'Sending message to user ' . $userLabel . ' failed';
					}
				}

				$this->view->results = $results;
				$this->view->errors = $errors;

				$this->view->breadcrumbs = array(
					array(
						'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
						'title' => 'Administration',
						'small' => 'Home:',
						'name' => 'Administration'
					),
					array (
						'url' => $this->view->url(array('controller' => 'user', 'action' => 'index')),
						'title' => 'Users',
						'small' => 'Users:',
						'name' => 'Overview'
					),
					array (
						'title' => 'Send message',
						'small' => 'Send message:',
						'name' => 'Message sent'
					)
				);

				$this->render('message-sent');
				return;
			}
		}

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array (
					'url' => $this->view->url(array('controller' => 'user', 'action' => 'index')),
					'title' => 'Users',
					'small' => 'Users:',
					'name' => 'Overview'
				),
				array (
					'title' => 'Send message',
					'small' => 'Send message:',
					'name' => 'New message'
				)
			);
	}
}