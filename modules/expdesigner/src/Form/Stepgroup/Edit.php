<?php
namespace Expdesigner\Form\Stepgroup;

class Edit extends Add
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Stepgroup');
		$this->addElement('hidden', 'stepgroupId');
	}
}