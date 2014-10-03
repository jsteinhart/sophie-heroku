<?php

return array(
	'defaultAction'	 => array(
		'type'		 => 'forward',
		'module'	 => 'sfwlogin',
		'controller'	 => 'login',
		'action'	 => 'index'
	),
	'errorException' => array(
		'pageTitle'	 => 'Application Exception',
		'headline'	 => 'Application Exception',
		'display'	 => array(
			'active'		 => true,
			'loggingStatus'		 => true,
			'loggingReferenceId'	 => true,
			'exception'		 => true,
			'exceptionCodeReference' => true,
			'exceptionCodeSnippet'	 => true,
			'exceptionStrackTrace'	 => true,
			'exceptionPrevious'	 => true,
			'exceptionPrintR'	 => true,
			'requestParameters'	 => true
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => '\Sfwdefault\Model\Error\Log\Exception',
			'requestParameters'	 => true,
			'userSession'		 => true
		)
	),
	'errorNotfound'	 => array(
		'display'	 => array(
			'active'		 => true,
			'requestParameters'	 => true,
			'loggingStatus'		 => true
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => '\Sfwdefault\Model\Error\Log\Notfound',
			'requestParameters'	 => true,
			'userSession'		 => true
		)
	),
	'errorMessage'	 => array(
		'display'	 => array(
			'active'		 => true,
			'errorMessage'		 => true,
			'requestParameters'	 => true,
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => '\Sfwdefault\Model\Error\Log\Error',
			'requestParameters'	 => true,
			'userSession'		 => true
		)
	)
);
