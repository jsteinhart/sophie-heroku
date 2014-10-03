<?php
class Sophie_Db_Treatment_Parameter extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_parameter';
	public $_primary = array('treatmentId', 'name');

	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				)
				);

	public function fetchAllByTreatmentId($treatmentId)
	{
		if (is_null($treatmentId))
		{
			throw new Exception('No treatmentId given.');
		}

		$select = $this->select();
		$select->where('treatmentId IN (?)', $treatmentId);
		$result = $select->query();
		return $result->fetchAll();
	}
}