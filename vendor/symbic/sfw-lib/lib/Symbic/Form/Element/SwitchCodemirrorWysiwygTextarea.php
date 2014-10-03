<?php
class Symbic_Form_Element_SwitchCodemirrorWysiwygTextarea extends Symbic_Form_Element_AbstractElement
{
	public $helper		= 'formSwitchCodemirrorWysiwygTextarea';

	public function getJsInstance()
	{
		return 'SymbicFormSwitchCodemirrorWysiwygTextarea.get(\'' . $this->getId() . '\')';
	}
}