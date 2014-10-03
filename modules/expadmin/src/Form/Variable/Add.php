<?php
namespace Expadmin\Form\Variable
{
	class Add extends \Symbic_Form_Standard
	{
		public function init()
		{
			$this->setLegend('Set Variable');

			$this->addElement('select', 'personContext', array (
				'label' => 'Person Context',
				'required' => true,
				'multiOptions' => array (
					'e' => 'Everyone',
					'g' => 'Group',
					'p' => 'Participant'
				),
				'onChange' => 'expadmin.sessionAddVariableContextChange()'
			));

			$this->addElement('text', 'participantLabel', array (
				'label'		=> 'Participant Label',
				'required'	=> true,
				'validators' => array (
					new \Sophie_Validate_Session_Participant_Label()
				),
			));

			$this->addElement('text', 'groupLabel', array (
				'label'		=> 'Group Label',
				'required'	=> true,
				'validators' => array (
					new \Sophie_Validate_Session_Group_Label()
				),
			));

			// add select for variable person context
			$this->addElement('select', 'proceduralContext', array (
				'label' => 'Procedural Context',
				'required' => true,
				'multiOptions' => array (
					'e' => 'Everywhere',
					'sg' => 'Stepgroup',
					'sl' => 'Stepgroup Loop'
				),
				'onChange' => 'expadmin.sessionAddVariableContextChange()'
			));

			$this->addElement('text', 'stepgroupLabel', array (
				'label'		=> 'Stepgroup Label',
				'required'	=> true,
				'validators' => array (
					new \Sophie_Validate_Treatment_Stepgroup_Label()
				),
			));

			$this->addElement('IntInput', 'stepgroupLoop', array (
				'label'		=> 'Stepgroup Loop',
				'required'	=> true,
				'min'       => 0,
			));

			$this->addElement('text', 'name', array (
				'label' => 'Name',
				'required' => true,
				'validators' => array (
					new \Sophie_Validate_Session_Variable_Name()
				),
			));

			$this->addElement('TextareaAutosize', 'value', array (
				'label' => 'Value',
				'required' => true,
			));

			$this->addElement('submit', 'submit', array (
				'label' => 'save'
			));
		}
	}
}