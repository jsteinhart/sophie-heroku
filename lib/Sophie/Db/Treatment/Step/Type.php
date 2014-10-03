<?php
class Sophie_Db_Treatment_Step_Type extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_step_type';
	public $_primary = array('stepId','typeLabel');

	public $_referenceMap    = array(
				'Step' => array(
            		'columns'           => array('stepId'),
            		'refTableClass'     => 'Sophie_Db_Treatment_Step',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function getByStep($id)
	{
		return $this->fetchAll($this->select()->where('stepId=?',$id));
	}

	public function setByStep($id, $types = array())
	{
		$db = Zend_Registry::get('db');

		if (sizeof($types) == 0)
		{
			$this->delete('stepId = ' . $id);
			return;
		}

		// loop over types
		foreach ($types as $typeLabel)
		{
			$this->replace(array('stepId'=>$id, 'typeLabel'=>$typeLabel));
		}

		foreach ($types as &$type)
		{
			$type = $db->quote($type);
		}

		$this->delete('stepId = ' . $id . ' AND NOT typeLabel IN (' . join(',', $types) . ')');
	}
}