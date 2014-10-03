<?php
class Symbic_View_Helper_TabContentPane extends Zend_View_Helper_Abstract
{
	public function tabContentPane($name, $body = '', $url = null, $preventcache = false, $preloadtab = false, $refreshonshow = false)
	{
		$html = '<div class="tab-pane" id="' . $name .'"';
		if (!is_null($url))
		{
			$html .= ' data-href="'. $url . '" 
						data-preventcache="' . $preventcache . '" 
						data-preloadtab="' . $preloadtab . '" 
						data-refreshonshow="' . $refreshonshow . '" ';
		}
		$html .= '>' . $body . '</div>';

		return $html;
	}
}