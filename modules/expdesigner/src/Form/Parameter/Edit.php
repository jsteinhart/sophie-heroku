<?php
namespace Expdesigner\Form\Parameter;

class Edit extends Add
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Parameter');
		$this->addElement('hidden', 'parameterName');
	}
}