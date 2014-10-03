<?php
namespace Sfwsysadmin\Form\Config\Mail;

class DefaultReplyTo extends \Symbic_Form
{
	public function init()
	{
		$this->setLegend('Default ReplyTo Mail Configuration');

		$defaultReplyToName = $this->createElement('text', 'name');
		$defaultReplyToName->setLabel('Name');
		$defaultReplyToName->setRequired(true);
		$this->addElement($defaultReplyToName);

		$defaultReplyToEmail = $this->createElement('text', 'email');
		$defaultReplyToEmail->setLabel('Email');
		$defaultReplyToEmail->setRequired(true);
		$defaultReplyToEmail->addValidator(new \Zend_Validate_EmailAddress());
		$this->addElement($defaultReplyToEmail);
		
		$this->addElement('SubmitInput', 'submit', array('ignore' => true, 'label' => 'Submit', 'class'=>'btn btn-primary pull-right'));
	}
}