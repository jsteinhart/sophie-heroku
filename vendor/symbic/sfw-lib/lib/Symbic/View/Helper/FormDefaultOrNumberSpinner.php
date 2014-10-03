<?php
class Symbic_View_Helper_FormDefaultOrNumberSpinner extends Zend_View_Helper_FormElement
{
	private $dummyId = '';

	public function formDefaultOrNumberSpinner($name, $value = null, $attribs = null)
    {
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, id, value, attribs, options, listsep, disable

		if (!isset($attribs['defaultValue']) || !isset($attribs['defaultLabel']))
		{
			throw new Exception('You need to set a defaultValue and a defaultLabel for the defaultOrNumberPicker form element');
		}
		$defaultValue = $attribs['defaultValue'];
		unset($attribs['defaultValue']);
		$defaultLabel = $attribs['defaultLabel'];
		unset($attribs['defaultLabel']);

		$escId = $this->view->escape($id);

		$formText = new Zend_Dojo_View_Helper_NumberSpinner();
		$formText->setView($this->view);
		$attribs = $attribs;
		if (is_null($value))
		{
			$attribs['disable'] = true;
		}

		$formCheckbox = new Zend_View_Helper_FormCheckbox();
		$formCheckbox->setView($this->view);
		$checkboxName = $name . '__use_default';
		$checkboxValue = '1';
		$checkboxAttribs = array();
		$checkboxAttribs['id'] = $id . '__use_default';
		$checkboxAttribs['checked'] = is_null($value);
		$checkboxAttribs['onchange'] = 'var x = dojo.byId("' . $escId . '");if(this.checked) { x.value = "' . $this->view->escape($defaultValue) . '"; x.disabled = true; } else { x.disabled = false; }';

		$xhtml  = '';
		$xhtml .= '<label for="' . $this->view->escape($checkboxAttribs['id']) . '" class="blank">';
		$xhtml .= $formCheckbox->formCheckbox($checkboxName, $checkboxValue, $checkboxAttribs);
		$xhtml .= ' ';
		$xhtml .= $this->view->escape($defaultLabel);
		$xhtml .= '</label>';
		$xhtml .= '<br />';
		$xhtml .= $formText->numberSpinner($name, $value, $attribs);

		return $xhtml; // . '<pre>' . $this->view->escape(print_r($options, 1)) . '</pre>';
	}

}
