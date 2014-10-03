<?php
namespace Expadmin\Form\Participant;

class Edit extends Add
{
	public function init()
	{
		parent::init();
		$this->setLegend('Edit Participant');
		$this->addElement('hidden', 'participantId');

		$this->removeElement('code');
		$this->removeElement('submit');

		$this->addElement('TextInput', 'code', array(
			'label' => 'Code',
			'required' => true,
		));
		
		$this->addElement('submit', 'submit', array(
			'label' => 'Edit',
			'ignore' => true,
		));
	}
}