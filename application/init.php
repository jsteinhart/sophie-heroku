<?php
/*
 * Symbic Framework Application Bootstrap
 *
 * Uses the following constants:
 *
 * CLI_CALL = false
 * START_TIME = microtime(true)
 * BASE_PATH = parent dir of file location
 * APPLICATION_PATH = BASE_PATH . /application
 * APPLICATION_CONFIG_PATH = APPLICATION_PATH . /configs
 * VAR_PATH = BASE_PATH . /var
 * VENDOR_PATH = BASE_PATH . /vendor
 *
 * APPLICATION_ENV = env() or production
 * APPLICATION_SESSION_HANDLER = env() or file
 * APPLICATION_SESSION_HANDLER_SAVE_PATH = env() or tcp://127.0.0.1:11211 for memcache handler / not set file handler and emtpy
 * APPLICATION_CACHE_BACKEND = env() or file
 * APPLICATION_CACHE_PREFIX = env() or null
 *
 * for memcache handler:
 * APPLICATION_CACHE_HOST = env() or 127.0.0.1
 * APPLICATION_CACHE_PORT = env() or 11211
 *
 * for file handler:
 * APPLICATION_CACHE_PATH = env() or VAR_PATH . /cache
 * APPLICATION_CACHE_NAMESPACE = env() or applicationCache_
 */

defined('CLI_CALL') || define('CLI_CALL', false);
defined('START_TIME') || define('START_TIME', microtime(true));

// init path constants
$basepath = dirname(__DIR__);
defined('BASE_PATH') || define('BASE_PATH', realpath($basepath));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', BASE_PATH . '/application');
defined('APPLICATION_CONFIG_PATH') || define('APPLICATION_CONFIG_PATH', APPLICATION_PATH . '/configs');
defined('VAR_PATH') || define('VAR_PATH', BASE_PATH . '/var');
defined('VENDOR_PATH') || define('VENDOR_PATH', BASE_PATH . '/vendor');

// change directory to BASE DIR
chdir(BASE_PATH);

// check if system offline file is present
if (file_exists(VAR_PATH . '/run/offline'))
{
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 7200'); // in seconds

	$offlinePageFile = APPLICATION_PATH . '/layouts/scripts/offline.html';
	if (file_exists($offlinePageFile))
	{
		readfile($offlinePageFile);
	}
	else
	{
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 300');
		echo '<html><body><h1>Sorry!</h1><p>The service is temporarily unavailable. Please visit this page again later.<p></body></html>';
	}
	exit;
}

// init application environment
if (!defined('APPLICATION_ENV'))
{
	$env = getenv('APPLICATION_ENV');
	if (empty($env))
	{
		define('APPLICATION_ENV', 'production');
	}
	else
	{
		define('APPLICATION_ENV', $env);
	}
}

// Set PHP settings
if (APPLICATION_ENV === 'development')
{
	ini_set('display_startup_errors',		1);
	ini_set('display_errors',				1);
}
else
{
	ini_set('display_startup_errors',		0);
	ini_set('display_errors',				0);
}

error_reporting(-1);
ini_set('session.auto-start',			0);
ini_set('session.gc_probability',		0);
ini_set('short_open_tag',				1);

// init session handler
if (!defined('APPLICATION_SESSION_HANDLER'))
{
	$handler = getenv('APPLICATION_SESSION_HANDLER');
	if (empty($handler))
	{
		define('APPLICATION_SESSION_HANDLER', 'file');
	}
	else
	{
		define('APPLICATION_SESSION_HANDLER', $handler);
	}
}

if (!defined('APPLICATION_SESSION_HANDLER_SAVE_PATH'))
{
	$savePath = getenv('APPLICATION_SESSION_HANDLER_SAVE_PATH');
	if (empty($savePath))
	{
		if (APPLICATION_SESSION_HANDLER === 'memcache')
		{
			define('APPLICATION_SESSION_HANDLER_SAVE_PATH', 'tcp://127.0.0.1:11211');
		}
		else
		{
			define('APPLICATION_SESSION_HANDLER_SAVE_PATH', null);
		}
	}
	else
	{
		define('APPLICATION_SESSION_HANDLER_SAVE_PATH', $savePath);
	}
}

if (APPLICATION_SESSION_HANDLER === 'memcache')
{
	ini_set('session.save_handler',	 'memcache');
}

if (APPLICATION_SESSION_HANDLER_SAVE_PATH != null)
{
	ini_set('session.save_path', APPLICATION_SESSION_HANDLER_SAVE_PATH);
}

// composer autoloading
$autoloader = require(VENDOR_PATH . '/autoload.php');

// setup error logging
$log = new Monolog\Logger('app');
$log->pushHandler(
	new Monolog\Handler\StreamHandler(VAR_PATH . '/log/application.log')
);
$log->pushProcessor(new Monolog\Processor\PsrLogMessageProcessor());
$log->pushProcessor(new Monolog\Processor\IntrospectionProcessor());

if (APPLICATION_ENV === 'development')
{
	if (isset($_SERVER['HTTP_USER_AGENT']))
	{
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false)
		{
			$log->pushHandler(new Monolog\Handler\ChromePHPHandler());
		}
		elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false)
		{
			$log->pushHandler(new Monolog\Handler\FirePHPHandler());
		}

		$log->pushProcessor(new Monolog\Processor\WebProcessor());
		$log->pushProcessor(new Monolog\Processor\MemoryPeakUsageProcessor());
		$log->pushProcessor(new Monolog\Processor\MemoryUsageProcessor());
	}
}

// register error handler
Monolog\ErrorHandler::register($log);

// setup error display handler
if (APPLICATION_ENV === 'development')
{
	$errorDisplay = new Symbic_Error_Debug();
}
else
{
	$errorDisplay = new Symbic_Error_Display();
}
$errorDisplay->register();

// init cache
if (!defined('APPLICATION_CACHE_BACKEND'))
{
	$cacheBackend = getenv('APPLICATION_CACHE_BACKEND');
	if (empty($cacheBackend))
	{
		$cacheBackend = 'file';
	}
	define('APPLICATION_CACHE_BACKEND', $cacheBackend);
}
	
if (APPLICATION_CACHE_BACKEND !== 'inactive')
{
	if (!defined('APPLICATION_CACHE_PREFIX'))
	{
		$cachePrefix = getenv('APPLICATION_CACHE_PREFIX');
		if (empty($cachePrefix))
		{
			$cachePrefix = null;
		}
		define('APPLICATION_CACHE_PREFIX', $cachePrefix);
	}

	$cache = new Zend_Cache_Core(array(
		'lifetime' => 7200,
		'automatic_serialization' => true,
		'cache_id_prefix' => APPLICATION_CACHE_PREFIX
		));

	switch (APPLICATION_CACHE_BACKEND)
	{
		case 'memcache':
			if (!defined('APPLICATION_CACHE_HOST'))
			{
				$cacheHost = getenv('APPLICATION_CACHE_HOST');
				if (empty($cacheHost))
				{
					$cacheHost = '127.0.0.1';
				}
				define('APPLICATION_CACHE_HOST', $cacheHost);
			}

			if (!defined('APPLICATION_CACHE_PORT'))
			{
				$cachePort = getenv('APPLICATION_CACHE_PORT');
				if (empty($cachePort))
				{
					$cachePort = 11211;
				}
				define('APPLICATION_CACHE_PORT', $cachePort);
			}
			
			// TODO: implement a memcache namespacing option
			// $cacheNamespace = getenv('APPLICATION_CACHE_NAMESPACE');

			$cache->setBackend(
				new Zend_Cache_Backend_Memcached(array( 'servers' => array( 0 =>
							array(
								'host' => APPLICATION_CACHE_HOST,
								'port' => APPLICATION_CACHE_PORT,
								'persistent' => 1
							)
						)
					)
				)
			);
			break;

		case 'noop':
			$cache->setBackend(
				new Zend_Cache_Backend_BlackHole()
			);
			break;

		default:
			if (!defined('APPLICATION_CACHE_PATH'))
			{
				$cachePath = getenv('APPLICATION_CACHE_PATH');
				if (empty($cachePath))
				{
					$cachePath = VAR_PATH . '/cache';
				}
				define('APPLICATION_CACHE_PATH', $cachePath);
			}

			if (!defined('APPLICATION_CACHE_NAMESPACE'))
			{
				$cacheNamespace = getenv('APPLICATION_CACHE_NAMESPACE');
				if (empty($cacheNamespace))
				{
					$cacheNamespace = 'applicationCache_';
				}
				define('APPLICATION_CACHE_NAMESPACE', $cacheNamespace);
			}
			
			$cache->setBackend(
				new Zend_Cache_Backend_File(array(
						'cache_dir'         => APPLICATION_CACHE_PATH,
						'file_name_prefix'  => APPLICATION_CACHE_NAMESPACE
					)
				)
			);
	}

	// Load Config
	$config = $cache->load('ApplicationConfig');
}

// load config from file
if (!isset($config) || !is_array($config))
{
	$localConfigPath = APPLICATION_CONFIG_PATH . '/application.php';

	// load config file
	if (file_exists($localConfigPath))
	{
		try
		{
			$config = array_replace_recursive(
						require(APPLICATION_CONFIG_PATH . '/application.default.php'),
						require(APPLICATION_CONFIG_PATH . '/application.php')
				);
		}
		catch (Exception $e)
		{
			throw new Exception('Application configuration is broken', null, $e);
		}

		if (APPLICATION_CACHE_BACKEND !== 'inactive')
		{
			$cache->save($config, 'ApplicationConfig');
		}
	}

	// run installer if config does not exist
	else
	{
		if (CLI_CALL)
		{
			throw new Exception('Application configuration is missing.');
		}

		$sfwinstallerConfigPath = VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwinstaller/application/configs/application.php';

		if (!file_exists($sfwinstallerConfigPath))
		{
			throw new Exception('Application configuration is missing and no sfwinstaller application available.');
		}

		try
		{
			$config = require($sfwinstallerConfigPath);
		}
		catch (Exception $e)
		{
			throw new Exception('Error loading sfwinstaller config', null, $e);
		}

		$sfwinstallerLocalConfigDir = APPLICATION_CONFIG_PATH . '/sfwinstaller';

		if (file_exists($sfwinstallerLocalConfigDir))
		{
			$sfwinstallerLocalDefaultConfigPath = APPLICATION_CONFIG_PATH . '/sfwinstaller/application.default.php';

			if (file_exists($sfwinstallerLocalDefaultConfigPath))
			{
				try
				{
					$config = array_replace_recursive($config, require($sfwinstallerLocalDefaultConfigPath));
				}
				catch (Exception $e)
				{
					throw new Exception('Error loading sfwinstaller local default config', null, $e);
				}
			}

			$sfwinstallerLocalConfigPath = APPLICATION_CONFIG_PATH . '/sfwinstaller/application.php';

			if (file_exists($sfwinstallerLocalConfigPath))
			{
				try
				{
					$config = array_replace_recursive($config, require($sfwinstallerLocalConfigPath));
				}
				catch (Exception $e)
				{
					throw new Exception('Error loading sfwinstaller local config', null, $e);
				}
			}
		}
	}
}

// Set PHP settings from config
if (isset($config['phpsettings']))
{
	foreach ($config['phpsettings'] as $key => $val)
	{
		ini_set($key, $val);
	}

	unset($config['phpsettings']);
}

// Add additional autoloaders from config
if (isset($config['autoloader']))
{
	if (!empty($config['autoloader']['namespaces']) && is_array($config['autoloader']['namespaces']))
	{
		foreach ($config['autoloader']['namespaces'] as $namespace => $path)
		{
			$autoloader->set($namespace, $path);
		}
	}

	if (!empty($config['autoloader']['psr4']) && is_array($config['autoloader']['psr4']))
	{
		foreach ($config['autoloader']['psr4'] as $namespace => $path)
		{
			$autoloader->setPsr4($namespace, $path);
		}
	}

	if (!empty($config['autoloader']['classmap']) && is_array($config['autoloader']['classmap']))
	{
		$autoloader->addClassMap($config['autoloader']['classmap']);
	}

	if (!empty($config['autoloader']['includeFiles']) && is_array($config['autoloader']['includeFiles']))
	{
		foreach ($config['autoloader']['includeFiles'] as $file)
		{
			require($file);
		}
	}

	unset($config['autoloader']);
}

// use zend_registry to allow global access to resources
Zend_Registry::set('autoloader', $autoloader);
Zend_Registry::set('log', $log);
Zend_Registry::set('config', $config);

if (APPLICATION_CACHE_BACKEND !== 'inactive')
{
	Zend_Registry::set('cache', $cache);
}