<?php
class Symbic_View_Helper_ButtonLink extends Symbic_View_Helper_Button
{
	public function buttonLink($content, $href = '#', $style = null, $size = null, $active = false, $attribs = array())
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

		$attribs['role'] = 'button';

		return $this->view->link($content, $href, $attribs);
	}
}