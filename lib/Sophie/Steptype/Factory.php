<?php
class Sophie_Steptype_Factory
{
	protected static $systemInstance = null;
	
	protected $basepaths = array();
	protected $useDefaultBasepath = true;

	public static function getSystemInstance()
	{
		if (is_null(self::$systemInstance))
		{
			self::$systemInstance = new Sophie_Steptype_Factory();
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
					throw new Exception('Sophie Steptype Factory basepaths option need to be an array');
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
		return BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'steptypes';
	}	

	public function addBasepathsFromSystemConfig()
	{
		$config = Zend_Registry::get('config');
		if (isset($config['systemConfig']['sophie']['steptypePaths']))
		{
			$configBasepaths = $config['systemConfig']['sophie']['steptypePaths'];
			if (is_array($configBasepaths))
			{
				foreach ($configBasepaths as $configBasepath)
				{
					$this->addBasepath($configBasepath);
				}
			}
		}
	}
	
	private function loadFromPath($steptype, $basepath)
	{
		$steptypeClass = $steptype . '_Steptype';
		if (class_exists($steptypeClass, false))
		{
			return true;
		}

		$steptypePath = $basepath . DIRECTORY_SEPARATOR . $steptype . DIRECTORY_SEPARATOR . 'Steptype.php';
		if (!file_exists($steptypePath))
		{
			return false;
		}
		
		try
		{
			require_once($steptypePath);
		}
		catch (Exception $e)
		{
			throw new Exception('Error loading steptype module ' . $steptype . ' from ' . $basepath);
		}

		if (!class_exists($steptypeClass, false))
		{
			throw new Exception('Error loading steptype module ' . $steptype . ' from ' . $basepath . ' steptype class not found in file');
		}

		return true;
	}
	
	public function load($steptype)
	{
		$steptypeClass = $steptype . '_Steptype';
		if (class_exists($steptypeClass, false))
		{
			return true;
		}
		
		$basepaths = $this->getBasepaths();
		foreach ($basepaths as $basepath)
		{
			if ($this->loadFromPath($steptype, $basepath))
			{
				return true;
			}
		}

		if ($this->useDefaultBasepath)
		{		
			if ($this->loadFromPath($steptype, $this->getDefaultBasepath()))
			{
				return true;
			}
		}

		throw new Exception('Steptype module not found: ' . $steptype);
	}
	
	public function get($steptype, $parameters = null)
	{
		$this->load($steptype);
		
		$steptypeClass = $steptype . '_Steptype';
		try
		{
			$step = new $steptypeClass($parameters);
		}
		catch (Exception $e)
		{
			throw new Exception("This type of step is not implemented");
		}
		return $step;
	}
}