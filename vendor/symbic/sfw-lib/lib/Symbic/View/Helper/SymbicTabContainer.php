<?php
class Symbic_View_Helper_SymbicTabContainer extends Zend_View_Helper_Abstract
{
	/*
	 * needs an array(
	 * 		array(
	 * 			'name' => the name for internal linking,
	 * 			'title' => the display name,
	 * 			'body' => the body content,
	 * 			'url' => for dynamic content loading, if used body will be overwritten ontabActivation
	 * 			//if Url given you can set the options:
	 * 			'preventcache' => boolean for caching,
	 * 			'preloadtab' => boolean for loading tab on start,
	 * 			'refreshonshow' => boolean for reloading on show,
	 * 		),
	 * 		array( ... ),
	 * )
	 */
	public function symbicTabContainer( $id ,$tabs, $activeTab, $htmlAttribs = array())
	{
		//Init variables to set optional values
		$selected = false;
		$preventcache = false; $preloadtab = false; $refreshonshow = false;
		$url = $body = '';

		//setup NavContent
		$navContent = '';
		$tabContent = '';
		foreach ($tabs as $tab)
		{
			extract($tab);
			$navContent .= $this->view->tabNavigationPane($name, $title);
			$tabContent .= $this->view->tabContentPane($name, $body, $url);
		}

		$html = '<div id="'. $id .'">'.
		 			$this->view->tabNavigationContainer($id.'_nav', $navContent, $activeTab, $htmlAttribs) .
					$this->view->tabContentContainer($id.'_div', $tabContent, $htmlAttribs).
				'</div>';
		return $html;
	}
}