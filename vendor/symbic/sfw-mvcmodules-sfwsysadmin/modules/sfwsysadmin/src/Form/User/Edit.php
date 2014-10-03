<?php
namespace Sfwsysadmin\Form\User;

class Edit extends Add
{
	public function init()
	{
		parent::init();
		$this->setLegend('Edit User');
		$this->addElement('hidden', 'userId');
		
		$password = $this->getElement('password');
		$password->setRequired(false);
		$password2 = $this->getElement('password2');
		$password2->setRequired(false);
		
		$submit = $this->getElement('submit');
		$submit->setLabel('Save');
	}
}