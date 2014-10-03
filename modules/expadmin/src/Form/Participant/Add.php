<?php
namespace Expadmin\Form\Participant;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Participant');

		/*$this->addElement('hidden', 'sessionId');*/

		/*$this->addElement('NumberSpinner', 'number', array(
			'label' => 'Number',
			'required' => true
		));*/

		/*$this->addElement('TextInput', 'label', array(
			'label' => 'Label',
			'required' => true
		));*/

		$this->addElement('select', 'stepId', array(
			'label' => 'Step',
			'multiOptions' => array(),
			'required' => true,
		));

		$this->addElement('IntInput', 'stepgroupLoop', array(
			'label' => 'Stepgroup Loop',
			'value'=>1,
			'min' => 1,
			'required' => true,
		));

		$this->addElement('DefaultOrTextInput', 'code', array(
			'label' => 'Code',
			'value' => null,
			'default' => 'auto',
			'checked' => 'checked'
		));

		$this->addElement('select', 'typeLabel', array(
			'label' => 'Type',
			'multiOptions' => array(),
			'required' => true,
		));

		$this->addElement('select', 'state', array(
			'label' => 'State',
			'multiOptions' => $this->getStateOptions(),
			'required' => true,
		));

		$this->addElement('submit', 'submit', array(
			'label' => 'Add',
			'ignore' => true,
		));
	}

	public function getStateOptions()
	{
		return array('new'=>'new', 'started'=>'started', 'finished'=>'finished', 'excluded'=>'excluded');
	}
}