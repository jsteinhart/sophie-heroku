<?php
namespace Expdesigner\Form\Treatment\Import;

class Url extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->addElement('text', 'nameUrl', array (
			'label' => 'Name'
		));

		$this->addElement('ComboBox', 'treatmentContentUrl', array (
			'placeHolder' => 'Please select or enter a new URL',
			'value' => '',
			'label' => 'Url',
			'required' => true
		));

		$this->addElement('submit', 'submitUrl', array (
			'label' => 'Import'
		));
	}
}