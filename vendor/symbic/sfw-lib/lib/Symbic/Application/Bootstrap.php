<?php
class Symbic_Application_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initMyCache()
	{
		$cache = Zend_Registry :: get('Zend_Cache');
		if (!is_null($cache))
		{
			Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
			Zend_Locale::setCache($cache);
		}
	}

	protected function _initMySession()
	{
		if (defined('CLI_CALL') && CLI_CALL)
		{
			return;
		}
		$this->bootstrap('session');
		$session = $this->getResource('session');
		Zend_Session::start();
	}

	protected function _initMyLocale()
	{
		$this->bootstrap('locale');
		$locale = $this->getResource('locale');
		Zend_Registry::set('locale', $locale);
		Zend_Registry::set('Zend_Locale', $locale);
	}

	protected function _initMyView()
	{
		$options = $this->getOptions();

		if (!isset($options['resources']['view']) || !isset($options['resources']['view']['encoding']))
		{
			$options['resources']['view']['encoding'] = 'utf-8';
		}

		$this->bootstrap('view');
		$view = $this->getResource('view');

		$helperLoader = Symbic_View_Loader_Helper::getInstance();
		$helperLoader->setMap('ApplicationBreadcrumbs', 'Application\View\Helper\ApplicationBreadcrumbs');
		$helperLoader->setMap('ApplicationNavigation', 'Application\View\Helper\ApplicationNavigation');

		$view->doctype('HTML5');

		$view->headTitle()->setSeparator(' - ');

		$headMeta = $view->headMeta();
		$headMeta->appendHttpEquiv('Imagetoolbar', 'false');
		$headMeta->appendName('robots', 'all');

		$headMeta->appendHttpEquiv('Content-Type', 'text/html; charset=' . $options['resources']['view']['encoding']);

		Zend_Registry::set('Zend_View', $view);
		Zend_Registry::set('view', $view);
	}

	protected function _initMyDojo()
	{
		$this->bootstrap('myview');
		$view = $this->getResource('view');

		$options = $this->getOptions();
		if(isset($options['resources']['dojo']))
		{
			$this->bootstrap('dojo');

			Zend_Dojo_View_Helper_Dojo::setUseDeclarative();

			$dojoHelper = $view->dojo();
			$dojoHelper->addStyleSheetModule('dijit.themes.claro');

			$this->bootstrap('locale');
			$locale = $this->getResource('locale');
			$dojoHelper->setDjConfigOption('locale', strtolower(str_replace('_', '-', $locale->toString())));
		}
	}

	protected function _initMyDb()
	{
		$this->bootstrap('db');
		$db = $this->getResource('db');
		Zend_Registry::set('db', $db);
	}

	protected function _initMyFrontController()
	{
		$options = $this->getOptions();

		$this->bootstrap('frontController');
		$front = $this->getResource('frontController');

		if (isset($options['resources']) && isset($options['resources']['view']) && isset($options['resources']['view']['encoding']))
		{
			// instead get view and use getEncoding()
			$response = new Zend_Controller_Response_Http();
			$response->setHeader('Content-Type', 'text/html; charset=' . $options['resources']['view']['encoding']);
			$front->setResponse($response);
		}
	}

	protected function _initMyTranslate()
	{
		$config = $this->getOptions();
		if(!isset($config['systemConfig']['translation']))
		{
			return;
		}
		$tc = $config['systemConfig']['translation'];

		$this->bootstrap('myFrontController');
		$this->bootstrap('myLocale');

		if(isset($tc['routing']))
		{

			if((int)($tc['routing']['enabled']) == 1)
			{
				$front = Zend_Controller_Front::getInstance();
				$front->registerPlugin(new Symbic_Translate_Plugin_LangSelector());
				$router = $front->getRouter();

				$defaultRoute = new Zend_Controller_Router_Route(
						':lang/:module/:controller/:action/*',
						array(
								'lang' => $tc['routing']['defaultLocale'],
								'module'=>'public',
								'controller'=>'index',
								'action'=>'index'
						)
				);

				$router->addRoute('default', $defaultRoute);
			}
		}
		if((int)($tc['enabled']) == 1)
		{
			//create Zend_Translate object
			$translate = new Zend_Translate(array(
					'adapter' => 'array',
					'disableNotices' => true
			)
			);
			if(isset($tc['useChache']) && (int)($tc['enabled']) == 1)
			{
				$cache = Zend_Registry :: get('Zend_Cache');
				if (!is_null($cache))
				{
					Zend_Translate::setCache($cache);
				}
			}
			if(!is_dir($tc['defaults']['folder']))
			{
				mkdir($tc['defaults']['folder']);
			}

			foreach($tc['defaults']['languages'] as $langKey => $langValue)
			{
				$filename = $tc['defaults']['folder']. DIRECTORY_SEPARATOR . $langValue.'.php';
				if(!is_file($filename))
				{
					$stream = fopen($filename, 'ca');
					if(fwrite($stream, '<?php return array ( ); ?>') == false)
					{
						throw new Exception('Creating translation object failed');
					}
				}

				$translate->addTranslation(array(
						'content' => $filename,
						'locale'  => $langKey
				)
				);
			}
			//set Locale
			$translate->setLocale(Zend_Registry::get('Zend_Locale'));

			if(isset($tc['log']))
			{
				if((int)($tc['log']['enabled']) == 1)
				{
					//set path for file
					$path = $tc['log']['logfile'];

					//Open filestream to logfile
					$stream = @fopen($path, 'ac', false);
					if (! $stream) {
						throw new Exception('Stream konnte nicht geöffnet werden');
					}
					$streamWriter = new Zend_Log_Writer_Stream($stream);

					//format messages in csv format
					$formatter = new Zend_Log_Formatter_Simple('%message%' . PHP_EOL);
					$streamWriter->setFormatter($formatter);

					// create Logger instance
					$logger = new Zend_Log();

					//add writer to logger
					$logger->addWriter($streamWriter);

					//set logger to translate object
					$translate->setOptions( array(
							'log' => $logger,
							'logUntranslated' => true,
							'logMessage'      => "%locale%;%message%",
					)
					);
				}
			}
			//Save translation object in registry
			Zend_Registry::set('Zend_Translate', $translate);
		}
	}

	protected function _initMyEventManager()
	{
		$eventManager = new Zend_EventManager_EventManager();
		Zend_Registry::set('eventManager', $eventManager);
		Zend_Registry::set('Zend_EventManager', $eventManager);
	}

	public function run()
	{
		defined('CLI_CALL') || define('CLI_CALL', (php_sapi_name() === 'cli'));

		if (CLI_CALL)
		{
			$this->runCli();
		}
		else
		{
			$this->runWeb();
		}
	}

	protected function bootstrapCli()
	{
		$skipResources = array('myview', 'myfrontcontroller', 'mysession', 'myform', 'router', 'layout', 'session', 'frontcontroller', 'dojo', 'view');

		$resources = $this->getClassResourceNames();
		foreach ($resources as $resource)
		{
			if (!in_array($resource, $skipResources))
			{
				$this->bootstrap($resource);
			}
		}

		$resources = $this->getPluginResourceNames();
		foreach ($resources as $resource)
		{
			if (!in_array($resource, $skipResources))
			{
				$this->bootstrap($resource);
			}
		}
	}

	public function runCli()
	{
		$this->bootstrapCli();
		// load tasks config
		$tasksConfig = array();

		$configFile = APPLICATION_CONFIG_PATH . DIRECTORY_SEPARATOR . 'tasks.default.php';
		if (file_exists($configFile))
		{
			$tasksConfig = array_replace_recursive($tasksConfig, require($configFile));
		}

		$configFile = APPLICATION_CONFIG_PATH . DIRECTORY_SEPARATOR . 'tasks.php';
		if (file_exists($configFile))
		{
			$tasksConfig = array_replace_recursive($cliConfig, require($configFile));
		}

		// run task
		$runner = new Symbic_Task_Runner_Cli($tasksConfig);
		$runner->run();
	}

	public function runJobQueue()
	{
		$this->bootstrapCli();
		// load tasks config
		$tasksConfig = array();

		$configFile = APPLICATION_CONFIG_PATH . DIRECTORY_SEPARATOR . 'tasks.default.php';
		if (file_exists($configFile))
		{
			$tasksConfig = array_replace_recursive($tasksConfig, require($configFile));
		}

		$configFile = APPLICATION_CONFIG_PATH . DIRECTORY_SEPARATOR . 'tasks.php';
		if (file_exists($configFile))
		{
			$tasksConfig = array_replace_recursive($cliConfig, require($configFile));
		}

		// run task
		$runner = new Symbic_Task_Runner($tasksConfig);
		$runner->runOpenJobs();
	}

	public function runWeb()
	{
		// bootstrap webapplication resources
		// at the moment bootstrap everything anyway
		// TODO: remove this when lazy init is used throughout the application
		$this->bootstrap();

		// start the session right now
		// TODO: check if we need to start the session here or manually at all
		Zend_Session::start();

		parent::run();
	}
}