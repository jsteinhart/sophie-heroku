<?php
/**
 * SoPHIE Sync API Class
 *
 * The Sync API provides functionality for checking if participants are in sync.
 * A sync condition is defined as a situation where all participants (for everyone
 * sync) or participants from a specific group (for group sync) are in the same
 * procedural context (same step and same stepgroupLoop) at the same time.
 */
class Sophie_Api_Sync_1_0_0_Api extends Sophie_Api_Abstract
{

	/**
	 * Get number of remaining participants missing for a sync condition.
	 *
	 * @param array $inStates
	 * @return Integer
	 */
	public function remainingEveryone(array $inStates = array(), $stepLabel = null, $stepgroupLoop = null)
	{
		$context = $this->getContext();
		$contextApi = $this->getContext()->getApi('context');

		$sessionId = $context->getSessionId();

		if ($stepLabel === null)
		{
			$stepId = $context->getStepId();
			$stepgroupLabel = $context->getStepgroupLabel();
		}
		else
		{
			$step = $context->getApi('step')->getStep($stepLabel);
			$stepId = $step['id'];
			$stepgroupLabel = $step['stepgroupLabel'];
		}

		if ($stepgroupLoop === null)
		{
			$stepgroupLoop = $context->getStepgroupLoop();
		}

		if (empty($inStates))
		{
			$inStates = array(
				'new',
				'started',
				'finished',
				// 'excluded'
			);
		}

		$db = $context->getDb();
		$selectNot = $db->select();

		$selectNot->from( Sophie_Db_Session_Participant::getInstance()->_name, array('num'=>new Zend_Db_Expr('count(*)')));
		$selectNot->where('sessionId = ?', $sessionId);
		$selectNot->where('(stepId != ' . $db->quote($stepId) . ' OR stepgroupLabel != ' . $db->quote($stepgroupLabel) . ' OR stepgroupLoop != ' . $db->quote($stepgroupLoop) . ' OR  stepId IS NULL) AND state IN (' . $db->quote($inStates) . ')');

		return (int)$selectNot->query()->fetchColumn();
	}

	/**
	 * Checks whether every participant is in the current procedural context.
	 *
	 * @param array $inStates
	 * @return Boolean
	 */
	public function checkEveryone(array $inStates = array(), $stepLabel = null, $stepgroupLoop = null)
	{
		return ($this->remainingEveryone($inStates, $stepLabel, $stepgroupLoop) === 0);
	}

	/**
	 * Get number of remaining participants from a group missing for a sync condition.
	 *
	 * @param string $groupLabel
	 * @param array $inStates
	 * @return Integer
	 */
	public function remainingGroup($groupLabel = null, array $inStates = array(), $stepLabel = null, $stepgroupLoop = null)
	{
		$context = $this->getContext();
		$contextApi = $this->getContext()->getApi('context');
		$groupApi = $this->getContext()->getApi('group');

		$groupLabel = $groupApi->translateLabel($groupLabel);
		$sessionId = $context->getSessionId();

		if ($stepLabel === null)
		{
			$stepId = $context->getStepId();
			$stepgroupLabel = $context->getStepgroupLabel();
		}
		else
		{
			$step = $context->getApi('step')->getStep($stepLabel);
			$stepId = $step['id'];
			$stepgroupLabel = $step['stepgroupLabel'];
		}

		if ($stepgroupLoop === null)
		{
			$stepgroupLoop = $context->getStepgroupLoop();
		}

		if (empty($inStates))
		{
			$inStates = array(
				'new',
				'started',
				'finished',
				// 'excluded'
			);
		}

		$db = $context->getDb();
		$selectNot = $db->select();

		$selectNot->from(
			array('p' => Sophie_Db_Session_Participant::getInstance()->_name),
			array('num'=>new Zend_Db_Expr('COUNT(*)'))
		);
		$selectNot->joinLeft(
			array('g' => Sophie_Db_Session_Participant_Group::getInstance()->_name),
			'p.sessionId = g.sessionId AND p.label = g.participantLabel',
			array()
		);
		$selectNot->where('p.sessionId = ?', $sessionId);
		$selectNot->where('g.stepgroupLabel = ?', $stepgroupLabel);
		$selectNot->where('g.stepgroupLoop = ?', $stepgroupLoop);
		$selectNot->where('g.groupLabel = ?', $groupLabel);
		$or = array(
			'p.stepId IS NULL',
			'p.stepId != ' . $db->quote($stepId),
			'p.stepgroupLabel != ' . $db->quote($stepgroupLabel),
			'p.stepgroupLoop != ' . $db->quote($stepgroupLoop)
		);
		$selectNot->where(implode(' OR ', $or));
		$selectNot->where('p.state IN (?)', $inStates);

		return (int)$selectNot->query()->fetchColumn();
	}

	/**
	 * Checks whether every participant from a group is in the current procedural context.
	 *
	 * @param string $groupLabel
	 * @param array $inStates
	 * @return Boolean
	 */
	public function checkGroup($groupLabel = null, array $inStates = array(), $stepLabel = null, $stepgroupLoop = null)
	{
		return ($this->remainingGroup($groupLabel, $inStates, $stepLabel, $stepgroupLoop) === 0);
	}
}