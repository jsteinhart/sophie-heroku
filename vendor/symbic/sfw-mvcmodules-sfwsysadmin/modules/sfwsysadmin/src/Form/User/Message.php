<?php
namespace Sfwsysadmin\Form\User;

class Message extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Send Message');

		$this->addElement('text', 'senderName', array (
			'label' => 'Sender Name',
			'required' => true
		));

		$this->addElement('text', 'senderEmail', array (
			'label' => 'Sender Email',
			'required' => true
		));

		$this->addElement('MultiCheckboxBoxed', 'recipientToUserIds', array (
			'label' => 'To',
			'multiOptions' => array(),
			'required' => true
		));

		$this->addElement('text', 'subject', array (
			'label' => 'Subject',
			'required' => true,
			'value' => 'System Message'
		));

		$this->addElement('TextareaAutosize', 'bodyText', array (
			'label' => 'Message',
			'required' => true
		));

		$this->addElement('TextareaAutosize', 'bodyTextFooter', array (
			'label' => 'Footer'
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Send',
			'ignore' => true
		));
	}
}