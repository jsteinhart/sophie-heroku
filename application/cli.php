<?php
if (php_sapi_name() !== 'cli' || (defined('CLI_CALL') && CLI_CALL === false))
{
	throw Exception('Cli script should only be exceuted from the cli');
}

defined('CLI_CALL') || define('CLI_CALL', true);

require(__DIR__ . '/init.php');

if (APPLICATION_CACHE_BACKEND !== 'inactive')
{
	Zend_Registry::set('Zend_Cache', $cache);
}

// Init and run Application
$application = new Zend_Application(APPLICATION_ENV, new Zend_Config($config));
Zend_Registry::set('application', $application);

$bootstrap = $application->getBootstrap();
Zend_Registry::set('bootstrap', $bootstrap);

$bootstrap->runCli();