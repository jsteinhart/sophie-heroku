<?php
require(__DIR__ . '/init.php');

if (APPLICATION_CACHE_BACKEND !== 'inactive')
{
	Zend_Registry::set('Zend_Cache', $cache);
}

// Fix to correctly detect ssl behind an http proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
{
	$_SERVER['HTTPS'] = 'on';
}

// Init and run Application
$application = new Zend_Application(APPLICATION_ENV, new Zend_Config($config));
Zend_Registry::set('application', $application);

$bootstrap = $application->getBootstrap();
Zend_Registry::set('bootstrap', $bootstrap);

$bootstrap->runWeb();