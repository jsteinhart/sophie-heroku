<?php
class Symbic_View_Helper_FormTelInput extends Symbic_View_Helper_FormInput
{
    public function formTelInput($name, $value = null, $attribs = null)
    {
		// TODO: http://andr-04.github.io/inputmask-multi/en.html
		return $this->renderInput($name, $value, $attribs);
    }
}