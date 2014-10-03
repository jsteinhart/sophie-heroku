<?php

/*
 *
 */
class Sfwuserprofile_IndexController extends Symbic_Controller_Action
{
	/*
	 *
	 */

	public function indexAction()
	{
		$userSession = $this->getUserSession();

		if (!$userSession->isLoggedIn())
		{
			$this->_error('You are currently not logged in');
			return;
		}

		$userModel = $this->getModelSingleton('user');

		$user = $userModel->find($userSession->getId())->current();
		if (is_null($user))
		{
			$this->_error('User not found');
			return;
		}

		$basicsForm	 = $this->getForm('Basics');
		$basicsForm->setDefaults($user->toArray());
		$basicsForm->addElement('hidden', 'formName', array(
			'value' => 'basics'));
		$passwordForm	 = $this->getForm('Password');
		$passwordForm->addElement('hidden', 'formName', array(
			'value' => 'password'));

		if ($this->getRequest()->isPost())
		{
			$formName = $this->getParam('formName', '');
			if ($formName == 'password')
			{
				if ($passwordForm->isValid($_POST))
				{
					$values = $passwordForm->getValues();

					if (md5($values['oldPassword']) != $user->password)
					{
						$oldPasswordElement = $passwordForm->getElement('oldPassword');
						$oldPasswordElement->addError('Password is not valid');
					}
					else
					{
						$newPasswordHash = md5($values['newPassword']);
						$userModel->update(array(
							'password' => $newPasswordHash), $userModel->getAdapter()->quoteInto('id = ?', $user->id));
						$this->_helper->flashMessenger('Password saved');
						$this->_helper->getHelper('Redirector')->gotoRoute();
						return;
					}
				}
			}
			elseif ($formName == 'basics')
			{
				if ($basicsForm->isValid($_POST))
				{
					$values = $basicsForm->getValues();

					unset($values['id']);
					unset($values['formName']);

					$userModel->update($values, $userModel->getAdapter()->quoteInto('id = ?', $user->id));

// TODO: if refresh fails, force relogin?
					$userSession->refresh();

					$this->_helper->flashMessenger('Userprofile saved');

					$this->_helper->getHelper('Redirector')->gotoRoute();
					return;
				}
			}
		}

		$this->view->basicsForm		 = $basicsForm;
		$this->view->passwordForm	 = $passwordForm;

		$this->view->breadcrumbs = array(
			array(
				'title'	 => 'User Profile',
				'small'	 => 'User Profile',
				'name'	 => 'Edit'
			)
		);
	}

}
