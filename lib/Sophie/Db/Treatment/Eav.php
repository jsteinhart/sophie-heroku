<?php
class Sophie_Db_Treatment_Eav extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_eav';
	public $_primary = array('treatmentId', 'name');

	public $_referenceMap    = array(
				'Step' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function get($treatmentId, $name)
	{
		$result = $this->find($treatmentId, $name)->current();
		if (is_null($result))
		{
			return null;
		}
		return $result->value;
	}
	
	public function getAll($treatmentId)
	{
		$result = array();
		
		$eav = $this->fetchAll(array('treatmentId = ?' => $treatmentId))->toArray();
		foreach ($eav as $value)
		{
			$result[ $value['name'] ] = $value['value'];
		}
		return $result;
	}

}