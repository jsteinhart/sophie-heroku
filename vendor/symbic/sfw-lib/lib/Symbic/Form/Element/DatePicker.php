<?php
class Symbic_Form_Element_DatePicker extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formDatePicker';

	public function isValid($value, $context = null)
	{
		$newValue = null;
		if (is_array($value))
		{
			$newValue = $value['year'] . '-' . $value['month'] . '-' . $value['day'];
		}
		$this->setValue($newValue);
		return parent :: isValid($newValue, $context);
	}
}