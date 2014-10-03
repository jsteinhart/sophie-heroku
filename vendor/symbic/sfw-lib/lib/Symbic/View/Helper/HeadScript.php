<?php
class Symbic_View_Helper_HeadScript extends Zend_View_Helper_Abstract
{

	/**#@+
	 * Capture type and/or attributes (used for hinting during capture)
	 * Copied from Zend_View_Helper_HeadScript to ensure backward compatibility
	 * @var string
	 */
	protected $_captureLock        = false;
	protected $_captureScriptType  = null;
	protected $_captureScriptAttrs = null;
	protected $_captureType;
	/**#@-*/

	public function getContainer()
	{
		return Symbic_View_Helper_Container_HeadScript::getInstance();
	}

	public function headScript($mode = 'FILE', $spec = null, $placement = 'APPEND', array $attribs = array(), $type = null, $allowDuplicate = false)
	{
		if ($spec === null)
		{
			return $this;
		}

		if (strtoupper($mode) == 'FILE')
		{
			if (strtoupper($placement) == 'APPEND')
			{
				return $this->appendFile($spec, $type, $attribs, $allowDuplicate);
			}
			else
			{
				return $this->prependFile($spec, $type, $attribs, $allowDuplicate);
			}
		}
		else
		{
			if (strtoupper($placement) == 'APPEND')
			{
				return $this->appendScript($spec, $type, $attribs, $allowDuplicate);
			}
			else
			{
				return $this->prependScript($spec, $type, $attribs, $allowDuplicate);
			}
		}
	}

	public function appendFile($src, $type = null, $attribs = array(), $allowDuplicate = false)
	{
		if (!is_null($type))
		{
			$attribs['type'] = $type;
		}
		$attribs['src'] = $src;

		$element = array();
		$element['id'] = 'href:' . $src;
		$element['attribs'] = $attribs;
		$element['allowDuplicate'] = $allowDuplicate;

		$this->getContainer()->append($element);

		return $this;
	}

	public function prependFile($src, $type = null, $attribs = array(), $allowDuplicate = false)
	{
		if (!is_null($type))
		{
			$attribs['type'] = $type;
		}
		$attribs['src'] = $src;

		$element = array();
		$element['id'] = 'href:' . $src;
		$element['attribs'] = $attribs;
		$element['allowDuplicate'] = $allowDuplicate;

		$this->getContainer()->prepend($element);

		return $this;
	}

	public function appendScript($script, $type = null, $attribs = array())
	{
		if (!is_null($type))
		{
			$attribs['type'] = $type;
		}

		$element = array();
		$element['source'] = $script;
		$element['attribs'] = $attribs;
		$this->getContainer()->append($element);
		return $this;
	}

	public function prependScript($script, $type = null, $attribs = array())
	{
		if (!is_null($type))
		{
			$attribs['type'] = $type;
		}

		$element = array();
		$element['source'] = $script;
		$element['attribs'] = $attribs;
		$this->getContainer()->prepend($element);
		return $this;
	}

	public function render()
	{
		$content = '';

		$first = true;
		foreach ($this->getContainer()->get() as $element)
		{
			if ($first)
			{
				$first = false;
			}
			else
			{
				$content .= PHP_EOL;
			}

			$attribsString = '';
			$type = null;

			if (!empty($element['attribs']))
			{
				if (!empty($element['attribs']['type']))
				{
					$type = $element['attribs']['type'];
					unset($element['attribs']['type']);
				}

				foreach ($element['attribs'] as $key => $value)
				{
					if ('defer' == $key)
					{
						$value = 'defer';
					}
					$attribsString .= sprintf(' %s="%s"', $key, $this->view->escape($value));
				}
			}

			$html  = '<script';
			if (!empty($type) && $type != 'text/javascript')
			{
				$html .= ' type="' . $type . '"';
			}

			if ($attribsString != '')
			{
				$html .= $attribsString;
			}

			$html .= '>';

			if (!empty($element['source']))
			{
				$html .= PHP_EOL;
				$html .= $element['source'];
				$html .= PHP_EOL;
			}
			$html .= '</script>';

			if (!empty($element['attribs'])
				&& !empty($element['attribs']['conditional'])
				&& is_string($element['attribs']['conditional']))
			{
				$html = '<!--[if ' . $element['attribs']['conditional'] . ']> ' . $html . '<![endif]-->';
			}

			$content .= $html;
		}

		$this->getContainer()->clear();

		return $content;
	}

	/**
	 * Start capture action
	 * Copied from Zend_View_Helper_HeadScript to ensure backward compatibility
	 *
	 * @param  mixed $captureType
	 * @param  string $typeOrAttrs
	 * @return void
	 */
	public function captureStart($captureType = Zend_View_Helper_Placeholder_Container_Abstract::APPEND, $type = 'text/javascript', $attrs = array())
	{
		if ($this->_captureLock) {
			require_once 'Zend/View/Helper/Placeholder/Container/Exception.php';
			$e = new Zend_View_Helper_Placeholder_Container_Exception('Cannot nest headScript captures');
			$e->setView($this->view);
			throw $e;
		}

		$this->_captureLock		   = true;
		$this->_captureType		   = $captureType;
		$this->_captureScriptType  = $type;
		$this->_captureScriptAttrs = $attrs;
		ob_start();
	}

	/**
	 * End capture action and store
	 * Copied from Zend_View_Helper_HeadScript to ensure backward compatibility
	 *
	 * @return void
	 */
	public function captureEnd()
	{
		$content				   = ob_get_clean();
		$type					   = $this->_captureScriptType;
		$attrs					   = $this->_captureScriptAttrs;
		$this->_captureScriptType  = null;
		$this->_captureScriptAttrs = null;
		$this->_captureLock		   = false;

		switch ($this->_captureType) {
			case Zend_View_Helper_Placeholder_Container_Abstract::SET:
			case Zend_View_Helper_Placeholder_Container_Abstract::PREPEND:
			case Zend_View_Helper_Placeholder_Container_Abstract::APPEND:
				$action = strtolower($this->_captureType) . 'Script';
				break;
			default:
				$action = 'appendScript';
				break;
		}
		$this->$action($content, $type, $attrs);
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			trigger_error('There occured an unthrowable exception "' . $e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine(), E_USER_WARNING);
			return $e->getMessage();
		}
	}
}