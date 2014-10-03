<?php
namespace Expdesigner\Form\Variable;

class Edit extends Add
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Variable');
		$this->addElement('hidden', 'variableName');
	}
}