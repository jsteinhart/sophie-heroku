<?php
namespace Sfwsysadmin\Form\Config\Mail;

class Transport extends \Symbic_Form
{
	public function init()
	{
		$this->setLegend('Mail Transport Configuration');

		$info = $this->createElement('StaticHtml', 'info');
		$info->setValue('The application uses mail to send out information to system users. SMTP configuration is recommended. Please configure the mailserver according to your setup.<br /><br />');
		$this->addElement($info);

		$type = $this->createElement('select', 'type', array('onChange' => 'sfwsysadmin.configMailChangeType();'));
		$type->setLabel('Type');
		$type->setRequired(true);
		$type->setMultiOptions(array('smtp'=>'SMTP','sendmail' => 'Sendmail (Linux only)',  'file' => 'Debugging Mode: Save mails to file'));
		$type->setValue('smtp');
		$this->addElement($type);
		
		$informations = $this->createElement('StaticHtml', 'informations', array('label'=>''), array());
		$this->addElement($informations);
		$host = $this->createElement('text', 'host');

		$host->setLabel('Hostname');
		$host->setRequired(true);
		$host->setValue('localhost');
		$host->addValidator(new \Zend_Validate_Hostname(\Zend_Validate_Hostname::ALLOW_ALL));
		$this->addElement($host);

		$port = $this->createElement('text', 'port');
		$port->setLabel('Port');
		$port->setRequired(true);
		$port->setValue('25');
		$this->addElement($port);

		$ssl = $this->createElement('select', 'ssl');
		$ssl->setLabel('Encryption');
		$ssl->setMultiOptions(array('none' => 'none', 'tls' => 'tls', 'ssl' => 'ssl'));
		$ssl->setAllowEmpty(true);
		$ssl->setValue('');
		$this->addElement($ssl);
		
		$auth = $this->createElement('select', 'auth', array('onChange' => 'sfwsysadmin.configMailChangeType();'));
		$auth->setLabel('Authentication');
		$auth->setMultiOptions(array('' => 'none', 'plain' => 'plain', 'login' => 'login', 'cram-md5' => 'cram-md5'));
		$auth->setAllowEmpty(true);
		$auth->setValue('');
		$this->addElement($auth);
		
		$username = $this->createElement('text', 'username');
		$username->setLabel('Username');
		$username->setRequired(true);
		$this->addElement($username);

		$password = $this->createElement('password', 'password', array('renderPassword' => true));
		$password->setLabel('Password');
		$password->setRequired(true);
		$this->addElement($password);
		
		$this->addElement('SubmitInput', 'submit', array('ignore' => true, 'label' => 'Submit', 'class'=>'btn btn-primary pull-right'));
	}
}