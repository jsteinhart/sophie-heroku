<?php
namespace Expdesigner\Form\Report;

class Edit extends Add
{
	public function init()
	{
		parent::init();

		$this->setLegend('Edit Report');
		$this->addElement('hidden', 'reportId');
	}
}