<?php
return array(
	'bootstrap'	 => array(
		'path'	 => APPLICATION_PATH . '/src/Bootstrap.php',
		'class'	 => 'Application\Bootstrap',
	),
	'resources'	 => array(
		'session'	=> array(
			'use_only_cookies'		=> true,
			'remember_me_seconds'	=> 864000,
			'cache_expire'			=> 38000,
			'cookie_lifetime'		=> 38000
		),
		'db'			=> array(
			'adapter'		 			=> 'Pdo_Mysql',
			'isDefaultTableAdapter'		=> true,
			'params'					=> array(
				'host'		 => '127.0.0.1',
				'port'		 => 3306,
				'username'	 => 'sophie',
				'password'	 => '',
				'charset'	 => 'utf8'
			)
		),
		'Symbic_Application_Resource_Modulemanager'	 => array(
			'modules' => array(
				'sfwdefault'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwdefault/modules/sfwdefault'
				),
				'sfwdashboard'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwdashboard/modules/sfwdashboard'
				),
				'sfwlogin'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwlogin/modules/sfwlogin'
				),
				'sfwsysadmin'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwsysadmin/modules/sfwsysadmin'
				),
				'sfwuserprofile' => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwuserprofile/modules/sfwuserprofile'
				),
				'sfwsystem'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwsystem/modules/sfwsystem'
				),
				'sfwassets'	 => array(
					'basePath' => VENDOR_PATH . '/symbic/sfw-mvcmodules-sfwassets/modules/sfwassets'
				),
				'expfront'	 => array(
					'basePath' => BASE_PATH . '/modules/expfront'
				),
				'expadmin'	 => array(
					'basePath' => BASE_PATH . '/modules/expadmin'
				),
				'expdesigner'	 => array(
					'basePath' => BASE_PATH . '/modules/expdesigner'
				),
				'sysadmin'	 => array(
					'basePath' => BASE_PATH . '/modules/sysadmin'
				),
			)
		),
		'modules'				=> array(),
		'frontcontroller'		=> array(
			'defaultmodule'				=> 'sfwdefault',
			'prefixDefaultModule'		=> true,
			'controllerdirectory'		=> array(),
			'plugins'					=> array(
				'ErrorHandler'	 => 'Zend_Controller_Plugin_ErrorHandler',
				'UserSessionAcl' => 'Symbic_Controller_Plugin_UserSessionAcl'
			)
		),
		'dojo' => array(
			'enable'		=> false,
			'localPath'		=> '/_scripts/dojo/dojo/dojo.js',
			'modulePaths'	=> array(
				'symbic'	=> '../../symbic',
				'sophie'	=> '../../sophie',
			)
		),
		'layout'					 => array(
			'layoutPath'	 => APPLICATION_PATH . '/layouts/scripts',
			'layout'	 => 'default',
		),
		'locale'					 => array(
			'default'	 => 'en_US',
			'force'		 => 'true'
		),
		'router'					 => array(
			'routes' => array(
				'expfrontThemeAsset'	 => array(
					'route'		 => '/expfront/theme/asset/:theme/:file/*',
					'defaults'	 => array(
						'module'	 => 'expfront',
						'controller'	 => 'theme',
						'action'	 => 'asset'
					)
				),
				'login'			 => array(
					'route'		 => '/login/*',
					'defaults'	 => array(
						'module'	 => 'sfwlogin',
						'controller'	 => 'login',
						'action'	 => 'index'
					)
				),
				'logout'		 => array(
					'route'		 => '/logout/*',
					'defaults'	 => array(
						'module'	 => 'sfwlogin',
						'controller'	 => 'logout',
						'action'	 => 'index'
					)
				),
				'home'			 => array(
					'route'		 => '/home/*',
					'defaults'	 => array(
						'module'	 => 'sfwdashboard',
						'controller'	 => 'index',
						'action'	 => 'index'
					)
				),
				'sfwmoduleasset'	 => array(
					'route'		 => '/sfwmoduleasset/:module/*',
					'defaults'	 => array(
						'controller'	 => 'sfwmoduleasset',
						'action'	 => 'index'
					)
				)
			)
		),
		'Symbic_Application_Resource_View'		=> array(),
		'mail'						 			=> array(
			'transport'	 => array(
				'type'		 => 'sendmail',
				'host'		 => 'localhost',
				'auth'		 => '',
				'username'	 => '',
				'password'	 => '',
				'register'	 => true
			),
			'defaultFrom'	 => array(
				'email'	 => '',
				'name'	 => 'SoPHIE'
			),
			'defaultReplyTo' => array(
				'email'	 => '',
				'name'	 => 'SoPHIE'
			)
		),
		'Symbic_Application_Resource_User_Service'	 => array(
			'models' => array(
				'symbicUserModelDbtable' => array(
					'class' => 'Symbic_User_Model_Dbtable'
				)
			)
		)
	),
	'systemConfig'	 => array(
		'acl'			 => array(
			'active'		 => true,
			'requireAuthDefault'	 => true,
			'requireAuthHandler'	 => array(
				'module'	 => 'sfwlogin',
				'controller'	 => 'login',
				'action'	 => 'index'
			),
			'requireAuthExceptions'	 => array(
				'expfront'	 => 'expfront_*',
				'default'	 => 'sfwdefault_*',
				'sfwlogin'	 => 'sfwlogin_*',
				'sfwsystem'	 => 'sfwsystem_*',
				'sfwassets'	 => 'sfwassets_*'
			)
		),
		'sophie'	=> array(
			'expfront'		=> array(
				'ajaxStepsyncLoopLimit'						=> 1,
				'ajaxStepsyncLoopSleep'						=> 250,
				'ajaxStepsyncSetLastContactInterval'		=> 5000,
				'timerGracePeriodServer'					=> 500,
				'timerGracePeriodClient'					=> 0,
				'showBackendLink'							=> true,
				'showLicenseLink'							=> true,
				'defaultLayoutTheme'						=> 'sophie_2_0_0',
				'defaultLayoutDesign'						=> 'default'
			),
			'steptypePaths'			=> array(),
			'apiPaths'				=> array()
		),
		'receiptPrinters'	 => array()
	)
);
