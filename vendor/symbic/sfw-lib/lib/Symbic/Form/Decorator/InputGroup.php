<?php
class Symbic_Form_Decorator_InputGroup extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$element = $this->getElement();

		if (!method_exists($element, 'getExtra'))
		{
			return $content;
		}

		$prepend = $element->getExtra('input-group-prepend');
		$append = $element->getExtra('input-group-append');

		if ($prepend === null && $append === null)
		{
			return $content;
		}
		
		if (!is_array($prepend))
		{
			$prepend = array('content' => $prepend);
		}

		if (!is_array($append))
		{
			$append = array('content' => $append);
		}

		$translator = $element->getTranslator();

		if (!empty($prepend['content']))
		{
			if ($translator !== null && (!isset($prepend['translate']) || $prepend['translate'] !== false))
			{
				$prepend['content'] = $translator->_($prepend['content']);
			}
			
			if (isset($prepend['replace']) && is_array($prepend['replace']) && sizeof($prepend['replace']) > 0)
			{
				array_unshift($append['replace'], $append['content']);
				$prepend['content'] = call_user_func_array('sprintf', $prepend['replace']);
			}
		}

		if (!empty($append['content']))
		{
			if ($translator !== null && (!isset($append['translate']) || $append['translate'] !== false))
			{
				$append['content'] = $translator->_($append['content']);
			}

			if (isset($append['replace']) && is_array($append['replace']) && sizeof($append['replace']) > 0)
			{
				array_unshift($append['replace'], $append['content']);
				$append['content'] = call_user_func_array('sprintf', $append['replace']);
			}
		}

		if (empty($prepend['content']) && empty($append['content']))
		{
			return $content;
		}
		
		if (!empty($prepend['content']))
		{
			$content = '<span class="input-group-addon">' . $prepend['content'] . '</span>' . $content;
		}

		if (!empty($append['content']))
		{
			$content .= '<span class="input-group-addon">' . $append['content'] . '</span>';
		}

		return  '<div class="input-group">' . $content . '</div>';
	}
}