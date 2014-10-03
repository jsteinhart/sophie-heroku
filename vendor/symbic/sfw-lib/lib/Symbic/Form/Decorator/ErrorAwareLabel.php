<?php
class Symbic_Form_Decorator_ErrorAwareLabel extends Zend_Form_Decorator_Label
{
    public function getClass()
    {
        $element = $this->getElement();
		$class = parent::getClass();
		if (!$element->hasErrors())
		{
			return $class;
		}
		
		$hasErrorClass = $this->getOption('hasErrorClass');		
		if (empty($hasErrorClass))
		{
			$hasErrorClass = 'hasError';
		}
		
		if (!empty($class))
		{
			$class .= ' ';
		}

		$class .= $hasErrorClass;

        return $class;
    }
}