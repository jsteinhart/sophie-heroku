<?php
namespace Expadmin\Form\Participant\Edit;

class All extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Edit all Participants');

		$this->addElement('hidden', 'sessionId');

		$this->addElement('select', 'stepId', array (
			'label' => 'Step',
			'multiOptions' => array(),
			'required' => true,
		));

		$this->addElement('IntInput', 'stepgroupLoop', array (
			'label' => 'Stepgroup Loop',
			'value'=>1,
			'min' => 1,
			'required' => true,
		));

		$this->addElement('select', 'state', array (
			'label' => 'State',
			'multiOptions' => $this->getStateOptions(),
			'value'=>'started',
			'required' => true,
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'Save',
			'ignored' => true,
		));

	}

	public function getStateOptions()
	{
		return array('new'=>'new', 'started'=>'started', 'finished'=>'finished', 'excluded'=>'excluded');
	}

}