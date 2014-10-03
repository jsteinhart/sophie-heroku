<?php
class Sophie_Db_Session_Participant_Group extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_participant_group';
	//public $_primary = 'id';

	public $_referenceMap    = array(
				'Session' => array(
            		'columns'           => array('sessionId'),
            		'refTableClass'     => 'Sophie_Db_Session',
            		'refColumns'        => array('id')
				)
				);

	// FUNCTIONS
	public function fetchRowByParticipantAndContext($participantLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$select = $this->select();
		$select->where('participantLabel = ?', $participantLabel);
		$select->where('sessionId = ?', $sessionId);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->where('stepgroupLoop = ?', $stepgroupLoop);
		return $this->fetchRow($select);
	}

	public function getLabelByParticipantAndContext($participantLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('gl' => $this->_name), array('groupLabel'));
		$select->where('sessionId = ?', $sessionId);
		$select->where('participantLabel = ?', $participantLabel);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->where('stepgroupLoop = ?', $stepgroupLoop);
		return $select->query()->fetchColumn();
	}

	public function fetchAllByParticipant($participantLabel, $sessionId)
	{
		$select = $this->select();
		$select->where('participantLabel = ?', $participantLabel);
		$select->where('sessionId = ?', $sessionId);
		$select->order('stepgroupLabel, stepgroupLoop');
		return $this->fetchAll($select);
	}

	public function fetchAllByParticipantAndStepgroup($participantLabel, $sessionId, $stepgroupLabel)
	{
		$select = $this->select();
		$select->where('participantLabel = ?', $participantLabel);
		$select->where('sessionId = ?', $sessionId);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->order('stepgroupLoop');
		return $this->fetchAll($select);
	}

	public function fetchAllByGroupAndContext($groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$select = $this->select();
		$select->where('groupLabel = ?', $groupLabel);
		$select->where('sessionId = ?', $sessionId);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->where('stepgroupLoop = ?', $stepgroupLoop);
		return $this->fetchAll($select);
	}

	public function fetchAllByGroupAndContextExcludeParticipant($groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop, $excludeParticipantLabel)
	{
		$select = $this->select();
		$select->where('participantLabel <> ?', $excludeParticipantLabel);
		$select->where('groupLabel = ?', $groupLabel);
		$select->where('sessionId = ?', $sessionId);
		$select->where('stepgroupLabel = ?', $stepgroupLabel);
		$select->where('stepgroupLoop = ?', $stepgroupLoop);
		return $this->fetchAll($select);
	}

	public function setMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$db = $this->getAdapter();
		$this->delete('sessionId = ' . $db->quote($sessionId) . ' AND groupLabel = ' . $db->quote($groupLabel) . ' AND stepgroupLabel = ' . $db->quote($stepgroupLabel) . ' AND stepgroupLoop = ' . $db->quote($stepgroupLoop) . ' AND NOT participantLabel IN (' . $db->quote($participantLabels) . ')');
		$this->addMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop);
	}

	public function addMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$params = array(
			'sessionId' => $sessionId,
			'groupLabel' => $groupLabel,
			'stepgroupLabel' => $stepgroupLabel,
			'stepgroupLoop' => $stepgroupLoop
		);
		foreach ($participantLabels as $participantLabel)
		{
			$params['participantLabel'] = $participantLabel;
			$this->replace($params);
		}
	}

	public function unsetMembersByGroupAndContext($participantLabels, $groupLabel, $sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$db = $this->getAdapter();
		$this->delete('sessionId = ' . $db->quote($sessionId) . ' AND groupLabel = ' . $db->quote($groupLabel) . ' AND stepgroupLabel = ' . $db->quote($stepgroupLabel) . ' AND stepgroupLoop = ' . $db->quote($stepgroupLoop) . ' AND participantLabel IN (' . $db->quote($participantLabels) . ')');
	}

	public function fetchNoneMembersByStepgroupLoop($sessionId, $stepgroupLabel, $stepgroupLoop)
	{
		$db = $this->getAdapter();
		$participantTable = Sophie_Db_Session_Participant::getInstance();
		$select = $this->getAdapter()->select();
		$select->from(array('session_participant'=>$participantTable->_name), 'session_participant.label');

		// Join Group Info
		$select->joinLeft(
					array('session_participant_group'=>Sophie_Db_Session_Participant_Group::getInstance()->_name),
					'session_participant.sessionId = session_participant_group.sessionId AND session_participant.label=session_participant_group.participantLabel AND session_participant.stepgroupLabel = session_participant_group.stepgroupLabel AND session_participant_group.stepgroupLabel = ' . $db->quote($stepgroupLabel) . ' and session_participant.stepgroupLoop=session_participant_group.stepgroupLoop AND session_participant_group.stepgroupLoop = ' . $db->quote($stepgroupLoop),
					array('session_group_label' => 'session_participant_group.groupLabel')
					);

		// WHERE filter by session
		$select->where('session_participant.sessionId = ?', $sessionId);
		$select->where('session_participant_group.groupLabel IS NULL');

		// ORDER
		$select->order(array('session_participant.number'));

		// GET LIST
		return $select->query()->fetchAll();
	}
}