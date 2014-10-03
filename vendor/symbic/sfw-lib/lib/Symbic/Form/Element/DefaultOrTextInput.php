<?php
class Symbic_Form_Element_DefaultOrTextInput extends Symbic_Form_Element_AbstractElement
{
	public $helper			= 'formDefaultOrText';
	public $defaultValue	= null;

	public function isValid($value, $context = null)
	{
		$checkboxName = $this->getName() . '__use_default';
		if (is_array($context) && isset($context[ $checkboxName ]) && $context[ $checkboxName ])
		{
			$this->setValue($this->defaultValue);
			return true;
		}
		$this->setValue($value);
		return parent :: isValid($value, $context);
	}
}