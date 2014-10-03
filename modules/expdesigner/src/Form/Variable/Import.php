<?php
namespace Expdesigner\Form\Variable;

class Import extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Import Variables');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('TextareaAutosize', 'variableContent', array (
			'label' => 'Variables',
			'required' => true,
		));

		$csvDelimiterOptions = array (
			';' => 'Semicolon (;)',
			"\t" => 'Tab'
		);
		$this->addElement('select', 'csvDelimiter', array (
			'multiOptions' => $csvDelimiterOptions,
			'label' => 'CSV Delimiter',
		), array ());
		
		$this->addElement('submit', 'submit', array (
			'label' => 'Import'
		));
	}
}