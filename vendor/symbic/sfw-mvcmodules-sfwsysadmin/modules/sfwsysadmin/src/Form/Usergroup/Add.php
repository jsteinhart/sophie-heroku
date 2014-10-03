<?php
namespace Sfwsysadmin\Form\Usergroup;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Usergroup');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('Multiselect', 'userIds', array (
			'label' => 'Users',
			'multiOptions' => array()
		));

		$this->addElement('SubmitInput', 'submit', array (
			'label' => 'Add',
			'ignore' => true
		));
	}
}