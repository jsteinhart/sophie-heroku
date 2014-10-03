<?php
class Sophie_Db_Session_Log extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_log';
	public $_primary = 'id';

	public $_referenceMap	 = array(
				'Session' => array(
					'columns'			=> array('sessionId'),
					'refTableClass'		=> 'Sophie_Db_Session',
					'refColumns'		=> array('id')
				));

	static public function log($sessionId__logObject, $content = null, $type = null /*notice,warning,error,debug*/, $details = null)
	{
		// allow logging by only one parameter (Sophie_Session_Log_Entry object)
		if ($sessionId__logObject instanceof Sophie_Session_Log_Entry)
		{
			$logObject = $sessionId__logObject;

			// encode data:
			$data = $logObject->data;
			if (!is_null($data))
			{
				$data = json_encode($data);
			}

			// write log:
			$table = self::getInstance();
			$table->insert(
				array(
					'sessionId' => $logObject->sessionId,
					'groupLabel' => $logObject->groupLabel,
					'participantLabel' => $logObject->participantLabel,
					'stepgroupLabel' => $logObject->stepgroupLabel,
					'stepgroupLoop' => $logObject->stepgroupLoop,
					'stepLabel' => $logObject->stepLabel,
					'microtime' => microtime(true),
					'content' => $logObject->content,
					'contentId' => $logObject->contentId,
					'type' => $logObject->type,
					'details' => $logObject->details,
					'data' => $data,
					)
				);
			return;
		}

		// fallback to old logging by giving all parameters:
		if (is_null($content))
		{
			throw new Exception('2nd argument (content) required for Sophie_Db_Session_Log::log()');
		}
		$sessionId = $sessionId__logObject;
		if (is_null($sessionId))
		{
			throw new Exception('Tried to log without session id: ' . $content);
		}

		if (is_null($type))
		{
			$type = 'notice';
		}

		$table = self::getInstance();
		$table->insert(
			array(
				'sessionId' => $sessionId,
				'microtime' => microtime(true),
				'content' => $content,
				'type' => $type,
				'details' => $details
				)
			);
	}
}