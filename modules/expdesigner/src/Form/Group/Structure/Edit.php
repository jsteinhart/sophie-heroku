<?php
namespace Expdesigner\Form\Group\Structure;

class Edit extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Edit Groupstructure');

		$this->addElement('hidden', 'treatmentId');
		$this->addElement('hidden', 'label');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
			'order' => 1
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save',
			'order' => 32000
		));
	}
	
	public function createElementsFromStructure($structure)
	{
		foreach ($structure as $name => $struc)
		{
			$this->addElement('IntInput', 'min_' . $name, array (
				'label' => 'Participant quantity of type “' . $struc['name'] . '”',
				'required' => true,
				'min' => 0
			));
		}
	}
	
}