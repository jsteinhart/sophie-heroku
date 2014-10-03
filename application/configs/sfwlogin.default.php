<?php
return array(
	'login' => array(
		'pageTitle' => 'Login',
		'headline' => 'Welcome to SoPHIE&#8212;the Software Platform for Human Interaction Experiments!',
		'postLoginTasks' => array(
			'dbupdate' => array(
				'type' => 'include',
				'file' => APPLICATION_PATH . '/sfwlogin/dbUpdateScript.php'
			)
		),

		'hash'					=> array (
			'active'				=> false
		),
		
		'rememberMe' => array(
			'active' => true
		),

		'throttleFailedLogin'	=> array(
			'active'				=> true,
		)
	),
	'forgotPassword' => array(
		'active' 		=> true,
		
		'captcha'		=> array (
			'active'				=> false,
			'trigger'				=> 'always'
		)
	)
);