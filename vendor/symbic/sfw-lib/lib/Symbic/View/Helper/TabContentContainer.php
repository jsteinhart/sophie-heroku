<?php
class Symbic_View_Helper_TabContentContainer extends Zend_View_Helper_Abstract
{
	public function tabContentContainer($name, $tabs, $htmlAttribs = array())
	{
		$class = 'tab-content';
		if(!empty($htmlAttribs['class']))
		{
			$class .= $htmlAttribs['class'];
			unset($htmlAttribs['class']);
		}
		$html = '<div class="'. $class .'"';

		foreach($htmlAttribs as $attrib => $value)
		{
			$html .= ' '. $attrib .'="'.$value.'"';
		}
		$html .= '>' . $tabs . '</div>';

		return $html;
	}
}