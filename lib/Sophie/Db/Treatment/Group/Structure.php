<?php
class Sophie_Db_Treatment_Group_Structure extends Symbic_Db_Table_Abstract
{
	// CONFIG
	protected $_name = 'sophie_treatment_group_structure';
	public $_referenceMap    = array(
				'Treatment' => array(
            		'columns'           => array('treatmentId'),
            		'refTableClass'     => 'Sophie_Db_Treatment',
            		'refColumns'        => array('id')
				));


	public function insert(array $data)
	{
		$this->assembleData($data, true);
		return parent::insert($data);
	}

	public function update(array $data, $where)
	{
		$this->assembleData($data, false);
		return parent::update($data, $where);
	}

	public function fetchDisassembledRow($treatmentId, $label)
	{
		// fetch row
		$select = $this->select();
		$select->where('treatmentId = ?', $treatmentId);
		$select->where('label = ?', $label);
		$result = $this->fetchRow($select);
		// create row if not existing
		if (is_null($result))
		{
			$data = array(
				'treatmentId' => $treatmentId,
				'label' => $label,
				'name' => $label
			);
			$this->insert($data);
			return $this->fetchDisassembledRow($treatmentId, $label);
		}
		// disassemble json data
		$result = $result->toArray();
		$structure = json_decode($result['structureJson'], true);
		$result['structure'] = array();

		// check structure against group types
		$Type = Sophie_Db_Treatment_Type :: getInstance();
		$select = $Type->select();
		$select->where('treatmentId = ?', $treatmentId);
		$select->order('label');
		$allTypes = $Type->fetchAll($select);
		foreach ($allTypes as $t)
		{
			if (isset($structure[$t->label]))
			{
				$result['structure'][$t->label] = $structure[$t->label];
			}
			else
			{
				$result['structure'][$t->label] = array(
					'min' => 0,
					'max' => 0
				);
			}
			$result['structure'][$t->label]['name'] = $t->name;
		}
		$result['structureJson'] = json_encode($result['structure']);
		return $result;
	}

	private function assembleData(&$data, $createDefault = false)
	{
		if (isset($data['structureJson']))
		{
			throw new Exception('It is not allowed to set structureJson directly. Use an array of object $data["structure"] instead.');
		}
		if (isset($data['structure']) && (is_array($data['structure']) || is_object($data['structure'])))
		{
			$data['structureJson'] = json_encode($data['structure']);
			unset($data['structure']);
		}
		elseif($createDefault && isset($data['treatmentId']))
		{
			$Type = Sophie_Db_Treatment_Type :: getInstance();
			$select = $Type->select();
			$select->where('treatmentId = ?', $data['treatmentId']);
			$allTypes = $Type->fetchAll($select);

			$structure = array();
			foreach ($allTypes as $t)
			{
				$structure[$t->label] = array(
					'min' => 0,
					'max' => 0
				);
			}
			$data['structureJson'] = json_encode($structure);
		}
	}
}