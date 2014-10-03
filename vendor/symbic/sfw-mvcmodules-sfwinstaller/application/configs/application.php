<?php
return array(
	'bootstrap' => array(
		'path' => dirname(__DIR__) . '/Bootstrap.php',
		'class' => 'Application_SfwinstallerBootstrap',
	),
	'resources' => array(
		'session' => array(
			'use_only_cookies' => true,
			'remember_me_seconds' => 864000,
			'cache_expire' => 38000,
			'cookie_lifetime' => 38000,
		),

		'layout' => array(
			'layoutPath' => dirname(__DIR__) . '/layouts/scripts',
			'layout' => 'default'
		),

		'locale' => array(
			'default' => 'en_US',
			'force' => true,
		),
		
		'Symbic_Application_Resource_View'		=> array(),

		'Symbic_Application_Resource_Modulemanager'	 => array(
			'modules' => array(
				'sfwdefault'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwdefault/modules/sfwdefault'
				),
				'sfwinstaller'	 => array(
					'basePath' => dirname(dirname(__DIR__)) . '/modules/sfwinstaller',
				)
			)
		),
		
		'modules'  => array(),

		'frontcontroller' => array(
			'defaultmodule'				=> 'sfwdefault',
			'prefixDefaultModule'		=> true,
			'controllerdirectory'		=> array(),
			'plugins'					=> array(
				'ErrorHandler'	 => 'Zend_Controller_Plugin_ErrorHandler'
			),
			'defaultmodule'				=> 'sfwdefault',
			'prefixDefaultModule'		=> true,
			'controllerdirectory'		=> array(),
		),

		'router'					 => array(
			'routes' => array(
				'home'	=> array(
					'route'		 => '/',
					'defaults'	 => array(
						'module'		=> 'sfwinstaller',
						'controller'	=> 'index',
						'action'		=> 'index',
					)
				),
				'sfwinstaller'	=> array(
					'route'		 => '/sfwinstaller/:action/*',
					'defaults'	 => array(
						'module'		=> 'sfwinstaller',
						'controller'	=> 'index',
						'action'		=> 'index',
					)
				),
				'sfwmoduleasset'	 => array(
					'route'		 => '/sfwmoduleasset/:module/*',
					'defaults'	 => array(
						'controller' => 'sfwmoduleasset',
						'action'	 => 'index'
					)
				),
			)
		),
	)
);