<?php
namespace Expdesigner\Form\Experiment;

class Import extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Import Experiment');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('textarea', 'experimentContent', array (
			'label' => 'Import Data',
			'required' => true
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Import'
		));
	}
}