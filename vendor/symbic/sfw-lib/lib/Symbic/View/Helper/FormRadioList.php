<?php
class Symbic_View_Helper_FormRadioList extends Zend_View_Helper_FormRadio
{
	public function formRadioList($name, $value = null, $attribs = null,
		$options = null)
	{

		$listsep = '';
		$info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
		extract($info); // name, value, attribs, options, listsep, disable

		// retrieve attributes for labels (prefixed with 'label_' or 'label')
		$label_attribs = array();
		foreach ($attribs as $key => $val) {
			$tmp    = false;
			$keyLen = strlen($key);
			if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
				$tmp = substr($key, 6);
			} elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
				$tmp = substr($key, 5);
			}

			if ($tmp) {
				// make sure first char is lowercase
				$tmp[0] = strtolower($tmp[0]);
				$label_attribs[$tmp] = $val;
				unset($attribs[$key]);
			}
		}

		$labelPlacement = 'append';
		foreach ($label_attribs as $key => $val) {
			switch (strtolower($key)) {
				case 'placement':
					unset($label_attribs[$key]);
					$val = strtolower($val);
					if (in_array($val, array('prepend', 'append'))) {
						$labelPlacement = $val;
					}
					break;
			}
		}

		// the radio button values and labels
		$options = (array) $options;

		// build the element
		$xhtml = '';

		// should the name affect an array collection?
		$name = $this->view->escape($name);
		if ($this->_isArray && ('[]' != substr($name, -2))) {
			$name .= '[]';
		}

		// ensure value is an array to allow matching multiple times
		$value = (array) $value;

		// Set up the filter - Alnum + hyphen + underscore
		require_once 'Zend/Filter/PregReplace.php';
		$pattern = @preg_match('/\pL/u', 'a')
			? '/[^\p{L}\p{N}\-\_]/u'    // Unicode
			: '/[^a-zA-Z0-9\-\_]/';     // No Unicode
		$filter = new Zend_Filter_PregReplace($pattern, "");

		// add radio buttons to the list.
		foreach ($options as $opt_value => $opt_label) {

			// Should the label be escaped?
			if ($escape) {
				$opt_label = $this->view->escape($opt_label);
			}

			// is it disabled?
			$disabled = '';
			if (true === $disable) {
				$disabled = ' disabled="disabled"';
			} elseif (is_array($disable) && in_array($opt_value, $disable)) {
				$disabled = ' disabled="disabled"';
			}

			// is it checked?
			$checked = '';
			if (in_array($opt_value, $value)) {
				$checked = ' checked="checked"';
			}

			// generate ID
			$optId = $id . '-' . $filter->filter($opt_value);
			$label_attribs['for'] = $optId;

			// Wrap the radios in labels
			if (!isset($label_attribs['class']))
			{
				$label_attribs['class'] = 'symbic_form_radioList_label';
			}
			$radioLabel = '<label' . $this->_htmlAttribs($label_attribs) . '>' . $opt_label . '</label>';

			if (!isset($attribs['class']))
			{
				$attribs['class'] = 'symbic_form_radioList_input';
			}

			$radio = '<input type="' . $this->_inputType . '"'
					. ' name="' . $name . '"'
					. ' id="' . $optId . '"'
					. ' value="' . $this->view->escape($opt_value) . '"'
					. $checked
					. $disabled
					. $this->_htmlAttribs($attribs)
					. $this->getClosingBracket();

			if (('prepend' == $labelPlacement) ? $opt_label : '')
			{
				$radio = $radioLabel . $radio;
			}
			if (('append' == $labelPlacement) ? $opt_label : '')
			{
				$radio .= $radioLabel;
			}

			// add to the array of radio buttons
			$xhtml .= $radio;
		}

		return $xhtml;
	}
}