<?php
namespace Expdesigner\Form\Treatment;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Treatment');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Add',
			'ignore' => true
		));
	}
}