<?php
return array(
	'resources' => array(
		'db' => array(
			'adapter' => 'Pdo_Mysql',
			'params' => array(
				'host' => '127.0.0.1',
				'port' => 3306,
				'username' => 'root',
				'password' => 'mysql',
				'dbname' => 'sophie',
				'isDefaultTableAdapter' => true
			),
		),
		'mail' => array(
			'transport' => array(
				'type' => 'sendmail',
				'register' => true
			),
			'defaultFrom' => array(
				'email' => '',
				'name' => 'SoPHIE',
			),
			'defaultReplyTo' => array(
				'email' => '',
				'name' => 'SoPHIE',
			),
		),
	),

	'systemConfig' => array(
		'admin' => array(
			'email' => '',
			'name' => 'Admin'
		),
		'sophie' => array(
			'expfront' => array(
				'ajaxStepsyncLoopLimit' => 50,
				'ajaxStepsyncLoopSleep' => 250,
				'showBackendLink' => false,
				'showLicenseLink' => false
			),

/*
			'expadmin' => array(
				'payoffReceiptPdfTemplates' => array(
					'uos' => array(
						'name' => 'University of Osnabrueck - Heinricht W. Risken Lehrstuhl'
						'file' => BASE_PATH . '/var/sophie/pdfs/payoffReceiptUosU2.pdf'
					),
				),
			),
*/
			'steptypePaths' => array(
				'custom' => BASE_PATH .'/var/sophie/customSteptypes'
			),
			'apiPaths' => array(
				'custom' => BASE_PATH . '/var/sophie/customApis'
			),
		),

/*
		'receiptPrinters' => array(
			'printer' => array(
				'name' => 'Epson Network Receipt Printer',
				'type' => 'epson-epos-server',
				'options' => array(
					'hostname' => '1.1.1.1'
				),
			),
		),
*/
	)
);