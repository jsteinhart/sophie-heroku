<?php
namespace Sfwsysadmin\Form\Config\Mail;

class DefaultFrom extends \Symbic_Form
{
	public function init()
	{
		$this->setLegend('Default From Mail Configuration');

		$defaultFromName = $this->createElement('text', 'name');
		$defaultFromName->setLabel('Name');
		$defaultFromName->setRequired(true);
		$this->addElement($defaultFromName);

		$defaultFromEmail = $this->createElement('text', 'email');
		$defaultFromEmail->setLabel('Email');
		$defaultFromEmail->setRequired(true);
		$defaultFromEmail->addValidator(new \Zend_Validate_EmailAddress());
		$this->addElement($defaultFromEmail);
		
		$this->addElement('SubmitInput', 'submit', array('ignore' => true, 'label' => 'Submit', 'class'=>'btn btn-primary pull-right'));
	}
}