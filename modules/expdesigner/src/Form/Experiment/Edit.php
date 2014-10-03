<?php
namespace Expdesigner\Form\Experiment;

class Edit extends Add
{
	public function init()
	{
		parent::init();
		$this->setLegend('Edit Experiment');
		$this->addElement('hidden', 'experimentId');
		$submit = $this->getElement('submit');
		$submit->setLabel('Save');
	}
}