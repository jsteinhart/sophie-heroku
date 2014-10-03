<?php
namespace Expdesigner\Form\Stepgroup;

class Add extends \Symbic_Form_Standard
{

	public function init()
	{
		$this->setLegend('Add Stepgroup');

		$this->setAttrib('onsubmit', 'return true;');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true
		));

		$this->addElement('text', 'label', array (
			'label' => 'Label',
			'required' => true,
			'maxlength' => 36,
			'showMaxlength' => true,
			//'tooltip' => 'When changing the label afterwards keep in mind that it already may have been used e.&nbsp;g. in steps or the group structure! Those references will not work correctly after changing the label.'
		));

		$this->addElement('IntInput', 'loop', array (
			'label' => 'Loops',
			'required' => true,
			'min' => -1,
			'value' => 1
		));

		$this->addElement('StaticHtml', 'loopInfo', array (
			'value' => '<div class="alert alert-info">Use -1 as a value for Stepgroup Loops to use infinite loops.</div>'
		));

		$runConditionScript = $this->createElement(
			'CodemirrorTextarea',
			'runConditionScript',
			array(
				'label' => 'Run Condition Script',
				'toolbar' => new \Sophie_Toolbar_CodeMirror_Php(),
			)
		);
		$this->addElement($runConditionScript);

		$this->addElement('select', 'runConditionFalse', array(
			'label' => 'On False Run Condition',
			'required' => true,
			'multiOptions' => array(
					'skipStepgroupLoop' => 'Skip Stepgroup Loop',
					'skipStepgroup' => 'Skip Stepgroup'
				),
			'value' => 'skipStepgroupLoop'
		));

		$this->addElement('select', 'grouping', array(
			'label' => 'Grouping',
			'required' => true,
			'multiOptions' => array(
					'inactive' => 'inactive',
					'static' => 'static'
				),
			'value' => 'static'
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save'
		));
	}
}