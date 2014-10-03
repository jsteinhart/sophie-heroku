<?php
namespace Expdesigner\Form\Treatment;

class Copy extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Copy Treatment');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'New treatment name',
			'required' => true
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Copy'
		));

	}
}