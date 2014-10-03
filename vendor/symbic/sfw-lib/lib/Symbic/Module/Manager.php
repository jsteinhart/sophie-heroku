<?php

class Symbic_Module_Manager extends Symbic_Singleton
{

	protected $application;
	protected $bootstrap;
	protected $eventManager;
	protected $autoloader;
	protected $applicationConfig;
	protected $cache;
	protected $modulesBasePath;
	protected $modules = array();

	public function setOptions($options)
	{
		if (!isset($options['modulesBasePath']))
		{
			$this->modulesBasePath = BASE_PATH . '/modules';
		}
		else
		{
			$this->modulesBasePath = $options['modulesBasePath'];
		}

		if (isset($options['modules']))
		{
			$this->addModules($options['modules']);
		}
	}

	public function setBootstrap($bootstrap)
	{
		$this->bootstrap = $bootstrap;
		return $this;
	}

	public function getBootstrap()
	{
		return $this->bootstrap;
	}

	public function setApplication($application)
	{
		$this->application = $application;
		return $this;
	}

	public function getApplication()
	{
		return $this->application;
	}

	public function setAutoloader($autoloader)
	{
		$this->autoloader = $autoloader;
		return $this;
	}

	public function getAutoloader()
	{
		return $this->autoloader;
	}

	public function setEventManager($eventManager)
	{
		$this->eventManager = $eventManager;
		return $this;
	}

	public function getEventManager()
	{
		return $this->eventManager;
	}

	public function setApplicationConfig($applicationConfig)
	{
		$this->applicationConfig = $applicationConfig;
		return $this;
	}

	public function getApplicationConfig()
	{
		return $this->applicationConfig;
	}

	public function setCache($cache)
	{
		$this->cache = $cache;
		return $this;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getFrontController()
	{
		return Zend_Controller_Front::getInstance();
	}

	public function addModules(array $modules)
	{
		foreach ($modules as $moduleName => $moduleOptions)
		{
			$this->addModule($moduleName, $moduleOptions);
		}
		return $this;
	}

	public function addModule($moduleName, array $moduleOptions)
	{
		if (isset($this->modules[$moduleName]))
		{
			throw Exception('Module can not be added more than once');
		}

		if (!isset($moduleOptions['namespace']))
		{
			$moduleOptions['namespace'] = ucfirst($moduleName);
		}
		else
		{
			$moduleOptions['namespace'] = ucfirst($moduleOptions['namespace']);
		}

		if (!isset($moduleOptions['basePath']))
		{
			$moduleOptions['basePath'] = $this->modulesBasePath . '/' . strtolower($moduleOptions['namespace']);
		}

		$basePath = realpath($moduleOptions['basePath']);
		if ($basePath === false)
		{
			throw new Exception('Module base path does not exist: ' . $moduleName . ' - ' . $moduleOptions['basePath']);
		}

		$moduleClass = $moduleOptions['namespace'] . '_Module';
		if (!class_exists($moduleClass))
		{
			if (!file_exists($basePath . '/Module.php'))
			{
				$moduleClass = 'Symbic_Module';
			}
			else
			{
				try
				{
					require($basePath . '/Module.php');
				}
				catch (Exception $e)
				{
					throw new Exception('Module can not be initialized');
				}
			}
		}

		$moduleOptions['moduleManager'] = $this;

		$module				 = new $moduleClass($moduleName, $moduleOptions);
		$this->modules[$moduleName]	 = $module;

		return $this;
	}

	public function getModules()
	{
		return $this->modules;
	}

	public function getModule($moduleName)
	{
		if (isset($this->modules[$moduleName]))
		{
			return $this->modules[$moduleName];
		}
		else
		{
			return null;
		}
	}

	public function bootstrapModule($moduleName)
	{
		$module = $this->getModule($moduleName);
		if (!is_null($module))
		{
			$module->bootstrap();
		}
	}

	public function bootstrapModules()
	{
		foreach ($this->getModules() as $module)
		{
			$module->bootstrap();
		}
		return $this;
	}
}