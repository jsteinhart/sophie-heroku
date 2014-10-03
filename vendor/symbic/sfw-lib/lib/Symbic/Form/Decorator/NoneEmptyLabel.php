<?php
class Symbic_Form_Decorator_NoneEmptyLabel extends Symbic_Form_Decorator_ErrorAwareLabel
{
    public function getLabel()
    {
		$label = parent::getLabel();
        if (empty($label)) {
            return ' ';
        }
		return $label;
    }
}