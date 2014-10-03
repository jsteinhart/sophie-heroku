<?php
class Symbic_Php_Syntaxcheck
{
	public static function validate($value)
	{
		$syntaxCheck = @eval('return true; ?>' . $value);

		if(php_sapi_name() != "cli")
		{
			if (function_exists('http_response_code') && http_response_code() !== 200)
			{
				http_response_code(200);
			}
			else
			{
				header($_SERVER["SERVER_PROTOCOL"]." 200 Ok"); 
			}
		}

		if ($syntaxCheck === true)
		{
			return true;
		}

		$parseError = error_get_last();

		if ($parseError)
		{
			$errorReporting = error_reporting();
			$displayErrors = ini_get('display_errors');
			
			error_reporting(0);
			ini_set('display_errors', 0);

			$orgErrorHandler = set_error_handler('self::nullErrorHandler', -1);
			trigger_error('cleared syntax check', E_USER_NOTICE);
			set_error_handler($orgErrorHandler);

			error_reporting($errorReporting);
			ini_set('display_errors', $displayErrors);

			return $parseError;
		}

		return false;
	}

	public static function nullErrorHandler($errno , $errstr, $errfile, $errline, $errcontext)
	{
		return false;
	}
}