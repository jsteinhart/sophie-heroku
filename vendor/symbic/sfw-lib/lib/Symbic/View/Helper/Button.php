<?php
class Symbic_View_Helper_Button extends Zend_View_Helper_HtmlElement
{
	public function getButtonClasses($style, $size, $active)
	{
		$class = array('btn');

		if (in_array($style, array('primary', 'success', 'info', 'warning', 'danger', 'link')))
		{
			$class[] = 'btn-' . $style;
		}
		else
		{
			$class[] = 'btn-default';
		}
		
		if (in_array($size, array('lg', 'sm', 'xs')))
		{
			$class[] = 'btn-' . $size;
		}

		if ($active === true)
		{
			$class[] = 'active';
		}

		if (!empty($attribs['disabled']))
		{
			$class[] = 'disabled';
		}

		return $class;
	}

	public function button($content, $onClick = null, $style = null, $size = null, $active = false, $attribs = array())
	{
		$class = $this->getButtonClasses($style, $size, $active);

		if (!empty($attribs['class']))
		{
			foreach ((array)$attribs['class'] as $className)
			{
				$class[] = $className;
			}
		}
		$attribs['class'] = $class;
		
		if (!empty($attribs['disabled']))
		{
			$attribs['disabled'] = 'disabled';
		}

		$html = '<button ';

		if (!empty($onClick))
		{
			$html .= 'onclick="' . $onClick . '" ';
		}

		$html .= $this->_htmlAttribs($attribs) . '>';
		$html .= $content;
		$html .= '</button>';
		return $html;
	}
}