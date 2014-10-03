<?php
class Symbic_View_Helper_FormText extends Symbic_View_Helper_FormInput
{
    public function formText($name, $value = null, $attribs = null)
    {
		if (!is_array($attribs))
		{
			$attribs = array();
		}
		$attribs['type'] = 'text';
		return $this->renderInput($name, $value, $attribs);
    }
}
