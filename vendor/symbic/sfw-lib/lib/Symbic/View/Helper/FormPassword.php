<?php
class Symbic_View_Helper_FormPassword extends Symbic_View_Helper_FormInput
{
	protected function pullInputTypeAttribute(&$attribs)
	{
		if (isset($attribs['type']))
		{
			unset($attribs['type']);
		}
		return 'password';
	}

	protected function getInputRenderValue($value, &$attribs)
	{
        // determine the XHTML value
		$valueString = '';
		if (array_key_exists('renderPassword', $attribs))
		{
			if ($attribs['renderPassword']) {
				$valueString = $value;
			}
			unset($attribs['renderPassword']);
		}

		return ' value="' . $this->view->escape($valueString) . '"';
	}
	
    public function formPassword($name, $value = null, $attribs = null)
    {
		return $this->renderInput($name, $value, $attribs);
    }
}