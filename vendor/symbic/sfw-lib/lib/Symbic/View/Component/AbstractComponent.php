<?php
abstract class Symbic_View_Component_AbstractComponent extends \Symbic_Base_AbstractSingleton
{
	protected $name				= null;
	
	protected $exports			= null;
	protected $dependencies		= null;
	
	protected $host				= null;
	protected $basePath			= null;
	protected $styles			= null;
	protected $scripts			= null;

	protected $definitionLoaded = false;

	protected function composePaths($path)
	{
		// replace \ with /
		// replace /a/../ with / etc.
		return $this->host . $this->basePath . $path;
	}

	protected function loadStyle($style)
	{
		if (isset($style['content']))
		{
			$view->headStyle()->appendStyle($style['content'], $style['attributes']);
		}
		else
		{
			$view->headStyle()->appendFile($this->composePaths($style['href']), $style['media'], $style['conditional'], $style['attributes']);
		}
	}
	
	protected function loadScript($script)
	{
		if (isset($script['content']))
		{
			$view->inlineScript()->appendScript($script['content']);
		}
		else
		{
			$view->inlineScript()->appendFile($this->composePaths($script['href']));
		}
		
	}

	public function loadDefinition(Zend_View_Interface $view)
	{
		if ($this->definitionLoaded)
		{
			return;
		}
		
		if (!empty($this->dependencies))
		{
			foreach ((array)$this->dependencies as $dependency)
			{
				$this->loadDependency($dependency);
			}
		}
		
		if (!empty($this->styles))
		{
			foreach ((array)$this->styles as $style)
			{
				$this->loadStyle($style);
			}
		}
		
		if (!empty($this->scripts))
		{
			foreach ((array)$this->scripts as $script)
			{
				$this->loadScript($script);
			}
		}

		$this->definitionLoaded = true;
 	}
}