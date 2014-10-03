<?php
return array(

	'defaultRequiredRight' => 'sfwsysadmin',
	
	'components' => array(
		'dashboard' => array(
			'active' => true,
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'index',
				'action' => 'index'
			)
		),
		'cache' => array(
			'active' => false,
			'name' => 'Application Cache',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'cache',
				'action' => 'index',
			),
			'icon' => '/_media/Icons/lightning.png',
		),
		'configMail' => array(
			'active' => false,
			'name' => 'Mail Settings',
			'category' => 'Application Configuration',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'config',
				'action' => 'mail',
			),
			'icon' => '/_media/Icons/email.png',
		),
		'installerInstall' => array(
			'active' => false,
			'name' => 'Application Installer',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'installer',
				'action' => 'install',
			)
		),
		'installerInstallsvn' => array(
			'active' => false,
			'name' => 'Application SVN Installer',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'installer',
				'action' => 'installsvn',
			),
			'config' => array(
				'username'	=> '',
				'password'	=> ''
			)
		),
		'logFile' => array(
			'active' => false,
			'name' => 'Application Logfiles',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'log',
				'action' => 'file',
			)
		),
		'logDb' => array(
			'active' => false,
			'name' => 'Application Log-Database',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'log',
				'action' => 'db',
			)
		),
		'mailqueue' => array(
			'active' => false,
			'name' => 'Mail Queue',
			'category' => 'Mail',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'mailqueue',
				'action' => 'index',
			)
		),
		'systeminfoPhpinfo' => array(
			'active' => false,
			'name' => 'PHP Info',
			'category' => 'System Info',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'systeminfo',
				'action' => 'phpinfo',
			),
			'icon' => '/_media/Icons/page_white_php.png',
		),
		'systeminfoLimits' => array(
			'active' => false,
			'name' => 'System Limits',
			'category' => 'System Info',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'systeminfo',
				'action' => 'limits',
			),
		),
		'task' => array(
			'active' => false,
			'name' => 'Background Tasks',
			'category' => 'Tasks',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'task',
				'action' => 'index',
			)
		),
		'user' => array(
			'active' => false,
			'name' => 'Users',
			'category' => 'User Management',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'user',
				'action' => 'index',
			),
			'icon' => '/_media/Icons/user.png',
		),
		'usergroup' => array(
			'active' => false,
			'name' => 'User Groups',
			'category' => 'User Management',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'usergroup',
				'action' => 'index',
			),
			'icon' => '/_media/Icons/group.png',
		),
		'dbversion' => array(
			'active' => false,
			'name' => 'Database Version',
			'category' => 'Application Maintenance',
			'routeParams' => array(
				'module' => 'sfwsysadmin',
				'controller' => 'dbversion',
				'action' => 'index',
			),
		),
	),

	'models' => array(
		'user' => 'Sfwsysadmin_Model_User'
	),

	'userMessage' => array(
		'defaultSenderName' => 'System Mailer',
		'defaultSenderEmail' => 'dummy@dummy.com',
		'showInactiveUserRecipients' => false,
		'defaultCopyToSenderUser>0',
		'subjectPrefix>System Message: ',
		'subjectDefault' => '',
		'bodyTextDefault' => '',
		'bodyTextFooterDefault>---
Url to login to the server:
{{ systemLoginUrl }}

In case you should face problems with the system please contact the system administrator {{ systemAdminName }} ({{ systemAdminEmail }}).'
	)
);