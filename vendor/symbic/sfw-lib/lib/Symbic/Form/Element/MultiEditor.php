<?php
class Symbic_Form_Element_MultiEditor extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formMultiEditor';
	
	public function getJsInstance()
	{
		return 'SymbicFormMultiEditor.get(\'' . $this->getId() . '\')';
	}
}