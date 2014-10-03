<?php

return array(
	'errorException' => array(
		'display'	 => array(
			'active'		 => true,
			'loggingStatus'		 => true,
			'loggingReferenceId'	 => true,
			'exception'		 => false,
			'exceptionCodeReference' => false,
			'exceptionCodeSnippet'	 => false,
			'exceptionStrackTrace'	 => false,
			'exceptionPrevious'	 => false,
			'exceptionPrintR'	 => false,
			'requestParameters'	 => false
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => 'Sfwdefault_Model_Error_Log_Exception',
			'requestParameters'	 => false,
			'userSession'		 => false
		)
	),
	'errorNotfound'	 => array(
		'display'	 => array(
			'active'		 => true,
			'requestParameters'	 => false,
			'loggingStatus'		 => true
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => 'Sfwdefault_Model_Error_Log_Notfound',
			'requestParameters'	 => false,
			'userSession'		 => false
		)
	),
	'errorMessage'	 => array(
		'display'	 => array(
			'active'		 => true,
			'errorMessage'		 => true,
			'requestParameters'	 => false,
		),
		'log'		 => array(
			'active'		 => true,
			'model'			 => 'Sfwdefault_Model_Error_Log_Error',
			'requestParameters'	 => false,
			'userSession'		 => false
		)
	)
);
