<?php
class Symbic_Form_Element_StaticHtml extends Symbic_Form_Element_AbstractElement
{
	public $helper = 'formStaticHtml';

	public function isValid($value, $context = null)
	{
		return true;
	}
}