<?php
class Sophie_Db_Treatment_Stepgroup extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_stepgroup';
	public $_primary = 'id';

	public $_referenceMap = array (
		'Treatment' => array (
			'columns' => array (
				'treatmentId'
			),
			'refTableClass' => 'Sophie_Db_Treatment',
			'refColumns' => array (
				'id'
			)
		)
	);

	public $_dependentTables = array (
		'Sophie_Db_Treatment_Step',
		'Sophie_Db_Treatment_Variable'
	);

	// FUNCTIONS

	public function insertPosition(array $data)
	{
		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			// fetch treatment to have a locking singleton
			$treatment = Sophie_Db_Treatment::getInstance()->find($data['treatmentId'])->current();
			$data['position'] = sizeof($treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup')) + 1;

			$stepgroupId = $this->insert($data);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			return false;
		}

		return $stepgroupId;
	}

	public function deletePosition($stepgroupId)
	{
		$db = $this->getAdapter();

		try
		{

			$db->beginTransaction();

			$stepgroup = $this->find($stepgroupId)->current();
			$treatmentId = $stepgroup->treatmentId;
			$stepgroupPosition = $stepgroup->position;

			$stepgroup->delete();

			$rowsBelow = $this->fetchAll($this->select()->where('treatmentId = ?', $treatmentId)->where('position > ?', $stepgroupPosition));

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
			return false;
		}

		return true;
	}

	public function getId($treatmentId, $stepgroupLabel)
	{
		$db = Zend_Registry::get('db');
		$result = $db->query('SELECT id FROM sophie_treatment_stepgroup WHERE treatmentId = ? AND label = ?', array($treatmentId, $stepgroupLabel));
		if (!$stepgroupId = $result->fetchColumn())
		{
			throw new Exception('Fetch stepgroup id failed: Stepgroup does not exist');
		}
		else
		{
			return $stepgroupId;
		}
	}

	public function getIdsByTreatment($treatmentId)
	{
		$db = Zend_Registry::get('db');
		$stepgroupIds = $db->fetchCol('SELECT id FROM sophie_treatment_stepgroup WHERE treatmentId = ? ORDER BY position', array($treatmentId));
		return $stepgroupIds;
	}
	
	public function fetchByLabel($treatmentId, $stepgroupLabel)
	{
		return $this->fetchRow('treatmentId = ' . $treatmentId . ' AND label = ' . $this->getAdapter()->quote($stepgroupLabel));
	}

	// TODO: add support for type filtering
	public function fetchRowByTreatmentPosition($treatmentId, $typeId = null, $position = 1)
	{
		return $this->fetchRow($this->select()->where('treatmentId=?', $treatmentId)->where('position = ?', $position));
	}

	// TODO: add support for type filtering
	public function fetchRowByTreatmentPositionActive($treatmentId, $typeId = null, $position = 1, $filterEmpty = false)
	{
		return $this->fetchRow($this->select()->where('treatmentId=?', $treatmentId)->where('position = ?', $position)->where('active = ?', 1)->where('loop > ?', 0));
	}

	// TODO: add support for type filtering
	public function fetchRowByTreatmentPositionActiveNonEmpty($treatmentId, $typeLabel = null, $position = 1)
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array (
			'sophie_treatment_stepgroup' => $this->_name
		), array (
			'*',
			'stepCount' => new Zend_Db_Expr('count(*)'
		)));
		$select->joinLeft(array (
		'sophie_treatment_step' => Sophie_Db_Treatment_Step :: getInstance()->_name), 'sophie_treatment_stepgroup.id = sophie_treatment_step.stepgroupId', array ());
		$select->where('sophie_treatment_stepgroup.treatmentId = ?', $treatmentId);
		$select->where('sophie_treatment_stepgroup.position >= ?', $position);
		$select->where('sophie_treatment_stepgroup.active = 1');
		$select->where('sophie_treatment_stepgroup.loop <> 0');
		$select->group('sophie_treatment_stepgroup.id');
		$select->having('stepCount > 0');
		$select->order('sophie_treatment_stepgroup.position ASC');
		return $select->query()->fetch();
	}

	public function fetchRowByTreatmentAndLabel($treatmentId, $label)
	{
		return $this->fetchRow($this->select()->where('treatmentId=?', $treatmentId)->where('label = ?', $label));
	}

	public function fetchLastRowByTreatmentId( $treatmentId )
	{
		return $this->fetchRow( $this->select()
									->where('treatmentId = ?', $treatmentId)
									->order('position DESC')
									->limit(1)
									);
	}
	
	public function moveUp($stepgroupId)
	{
		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			$stepgroup = $this->find($stepgroupId)->current();
			if ($stepgroup->position > 1)
			{
				$oldPosition = $stepgroup->position;
				$newPosition = $stepgroup->position - 1;

				$stepgroup2 = $this->fetchRowByTreatmentPosition($stepgroup->treatmentId, null, $newPosition);

				$stepgroup2->position = new Zend_Db_Expr('NULL');
				$stepgroup2->save();

				$stepgroup->position = $newPosition;
				$stepgroup->save();

				$stepgroup2->position = $oldPosition;
				$stepgroup2->save();
			}

			$db->commit();

		}
		catch (Exception $e)
		{
			$db->rollBack();
			return false;
		}
		return true;
	}

	public function moveDown($stepgroupId)
	{
		$db = $this->getAdapter();

		try
		{
			$db->beginTransaction();

			$stepgroup = $this->find($stepgroupId)->current();

			$oldPosition = $stepgroup->position;
			$newPosition = $stepgroup->position + 1;

			$stepgroup2 = $this->fetchRowByTreatmentPosition($stepgroup->treatmentId, null, $newPosition);

			if (!is_null($stepgroup2))
			{
				$stepgroup2->position = new Zend_Db_Expr('NULL');
				$stepgroup2->save();

				$stepgroup->position = $newPosition;
				$stepgroup->save();

				$stepgroup2->position = $oldPosition;
				$stepgroup2->save();
			}

			$db->commit();

		}
		catch (Exception $e)
		{
			$db->rollBack();
			return false;
		}
		return true;
	}

	public function moveToPosition($stepgroupId, $targetPosition)
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

		//try
		//{
			$db->beginTransaction();

			$stepgroup = $this->find($stepgroupId)->current();

			// set targetPosition to max position in stepgroup
			$lastStepgroup = $this->fetchLastRowByTreatmentId($stepgroup->treatmentId);
			if ($lastStepgroup->position < $targetPosition)
			{
				$targetPosition = $lastStepgroup->position;
			}
			
			$stepgroupPositionOld = $stepgroup->position;
			$stepgroup->position = new Zend_Db_Expr('NULL');
			$stepgroup->save();

			if ($stepgroupPositionOld > $targetPosition)
			{
				$rowsAbove = $this->fetchAll( $this->select()
									->where('treatmentId = ?', $stepgroup->treatmentId)
									->where('position IS NOT NULL')
									->where('position < ?', $stepgroupPositionOld)
									->where('position >= ?', $targetPosition)
									->order('position DESC')
									);

				foreach ($rowsAbove as $rowAbove)
				{
					$rowAbove->position = $rowAbove->position + 1;
					$rowAbove->save();
				}
			}

			elseif ($stepgroupPositionOld < $targetPosition)
			{
				$rowsBelow = $this->fetchAll( $this->select()
									->where('treatmentId = ?', $stepgroup->treatmentId)
									->where('position IS NOT NULL')
									->where('position > ?', $stepgroupPositionOld)
									->where('position <= ?', $targetPosition)
									->order('position ASC')
									);

				foreach ($rowsBelow as $rowBelow)
				{
					$rowBelow->position = $rowBelow->position - 1;
					$rowBelow->save();
				}
			}

			$stepgroup->position = $targetPosition;
			$stepgroup->save();

			$db->commit();
		//}
		//catch(Exception $e)
		//{
		//	$db->rollBack();
		//	return false;
		//}
		return true;
	}

	public function copyById($id, $overrideData = array())
	{
		$stepgroup = $this->find($id)->current();
		if (is_null($stepgroup))
		{
			return false;
		}
		
		$values = $stepgroup->toArray();
		unset($values['id']);
		$values['position']++;

		$values['label'] = substr($values['label'], 0, 30) . '_' . uniqid();

		$values = array_merge($values, $overrideData);

		$newId = $this->insertPosition($values);

		$stepModel = Sophie_Db_Treatment_Step::getInstance();
		$steps = $stepModel->fetchAll('stepgroupId = ' . $stepgroup->id);
		
		foreach ($steps as $step)
		{
			$stepModel->copyById($step->id, array('stepgroupId' => $newId));
		}

		return $newId;
	}	
}