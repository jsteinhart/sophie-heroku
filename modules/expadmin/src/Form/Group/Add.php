<?php
namespace Expadmin\Form\Group;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Groups');

		$this->addElement('hidden', 'sessionId');

		// TODO unse NumberInput
		$this->addElement('text', 'groupNumber', array (
			'label' => 'Number of Groups',
			'required' => true
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Add', 'ignore' => true));
	}
}