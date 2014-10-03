<?php
/**
 * SoPHIE Log API Class
 *
 * The Log API provides functionality for creating entries in the session log
 */
class Sophie_Api_Log_1_0_0_Api extends Sophie_Api_Abstract
{

	static private $dummyStorage = array();

	static public function getDummyStorage()
	{
		return self :: $dummyStorage;
	}
	
	private function log($message, $level = 'notice')
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			self :: $dummyStorage[] = array('message' => $message, 'level' => $level);
			return;
		}

		$logEntry = new Sophie_Session_Log_Entry($sessionId);
		$logEntry->setContext($this->getContext());
		$logEntry->content = $message;
		$logEntry->type = $level;

		Sophie_Db_Session_Log :: log($logEntry);
	}

	/**
	 * Write log message with severity debug
	 *
	 * @param String $message
	 */
	public function debug($message)
	{
		$this->log($message, 'debug');
	}

	/**
	 * Write log message with severity notice
	 *
	 * @param String $message
	 */
	public function notice($message)
	{
		$this->log($message, 'notice');
	}

	/**
	 * Write log message with severity warning
	 *
	 * @param String $message
	 */
	public function warning($message)
	{
		$this->log($message, 'warning');
	}

	/**
	 * Write log message with severity error
	 *
	 * @param String $message
	 */
	public function error($message)
	{
		$this->log($message, 'error');
	}

	/**
	 * Write an event log message
	 *
	 * @param String $message
	 */
	public function event($message)
	{
		$this->log($message, 'event');
	}
}