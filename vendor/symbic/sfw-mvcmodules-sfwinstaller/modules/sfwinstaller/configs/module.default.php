<?php
return array(
	'steps' => array(
		array(
			'class'		=> '\Sfwinstaller\Installer\Step\License',
			'name'		=> 'Application License',
		),
		array(
			'class'		=> '\Sfwinstaller\Installer\Step\Dbconfig',
			'name'		=> 'Configure Database',
		),
		array(
			'class'		=> '\Sfwinstaller\Installer\Step\Mailconfig',
			'name'		=> 'Configure Mailserver',
		),
		array(
			'class'		=> '\Sfwinstaller\Installer\Step\Adminuser',
			'name'		=> 'Admin User'
		),
		array(
			'class'		=> '\Sfwinstaller\Installer\Step\Confirm',
			'name'		=> 'Confirm Settings'
		)
	)
);