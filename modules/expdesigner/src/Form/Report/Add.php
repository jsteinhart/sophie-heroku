<?php
namespace Expdesigner\Form\Report;

class Add extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->setLegend('Add Report');

		$this->addElement('hidden', 'treatmentId');

		$this->addElement('text', 'name', array (
			'label' => 'Name',
			'required' => true,
		));

		$this->addElement('hidden', 'type', array (
			'value' => 'php-raw-output'
		));
		
		/*$this->addElement('select', 'type', array (
			'label' => 'Type',
			'required' => true,
			'multiOptions' => array('php-raw-output' => 'PHP Raw Output')
		));*/
		
		$definitionElement = $this->createElement('CodemirrorTextarea', 'definition', array (
			'label' => 'Definition',
			'required' => true,
			'toolbar' => new \Sophie_Toolbar_CodeMirror_Php()
		));
		$this->addElement($definitionElement);
		
		$this->addElement('StaticHtml', 'definitionValidator', array (
			'value' => '<div id="definitionSanitizerMessages" class="alert alert-danger" style="display:none;"></div>',
			'label' => '',
		));

		$this->addElement('submit', 'submit', array (
			'label' => 'save',
			'ignore' => true
		));
	}
}