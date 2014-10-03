<?php
return array(
	'components' => array(
		'cache' => array(
			'active' => true
		),
		'installerInstall' => array(
			'active' => true
		),
		'installerSvninstall' => array(
			'active' => true
		),
		'logFile' => array(
			'active' => true
		),
		'mailqueue' => array(
			'active' => true
		),
		'systeminfoPhpinfo' => array(
			'active' => true
		),
		'task' => array(
			'active' => true
		),
		'user' => array(
			'active' => true
		),
		'usergroup' => array(
			'active' => true
		)
	),
	'userMessage' => array(
		'defaultSenderName' => 'System Mailer',
		'defaultSenderEmail' => 'dummy@dummy.com',
		'showInactiveUserRecipients' => false
		'defaultCopyToSenderUser' => false,
		'subjectPrefix' => 'System Message: ',
		'subjectDefault' => '',
		'bodyTextDefault' => '',
		'bodyTextFooterDefault' => '---
Url to login to the server:
{{ systemLoginUrl }}

In case you should face problems with the system please contact the system administrator {{ systemAdminName }} ({{ systemAdminEmail }}).'
	)
);