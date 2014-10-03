<?php
namespace Sfwassets\View\Helper
{
	class RequireJs extends \Symbic_View_Helper_RequireJs
	{
		protected static $requireScript = '/sfwassets/requirejs/script';
		protected static $mainScript = '/sfwassets/requirejs/main';
		
		protected static $config = array(
			'baseUrl' => '/sfwassets/requirejs/lib',
		);

		public function sfwassetsRequireJs()
		{
			return $this;
		}
	}
}