<?php
class Symbic_Form_Element_CodemirrorTextarea extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formCodemirrorTextarea';
	
	public function getJsInstance()
	{
		return 'SymbicFormCodemirrorTextarea.get(\'' . $this->getId() . '\')';
	}
}