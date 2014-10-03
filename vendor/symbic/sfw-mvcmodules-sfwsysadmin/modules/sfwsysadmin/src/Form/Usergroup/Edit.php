<?php
namespace Sfwsysadmin\Form\Usergroup;

class Edit extends Add
{
	public function init()
	{
		parent::init();
		$this->setLegend('Edit Usergroup');
		$this->addElement('hidden', 'usergroupId');
		$submit = $this->getElement('submit');
		$submit->setLabel('Save');
	}
}