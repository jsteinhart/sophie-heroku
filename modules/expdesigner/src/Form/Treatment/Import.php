<?php
namespace Expdesigner\Form\Treatment;

class Import extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Import Treatment');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('textarea', 'treatmentContent', array (
			'label' => 'Content',
			'required' => true
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Import'
		));

	}
}