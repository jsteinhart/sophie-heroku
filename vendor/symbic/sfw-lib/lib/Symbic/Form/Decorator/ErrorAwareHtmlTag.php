<?php
class Symbic_Form_Decorator_ErrorAwareHtmlTag extends Zend_Form_Decorator_HtmlTag
{
    protected function _htmlAttribs(array $attribs)
    {
        $element = $this->getElement();
		
		$hasErrorClass = $this->getOption('hasErrorClass');		
		if (empty($hasErrorClass))
		{
			$hasErrorClass = 'hasError';
		}

		if ($element->hasErrors())
		{
			if (!empty($attribs['class']))
			{
				$attribs['class'] .= ' ';
			}
			$attribs['class'] .= $hasErrorClass;
		}
		
        return parent::_htmlAttribs($attribs);
    }
}