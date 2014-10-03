<?php
class Symbic_View_Helper_FormInput extends Zend_View_Helper_FormElement
{
	static public $componentUrlJqueryJs = '/components/jquery/1.10.2/jquery-1.10.2.min.js';
	static public $componentUrlBootstrapJs = '/components/bootstrap/3.0.3/js/bootstrap.min.js';
	static public $componentUrlBootstrapCss = '/components/bootstrap/3.0.3/css/bootstrap.min.css';
	static public $componentUrlBootstrapMaxlengthJs = '/components/bootstrap-maxlength/1.5.3/bootstrap-maxlength.min.js';

	protected function pullInputFlagAttributes($disable, &$attribs)
	{
		// build the element
		$flags = array();

		// form flag attribute disabled
		if ($disable)
		{
			$flags[] = 'disabled';
			if (isset($attribs['disabled']))
			{
				unset($attribs['disabled']);
			}
		}

		// other flag attributes
		foreach (array('autofocus', 'checked', 'formnovalidate', 'multiple', 'readonly', 'required') as $flagName)
		{
			if (isset($attribs[$flagName]))
			{
				if ($attribs[$flagName])
				{
					$flags[] = $flagName;
				}
				unset($attribs[$flagName]);
			}
		}

		// autocomplete: on/off
		if (isset($attribs['autocomplete']))
		{
			if ($attribs['autocomplete'] && $attribs['autocomplete'] !== 'off')
			{
				$flags[] = 'autocomplete="on"';
			}
			else
			{
				$flags[] = 'autocomplete="off"';
			}
			unset($attribs['autocomplete']);
		}

		if (sizeof($flags) > 0)
		{
			return ' ' . implode(' ', $flags);
		}
		return '';
	}

	protected function pullInputTypeAttribute(&$attribs)
	{
		if (isset($attribs['type']))
		{
			$type = $attribs['type'];
			unset($attribs['type']);
			return $type;
		}
		return 'text';
	}

	protected function getInputRenderName($name, &$attribs)
	{
		return ' name="' . $this->view->escape($name) . '"';
	}

	protected function getInputRenderId($id, &$attribs)
	{
		return ' id="' . $this->view->escape($id) . '"';
	}

	protected function getInputRenderValue($value, &$attribs)
	{
		return ' value="' . $this->view->escape($value) . '"';
	}

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
		return '<input type="' . $this->pullInputTypeAttribute($attribs) . '"'
				. $this->getInputRenderName($name, $attribs)
				. $this->getInputRenderId($id, $attribs)
				. $this->getInputRenderValue($value, $attribs)
				. $this->pullInputFlagAttributes($disable, $attribs)
				// render remaining attributes
				. $this->_htmlAttribs($attribs)
				. '>';
	}

	public function formInput($name, $value = null, $attribs = null)
	{
		return $this->renderInput($name, $value, $attribs);
	}

	protected function initShowMaxlength($id, $configuration)
	{
		$this->view->headStyle()->appendFile(self :: $componentUrlBootstrapCss);
		$this->view->inlineScript()->appendFile(self :: $componentUrlJqueryJs);
		$this->view->inlineScript()->appendFile(self :: $componentUrlBootstrapJs);
		$this->view->inlineScript()->appendFile(self :: $componentUrlBootstrapMaxlengthJs);

		if (is_array($configuration))
		{
			$configuration = Zend_Json::encode($configuration, false, array('enableJsonExprFinder' => true));
		}
		if (!is_string($configuration))
		{
			$configuration = '';
		}
		$this->view->jsOnLoad()->appendScript('$("#' . $this->view->escape($id) . '").maxlength(' . $configuration . ')');
	}
}
