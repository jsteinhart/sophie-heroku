<?php
/**
 * SoPHIE Duration API Class
 *
 * The Duration API provides methods to get the duration a participant stayed in a step
 *
 * @hidden 1
 */

class Sophie_Api_Duration_1_0_0_Api extends Sophie_Api_Abstract
{
	public function fromEnterToLeave($stepLabel = null, $stepgroupLabel = null, $stepgroupLoop = null, $participantLabel = null)
	{
		$context = $this->getContext();
		$sessionId = $context->getSessionId();
		if (is_null($sessionId))
		{
			return -1;
		}

		$errorEntry = new Sophie_Session_Log_Entry($sessionId);
		$errorEntry->setContext($context);
		$errorEntry->type = 'warning';

		try
		{
			$participantLabel = (is_null($participantLabel))
				? $context->getParticipantLabel()
				: $context->getApi('participant')->translateLabel($participantLabel);

			$processApi = $context->getApi('process');

			$stepgroupLabel = (is_null($stepgroupLabel))
				? $context->getStepgroupLabel()
				: $processApi->translateStepgroupLabel($stepgroupLabel);

			$stepgroupLoop = (is_null($stepgroupLoop))
				? $context->getStepgroupLoop()
				: $processApi->translateStepgroupLoop($stepgroupLoop);

			$stepLabel = (is_null($stepLabel))
				? $context->getStepLabel()
				: $processApi->translateStepLabel($stepLabel);
		}
		catch (Exception $e)
		{
			$errorEntry->content = $e->getMessage();
			$errorEntry->contentId = 'Sophie_Api_Duration_Context';
			Sophie_Db_Session_Log :: log($errorEntry);
			return -1;
		}

		$logModel = Sophie_Db_Session_Log :: getInstance();
		$select = $logModel->getAdapter()->select();
		$select->from(array('logs' => $logModel->_name), array('eventId' => 'id', 'microtime', 'contentId'));

		$select->where('sessionId = ?', $sessionId);
		$select->where('groupLabel IS NULL');
		$select->where('participantLabel = ?', $participantLabel);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->where('stepgroupLoop = ?', $stepgroupLoop);
		$select->where('stepLabel = ?', $stepLabel);
		$select->where('type = ?', 'event');
		$select->where('contentId IN (?)', array('Sophie_Enter_Step', 'Sophie_Leave_Step'));

		$select->order(array('microtime'));

		$logEntries = $select->query()->fetchAll();

		$duration = 0;
		$entryTime = null;
		foreach ($logEntries as $logEntry)
		{
			if ($logEntry['contentId'] == 'Sophie_Enter_Step')
			{
				if (is_null($entryTime))
				{
					$entryTime = $logEntry['microtime'];
				}
				else
				{
					$errorEntry->content = 'Duration calculation result unreliable (consecutive enter)';
					$errorEntry->contentId = 'Sophie_Api_Duration_Consecutive_Enter';
					$errorEntry->data = $logEntry;
					Sophie_Db_Session_Log :: log($errorEntry);
				}
			}
			else if ($logEntry['contentId'] == 'Sophie_Leave_Step')
			{
				if (!is_null($entryTime))
				{
					$duration += ($logEntry['microtime'] - $entryTime);
					$entryTime = null;
				}
				else
				{
					$errorEntry->content = 'Duration calculation result unreliable (consecutive leave)';
					$errorEntry->contentId = 'Sophie_Api_Duration_Consecutive_Leave';
					$errorEntry->data = $logEntry;
					Sophie_Db_Session_Log :: log($errorEntry);
				}
			}
		}
		if (!is_null($entryTime))
		{
			$errorEntry->content = 'Duration calculation result unreliable (missing leave)';
			$errorEntry->contentId = 'Sophie_Api_Duration_Missing_Leave';
			$errorEntry->data = null;
			Sophie_Db_Session_Log :: log($errorEntry);
			return -1;
		}
		return round($duration, 3);
	}
}