<?php
class Sophie_Db_Experiment extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_experiment';
	public $_primary = 'id';

	public $_dependentTables = array (
		'Sophie_Db_Treatment'
	);

	// FUNCTIONS
	public function fetchAllJoinTreatmentCount()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array (
			'experiment' => $this->_name
		));
		$select->joinLeft(array (
		'treatment' => Sophie_Db_Treatment :: getInstance()->_name), 'experiment.id = treatment.experimentId', array (
			'treatment_count' => new Zend_Db_Expr('count(distinct treatment.id)'
		)));
		$select->group('experiment.id');
		$select->order('experiment.name');
		return $select->query()->fetchAll();
	}

	public function fetchAllJoinTreatmentCountOwnerName()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array (
			'experiment' => $this->_name
		));
		$select->joinLeft(array (
		'treatment' => Sophie_Db_Treatment :: getInstance()->_name), 'experiment.id = treatment.experimentId', array (
			'treatment_count' => new Zend_Db_Expr('count(distinct treatment.id)'
		)));
		$select->joinLeft(array (
		'user' => System_Db_User :: getInstance()->_name), 'experiment.ownerId = user.id', array (
			'ownerName' => 'user.Name'
		));
		$select->group('experiment.id');
		$select->order('experiment.name');
		return $select->query()->fetchAll();
	}

	public function getOverviewSelect()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array (
			'experiment' => $this->_name
		), '*');

		$select->joinLeft(
			array ('treatment' => Sophie_Db_Treatment :: getInstance()->_name), 
			'experiment.id = treatment.experimentId AND treatment.state <> "deleted"', 
			array ('treatment_count' => new Zend_Db_Expr('count(distinct treatment.id)'))
		);

		$select->joinLeft(array (
		'user' => System_Db_User :: getInstance()->_name), 'experiment.ownerId = user.id', array (
			'ownerName' => 'user.Name'
		));

		$select->where('experiment.state <> "deleted"');

		return $select;
	}
}