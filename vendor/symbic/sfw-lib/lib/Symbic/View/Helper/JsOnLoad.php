<?php
class Symbic_View_Helper_JsOnLoad extends Zend_View_Helper_Abstract
{
	// supported frameworks: none|dojo|jquery
	protected static $defaultFramework = 'none';
	protected static $scripts = array();

	public function jsOnLoad()
	{
		return $this;
	}

	public function prependScript($script)
	{
		// TODO: implement defer and async options
		array_unshift(self::$scripts, array('script', $script));
	}

	public function appendScript($script)
	{
		// TODO: implement defer and async options
		self::$scripts[] = array('script', $script);
	}
	
	public function prependFile($file)
	{
		// TODO: implement defer and async options
		array_unshift(self::$scripts, array('file', $file));
	}

	public function appendFile($file)
	{
		// TODO: implement defer and async options
		self::$scripts[] = array('file', $file);
	}
	
	public function render($useFramework = null)
	{
		if (sizeof(self::$scripts) == 0)
		{
			return '';
		}
		
		$content = '';
		foreach (self::$scripts as $script)
		{
			if ($script[0] == 'file')
			{
				$content .= '(function(){ var snode;';
				$content .= 'snode = document.createElement(\'script\');';
				$content .= 'snode.setAttribute(\'type\',\'text/javascript\');';
				$content .= 'snode.setAttribute(\'src\',\'' . $script[1] . '\');';
				$content .= 'document.getElementsByTagName(\'head\')[0].appendChild(snode);';
				$content .= '})();';
				continue;
			}
			
			elseif ($script[0] == 'script')
			{
				$content .= '(function(){';
				$content .= $script[1];
				$content .= '})();';
				continue;
			}			
			
			throw new Exception('Unkown script type');
		}

		if (is_null($useFramework))
		{
			$useFramework = self::$defaultFramework;
		}

		if ($useFramework == 'jquery')
		{
			// TODO: should we activate jquery here?
			return '<script>+function(){"use strict";jQuery(window).load(function(){' . $content . '});}();</script>';
		}
		elseif ($useFramework == 'dojo')
		{
			// TODO: should we activate dojo here?
			return '<script>+function(){"use strict";dojo.addOnLoad(function(){' . $content . '});}();</script>';
		}
		else
		{
			$content = '<script>+function(){"use strict";var jsOnLoad=function(){' . $content . '};';
			$content .= 'if(typeof window.onload!=\'function\'){';
			$content .= 'window.onload=jsOnLoad;';
			$content .= '}else{';
			$content .= 'var prevOnLoad=window.onload;';
			$content .= 'window.onload=function(){';
			$content .= 'if(prevOnLoad){prevOnLoad();}';
			$content .= 'jsOnLoad();';
			$content .= '};';
			$content .= '}}();</script>';
			return $content;
		}

		throw new Exception('Unkown JsOnLoad rendering framework: ' . $useFramework);
	}
	
	public function __toString()
	{
		return $this->render();
	}
}