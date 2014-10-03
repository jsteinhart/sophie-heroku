<?php
class Symbic_View_Helper_HeadStyle extends Zend_View_Helper_Abstract
{
	/**
	 * Capture type and/or attributes (used for hinting during capture)
	 * Copied from Zend_View_Helper_HeadStyle to ensure backward compatibility
	 * @var string
	 */
	protected $_captureAttrs = null;

	/**
	 * Capture lock
	 * Copied from Zend_View_Helper_HeadStyle to ensure backward compatibility
	 * @var bool
	 */
	protected $_captureLock = false;

	/**
	 * Capture type (append, prepend, set)
	 * Copied from Zend_View_Helper_HeadStyle to ensure backward compatibility
	 * @var string
	 */
	protected $_captureType;

	public function getContainer()
	{
		return Symbic_View_Helper_Container_HeadStyle::getInstance();
	}

	public function headStyle($content = null, $placement = 'APPEND', $attributes = array(), $allowDuplicate = true)
	{
		if ($content !== null)
		{
			switch (strtoupper($placement))
			{
				case 'PREPEND':
					$this->prependStyle($content, $attributes);
					break;
				case 'APPEND':
				default:
					$this->appendStyle($content, $attributes);
					break;
			}
		}
		return $this;
	}

	public function appendStyle($content, $attributes = array(), $allowDuplicate = true)
	{
		$this->getContainer()->append(
			array(
				'id'				=> 'content:' . $content,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('content' => $content, 'attributes' => $attributes)
			)
		);
		return $this;
	}

	public function prependStyle($content, $attributes = array(), $allowDuplicate = true)
	{
		$this->getContainer()->prepend(
			array(
				'id'				=> 'content:' . $content,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('content' => $content, 'attributes' => $attributes)
			)
		);
		return $this;
	}

	public function appendFile($href, $media = 'screen', $conditional = FALSE, $attributes = null, $allowDuplicate = false)
	{
		$attributes['media'] = $media;
		$attributes['conditional'] = $conditional;

		$this->getContainer()->append(
			array(
				'id'				=> 'href:' . $href,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('href' => $href, 'attributes' => $attributes)
			)
		);
		return $this;
	}

	public function prependFile($href, $media = 'screen', $conditional = FALSE, $attributes = null, $allowDuplicate = false)
	{
		$attributes['media'] = $media;
		$attributes['conditional'] = $conditional;

		$this->getContainer()->prepend(
			array(
				'id'				=> 'href:' . $href,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('href' => $href, 'attributes' => $attributes)
			)
		);
		return $this;
	}

	public function render()
	{
		$content = '';

		foreach ($this->getContainer()->get() as $element)
		{
			if (!isset($element['data']))
			{
				throw new Exception('Illegal element structure returned in ' . __CLASS__);
			}

			$element = $element['data'];

			if (!is_array($element))
			{
				throw new Exception('Illegal element type for headStyle in ' . __CLASS__);
			}

			if (!isset($element['attributes']) || !is_array($element['attributes']))
			{
				$element['attributes'] = array();
			}

			// render inline style

			if (!empty($element['content']))
			{
				$content .= '<style type="text/css"';
				if (!empty($element['attributes']['media']))
				{
					$content .= ' media="' . $element['attributes']['media'] . '"';
				}

				foreach ($element['attributes'] as $key => $value)
				{
					if ($key === 'media' || $key === 'content' || $key === 'conditional')
					{
						continue;
					}
					$content .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));
				}
				$content .= '>' . PHP_EOL;

				if (!empty($element['attributes']['conditional']))
				{
					$content .= '<!--[if ' . $element['attributes']['conditional'] . ']>' . PHP_EOL;
				}

				$content .= $element['content'] . PHP_EOL;

				if (!empty($element['attributes']['conditional']))
				{
					$content .= '<![endif]-->' . PHP_EOL;
				}

				$content .= '</style>' . PHP_EOL;
			}

			// render link
			elseif (!empty($element['href']))
			{
				if (!empty($element['attributes']['conditional']))
				{
					$content .= '<!--[if ' . $element['attributes']['conditional'] . ']>' . PHP_EOL;
				}

				$content .= '<link type="text/css" href="' . $element['href'] . '"';
				if (!empty($element['attributes']['media']))
				{
					$content .= ' media="' . $element['attributes']['media'] . '"';
				}
				$content .= ' rel="stylesheet"';

				foreach ($element['attributes'] as $key => $value)
				{
					if ($key === 'media' || $key === 'href' || $key === 'conditional')
					{
						continue;
					}
					$content .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));
				}

				$content .= '>' . PHP_EOL;

				if (!empty($element['attributes']['conditional']))
				{
					$content .= '<![endif]-->' . PHP_EOL;
				}
			}
			else
			{
				throw new Exception('Unknown stylesheet type with neither content or href setting');
			}
		}

		$this->getContainer()->clear();

		return $content;
	}

	/**
	 * Start capture action
	 * Copied from Zend_View_Helper_HeadStyle to ensure backward compatibility
	 *
	 * @param  mixed $captureType
	 * @param  string $typeOrAttrs
	 * @return void
	 */
	public function captureStart($type = Zend_View_Helper_Placeholder_Container_Abstract::APPEND, $attrs = null)
	{
		if ($this->_captureLock) {
			require_once 'Zend/View/Helper/Placeholder/Container/Exception.php';
			$e = new Zend_View_Helper_Placeholder_Container_Exception('Cannot nest headStyle captures');
			$e->setView($this->view);
			throw $e;
		}

		$this->_captureLock		   = true;
		$this->_captureAttrs	   = $attrs;
		$this->_captureType		   = $type;
		ob_start();
	}

	/**
	 * End capture action and store
	 * Copied from Zend_View_Helper_HeadStyle to ensure backward compatibility
	 *
	 * @return void
	 */
	public function captureEnd()
	{
		$content			 = ob_get_clean();
		$attrs				 = $this->_captureAttrs;
		$this->_captureAttrs = null;
		$this->_captureLock	 = false;

		switch ($this->_captureType) {
			case Zend_View_Helper_Placeholder_Container_Abstract::SET:
				$this->setStyle($content, $attrs);
				break;
			case Zend_View_Helper_Placeholder_Container_Abstract::PREPEND:
				$this->prependStyle($content, $attrs);
				break;
			case Zend_View_Helper_Placeholder_Container_Abstract::APPEND:
			default:
				$this->appendStyle($content, $attrs);
				break;
		}
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