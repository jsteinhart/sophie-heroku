<?php

// init base path
$basepath = dirname(__DIR__);
defined('BASE_PATH') || define('BASE_PATH', realpath($basepath));

// change directory to BASE DIR
chdir(BASE_PATH);

// composer autoloading
defined('VENDOR_PATH') || define('VENDOR_PATH', BASE_PATH . '/vendor');
$autoloader = @include VENDOR_PATH . '/autoload.php';

// define TESTS_PATH
defined('TESTS_PATH') || define('TESTS_PATH', BASE_PATH . '/tests');