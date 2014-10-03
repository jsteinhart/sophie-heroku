<?php
class Sophie_Db_Treatment_Step extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_step';
	public $_primary = 'id';

	public $_referenceMap    = array(
				'Stepgroup' => array(
            		'columns'           => array('stepgroupId'),
            		'refTableClass'     => 'Sophie_Db_Treatment_Stepgroup',
            		'refColumns'        => array('id')
				));

	public $_dependentTables = array('Sophie_Db_Steptype', 'Sophie_Db_Treatment_Step_Type', 'Sophie_Db_Session_Participant');

	// FUNCTIONS
	public function insertPosition(array $data)
	{
		$db = $this->getAdapter();

		if (!empty($data['position']))
		{
			$targetPosition = $data['position'];
		}
		else
		{
			$targetPosition = null;
		}

		try
		{
			$db->beginTransaction();

			// fetch treatment to have a locking singleton
			$stepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->find($data['stepgroupId'])->current();
			$data['position'] = sizeof($stepgroup->findDependentRowset('Sophie_Db_Treatment_Step')) + 1;

			$id = $this->insert($data);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			return false;
		}

		if (!is_null($targetPosition))
		{
			$this->moveToPosition($id, $targetPosition);
		}

		return $id;
	}

	public function insert(array $data)
	{
		$stepId = parent::insert($data);
		Sophie_Db_Treatment_Step_Type::getInstance()->setByStep($stepId);
		return $stepId;
	}

	public function copyById($id, $overrideData = array())
	{
		$step = $this->find($id)->current();
		if (is_null($step))
		{
			return false;
		}

		$values = $step->toArray();
		unset($values['id']);
		$values['position']++;

		$values['label'] = substr($values['label'], 0, 30) . '_' . uniqid();

		$values = array_merge($values, $overrideData);

		$newId = $this->insertPosition($values);
		$newStep = $this->find($newId)->current();

		$stepEavModel = Sophie_Db_Treatment_Step_Eav::getInstance();
		$stepAttributes = $stepEavModel->fetchAll(array('stepId = ?' => $id));
		foreach ($stepAttributes as $stepAttribute)
		{
			$stepAttribute2 = $stepAttribute->toArray();
			$stepAttribute2['stepId'] = $newId;
			$stepEavModel->insert($stepAttribute2);
		}

		//Copy Run Conditions for Participant Type
		$stepTypeRunConditions = Sophie_Db_Treatment_Step_Type::getInstance()->getByStep($id)->toArray();

		$runConditionsForParticipantTypes = array();
		foreach($stepTypeRunConditions as $rc)
		{
			$runConditionsForParticipantTypes[] = $rc['typeLabel'];
		}
		Sophie_Db_Treatment_Step_Type::getInstance()->setByStep($newId,$runConditionsForParticipantTypes);

		return $newId;
	}

	public function deletePosition($stepId)
	{
		$db = $this->getAdapter();

		try {

			$db->beginTransaction();

			$step = $this->find($stepId)->current();

			$rowsBelow = $this->fetchAll( $this->select()
								->where('stepgroupId = ?', $step->stepgroupId)
								->where('position > ?', $step->position)
								);

			$step->delete();

			foreach ($rowsBelow as $rowBelow)
			{
				$rowBelow->position = $rowBelow->position - 1;
				$rowBelow->save();
			}

			$db->commit();

		}
		catch (Exception $e)
		{
			$db->rollBack();
		}
	}

	public function fetchAllByStepgroupLabelJoinParticipantTypesAndSteptype( $treatmentId, $stepgroupLabel )
	{
		$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->getId($treatmentId, $stepgroupLabel);
		return $this->fetchAllByStepgroupIdJoinParticipantTypesAndSteptype( $stepgroupId );
	}

	public function fetchAllByStepgroupIdJoinParticipantTypesAndSteptype( $stepgroupId )
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('step'=>$this->_name), '*');
		$select->joinLeft(array('step_type'=>Sophie_Db_Treatment_Step_Type::getInstance()->_name), 'step.id = step_type.stepId', array('type_names'=>new Zend_Db_Expr('GROUP_CONCAT(step_type.typeLabel SEPARATOR \',\')')));
		$select->joinLeft(array('steptype'=>Sophie_Db_Steptype::getInstance()->_name), 'step.steptypeSystemName = steptype.systemName', array('steptype_name'=>'steptype.name', 'steptype_isActive'=>'steptype.isActive', 'steptype_isBroken'=>'steptype.isBroken'));
		$select->joinLeft(array('eavT'=>Sophie_Db_Treatment_Step_Eav::getInstance()->_name), 'eavT.stepId = step.id AND eavT.name="timerEnabled"', array('timerEnabled'=>'eavT.value'));
		$select->joinLeft(array('eavTS'=>Sophie_Db_Treatment_Step_Eav::getInstance()->_name), 'eavTS.stepId = step.id AND eavTS.name="timerStart"', array('timerStart'=>'eavTS.value'));
		$select->where('step.stepgroupId = ?', $stepgroupId);
		$select->order('step.position ASC');
		$select->group(array('step.id'));
		return $select->query()->fetchAll();
	}

	public function fetchAllForStructureDetailsByStepgroupId( $stepgroupId )
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('step'=>$this->_name), '*');
		$select->joinLeft(array('step_type'=>Sophie_Db_Treatment_Step_Type::getInstance()->_name), 'step.id = step_type.stepId', array('type_names'=>new Zend_Db_Expr('GROUP_CONCAT(step_type.typeLabel SEPARATOR \',\')')));
		$select->joinLeft(array('steptype'=>Sophie_Db_Steptype::getInstance()->_name), 'step.steptypeSystemName = steptype.systemName', array('steptype_name'=>'steptype.name', 'steptype_isActive'=>'steptype.isActive', 'steptype_isBroken'=>'steptype.isBroken'));
		$select->joinLeft(array('eavInternalNote'=>Sophie_Db_Treatment_Step_Eav::getInstance()->_name), 'eavInternalNote.stepId = step.id AND eavInternalNote.name="internalNote"', array('internalNote'=>'eavInternalNote.value'));
		$select->joinLeft(array('eavT'=>Sophie_Db_Treatment_Step_Eav::getInstance()->_name), 'eavT.stepId = step.id AND eavT.name="timerEnabled"', array('timerEnabled'=>'eavT.value'));
		$select->joinLeft(array('eavTS'=>Sophie_Db_Treatment_Step_Eav::getInstance()->_name), 'eavTS.stepId = step.id AND eavTS.name="timerStart"', array('timerStart'=>'eavTS.value'));
		$select->where('step.stepgroupId = ?', $stepgroupId);
		$select->order('step.position ASC');
		$select->group(array('step.id'));
		return $select->query()->fetchAll();
	}

	public function fetchRowByStepgroupLabelPositionAndType( $treatmentId, $stepgroupLabel, $typeLabel, $position = 1)
	{
		$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->getId($treatmentId, $stepgroupLabel);
		return $this->fetchRowByStepgroupIdPositionAndType( $stepgroupId, $typeLabel, $position);
	}

	public function fetchRowByStepgroupIdPositionAndType( $stepgroupId, $typeLabel, $position = 1)
	{
		if (is_null($position))
		{
			$position = 1;
		}
		$select = Zend_Registry::get('db')->select();
		$select->from(array('step'=>$this->_name), array('id'));
		$select->joinLeft(array('type'=>Sophie_Db_Treatment_Step_Type::getInstance()->_name), 'step.id = type.stepId', array());
		$select->where('step.stepgroupId = ?', $stepgroupId);
		$select->where('step.position >= ?', $position);
		$select->where('type.typeLabel IS NULL OR type.typeLabel = ?', $typeLabel);
		$select->order('step.position ASC');
		$select->limit(1,0);
		$stepId = $select->query()->fetchColumn();
		return 	$this->find($stepId)->current();
	}

	public function fetchRowByStepgroupLabelAndPosition( $treatmentId, $stepgroupLabel, $position = 1)
	{
		$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->getId($treatmentId, $stepgroupLabel);
		return $this->fetchRowByStepgroupIdAndPosition( $stepgroupId, $position);
	}

	public function fetchRowByStepgroupIdAndPosition($stepgroupId, $position = 1)
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('step'=>$this->_name), array('id'));
		$select->where('step.stepgroupId = ?', $stepgroupId);
		$select->where('step.position = ?', $position);
		return $select->query()->fetchRow();
	}

	public function fetchStepgroupLabelByStepId( $stepId )
	{
		$step = $this->find($stepId)->current();
		if (is_null($step))
		{
			return null;
		}
		$stepgroup = $step->findParentRow('Sophie_Db_Treatment_Stepgroup');
		if (empty($stepgroup->label))
		{
			return null;
		}
		return $stepgroup->label;
	}

	/*public function fetchRowByStepgroupPosition( $treatmentId, $stepgroupLabel, $position )
	{
		$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->getId($treatmentId, $stepgroupLabel);

		return $this->fetchRow( $this->select()
									->where('stepgroupId = ?', $stepgroupId)
									->where('position = ?', $position)
									);
	}*/

	public function fetchRowByStepgroupIdPosition( $stepgroupId, $position )
	{
		return $this->fetchRow( $this->select()
									->where('stepgroupId = ?', $stepgroupId)
									->where('position = ?', $position)
									);
	}

	public function fetchLastRowByStepgroupId( $stepgroupId )
	{
		return $this->fetchRow( $this->select()
									->where('stepgroupId = ?', $stepgroupId)
									->order('position DESC')
									->limit(1)
									);
	}

	public function fetchLastRowByStepgroupLabel($treatmentId, $stepgroupLabel)
	{
		$stepgroupId = Sophie_Db_Treatment_Stepgroup::getInstance()->getId($treatmentId, $stepgroupLabel);
		return $this->fetchLastRowByStepgroupId( $stepgroupId );
	}

	public function fetchByLabel($treatmentId, $stepLabel)
	{
		$stepgroupIds = Sophie_Db_Treatment_Stepgroup::getInstance()->getIdsByTreatment($treatmentId);
		return $this->fetchRow('stepgroupId IN (' . implode(',', $stepgroupIds). ') AND label = ' . $this->getAdapter()->quote($stepLabel));
	}

	public function fetchByTreatmentIdAndId($treatmentId, $stepId)
	{
		$stepgroupIds = Sophie_Db_Treatment_Stepgroup::getInstance()->getIdsByTreatment($treatmentId);
		return $this->fetchRow('stepgroupId IN (' . implode(',', $stepgroupIds). ') AND id = ' . $this->getAdapter()->quote($stepId));
	}

	public function fetchUsedSteptypes( $treatmentId )
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('step'=>$this->_name), array('steptypeSystemName', 'cnt' => 'COUNT(*)'));

		$select->joinInner(array('stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name), 'step.stepgroupId = stepgroup.id', array());

		$select->joinLeft(array('steptype'=>Sophie_Db_Steptype::getInstance()->_name), 'step.steptypeSystemName = steptype.systemName', array('steptype_name'=>'steptype.name', 'steptype_version'=>'steptype.version'));
		$select->where('stepgroup.treatmentId = ?', $treatmentId);
		$select->order('steptype.name ASC');
		$select->order('steptype.version DESC');
		$select->group(array('step.steptypeSystemName'));


		$dbResult = $select->query()->fetchAll();
		// die('<pre>' . print_r($dbResult, 1));
		$result = array();
		foreach ($dbResult as $res)
		{
			$result[$res['steptypeSystemName']] = $res['steptype_name'] . ' (' . $res['cnt'] . html_entity_decode('&times;', ENT_NOQUOTES, 'UTF-8') . ')';
		}
		return $result;
	}

	public function moveUp($stepId)
	{
		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			$step = $this->find($stepId)->current();
			if ($step->position > 1)
			{
				$oldPosition = $step->position;
				$newPosition = $step->position - 1;

				$step2 = $this->fetchRowByStepgroupIdPosition( $step->stepgroupId, $newPosition );

				$step2->position = new Zend_Db_Expr('NULL');
				$step2->save();

				$step->position = $newPosition;
				$step->save();

				$step2->position = $oldPosition;
				$step2->save();
			}

			$db->commit();

		}
		catch(Exception $e)
		{
			$db->rollBack();
			return false;
		}
		return true;
	}

	public function moveToPosition($stepId, $targetPosition, $targetStepgroupId = null)
	{
		if (!is_numeric($targetPosition))
		{
			throw new Exception('Target position has to be an integer value');
		}

		$targetPosition = (int)$targetPosition;
		if ($targetPosition < 1)
		{
			throw new Exception('Target position has to be at least 1');
		}

		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			$step = $this->find($stepId)->current();

			$stepgroupIdOld = $step->stepgroupId;
			if (is_null($targetStepgroupId))
			{
				$targetStepgroupId = $stepgroupIdOld;
			}
			elseif ($stepgroupIdOld != $targetStepgroupId)
			{
				$oldStepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->find($stepgroupIdOld);
				if (is_null($oldStepgroup))
				{
					throw new Exception('Old stepgroup does not exist');
				}
				$oldStepgroup = $oldStepgroup->current();

				$targetStepgroup = Sophie_Db_Treatment_Stepgroup::getInstance()->find($targetStepgroupId);
				if (is_null($targetStepgroup))
				{
					throw new Exception('Target stepgroup does not exist');
				}

				$targetStepgroup = $targetStepgroup->current();
				if ($targetStepgroup->treatmentId != $oldStepgroup->treatmentId)
				{
					throw new Exception('Target stepgroup does not belong to treatment');
				}
			}

			// set targetPosition to max position in stepgroup
			$lastStep = $this->fetchLastRowByStepgroupId($targetStepgroupId);
			if (is_null($lastStep))
			{
				$targetPosition = 1;
			}
			elseif ($lastStep->position < $targetPosition)
			{
				$targetPosition = $lastStep->position;
			}

			$stepPositionOld = $step->position;
			$step->position = new Zend_Db_Expr('NULL');
			$step->save();

			if ($stepgroupIdOld != $targetStepgroupId)
			{
				$rowsOldBelow = $this->fetchAll( $this->select()
									->where('stepgroupId = ?', $stepgroupIdOld)
									->where('position IS NOT NULL')
									->where('position >= ?', $stepPositionOld)
									->order('position ASC')
									);

				foreach ($rowsOldBelow as $rowOldBelow)
				{
					$rowOldBelow->position = $rowOldBelow->position - 1;
					$rowOldBelow->save();
				}

				$rowsBelow = $this->fetchAll( $this->select()
									->where('stepgroupId = ?', $targetStepgroupId)
									->where('position IS NOT NULL')
									->where('position >= ?', $targetPosition)
									->order('position DESC')
									);

				foreach ($rowsBelow as $rowBelow)
				{
					$rowBelow->position = $rowBelow->position + 1;
					$rowBelow->save();
				}

				$step->stepgroupId = $targetStepgroupId;
			}

			elseif ($stepPositionOld > $targetPosition)
			{
				$rowsAbove = $this->fetchAll( $this->select()
									->where('stepgroupId = ?', $step->stepgroupId)
									->where('position IS NOT NULL')
									->where('position < ?', $stepPositionOld)
									->where('position >= ?', $targetPosition)
									->order('position DESC')
									);

				foreach ($rowsAbove as $rowAbove)
				{
					$rowAbove->position = $rowAbove->position + 1;
					$rowAbove->save();
				}
			}

			elseif ($stepPositionOld < $targetPosition)
			{
				$rowsBelow = $this->fetchAll( $this->select()
									->where('stepgroupId = ?', $step->stepgroupId)
									->where('position IS NOT NULL')
									->where('position > ?', $stepPositionOld)
									->where('position <= ?', $targetPosition)
									->order('position ASC')
									);

				foreach ($rowsBelow as $rowBelow)
				{
					$rowBelow->position = $rowBelow->position - 1;
					$rowBelow->save();
				}
			}

			$step->position = $targetPosition;
			$step->save();

			$db->commit();
		}
		catch(Exception $e)
		{
			$db->rollBack();
			return false;
		}
		return true;
	}

	public function moveDown($stepId)
	{
		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			$step = $this->find($stepId)->current();

			$oldPosition = $step->position;
			$newPosition = $step->position + 1;

			$step2 = $this->fetchRowByStepgroupIdPosition($step->stepgroupId, $newPosition );

			if (! is_null($step2))
			{

				$step2->position = new Zend_Db_Expr('NULL');
				$step2->save();

				$step->position = $newPosition;
				$step->save();

				$step2->position = $oldPosition;
				$step2->save();
			}

			$db->commit();

		}
		catch(Exception $e)
		{
			$db->rollBack();
			return false;
		}
		return true;
	}
}