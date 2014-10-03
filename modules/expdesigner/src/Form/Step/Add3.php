<?php
namespace Expdesigner\Form\Step;

class Add3 extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Step');

		$this->addElement('hidden', 'stepgroupId');
		$this->addElement('hidden', 'steptypeId');

		$this->addElement('text', 'name', array (
			'label' => 'Internal Name',
			'required' => true
		));

		$this->addElement('text', 'label', array (
			'label' => 'Label',
			'required' => false,
		));
		
		$this->addElement('submit', 'submit', array('label'=>'Add'));
	}
}