<?php
class Sophie_Db_Treatment extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment';
	public $_primary = 'id';

	public $_referenceMap = array (
		'Experiment' => array (
			'columns' => array (
				'experimentId'
			),
			'refTableClass' => 'Sophie_Db_Experiment',
			'refColumns' => array (
				'id'
			)
		)
	);

	public $_dependentTables = array (
		'Sophie_Db_Treatment_Stepgroup',
		'Sophie_Db_Treatment_Group',
		'Sophie_Db_Treatment_Type',
		'Sophie_Db_Treatment_Group_Type',
		'Sophie_Db_Session',
		'Sophie_Db_Treatment_Asset',
	);

	// FUNCTIONS
	public function fetchAllJoinCountings($experimentId = null, $showDeleted = false)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array (
			'treatment' => $this->_name
		));
		$select->joinLeft(array (
		'stepgroup' => Sophie_Db_Treatment_Stepgroup :: getInstance()->_name), 'treatment.id = stepgroup.treatmentId', array (
			'stepgroup_count' => new Zend_Db_Expr('count(stepgroup.label)'
		)));
		//    $select->joinLeft(array('group'=>Sophie_Db_Treatment_Group::getInstance()->_name), 'treatment.id = group.treatmentId', array('group_count'=>new Zend_Db_Expr('count(group.number)')));
		$select->joinLeft(array (
		'type' => Sophie_Db_Treatment_Type :: getInstance()->_name), 'treatment.id = type.treatmentId', array (
			'type_count' => new Zend_Db_Expr('count(type.label)'
		)));
		if (!is_null($experimentId))
		{
			$select->where('treatment.experimentId = ?', $experimentId);
		}
		if (!$showDeleted)
		{
			$select->where('treatment.state <> "deleted"');
		}
		$select->group('treatment.id');
		$select->order('treatment.name');
		return $select->query()->fetchAll();
	}

	public function fetchAllPairs()
	{
		// TODO: join experiment and concat name from experiment.name :: treatment.name
		$select = $this->getAdapter()->select();
		$select->from($this->_name, array (
			'id',
			'name'
		));
		$select->order('name');

		$pairs = array ();
		$result = $select->query()->fetchAll();
		foreach ($result as $item)
		{
			$pairs[$item['id']] = $item['name'];
		}
		return $pairs;
	}

	public function getStepgroups($treatmentId)
	{
		$db = $this->getAdapter();

		$dbStepgroup = Sophie_Db_Treatment_Stepgroup :: getInstance();
		return $dbStepgroup->fetchAll($db->quoteInto('treatmentId=?', $treatmentId), 'position ASC');
	}

	public function getGroups($ID, $withID = false, $onlyActive = true)
	{
		$db = $this->getAdapter();

		$sql = 'SELECT * FROM `sophie_treatment_group` WHERE treatmentId = ?';
		if ($onlyActive)
		{
			$sql .= ' AND active=1';
		}
		$sql .= ' ORDER BY name';
		$rows = $db->fetchAll($sql, $ID);

		if ($withID)
		{
			return Achivo_Miscellaneous :: assocID($rows, 'id');
		}
		else
		{
			return $rows;
		}
	}

	public function getTypes($ID, $withID = false)
	{
		$db = $this->getAdapter();

		$sql = 'SELECT * FROM `sophie_treatment_type` WHERE treatmentId= ?';
		$rows = $db->fetchAll($sql, $ID);

		if ($withID)
		{
			return Achivo_Miscellaneous :: assocID($rows, 'id');
		}
		else
		{
			return $rows;
		}
	}
}