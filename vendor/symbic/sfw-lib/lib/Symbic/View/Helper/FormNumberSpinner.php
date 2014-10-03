<?php
class Symbic_View_Helper_FormNumberSpinner extends Symbic_View_Helper_FormInput
{
    public function formNumberSpinner($name, $value = null, $attribs = null)
    {
		return $this->renderInput($name, $value, $attribs);
    }
}