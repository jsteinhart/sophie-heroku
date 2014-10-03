<?php
namespace Sfwinstaller\Installer\Step;

class Mailconfig extends AbstractStep
{
	public function getForm()
	{
		$form = parent::getForm();

		$form->setLegend('Mail Configuration');

		$info = $form->createElement('StaticHtml', 'info', array('value' => 'The application uses mail to send out information to system users. SMTP configuration is recommended. Please configure the mailserver according to your setup.<br /><br />'));
		$form->addElement($info);

		$type = $form->createElement('select', 'type', array('onChange' => 'changeVisibility();'));
		$type->setLabel('Type');
		$type->setRequired(true);
		$type->setMultiOptions(array('smtp'=>'SMTP','sendmail' => 'Sendmail (Linux only)',  'file' => 'Debugging Mode: Save mails to file'));
		$type->setValue('smtp');
		$form->addElement($type);
		
		$informations = $form->createElement('StaticHtml', 'informations', array('label'=>''), array());
		$form->addElement($informations);
		
		$host = $form->createElement('text', 'host');
		$host->setLabel('Hostname');
		$host->setRequired(true);
		$host->setValue('localhost');
		$host->addValidator(new \Zend_Validate_Hostname(\Zend_Validate_Hostname::ALLOW_ALL));
		$form->addElement($host);

		$port = $form->createElement('text', 'port');
		$port->setLabel('Port');
		$port->setRequired(true);
		$port->setValue('25');
		$form->addElement($port);

		$ssl = $form->createElement('select', 'ssl');
		$ssl->setLabel('Encryption');
		$ssl->setMultiOptions(array('' => 'none', 'tls' => 'tls', 'ssl' => 'ssl'));
		$ssl->setValue('');
		$form->addElement($ssl);
		
		$auth = $form->createElement('select', 'auth');
		$auth->setLabel('Authentication');
		$auth->setMultiOptions(array('' => 'none', 'plain' => 'plain', 'login' => 'login', 'cram-md5' => 'cram-md5'));
		$auth->setValue('');
		$form->addElement($auth);
		
		$username = $form->createElement('text', 'username');
		$username->setLabel('Username');
		//$username->setRequired(true);
		$form->addElement($username);

		$password = $form->createElement('password', 'password');
		$password->setLabel('Password');
		//$password->setRequired(true);
		$form->addElement($password);

		$defaultEmail = $form->createElement('text', 'defaultEmail');
		$defaultEmail->setLabel('System E-Mail');
		$defaultEmail->setRequired(true);
		$defaultEmail->addValidator(new \Zend_Validate_EmailAddress());
		$form->addElement($defaultEmail);

		
		$form->addElement('submit', 'submit', array('label'=>'Next', 'class'=>'btn btn-primary pull-right'));

		return $form;
	}
	
	public function processValid($form)
	{
		$values = $form->getValues();

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

		if (!$failed)
		{
			$this->setValues($values);
			return true;
		}

		return false;
	}

	public function processForm($form)
	{
		if($this->getRequest()->getParam('type')=='file' || $this->getRequest()->getParam('type')=='sendmail')
		{
			$form->getElement('host')->clearValidators();
			$form->getElement('defaultEmail')->clearValidators();
			$form->getElement('host')->isRequired(false);
			$form->getElement('username')->setRequired(false);
			$form->getElement('password')->setRequired(false);
			$form->getElement('defaultEmail')->setRequired(false);
		}

		if ($form->isValid($_POST))
		{
			if ($this->processValid($form) === true)
			{
				return true;
			}
		}
		return false;
	}

	public function render($form)
	{
		// TODO: add javascript to make form more intuitive
		// $form->getView()->jsOnLoad('...');
		
		echo $form->render();
	}
}