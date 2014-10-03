<?php
/**
 *
 */
class Symbic_Error_Display
{
    private static $fatalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

	protected $reservedMemory = '';

	public function register($reservedMemorySize = 10240)
	{
		$this->reservedMemory = str_repeat(' ', $reservedMemorySize);
		// exceptions shouldn't be catched here but elsewhere
		//set_exception_handler(array($this, 'handleError'));
		register_shutdown_function(array($this, 'shutdown'));
	}

	/**
	 *
	 */
	public function shutdown()
	{
		$this->reservedMemory = null;

		// check if last error is fatal
        $e = error_get_last();
		if ($e && in_array($e['type'], self::$fatalErrors))
		{
			$this->handleError($e);
		}
	}

	/**
	 *
	 */
	public function handleError($e)
	{
		if (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		if (php_sapi_name() === 'cli')
		{
			echo $this->renderConsole($e);
			flush();
			return;
		}

		// necessary to avoid connection abort after connection close?
		ignore_user_abort(true);
		set_time_limit(0);

		if (function_exists('http_response_code'))
		{
			http_response_code(500);
		}
		else
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
		{
			header('Content-type: application/json');
			$content = $this->renderXMLHttpRequest($e);
		}
		else
		{
			// TODO: implement a redirect instead:  header("Location: http://localhost/error-capture");
			header('Content-type: text/html');
			$content = $this->renderHtml($e);
		}

		header("Content-Length: " . strlen($content));

		echo $content;
		flush(); 
	}

	/**
	 *
	 */
	public function renderXMLHttpRequest($e)
	{
		return json_encode(array(
			'error' => 'Uncaught fatal application error'
		));
	}

	/**
	 *
	 */
	public function renderHtml($e)
	{
		return "<!DOCTYPE html>\n<html lang=\"en\">\n<body>\nUncaught fatal application error" . str_repeat("\n", 100) . "</body>\n</html>\n";
	}

	/**
	 *
	 */
	public function renderConsole($e)
	{
		return "Uncaught fatal application error\n";
	}
}
