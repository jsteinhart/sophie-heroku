<?php
class Symbic_View_Helper_HeadLink extends Zend_View_Helper_Abstract
{
	public function getContainer()
	{
		return Symbic_View_Helper_Container_HeadLink::getInstance();
	}

	public function headLink(array $attributes = null, $placement = 'APPEND', $allowDuplicate = false)
	{
		if ($attributes !== null)
		{
			$placement = strtoupper($placement);

			if (empty($attributes['href']))
			{
				throw new Exception('href attribute is required in ' . __CLASS__);
			}

			if (empty($attributes['media']))
			{
				$attributes['media'] = 'screen';
			}

			if (empty($attributes['conditional']))
			{
				$attributes['conditional'] = null;
			}

			if (empty($attributes['extras']))
			{
				$attributes['extras'] = array();
			}
			else
			{
				$attributes['extras'] = (array)$attributes['extras'];
			}

			if (isset($attributes['type']) && $attributes['type'] === 'text/css')
			{
				switch ($placement)
				{
					case 'PREPEND':
						$this->prependStylesheet($attributes['href'], $attributes['media'], $attributes['conditional'], $attributes['extras'], $allowDuplicate);
						break;

					case 'APPEND':
					default:
						$this->appendStylesheet($attributes['href'], $attributes['media'], $attributes['conditional'], $attributes['extras'], $allowDuplicate);
						break;
				}
			}
			else
			{
				switch ($placement)
				{
					case 'PREPEND':
						$this->prependAlternate($attributes['href'], $attributes['media'], $attributes['conditional'], $attributes['extras'], $allowDuplicate);
						break;

					case 'APPEND':
					default:
						$this->appendAlternate($attributes['href'], $attributes['media'], $attributes['conditional'], $attributes['extras'], $allowDuplicate);
						break;
				}
			}
		}
		return $this;
	}

	public function appendStylesheet($href, $media = 'screen', $conditional = null, $attributes = array(), $allowDuplicate = false)
	{
		$this->view->headStyle()->appendFile($href, $media, $conditional, $attributes, $allowDuplicate);
		return $this;
	}

	public function prependStylesheet($href, $media = 'screen', $conditional = null, $attributes = array(), $allowDuplicate = false)
	{
		$attributes['media'] = $media;
		$attributes['conditional'] = $conditional;
		$this->view->headStyle()->prependFile($href, $media, $conditional, $attributes, $allowDuplicate);
		return $this;
	}

	public function appendAlternate($href, $media = 'screen', $conditional = null, $extras = array(), $allowDuplicate = false)
	{
		$this->getContainer()->append(
			array(
				'id'				=> 'href:' . $href,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('href' => $href, 'media' => $media, 'conditional' => $conditional, 'extras' => $extras)
			)
		);
		return $this;
	}

	public function prependAlternate($href, $media = 'screen', $conditional = null, $extras = array(), $allowDuplicate = false)
	{
		$this->getContainer()->prepend(
			array(
				'id'				=> 'href:' . $href,
				'allowDuplicate'	=> $allowDuplicate,
				'data'				=> array('href' => $href, 'media' => $media, 'conditional' => $conditional, 'extras' => $extras)
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

			if (!empty($element['conditional']))
			{
				$content .= '<!--[if ' . $element['conditional'] . ']>' . PHP_EOL;
			}

			$content .= '<link type="text/css" media="';
			if (empty($element['media']))
			{
				$content .= 'screen';
			}
			else
			{
				$content .= $element['media'];
			}
			$content .= '" ';

			$content .= ' href="' . $element['href'] . '"';

			foreach ($element['extras'] as $key => $value)
			{
				sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));
			}
			$content .= '>' . PHP_EOL;

			if (!empty($element['conditional']))
			{
				$content .= '<![endif]-->' . PHP_EOL;
			}
		}
		
		$this->getContainer()->clear();

		return $content;
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