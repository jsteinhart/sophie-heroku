<?php
class Sfwsysadmin_ConfigController extends Symbic_Controller_Action
{
	public function mailAction()
	{
		if (!$this->getModule()->isAllowed('configMail'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'Application Config',
					'small' => 'Application Config:',
					'name' => 'Mail Configuration'
				)
			);
	}

	public function mailtransportAction()
	{
		// TODO: username bug!

		if (!$this->getModule()->isAllowed('configMail'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$mailConfigModel = $this->getModelSingleton('Config_Mail');

		$form = $this->getForm('Config_Mail_Transport');
		$form->setDefaults($mailConfigModel->getTransport());

		$typeElement = $form->getElement('type');
		$hostElement = $form->getElement('host');
		$portElement = $form->getElement('port');
		$sslElement = $form->getElement('ssl');
		$authElement = $form->getElement('auth');
		$usernameElement = $form->getElement('username');
		$passwordElement = $form->getElement('password');
		
		if ($this->getRequest()->isPost())
		{
			$transportType = $_POST['type'];
			if($transportType == 'file' || $transportType == 'sendmail')
			{
				$hostElement->setRequired(false);
				$portElement->setRequired(false);
				$sslElement->setRequired(false);
				$authElement->setRequired(false);
				$usernameElement->setRequired(false);
				$passwordElement->setRequired(false);
			}
			elseif ($transportType == 'smtp')
			{
				$authType = $_POST['auth'];
				if ($authType == '')
				{
					$usernameElement->setRequired(false);
					$passwordElement->setRequired(false);
				}
			}

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				/*$infoElement = $form->getElement('info');

				$failed = false;
				try {
					// TODO: check settings and connection
					//$mailTransport = new Zend_Mail_Transport_Smtp();
					//$mail = new Zend_Mail();
				}
				catch(Exception $e)
				{
					$infoElement->addError('Connection failed: ' . $e->getMessage());
					$failed = true;
				}
				
				if ($failed == false)
				{
					
				}*/
				
				$mailConfigModel->updateTransport($values);
				
				// TODO: clear application config cache?!

				$this->_helper->flashMessenger('Updated application configuration mail transport settings.');
			}
		}

		$hostElement->setRequired(true);
		$portElement->setRequired(true);
		$sslElement->setRequired(true);
		$authElement->setRequired(true);
		$usernameElement->setRequired(true);
		$passwordElement->setRequired(true);

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration',
				),
				array(
					'url' => $this->view->url(array('controller' => 'config', 'action' => 'mail')),
					'title' => 'Application Config',
					'small' => 'Application Config:',
					'name' => 'Mail Configuration',
				),
				array(
					'title' => 'Mail Configuration',
					'small' => 'Mail Configuration:',
					'name' => 'Mail Transport'
				)
			);
	}

	public function maildefaultfromAction()
	{
		if (!$this->getModule()->isAllowed('configMail'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$mailConfigModel = $this->getModelSingleton('Config_Mail');

		$form = $this->getForm('Config_Mail_DefaultFrom');
		$form->setDefaults($mailConfigModel->getDefaultFrom());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$mailConfigModel->updateDefaultFrom($values);
				
				// TODO: clear application config cache?!

				$this->_helper->flashMessenger('Updated application configuration mail Default From settings.');
			}
		}

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration',
				),
				array(
					'url' => $this->view->url(array('controller' => 'config', 'action' => 'mail')),
					'title' => 'Application Config',
					'small' => 'Application Config:',
					'name' => 'Mail Configuration',
				),
				array(
					'title' => 'Mail Configuration',
					'small' => 'Mail Configuration:',
					'name' => 'Default From'
				)
			);
	}

	public function maildefaultreplytoAction()
	{
		if (!$this->getModule()->isAllowed('configMail'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$mailConfigModel = $this->getModelSingleton('Config_Mail');

		$form = $this->getForm('Config_Mail_DefaultReplyTo');
		$form->setDefaults($mailConfigModel->getDefaultReplyTo());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$mailConfigModel->updateDefaultReplyTo($values);
				
				// TODO: clear application config cache?!

				$this->_helper->flashMessenger('Updated application configuration mail default ReplyTo settings.');
			}
		}

		$this->view->form = $form;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration',
				),
				array(
					'url' => $this->view->url(array('controller' => 'config', 'action' => 'mail')),
					'title' => 'Application Config',
					'small' => 'Application Config:',
					'name' => 'Mail Configuration',
				),
				array(
					'title' => 'Mail Configuration',
					'small' => 'Mail Configuration:',
					'name' => 'Default ReplyTo'
				)
			);
	}

	public function mailtestAction()
	{
		if (!$this->getModule()->isAllowed('configMail'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$form = $this->getForm('Config_Mail_Test');

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$mail = new \Zend_Mail('UTF-8');
				$mail->addTo($values['to']);

				$mail->setSubject('** TEST MAIL **');
				$mail->setBodyText('This message was sent on ' . date(DATE_RFC822));

				try
				{
					$mail->send();
					$this->_helper->flashMessenger('Test mail sent to ' . $values['to']);

				}
				catch(\Exception $e)
				{
					$this->_helper->flashMessenger('Sending test mail to ' . $values['to'] . ' failed');
				}
			}
		}
		$this->view->form = $form;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration',
				),
				array(
					'url' => $this->view->url(array('controller' => 'config', 'action' => 'mail')),
					'title' => 'Application Config',
					'small' => 'Application Config:',
					'name' => 'Mail Configuration',
				),
				array(
					'title' => 'Mail Configuration',
					'small' => 'Mail Configuration:',
					'name' => 'Mail Test'
				)
			);
	}
}