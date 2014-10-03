<?php
return array(
	'defaultRequiredRight' => 'admin',
	'components' => array(
		'cache' => array(
			'active' => true
		),
		'configMail' => array(
			'active' => true
		),
		'systeminfoPhpinfo' => array(
			'active' => true
		),
		'user' => array(
			'active' => true
		),
		'usergroup' => array(
			'active' => true
		),
		'logFile' => array(
			'active' 	=> true,
			'config'	=> array(
				'files'	=> array(
					'ApplicationLog' => array(
						'name'	=> 'Application Log',
						'path'	=> 'var/log/application.log'
					)
				)
			)
		),
		'sophieSteptypes' => array(
			'active' => true,
			'name' => 'Steptype Management',
			'category' => 'SoPHIE',
			'routeParams' => array(
				'module' => 'sysadmin',
				'controller' => 'steptype',
				'action' => 'index'
			),
			'icon' => '/_media/Icons/page_gear.png'
		),
		'sophieTrash' => array(
			'active' => true,
			'name' => 'Object Trash',
			'category' => 'SoPHIE',
			'routeParams' => array(
				'module' => 'sysadmin',
				'controller' => 'trash',
				'action' => 'index'
			),
			'icon' => '/_media/Icons/bin_closed.png'
		)
	),
	'models' => array(
		'user' => '\Sophie_Sfwsysadmin_Model_User'
	),
	'userMessage' => array(
		'from' => array(
			'email' => '',
			'name' => 'SoPHIE'
		)
	)
);