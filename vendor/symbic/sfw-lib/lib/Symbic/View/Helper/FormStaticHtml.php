<?php

/**
 *
 */
class Symbic_View_Helper_FormStaticHtml extends Zend_View_Helper_FormElement
{

	/**
	 *
	 * @param type $name
	 * @param type $value
	 * @param string $attribs
	 * @return type
	 */
	public function formStaticHtml($name, $value = null, $attribs = null)
	{
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info);

		if (empty($attribs['class']))
		{
			$attribs['class'] = 'symbic_form_staticHtml';
		}

		if (null !== ($translator = $this->getTranslator()))
		{
			$value = $translator->_($value);
		}

		if (isset($attribs['valueParameters']))
		{
			if (is_array($attribs['valueParameters']))
			{
				array_unshift($attribs['valueParameters'], $value);
				$value = call_user_func_array('sprintf', $attribs['valueParameters']);
			}
			else
			{
				$value = sprintf($value, (string) $attribs['valueParameters']);
			}
			unset($attribs['valueParameters']);
		}

		return '<div class="' . $this->view->escape((string) $attribs['class']) . '">' . $value . '</div>';
	}

}
