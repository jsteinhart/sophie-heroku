<?php
class Sophie_Db_Session extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session';
	public $_primary = 'id';

	public $_dependentTables = array('Sophie_Db_Session_Participant', 'Sophie_Db_Session_Participant_Group', 'Sophie_Db_Session_Variable');

	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				),
				'Sessiontype' => array(
            		'columns'           => array('sessiontypeId'),
            		'refTableClass'     => 'Sophie_Db_Treatment_Sessiontype',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function fetchAllJoinSessiontypeTreatmentExperiment()
	{
		$select = $this->getAdapter()->select();
		$select->from(array('session'=>$this->_name), '*');

		$select->joinLeft(
					array('sessiontype'=>Sophie_Db_Treatment_Sessiontype::getInstance()->_name),
					'session.sessiontypeId = sessiontype.id',
					array('sessiontype_name'=>'sessiontype.name')
		);

		$select->joinLeft(
					array('treatment'=>Sophie_Db_Treatment::getInstance()->_name),
					'sessiontype.treatmentId = treatment.id',
					array('treatment_name'=>'treatment.name')
		);

		$select->joinLeft(
					array('experiment'=>Sophie_Db_Treatment::getInstance()->_name),
					'treatment.experimentId = experiment.id',
					array('experiment_name'=>'experiment.name')
		);

		$select->order(array('session.name'));
		return $select->query()->fetchAll();
	}

	public function getOverviewSelect()
	{
		$select = $this->getAdapter()->select();
		$select->from(array('session'=>$this->_name), '*');

		$select->joinLeft(
					array('sessiontype'=>Sophie_Db_Treatment_Sessiontype::getInstance()->_name),
					'session.sessiontypeId = sessiontype.id',
					array('sessiontype_name'=>'sessiontype.name')
		);

		$select->joinLeft(
					array('treatment'=>Sophie_Db_Treatment::getInstance()->_name),
					'session.treatmentId = treatment.id',
					array('treatment_name'=>'treatment.name')
		);

		$select->joinLeft(
					array('experiment'=>Sophie_Db_Experiment::getInstance()->_name),
					'treatment.experimentId = experiment.id',
					array('experiment_name'=>'experiment.name')
		);

		return $select;
	}

}