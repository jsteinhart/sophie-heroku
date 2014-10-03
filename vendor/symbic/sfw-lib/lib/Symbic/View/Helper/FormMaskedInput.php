<?php
class Symbic_View_Helper_FormMaskedInput extends Symbic_View_Helper_FormInput
{
    public function formMaskedInput($name, $value = null, $attribs = null)
    {
		// TODO: implement masked input
		// http://andr-04.github.io/inputmask-multi/en.html
		// http://mavrin.github.io/maskInput/
		return $this->renderInput($name, $value, $attribs);
    }
}
