<?php
namespace Expadmin\Form\Variable;

class Listing extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('List Variables');

		$order = 0;
		$order += 100;
		$this->addElement('Multiselect', 'filterNames', array (
			'label' => 'Filter Names',
			'multiOptions' => array (),
			'order' => $order
		));

		$order += 100;
		$this->addElement('variableContextMultiSelect', 'filterVariableTypes',
			array(
				'label'=>'Filter Variable Type',
				'order' => $order
			),
			array()
		);

		$order += 100;
		$this->addElement('Multiselect', 'filterStepgroupLabels', array (
			'label' => 'Filter Stepgroups',
			'multiOptions' => array (),
			'order' => $order
		));

		$order += 100;
		$this->addElement('MultiCheckboxBoxed', 'filterStepgroupLoops', array (
			'label' => 'Filter Stepgroup Loops',
			'multiOptions' => array (),
			'order' => $order
		));

		$order += 100;
		$this->addElement('checkbox', 'filterSystemVariables', array (
			'label' => 'Exclude System Variable',
			'checked' => true,
			'required' => true,
			'order' => $order
		));

		$order += 100;
		$this->addElement('checkbox', 'includeParticipantCodes', array (
			'label' => 'Include Participant Codes',
			'checked' => false,
			'required' => true,
			'order' => $order
		));

		$order += 100;
		$this->addElement('select', 'outputFormat', array (
			'label' => 'Output Format',
			'multiOptions' => array (
				'html' => 'html',
				'csv' => 'csv',
				//'xlsx' => 'xlsx'
			),
			'required' => true,
			'order' => $order
		));

		$order += 100;
		$this->addElement('submit', 'submitVariableList', array (
			'label' => 'submit',
			'order' => $order
		));
	}
}