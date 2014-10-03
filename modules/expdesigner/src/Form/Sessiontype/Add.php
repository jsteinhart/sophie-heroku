<?php
namespace Expdesigner\Form\Sessiontype;

class Add extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Add Sessiontype');

		$this->addElement('hidden', 'treatmentId');
		$this->addElement('hidden', 'style', array('value' => 'static'));

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
		));

		$this->addElement('select', 'participantMgmt', array (
			'label' => 'Participant Management',
			'required' => true,
			'multiOptions' => array( 'static' => 'static', 'dynamic' => 'dynamic')
		));

		$this->addElement('text', 'size', array (
			'label' => 'Number of Groups',
			'required' => true,
			'value' => 1,
			'constraints' => array ('min' => 1)
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save'
		));
	}
}