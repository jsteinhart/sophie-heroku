<?php
/**
 * SoPHIE Group API Class
 *
 * The Group API provides group related functionality within a given
 * execution context.
 */
class Sophie_Api_Group_1_0_0_Api extends Sophie_Api_Abstract
{
	/**
	 * @var Sophie_Db_Session_Group
	 */
	protected $groupTable = null;

	/**
	 * @var Sophie_Db_Session_Participant_Group
	 */
	protected $participantGroupTable = null;

	/**
	 * Set the internal group database table object.
	 *
	 * @param Sophie_Db_Session_Group $groupTable
	 */
	protected function setGroupTable(Sophie_Db_Session_Group $groupTable)
	{
		$this->groupTable = $groupTable;
	}

	/**
	 * Get the internal group database table object.
	 *
	 * @return Sophie_Db_Session_Group
	 */
	protected function getGroupTable()
	{
		if (is_null($this->groupTable))
		{
			$this->groupTable = Sophie_Db_Session_Group::getInstance();
		}
		return $this->groupTable;
	}

	/**
	 * Set the internal participant group database table object.
	 *
	 * @param Sophie_Db_Session_Participant_Group $participantGroupTable
	 */
	protected function setParticipantGroupTable(Sophie_Db_Session_Participant_Group $participantGroupTable)
	{
		$this->participantGroupTable = $participantGroupTable;
	}

	/**
	 * Get the internal participant group database table object.
	 *
	 * @return Sophie_Db_Session_Participant_Group
	 */
	protected function getParticipantGroupTable()
	{
		if (is_null($this->participantGroupTable))
		{
			$this->participantGroupTable = Sophie_Db_Session_Participant_Group::getInstance();
		}
		return $this->participantGroupTable;
	}

	/**
	 * Get an array of group labels.
	 *
	 * The method returns an array of labels of all groups of a session.
	 *
	 * @return Array List of group labels
	 */
	public function getGroupLabels()
	{
		$groups = $this->getGroupTable()->fetchAll('sessionId = ' . $this->getContext()->getSessionId(), 'number');
		$groupLabels = array();
		foreach ($groups as $group)
		{
			$groupLabels[] = $group['label'];
		}
		return $groupLabels;
	}

	/**
	 * Get an array of group labels for groups without members.
	 *
	 * The method returns an array of labels of groups of a session not containing any members.
	 *
	 * @return Array List of group labels
	 */
	public function getEmptyGroupLabels($stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$stepgroup = $this->getContext()->getApi('stepgroup')->get($stepgroupLabel);
		if ($stepgroup['grouping'] == 'inactive')
		{
			$session = $this->getContext()->getSession();
			if ($session['participantMgmt'] == 'static')
			{
				$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
				return array();
			}
		}

		$groups = $this->getGroupLabels();
		$groupLabels = array();
		foreach ($groups as $group)
		{
			$members = $this->getGroupMemberLabels($group,
					array(
						'stepgroupLabel' => $stepgroupLabel,
						'stepgroupLoop' => $stepgroupLoop
					)
				);
			if (sizeof($members) == 0)
			{
				$groupLabels[] = $group;
			}
		}
		return $groupLabels;
	}

	/**
	 * Get an array of labels of participants within a group.
	 *
	 * The method returns an array of labels of all participants within a group of a session. The array can be restricted to labels for participants of a specific type by passing an option array with an element typeLabel to the method.
	 *
	 * @param String $label Group special label to be translated. Defaults to %current%.
	 * @param Array $options Array of options stepgroupLabel and stepgroupLoop for %localGroup%
	 * @return Array List of group participant labels
	 */
	public function getGroupMemberLabels($label = '%current%', $options = array())
	{
		// TODO: add an option for participantState = 'new', 'started', 'finished', 'excluded'
		if (!isset($options['stepgroupLabel']) || $options['stepgroupLabel'] == '%current%')
		{
			$options['stepgroupLabel'] = $this->getContext()->getStepgroupLabel();
		}

		if (!isset($options['stepgroupLoop']) || $options['stepgroupLoop'] == '%current%')
		{
			$options['stepgroupLoop'] = $this->getContext()->getStepgroupLoop();
		}

		$stepgroup = $this->getContext()->getApi('stepgroup')->get($options['stepgroupLabel']);
		if ($stepgroup['grouping'] == 'inactive')
		{
			$session = $this->getContext()->getSession();
			if ($session['participantMgmt'] == 'static')
			{
				$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
				return array();
			}
		}

		$sessionId = $this->getContext()->getSessionId();
		$label = $this->translateLabel($label, $options);
		$participants = $this->getParticipantGroupTable()->fetchAllByGroupAndContext(
			$label, $sessionId, $options['stepgroupLabel'], $options['stepgroupLoop']
		);

		$participantLabels = array();
		foreach ($participants as $participant)
		{
			$participantLabels[] = $participant['participantLabel'];
		}
		return $participantLabels;
	}

	/**
	 * Get an array of labels for participants which are not in a group.
	 *
	 * The method returns an array of labels of participants which are not in a group . The array can be restricted to labels for participants of a specific type by passing an option array with an element typeLabel to the method.
	 *
	 * @param String $stepgroupLabel
	 * @param Integer|String $stepgroupLoop
	 * @return Array List of participant labels
	 */
	public function getNoneMemberLabels($stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		// TODO: add an option for participantState = 'new', 'started', 'finished', 'excluded'
		$session = $this->getContext()->getSession();
		$stepgroup = $this->getContext()->getApi('stepgroup')->get($stepgroupLabel);
		if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
		{
			$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
			return array();
		}

		if ($stepgroupLoop == '%current%')
		{
			$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		}

		$sessionId = $this->getContext()->getSessionId();

		$participants = $this->getParticipantGroupTable()->fetchNoneMembersByStepgroupLoop($sessionId, $stepgroup['label'], $stepgroupLoop);
		$participantLabels = array();
		foreach ($participants as $participant)
		{
			$participantLabels[] = $participant['label'];
		}
		return $participantLabels;
	}

	/**
	 *
	 * @param String $participantLabel
	 * @param String $stepgroupLabel
	 * @param Integer|String $stepgroupLoop
	 * @return null|string
	 */
	public function getGroupMembership($participantLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$session = $this->getContext()->getSession();
		$stepgroup = $this->getContext()->getApi('stepgroup')->get($stepgroupLabel);
		if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
		{
			$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
			return array();
		}

		// TODO: translate labels and/or use $stepgroup['label']
		if ($stepgroupLoop == '%current%')
		{
			$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		}

		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel);

		$sessionId = $this->getContext()->getSessionId();

		$groupLabel = $this->getParticipantGroupTable()->getLabelByParticipantAndContext($participantLabel, $sessionId, $stepgroup['label'], $stepgroupLoop);
		if ($label === false)
		{
			return null;
		}
		return $groupLabel;
	}

	/**
	 * Translates a group special label considering the context into a label of a specific participant.
	 *
	 * The method supports the special labels %current% and %localGroup%.
	 * %current% translates to the label  of the participant which is currently active within the current procedural context.
	 * %localGroup% translates to the label of the group for a specified procedural context. The procedural context is defined by passing stepgroupLabel and stepgroupLoop as options in an array as a second parameter to the method.
	 *
	 * @param String $label Group special label to be translated. Defaults to %current%.
	 * @param Array $options Array of options stepgroupLabel and stepgroupLoop for %localGroup%
	 * @return String|null Group label
	 */
	public function translateLabel($label, $options = array())
	{
		$stepgroup = $this->getContext()->getStepgroup();

		if (is_null($label))
		{
			$label = '%current%';
		}

		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return $label;
		}

		// prepare options:
		if (!isset($options) || !is_array($options))
		{
			$options = array();
		}
		if (!isset($options['stepgroupLabel']))
		{
			$options['stepgroupLabel'] = '%current%';
		}
		$options['stepgroupLabel'] = $this->getContext()->getApi('process')->translateStepgroupLabel($options['stepgroupLabel']);

		if (!isset($options['stepgroupLoop']))
		{
			$options['stepgroupLoop'] = '%current%';
		}
		$options['stepgroupLoop'] = $this->getContext()->getApi('process')->translateStepgroupLoop($options['stepgroupLoop']);

		$personContextLevel = $this->getContext()->getPersonContextLevel();
		if ($personContextLevel == 'participant')
		{
			$participantLabel = $this->getContext()->getParticipantLabel();

			if ($label === '%current%')
			{
				$session = $this->getContext()->getSession();
				if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
				{
					$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
					return null;
				}

				$label = $this->getParticipantGroupTable()->getLabelByParticipantAndContext(
					$participantLabel,
					$sessionId,
					$options['stepgroupLabel'],
					$options['stepgroupLoop']
				);
			}
			elseif ($label === '%localGroup%')
			{
				$label = $this->getParticipantGroupTable()->getLabelByParticipantAndContext(
					$participantLabel,
					$sessionId,
					$options['stepgroupLabel'],
					$options['stepgroupLoop']
				);
			}
		}
		elseif ($personContextLevel == 'group')
		{
			if ($label === '%current%')
			{
				$session = $this->getContext()->getSession();
				if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
				{
					$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
					return null;
				}

				$label = $this->getContext()->getGroupLabel();
			}
			elseif ($label === '%localGroup%')
			{
				throw new Exception('Cannot translate group label ' . $label . ' in person context level ' . $personContextLevel);
			}
		}
		elseif ($personContextLevel == 'none')
		{
			if ($label === '%current%')
			{
				throw new Exception('Cannot translate group label ' . $label . ' in person context level ' . $personContextLevel);
			}
			elseif ($label === '%localGroup%')
			{
				if (!isset($options['participantLabel']))
				{
					throw new Exception('Participant Label has to be set to refer to localGroup in "none" context.');
				}

				$label = $this->getParticipantGroupTable()->getLabelByParticipantAndContext(
					$options['participantLabel'],
					$sessionId,
					$options['stepgroupLabel'],
					$options['stepgroupLoop']
				);
			}
		}

		if (!is_string($label))
		{
			return null;
		}
		return $label;
	}

	/**
	 * Set group members for the current or a specified procedural context
	 *
	 * The method ...
	 *
	 * @param Array $participantLabels Array of participantLabels to set as members of this group
	 * @param String $groupLabel defaults to %current%

	 */
	public function setGroupMembers($participantLabels = array(), $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return;
		}

		$groupLabel = $this->translateLabel($groupLabel);
		$stepgroup = $this->getContext()->getApi('stepgroup')->get($stepgroupLabel);
		$session = $this->getContext()->getSession();
		if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
		{
			$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
			return false;
		}
		$stepgroupLabel = $stepgroup['label'];

		if ($stepgroupLoop == '%current%')
		{
			$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		}
		$participantGroupTable = Sophie_Db_Session_Participant_Group::getInstance();
		try
		{
			$participantGroupTable->setMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop);
		}
		catch (Exception $e)
		{
			return false;
		}

		$this->getContext()->getApi('log')->notice('Set participants (' . implode(',', $participantLabels) . ') as group members for group ' . $groupLabel);
		return true;
	}

	/**
	 * Add group members for the current or a specified procedural context
	 *
	 * The method ...
	 *
	 * @param Array $participantLabels Array of participantLabels to set as members of this group
	 * @param String $groupLabel defaults to %current%

	 */
	public function addGroupMember($participantLabel = '%current%', $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel);
		return $this->addGroupMembers(array($participantLabel), $groupLabel, $stepgroupLabel, $stepgroupLoop);
	}

	/**
	 * Add group members for the current or a specified procedural context
	 *
	 * The method ...
	 *
	 * @param Array $participantLabels Array of participantLabels to set as members of this group
	 * @param String $groupLabel defaults to %current%

	 */
	public function addGroupMembers($participantLabels = array(), $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return;
		}

		$groupLabel = $this->translateLabel($groupLabel);
		$stepgroup = $this->getContext()->getStepgroup($stepgroupLabel);
		$session = $this->getContext()->getSession();
		if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
		{
			$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
			return false;
		}
		$stepgroupLabel = $stepgroup['label'];

		if ($stepgroupLoop == '%current%')
		{
			$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		}
		$participantGroupTable = Sophie_Db_Session_Participant_Group::getInstance();
		try
		{
			$participantGroupTable->addMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop);
		}
		catch (Exception $e)
		{
			return false;
		}

		$this->getContext()->getApi('log')->notice('Added participants (' . implode(',', $participantLabels) . ') to group ' . $groupLabel);
		return true;
	}

	/**
	 * Remove group member for the current or a specified procedural context
	 *
	 * The method ...
	 *
	 * @param String $participantLabel participantLabel of the member to remove from the group
	 * @param String $groupLabel defaults to %current%

	 */
	public function removeGroupMember($participantLabel = '%current%', $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$participantLabel = $this->getContext()->getApi('participant')->translateLabel($participantLabel);
		return $this->removeGroupMembers(array($participantLabel), $groupLabel, $stepgroupLabel, $stepgroupLoop);
	}

	/**
	 * Remove group members for the current or a specified procedural context
	 *
	 * The method ...
	 *
	 * @param Array $participantLabels Array of participantLabels to set as members of this group
	 * @param String $groupLabel defaults to %current%

	 */
	public function removeGroupMembers($participantLabels = array(), $groupLabel = '%current%', $stepgroupLabel = '%current%', $stepgroupLoop = '%current%')
	{
		$sessionId = $this->getContext()->getSessionId();
		if (is_null($sessionId))
		{
			// there is no session i.q. this is a preview api call
			return;
		}

		$groupLabel = $this->translateLabel($groupLabel);
		$stepgroup = $this->getContext()->getStepgroup($stepgroupLabel);
		$session = $this->getContext()->getSession();
		if ($stepgroup['grouping'] == 'inactive' && $session['participantMgmt'] == 'static')
		{
			$this->getContext()->getApi('log')->error('Trying to access group in a stepgroup without grouping');
			return false;
		}
		$stepgroupLabel = $stepgroup['label'];

		if ($stepgroupLoop == '%current%')
		{
			$stepgroupLoop = $this->getContext()->getStepgroupLoop();
		}
		$participantGroupTable = Sophie_Db_Session_Participant_Group::getInstance();

		try
		{
			$participantGroupTable->unsetMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop);
		}
		catch (Exception $e)
		{
			return false;
		}

		$this->getContext()->getApi('log')->notice('Removed participants (' . implode(',', $participantLabels) . ' from group ' . $groupLabel);
		return true;
	}
}