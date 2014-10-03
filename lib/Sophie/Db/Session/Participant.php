<?php
class Sophie_Db_Session_Participant extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_participant';
	public $_primary = array('id');

	public $_dependentTables = array('Sophie_Db_Session_Participant_Group', 'Sophie_Db_Session_Variable');

	public $_referenceMap    = array(
				'Session' => array(
            		'columns'           => array('sessionId'),
            		'refTableClass'     => 'Sophie_Db_Session',
            		'refColumns'        => array('id')
				),
				'Step' => array(
            		'columns'           => array('stepId'),
            		'refTableClass'     => 'Sophie_Db_Treatment_Step',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function fetchRowBySessionAndNumber($sessionId, $number)
	{
		return $this->fetchRow($this->select()->where('sessionId = ?', $sessionId)->where('number = ?', $number));
	}

	public function fetchRowBySessionAndLabel($sessionId, $label)
	{
		return $this->fetchRow($this->select()->where('sessionId = ?', $sessionId)->where('label = ?', $label));
	}

	public function fetchRowBySessionAndType($sessionId, $typeLabel)
	{
		return $this->fetchRow($this->select()->where('sessionId = ?', $sessionId)->where('typeLabel = ?', $typeLabel));
	}

	public function fetchMaxNumberBySession($sessionId)
	{
		$db = $this->getAdapter();
		return $db->fetchOne('SELECT max(number) FROM sophie_session_participant WHERE sessionId = ' . $db->quote($sessionId));
	}

	public function fetchMaxTypeNumberBySession($sessionId, $typeLabel)
	{
		$db = $this->getAdapter();
		return $db->fetchOne('SELECT max(number) FROM sophie_session_participant WHERE sessionId = ' . $db->quote($sessionId) . ' AND typeLabel = ' . $db->quote($typeLabel));
	}
	
	public function fetchAllBySession($sessionId)
	{
		return $this->fetchAll($this->select()->where('sessionId = ?', $sessionId));
	}

	public function fetchAllBySessionIdAndStates($sessionId, $states = null)
	{
		$select = $this->select();
		$select->where('sessionId = ?', $sessionId);
		if (!is_null($states))
		{
			$select->where('state IN (?)', $states);
		}
		return $this->fetchAll($select);
	}

	public function findBySessionWithState( $sessionId )
	{
		$select = $this->getAdapter()->select();

		$select->from(array('session_participant'=>$this->_name), '*');

		// Join Session
		$select->joinLeft(
					array('session'=>Sophie_Db_Session::getInstance()->_name),
					'session_participant.sessionId = session.id',
					array()
					);

		// Join Group Info
		$select->joinLeft(
					array('session_participant_group'=>Sophie_Db_Session_Participant_Group::getInstance()->_name),
					'session_participant.sessionId = session_participant_group.sessionId AND session_participant.label=session_participant_group.participantLabel AND session_participant.stepgroupLabel=session_participant_group.stepgroupLabel and session_participant.stepgroupLoop=session_participant_group.stepgroupLoop',
					array('session_group_label' => 'session_participant_group.groupLabel')
					);

		// Add Stepgroup & Step
		$select->joinLeft(
					array('treatment_stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name),
					'session.treatmentId = treatment_stepgroup.treatmentId AND session_participant.stepgroupLabel = treatment_stepgroup.label',
					array('treatment_stepgroup_name' => new Zend_Db_Expr('SUBSTRING(treatment_stepgroup.name, 1, 8)'), 'treatment_stepgroup_position' => 'position')
					);
		$select->joinLeft(
					array('treatment_step'=>Sophie_Db_Treatment_Step::getInstance()->_name),
					'session_participant.stepId = treatment_step.id',
					array('treatment_step_type'=>'steptypeSystemName', 'treatment_step_name'=> new Zend_Db_Expr('SUBSTRING(treatment_step.name, 1, 20)'), 'treatment_step_position' => 'position')
					);

		// WHERE filter by session
		$select->where('session_participant.sessionId = ?', $sessionId);

		// ORDER
		$select->order(array('session_participant.number'));

		// GET LIST
		return $select->query()->fetchAll();
	}

	public function fetchSessionOverview( $sessionId )
	{

		$response = array();
		// ['backwardestStep']
		// ['furthestStep']
		// ['furthestContact']

		$select = $this->getAdapter()->select();
		$select->from(array('session_participant'=>$this->_name), array('label' => 'session_participant.label', 'state' => 'session_participant.state'));

		$select->joinLeft(
					array('session'=>Sophie_Db_Session::getInstance()->_name),
					'session_participant.sessionId = session.id AND session.id = ' . $sessionId,
					array()
					);

		// Add Stepgroup & Step
		$select->joinLeft(
					array('treatment_step'=>Sophie_Db_Treatment_Step::getInstance()->_name),
					'session_participant.stepId = treatment_step.id',
					array('treatment_step_type'=>'steptypeSystemName', 'treatment_step_name'=> new Zend_Db_Expr('SUBSTRING(treatment_step.name, 1, 20)'), 'treatment_step_position' => 'position')
					);

		$select->joinLeft(
					array('treatment_stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name),
					'session.treatmentId = treatment_stepgroup.treatmentId AND treatment_step.stepgroupId = treatment_stepgroup.id',
					array('treatment_stepgroup_label' => new Zend_Db_Expr('SUBSTRING(treatment_stepgroup.label, 1, 8)'), 'treatment_stepgroup_position' => 'position')
					);

		// WHERE filter by session
		$select->where('session_participant.sessionId = ?', $sessionId);

		$select2 = clone $select;

		// GET BACKWARDEST
		$select->order(array('treatment_stepgroup_position', 'treatment_step_position'));
		$backwardest = $select->query()->fetch();

		if ($backwardest['state']=='new')
		{
			$response['backwardestStep'] = 'Login';
		}
		else
		{
			$response['backwardestStep'] = $backwardest['treatment_stepgroup_label'] . ' / ' . $backwardest['treatment_step_name'];
		}

		// GET FURTHEST
		$select2->order(array('treatment_stepgroup_position DESC', 'treatment_step_position DESC'));
		$furthest = $select2->query()->fetch();

		if ($furthest['state']=='new')
		{
			$response['furthestStep'] = 'Login';
		}
		else
		{
			$response['furthestStep'] = $furthest['treatment_stepgroup_label'] . ' / ' . $furthest['treatment_step_name'];
		}

		// GET FURTHEST CONTACT
		$select = $this->getAdapter()->select();
		$select->from(array('session_participant'=>$this->_name), array('label' => 'session_participant.label', 'state' => 'session_participant.state', 'lastContact' => 'session_participant.lastContact',));
		$select->order(array('session_participant.lastContact DESC'));
		$furthestContact = $select->query()->fetch();

		if ($furthestContact['state']=='new')
		{
			$response['furthestContact'] = '-';
		}
		else
		{
			$response['furthestContact'] = floor(microtime(true) - $furthestContact['lastContact']);
		}

		return $response;
	}
	
	public function checkUniqueCode($code)
	{
		$existingRow = $this->fetchRow($this->select()->where('code = ?', $code));
		if (is_null($existingRow))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}