<?php
class Symbic_View_Helper_FormTextarea extends Symbic_View_Helper_FormInput
{
	protected function renderInput($name, $value, $attribs)
	{
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable

		if (isset($attribs['showMaxlength']))
		{
			if ($id && isset($attribs['maxlength']))
			{
				$this->initShowMaxlength($id, $attribs['showMaxlength']);
			}
			unset($attribs['showMaxlength']);
		}
		$xhtml = '<textarea '
				. $this->getInputRenderName($name, $attribs)
				. $this->getInputRenderId($id, $attribs)
				. $this->pullInputFlagAttributes($disable, $attribs)
				// render remaining attributes
				. $this->_htmlAttribs($attribs)
				. '>' . $this->view->escape($value) . '</textarea>';
		return $xhtml;
	}

	public function formTextarea($name, $value = null, $attribs = null)
	{
		return $this->renderInput($name, $value, $attribs);
	}
}
