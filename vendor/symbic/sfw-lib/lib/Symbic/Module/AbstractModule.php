<?php

/**
 *
 */
abstract class Symbic_Module_AbstractModule
{
	// zend1 -> Modulename_Resourcename_NAME_NAME2
	// namespace -> Modulename\Resourcename\NAME\NAME2
	protected $resourceClassNaming = 'namespace';

	/**
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 *
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @var Boolean
	 */
	protected $bootstraped = false;

	/**
	 *
	 * @var Symbic_Module_Manager
	 */
	protected $moduleManager;

	/**
	 *
	 * @var type
	 */
	protected $moduleOptions;

	/**
	 *
	 * @var type
	 */
	protected $translator;

	/**
	 *
	 * @var type
	 */
	protected $moduleConfig;

	/**
	 *
	 * @param type $name
	 * @param array $options
	 */
	public function __construct($name, array $options = array())
	{
		$className	 = get_class($this);
		$this->namespace = substr($className, 0, strrpos($className, '_'));
		$this->name	 = $name;

		if (sizeof($options) > 0)
		{
			$this->setOptions($options);
		}
	}

	/**
	 *
	 * @param type $basePath
	 * @return \Symbic_Module
	 * @throws Exception
	 */
	protected function setBasePath($basePath)
	{
		$this->basePath = realpath($basePath);
		if ($this->basePath === false)
		{
			throw new Exception('Module basepath does not exist: ' . $basePath);
		}
		return $this;
	}

	/**
	 *
	 * @return type
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/**
	 *
	 * @param type $name
	 * @return \Symbic_Module
	 */
	protected function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 *
	 * @return type
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @param type $namespace
	 * @return \Symbic_Module
	 */
	protected function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}

	/**
	 *
	 * @return type
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 *
	 * @param array $options
	 * @return \Symbic_Module
	 */
	protected function setOptions(array $options)
	{
		if (isset($options['basePath']))
		{
			$this->setBasePath($options['basePath']);
		}

		if (isset($options['namespace']))
		{
			$this->setNamespace($options['namespace']);
		}

		if (isset($options['moduleManager']))
		{
			$this->setModuleManager($options['moduleManager']);
		}
		return $this;
	}

	/**
	 *
	 * @param Symbic_Module_Manager $moduleManager
	 * @return \Symbic_Module
	 */
	protected function setModuleManager(Symbic_Module_Manager $moduleManager)
	{
		$this->moduleManager = $moduleManager;
		return $this;
	}

	/**
	 *
	 * @return type
	 * @throws Exception
	 */
	protected function getModuleManager()
	{
		if ($this->moduleManager !== null)
		{
			return $this->moduleManager;
		}
		throw new Exception('Module is not attached to a module manager');
	}

	/**
	 * Bootstrap the module
	 *
	 * @return null
	 */
	public function bootstrap()
	{
		if ($this->bootstraped)
		{
			return;
		}

		$frontController = $this->getModuleManager()->getFrontController();
		$frontController->addControllerDirectory($this->getBasePath() . '/controllers', $this->getName());
		
		$this->bootstraped = true;
	}

	/**
	 * Get module default config from the module package
	 *
	 * @return array
	 * @throws Exception
	 */
	final protected function getModuleDefaultConfig()
	{
		// try to get config in php format
		$configPath = $this->getBasePath() . '/configs/module.default.php';
		if (file_exists($configPath))
		{
			try
			{
				$config = include($configPath);
			}
			catch (Exception $e)
			{
				throw new Exception('Module Default Config File could not be loaded');
			}
			return $config;
		}

		// fallback to xml config
		$configPath = $this->getBasePath() . '/configs/module.default.xml';
		if (file_exists($configPath))
		{
			$configString = file_get_contents($configPath);
			if ($configString === FALSE)
			{
				throw new Exception('Module Default Config File could not be loaded');
			}

			try
			{
				$config	 = new Zend_Config_Xml($configString);
				$config	 = $config->toArray();
			}
			catch (Exception $e)
			{
				throw new Exception('Module Default Config could not be loaded', null, $e);
			}
			return $config;
		}
		return array();
	}

	/**
	 * Get application level default config for module
	 *
	 * @return array
	 * @throws Exception
	 */
	final protected function getModuleApplicationDefaultConfig()
	{
		// try to get config in php format
		$configPath = APPLICATION_PATH . '/configs/' . $this->getName() . '.default.php';
		if (file_exists($configPath))
		{
			try
			{
				$config = include($configPath);
			}
			catch (Exception $e)
			{
				throw new Exception('Module Application Default Config File could not be loaded');
			}
			return $config;
		}

		// fallback to xml format
		$configPath = APPLICATION_PATH . '/configs/' . $this->getName() . '.default.xml';
		if (file_exists($configPath))
		{
			$configString = file_get_contents($configPath);
			if ($configString === FALSE)
			{
				throw new Exception('Module Application Default Config File could not be loaded');
			}

			try
			{
				$config	 = new Zend_Config_Xml($configString);
				$config	 = $config->toArray();
			}
			catch (Exception $e)
			{
				throw new Exception('Module Application Default Config could not be loaded', null, $e);
			}
			return $config;
		}
		return array();
	}

	/**
	 * Get application level local config for module
	 *
	 * @return array
	 * @throws Exception
	 */
	final protected function getModuleApplicationConfig()
	{
		$configPath = APPLICATION_PATH . '/configs/' . $this->getName() . '.php';
		if (file_exists($configPath))
		{
			try
			{
				$config = include($configPath);
			}
			catch (Exception $e)
			{
				throw new Exception('Module Config File could not be loaded');
			}
			return $config;
		}

		$configPath = APPLICATION_PATH . '/configs/' . $this->getName() . '.xml';
		if (file_exists($configPath))
		{
			$configString = file_get_contents($configPath);
			if ($configString === FALSE)
			{
				throw new Exception('Module Config File could not be loaded');
			}

			try
			{
				$config	 = new Zend_Config_Xml($configString);
				$config	 = $config->toArray();
			}
			catch (Exception $e)
			{
				throw new Exception('Module Config could not be loaded', null, $e);
			}
			return $config;
		}
		return array();
	}

	/**
	 * Returns the module config as an array
	 *
	 * @return array
	 */
	final public function getModuleConfig()
	{
		$moduleName = $this->getName();

		if (Zend_Registry::isRegistered('ModuleConfig_' . $moduleName))
		{
			return Zend_Registry::get('ModuleConfig_' . $moduleName);
		}

		if (APPLICATION_ENV == 'production')
		{
			$cache		 = Zend_Registry::get('Zend_Cache');
			$moduleConfig	 = $cache->load('ModuleConfig_' . $moduleName);
			if ($moduleConfig !== false && is_array($moduleConfig))
			{
				Zend_Registry::set('ModuleConfig_' . $moduleName, $moduleConfig);
				return $moduleConfig;
			}
		}

		// get default config
		$moduleConfig	 = $this->getModuleDefaultConfig();
		$moduleConfig	 = array_replace_recursive($moduleConfig, $this->getModuleApplicationDefaultConfig());
		$moduleConfig	 = array_replace_recursive($moduleConfig, $this->getModuleApplicationConfig());

		if (APPLICATION_ENV == 'production' && is_array($moduleConfig))
		{
			$cache->save($moduleConfig, 'ModuleConfig_' . $moduleName);
		}

		Zend_Registry::set('ModuleConfig_' . $moduleName, $moduleConfig);
		return $moduleConfig;
	}

	/**
	 *
	 * @param type $formName
	 * @param type $options
	 * @return \Zend_Form
	 * @throws Exception
	 */
	final public function getForm($formName, $options = array())
	{
		if ($this->resourceClassNaming == 'namespace')
		{
			$formClassName = ucfirst('\\' . $this->getNamespace()) . '\\Form\\' . ucfirst(str_replace('_', '\\', $formName));
		}
		else
		{
			$formClassName = ucfirst($this->getNamespace()) . '_Form_' . ucfirst($formName);
		}

		if (!is_array($options))
		{
			throw new Exception('Only array options parameter is allowed for getForm');
		}

		if (!empty($options['noModuleTranslator']))
		{
			$noModuleTranslator = true;
			unset($options['noModuleTranslator']);
		}
		else
		{
			$noModuleTranslator = false;
		}

		try
		{
			$form = new $formClassName($options);
		}
		catch (Exception $e)
		{
			throw new Exception('Failed initializing form ' . $formName . ' in module ' . $this->getNamespace(), null, $e);
		}
		if (!$noModuleTranslator)
		{
			try
			{
				$form->setTranslator($this->getTranslator());
			}
			catch (Exception $e)
			{
				throw new Exception('Failed initializing form translator ' . $formName . ' in module ' . $this->getNamespace(), null, $e);
			}
		}

		if (method_exists($form, 'setModule'))
		{
			$form->setModule($this);
		}

		return $form;
	}

	/**
	 *
	 * @param type $modelName
	 * @return object
	 * @throws Exception
	 */
	final public function getModel($modelName)
	{
		if ($this->resourceClassNaming == 'namespace')
		{
			$modelClassName = ucfirst('\\' . $this->getNamespace()) . '\\Model\\' . ucfirst(str_replace('_', '\\', $modelName));
		}
		else
		{
			$modelClassName = ucfirst($this->getNamespace()) . '_Model_' . ucfirst($modelName);
		}

		$args = func_get_args();
		array_shift($args);

		try
		{
			$ref	 = new ReflectionClass($modelClassName);
			$model	 = $ref->newInstanceArgs($args);
			if (method_exists($model, 'setModule'))
			{
				$model->setModule($this);
			}
			return $model;
		}
		catch (Exception $e)
		{
			throw new Exception('Failed initializing model ' . $modelClassName);
		}
	}

	/**
	 *
	 * @param type $modelName
	 * @return object
	 * @throws Exception
	 */
	final public function getModelSingleton($modelName)
	{
		if ($this->resourceClassNaming == 'namespace')
		{
			$modelClassName = ucfirst('\\' . $this->getNamespace()) . '\\Model\\' . ucfirst(str_replace('_', '\\', $modelName));
		}
		else
		{
			$modelClassName = ucfirst($this->getNamespace()) . '_Model_' . ucfirst($modelName);
		}

		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		try
		{
			$model = call_user_func_array($modelClassName . '::getInstance', $args);
		}
		catch (Exception $e)
		{
			throw new Exception('Failed initializing model ' . $modelClassName);
		}

		if (method_exists($model, 'setModule'))
		{
			$model->setModule($this);
		}
		return $model;
	}

	/**
	 *
	 * @return Zend_Translate
	 * @throws Exception
	 */
	final public function getTranslator()
	{
		if (is_null($this->translator))
		{
			$this->translator = new Zend_Translate(
				array(
				'adapter'	 => 'array',
				'disableNotices' => true
				)
			);

			$cache = Zend_Registry::get('Zend_Cache');
			if (!is_null($cache))
			{
				$this->translator->setCache($cache);
			}

			$translationAdded = false;

			$languageFilesPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
			if (is_dir($languageFilesPath))
			{
				$languageFiles = scandir($languageFilesPath);
				if (sizeof($languageFiles) > 2)
				{
					foreach ($languageFiles as $languageFile)
					{
						if ($languageFile == '.' || $languageFile == '..')
						{
							continue;
						}
						$fileLocale		 = substr($languageFile, 0, strrpos($languageFile, '.'));
						$this->translator->addTranslation(array(
							'content'	 => $languageFilesPath . $languageFile,
							'locale'	 => $fileLocale
						));
						$translationAdded	 = true;
					}
				}
			}

			// add application level translations
			$languageFilesPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . strtolower($this->getNamespace()) . DIRECTORY_SEPARATOR;
			if (is_dir($languageFilesPath))
			{
				$languageFiles = scandir($languageFilesPath);
				if (sizeof($languageFiles) > 2)
				{
					foreach ($languageFiles as $languageFile)
					{
						if ($languageFile == '.' || $languageFile == '..')
						{
							continue;
						}
						$fileLocale		 = substr($languageFile, 0, strrpos($languageFile, '.'));
						$this->translator->addTranslation(array(
							'content'	 => $languageFilesPath . $languageFile,
							'locale'	 => $fileLocale
						));
						$translationAdded	 = true;
					}
				}
			}


			if (!$translationAdded)
			{
				$this->translator->addTranslation(array(
					'content'	 => array(
						'_' => '_'
					),
					'locale'	 => 'en_US'
				));
			}

			$this->translator->setLocale(Zend_Registry::get('Zend_Locale'));
		}
		return $this->translator;
	}

	/**
	 *
	 * @return Symbic_User_Session
	 */
	public function getUserSession()
	{
		return Symbic_User_Session::getInstance();
	}

}
