<?php
class Sophie_Db_Treatment_Sessiontype_Parameter extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_sessiontype_parameter';
	public $_primary = array('sessiontypeId', 'name');

	public $_referenceMap	 = array(
				'Sessiontype' => array(
					'columns'			=> array('sessiontypeId'),
					'refTableClass'		=> 'Sophie_Db_Treatment_Sessiontype',
					'refColumns'		=> array('id')
				)
				);

	public function fetchAllBySessiontypeId($sessiontypeId)
	{
		if (is_null($sessiontypeId))
		{
			throw new Exception('No sessiontypeId given.');
		}

		$select = $this->select();
		$select->where('sessiontypeId IN (?)', $sessiontypeId);
		$result = $select->query();
		return $result->fetchAll();
	}
}