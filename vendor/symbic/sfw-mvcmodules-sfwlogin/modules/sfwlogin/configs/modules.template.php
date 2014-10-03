<?php

return array(
	'login' => array(
		'pageTitle' => 'Login Form',
		'headline'  => 'Welcome to the Login Form!',
		'postLoginTasks' => array(
			'dbupdate' => array(
				'type' => 'include',
				'file' => APPLICATION_PATH . '/sfwlogin/dbUpdateScript.php'
			)
		)
	),
	'rememberMe' => array(
		'active' => true
	),
	'forgotPassword' => array(
		'active' => true
	)
);
