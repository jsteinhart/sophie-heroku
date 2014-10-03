<?php
namespace Expadmin\Form\Variable;

class Import extends \Symbic_Form_Standard
{
	public function init()
	{
		$this->addElement('TextareaAutosize', 'sessionVariableImportContent', array (
			'label' => 'Variable Data',
			'required' => true
		));

		$this->addElement('submit', 'submitVariableImport', array('label' => 'submit'));
	}

}