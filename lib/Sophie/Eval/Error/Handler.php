<?php
class Sophie_Eval_Error_Handler
{
	// MAGICALLY SET SoPHIE CONTEXT
	static public $context = null;
	// Name of script, e. g. "Admin Process", "Run Condition", "Render"
	static public $script = null;
	
	static public $logToSession = true;
	static public $printError = false;

	static public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!error_reporting())
		{
			// do not log errors of statements prepended by the @ error-control operator:
			return false;
		}
		if (is_null(self::$context) || is_null(self::$script))
		{
			throw new Exception('Error handler called without being proper initialized (missing context / script)');
			return false;
		}
		$exit = false;
		$type = 'error';
		$name = '';
		$result = true;
		
		switch ($errno)
		{
			case E_USER_ERROR:
			case E_ERROR:
				$name = 'Error';
				$type = 'error';
				$exit = true;
				break;
			case E_USER_WARNING:
			case E_WARNING:
				$name = 'Warning';
				$type = 'warning';
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$name = 'Notice';
				$type = 'debug';
				break;
			case E_STRICT:
				$name = 'Strict (Recommendation)';
				$type = 'debug';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$name = 'Deprecated';
				$type = 'debug';
				break;
			default:
				$name = 'Unknown Error';
				$type = 'error';
				// continue with default error handler
				$result = false;
		}

		$processLevel = self::$context->getProcessContextLevel();
		if ($processLevel == 'step')
		{
			$step = self::$context->getStep();
			$processContextDesc = 'Step: ' . $step['name'];
		}
		else
		{
			$processContextDesc = 'None step script';
		}
		
		// remove install directory from $errfile
		$errfile = str_replace(realpath(dirname(dirname(dirname(dirname(__DIR__))))), '', realpath($errfile));
		$message = $name . ': ' . $errstr . ' (' . $errno . ')';
		$details = $processContextDesc . ', Script: ' . self :: $script . ', File: ' . $errfile . ', Line: ' . $errline;

		if (self::$logToSession)
		{
			Sophie_Db_Session_Log :: log(self :: $context->getSessionId(), $message, $type, $details);
		}
		
		if (self::$printError || $exit)
		{
            echo htmlentities($message);
		}
		
		if ($exit)
		{
		  exit;
		}
		return $result;
	}

}