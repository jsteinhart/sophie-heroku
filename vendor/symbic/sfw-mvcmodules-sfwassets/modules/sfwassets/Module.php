<?php
/**
 *
 */
class Sfwassets_Module extends \Symbic_Module_Standard
{
	/**
	 *
	 */
	public function bootstrap()
	{
		// add view helper
		$helperLoader = Symbic_View_Loader_Helper::getInstance();
		$helperLoader->setMap('SfwassetsCollectionUrl', '\Sfwassets\View\Helper\CollectionUrl');
		$helperLoader->setMap('SfwassetsRequireJs', '\Sfwassets\View\Helper\RequireJs');
		
		// add route
		/*
			'sfwassetsCollection'	 => array(
				'route'		 => '/sfwassets/collection/:name/*',
				'defaults'	 => array(
					'module'	 => 'sfwassets',
					'controller'	 => 'collection',
					'action'	 => 'index'
		*/

		$return		 = parent::bootstrap();
	}
}