<?php
namespace Expdesigner\Form\Sessiontype;

class Edit extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Edit Sessiontype');

		$this->addElement('hidden', 'id');
		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('select', 'participantMgmt', array (
			'label' => 'Participant Management',
			'required' => true,
			'multiOptions' => array( 'static' => 'static', 'dynamic' => 'dynamic')
		));

		$this->addElement('IntInput', 'size', array (
			'label' => 'Number of Groups',
			'required' => true,
			'value' => 1,
			'min' => 1
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save'
		));
	}
}