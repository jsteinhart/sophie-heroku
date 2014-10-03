<?php
class Symbic_View_Helper_FormDefaultOrText extends Zend_View_Helper_FormElement
{
	private $dummyId = '';

	public function formDefaultOrText($name, $value = null, $attribs = null)
    {
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, id, value, attribs, options, listsep, disable

		if (!isset($attribs['default']))
		{
			$attribs['default'] = '-/-';
		}
		$attribs['default'] = (string)$attribs['default'];

		$escId = $this->view->escape($id);

		$formText = new Zend_View_Helper_FormText();
		$formText->setView($this->view);
		$textValue = $value;
		$textAttribs = $attribs;
		if (is_null($value))
		{
			$textValue = $attribs['default'];
			$textAttribs['disable'] = true;
		}

		$formCheckbox = new Zend_View_Helper_FormCheckbox();
		$formCheckbox->setView($this->view);
		$checkboxName = $name . '__use_default';
		$checkboxValue = '1';
		$checkboxAttribs = $attribs;
		$checkboxAttribs['id'] = $id . '__use_default';
		$checkboxAttribs['checked'] = is_null($value);
		$checkboxAttribs['onchange'] = 'var x = dojo.byId("' . $escId . '");if(this.checked) { x.value = "' . $this->view->escape($attribs['default']) . '"; x.disabled = true; } else { x.disabled = false; }';

		$xhtml  = '';
		$xhtml .= '<label for="' . $this->view->escape($checkboxAttribs['id']) . '" class="blank">';
		$xhtml .= $formCheckbox->formCheckbox($checkboxName, $checkboxValue, $checkboxAttribs);
		$xhtml .= ' ';
		$xhtml .= 'Standard-Wert (&bdquo;' . $this->view->escape($attribs['default']) . '&ldquo;) verwenden';
		$xhtml .= '</label>';
		$xhtml .= '<br />';
		$xhtml .= $formText->formText($name, $textValue, $textAttribs);

		return $xhtml; // . '<pre>' . $this->view->escape(print_r($options, 1)) . '</pre>';
    }
}