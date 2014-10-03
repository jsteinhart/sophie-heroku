<?php

/**
 *
 */
class Symbic_Task_Log
{
	/**
	 * @var Symbic_Task_Log_Db
	 */
	protected $logModel = null;

	/**
	 * @var int / null
	 */
	protected $jobId = null;

	protected $output = '';
	protected $previousErrorHandler = null;
	protected $previousExceptionHandler = null;

	final public function __construct($jobId = null)
	{
		$this->jobId = $jobId;
		// get log model
		$this->logModel = Symbic_Task_Log_Db :: getInstance();
	}

	/*
	 * @param string $content
	 * @param type $type 'message','notice','warning','error','exception'
	 */
	final public function log($content, $type = 'message')
	{
		try
		{
			if (empty($content))
			{
				return null;
			}

			if (!in_array($type, array('message','notice','warning','error','exception')))
			{
				$type = 'message';
			}

			if (strlen($content) > 65535)
			{
				$contents = str_split($content, 65000);
				$cnt = count($contents);
				$i = 0;
				$lastId = null;
				foreach ($contents as $c)
				{
					$i++;
					$lastId = $this->log('Log split in ' . $cnt . ' parts' . PHP_EOL . 'Part ' . $i . ':' . PHP_EOL . PHP_EOL . $c, $type);
				}
				return $lastId;
			}

			return $this->logModel->insert(array(
				'jobId' => $this->jobId,
				'date' => microtime(true),
				'content' => $content,
				'type' => $type,
			));
		}
		catch (Exception $e)
		{
			echo 'Exception: ' . $e->getMessage();
			return null;
		}
	}

	final public function startOutputLogger()
	{
		$this->output = '';
		// save the output in $this->output to be logged afterwards.
		/*
		 * Set the chunk size to one byte (or two bytes) to show the output immediatly to the user (looking at the console).
		 * Prior to PHP 5.4.0, the value 1 was a special case value that set the chunk size to 4096 bytes. Therefore the
		 * chunk size is set to 2 for other PHP < 5.4.0.
		 */
		$chunkSize = (version_compare(PHP_VERSION, '5.4.0', '>=')) ? 1 : 2;
		ob_start(array($this, 'saveAndEchoOutput'), $chunkSize);
	}

	/*
	 * Output callback to be used with ob_start.
	 * Will save the output to $this->output.
	 */
	final public function saveAndEchoOutput($buffer, $phase)
	{
		$this->output .= $buffer;
		return $buffer;
	}

	final public function stopOutputLogger()
	{
		ob_end_flush();
		$this->log($this->output, 'message');
		$this->output = '';
	}

	final public function startErrorLogger()
	{
		$this->previousErrorHandler = set_error_handler(array($this, 'logErrorHandler'));
	}

	final public function logErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		switch ($errno)
		{
			case E_USER_ERROR:
			case E_ERROR:
				$type = 'error';
				$literalType = 'Error';
				break;
			case E_USER_WARNING:
			case E_WARNING:
				$type = 'warning';
				$literalType = 'Warning';
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$type = 'notice';
				$literalType = 'Notice';
				break;
			case E_STRICT:
				$type = 'error';
				$literalType = 'Strict (Recommendation)';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$type = 'warning';
				$literalType = 'Deprecated';
				break;
			default:
				$type = 'error';
				$literalType = 'Unknown Error: ' . $errno;
		}

		$content =	'PHP Error' . PHP_EOL .
					PHP_EOL .
					'Type: ' . $literalType . PHP_EOL .
					'Number: ' . $errno . PHP_EOL .
					'String: ' . $errstr . PHP_EOL .
					'File: ' . $errfile . PHP_EOL .
					'Line: ' . $errline;
		$this->log($content, $type);

		// continue with the default error handler:
		return false;
	}

	final public function stopErrorLogger()
	{
		set_error_handler($this->previousErrorHandler);
	}

	final public function startExceptionLogger()
	{
		$this->previousExceptionHandler = set_exception_handler(array($this, 'logExceptionHandler'));
	}

	final public function logExceptionHandler($exception)
	{
		try
		{
			$content =	get_class($exception) . PHP_EOL .
						PHP_EOL .
						'Message: ' . $exception->getMessage() . PHP_EOL .
						'Code: ' . $exception->getCode() . PHP_EOL .
						'File: ' . $exception->getFile() . PHP_EOL .
						'Line: ' . $exception->getLine() . PHP_EOL .
						'Trace: ' . $exception->getTraceAsString();
			$this->log($content, 'exception');
		}
		catch (Exception $e)
		{
			echo 'Exception within exception log handler: ' . $e->getMessage();
		}
		exit;
	}

	final public function stopExceptionLogger()
	{
		set_exception_handler($this->previousExceptionHandler);
	}

}
