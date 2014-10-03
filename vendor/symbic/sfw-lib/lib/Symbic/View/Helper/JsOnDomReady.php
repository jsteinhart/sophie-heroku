<?php
class Symbic_View_Helper_JsOnDomReady extends Zend_View_Helper_Abstract
{
	// supported frameworks: jquery
	protected static $defaultFramework = 'jquery';
	protected static $scripts = array();

	public function jsOnDomReady()
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
		
		$content = 'var snode;';
		foreach (self::$scripts as $script)
		{
			if ($script[0] == 'file')
			{
				$content .= 'snode = document.createElement(\'script\');';
				$content .= 'snode.setAttribute(\'type\',\'text/javascript\');';
				$content .= 'snode.setAttribute(\'src\',\'' . $script[1] . '\');';
				$content .= 'document.getElementsByTagName(\'head\')[0].appendChild(snode);';
				continue;
			}
			
			if ($script[0] == 'script')
			{
				$content .= $script[1];
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
			return '<script>+function(){"use strict";jQuery(document).ready(function(){' . $content . '});}();</script>';
		}
		
		throw new Exception('Unkown JsOnDomReady rendering framework: ' . $useFramework);
	}
	
	public function __toString()
	{
		return $this->render();
	}
}