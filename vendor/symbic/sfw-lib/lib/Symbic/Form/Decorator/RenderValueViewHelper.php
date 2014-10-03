<?php
class Symbic_Form_Decorator_RenderValueViewHelper extends Zend_Form_Decorator_ViewHelper
{    
	public function getValue($element)
    {
		if (method_exists($element, 'getRenderValue'))
		{
			return $element->getRenderValue();
		}

        return $element->getValue();
    }
}