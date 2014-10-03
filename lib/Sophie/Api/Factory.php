<?php
class Sophie_Api_Factory
{
	protected static $systemInstance = null;
	
	protected $basepaths = array();
	protected $useDefaultBasepath = true;

	public static function getSystemInstance()
	{
		if (is_null(self::$systemInstance))
		{
			self::$systemInstance = new Sophie_Api_Factory();
			self::$systemInstance->addBasepathsFromSystemConfig();
		}
		return self::$systemInstance;
	}
	
	public function __construct($options = null)
	{
		if (is_array($options))
		{
			if (isset($options['useDefaultBasepath']))
			{
				$this->useDefaultBasepath = (bool)$options['useDefaultBasepath'];
			}
			
			if (isset($options['basepaths']))
			{
				if (!is_array($options['basepaths']))
				{
					throw new Exception('Sophie Api Factory basepaths option need to be an array');
				}

				$this->setBasepaths($options['basepaths']);
			}
		}
	}
	
	public function getBasepaths()
	{
		return $this->basepaths;
	}

	public function setBasepaths($basepaths)
	{
		$this->basepaths = $basepaths;
	}

	public function addBasepath($basepath)
	{
		$this->basepaths[] = $basepath;
	}	

	public function getDefaultBasepath()
	{
		return BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'apis';
	}	

	public function addBasepathsFromSystemConfig()
	{
		$config = Zend_Registry::get('config');
		if (isset($config['systemConfig']['sophie']['apiPaths']))
		{
			$configBasepaths = $config['systemConfig']['sophie']['apiPaths'];
			if (is_array($configBasepaths))
			{
				foreach ($configBasepaths as $configBasepath)
				{
					$this->addBasepath($configBasepath);
				}
			}
		}
	}
	
	private function loadFromPath($api, $basepath)
	{
		$apiClass = $api . '_Api';
		if (class_exists($apiClass, false))
		{
			return true;
		}

		$apiPath = $basepath . DIRECTORY_SEPARATOR . $api . DIRECTORY_SEPARATOR . 'Api.php';
		if (!file_exists($apiPath))
		{
			return false;
		}
		
		try
		{
			require_once($apiPath);
		}
		catch (Exception $e)
		{
			throw new Exception('Error loading api module ' . $api . ' from ' . $basepath);
		}

		if (!class_exists($apiClass, false))
		{
			throw new Exception('Error loading api module ' . $api . ' from ' . $basepath . ' api class not found in file');
		}

		return true;
	}
	
	public function load($api)
	{
		$apiClass = $api . '_Api';
		if (class_exists($apiClass, false))
		{
			return true;
		}
		
		$basepaths = $this->getBasepaths();
		foreach ($basepaths as $basepath)
		{
			if ($this->loadFromPath($api, $basepath))
			{
				return true;
			}
		}

		if ($this->useDefaultBasepath)
		{		
			if ($this->loadFromPath($api, $this->getDefaultBasepath()))
			{
				return true;
			}
		}

		throw new Exception('Api module not found: ' . $api);
	}
	
	public function get($api, Sophie_Context $context, $parameters = null)
	{
		$this->load($api);
		
		$apiClass = $api . '_Api';
		try
		{
			$api = new $apiClass($context, $parameters);
		}
		catch (Exception $e)
		{
			throw new Exception("This api is not implemented");
		}
		return $api;
	}

	public function scanApiDirectories()
	{
		$apis = array();
		$basepaths = $this->getBasepaths();
		if ($this->useDefaultBasepath)
		{
			$basepaths[] = $this->getDefaultBasepath();
		}
		foreach ($basepaths as $basepath)
		{
			echo $basepath;
			$dirs = glob($basepath . DIRECTORY_SEPARATOR . $className . '*_Api_*_*_*_*', GLOB_ONLYDIR);
			foreach ($dirs as $dir)
			{
				$apis[] = basename($dir);
			}
		}
		return $apis;
	}
	
	public function findLastApiVersion($className)
	{
		$highestVersion = null;
		$prefixLength = strlen($className);
		$basepaths = $this->getBasepaths();
		if ($this->useDefaultBasepath)
		{
			$basepaths[] = $this->getDefaultBasepath();
		}

		foreach ($basepaths as $basepath)
		{
			$dirs = glob($basepath . DIRECTORY_SEPARATOR . $className . '_*_*_*', GLOB_ONLYDIR);
			if ($dirs === false)
			{
				continue;
			}

			foreach ($dirs as $dir)
			{
				$version = substr(basename($dir), $prefixLength + 1);
				$version = str_replace('_', '.', $version);

				if (is_null($highestVersion))
				{
					$highestVersion = $version;
					continue;
				}

				if (version_compare($version, $highestVersion) > 0)
				{
					$highestVersion = $version;
					continue;
				}
			}
		}
		return $highestVersion;
	}
}