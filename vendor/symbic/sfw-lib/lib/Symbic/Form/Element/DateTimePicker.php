<?php
class Symbic_Form_Element_DateTimePicker extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formDateTimePicker';

	public function isValid($value, $context = null)
	{
		$newValue = null;
		if (is_array($value))
		{
			$newValue = $value['year'] . '-' . $value['month'] . '-' . $value['day'] . ' ' . $value['time'];
		}
		$this->setValue($newValue);
		return parent :: isValid($newValue, $context);
	}
}