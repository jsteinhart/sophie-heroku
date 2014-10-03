<?php
class Sophie_Db_Treatment_Step_Eav extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_step_eav';
	public $_primary = array('stepId', 'name');

	public $_referenceMap    = array(
				'Step' => array(
            		'columns'           => array('stepId'),
            		'refTableClass'     => 'Sophie_Db_Treatment_Step',
            		'refColumns'        => array('id')
				));

	// FUNCTIONS
	public function get($stepId, $name)
	{
		$result = $this->find($stepId, $name)->current();
		if (is_null($result))
		{
			return null;
		}
		return $result->value;
	}
	
	public function fetchUsedAttributes( $treatmentId )
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('eav'=>$this->_name), array('eav_name' => 'name', 'cnt' => 'COUNT(*)'));
		
		$select->joinInner(array('step'=>Sophie_Db_Treatment_Step::getInstance()->_name), 'eav.stepId = step.id', array());
		$select->joinInner(array('stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name), 'step.stepgroupId = stepgroup.id', array());
		
		$select->where('stepgroup.treatmentId = ?', $treatmentId);
		$select->where('eav.value != ""');
		$select->order('eav.name ASC');
		$select->group(array('eav.name'));
		
		$dbResult = $select->query()->fetchAll();
		// die('<pre>' . print_r($dbResult, 1));
		$result = array();
		foreach ($dbResult as $res)
		{
			$result[$res['eav_name']] = $res['eav_name'] . ' (' . $res['cnt'] . html_entity_decode('&times;', ENT_NOQUOTES, 'UTF-8') . ')';
		}
		return $result;
	}
	
	public function searchStructure( $treatmentId, $query, $steptypes, $attribs )
	{
		$select = Zend_Registry::get('db')->select();
		$select->from(array('eav'=>$this->_name), array('stepId', 'eav_name' => 'name', 'eav_value' => 'value'));
		
		$select->joinInner(array('step'=>Sophie_Db_Treatment_Step::getInstance()->_name), 'eav.stepId = step.id', array('step_name' => 'name'));
		$select->joinInner(array('stepgroup'=>Sophie_Db_Treatment_Stepgroup::getInstance()->_name), 'step.stepgroupId = stepgroup.id', array());
		
		$select->joinLeft(array('steptype'=>Sophie_Db_Steptype::getInstance()->_name), 'step.steptypeSystemName = steptype.systemName', array('steptype_name'=>'steptype.name', 'steptype_version'=>'steptype.version'));
		
		$select->where('stepgroup.treatmentId = ?', $treatmentId);
		$select->where('step.steptypeSystemName IN (?)', $steptypes);
		$select->where('eav.value LIKE ?', '%' . $query . '%');
		$select->where('eav.name IN (?)', $attribs);
		
		$dbResult = $select->query()->fetchAll();
		$result = array();
		
		$i = 0;
		$lastStepId = null;
		foreach ($dbResult as $res)
		{
			if ($lastStepId != $res['stepId'])
			{
				$i++;
				$result[$i] = $res;
				$result[$i]['eav'] = array();
				$lastStepId = $res['stepId'];
			}
			$result[$i]['eav'][] = array(
				'name' => $res['eav_name'],
				'value' => $res['eav_value']
			);
		}
		return $result;
	}
}