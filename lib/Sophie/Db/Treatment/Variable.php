<?php
class Sophie_Db_Treatment_Variable extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_treatment_variable';
	public $_primary = array (
		'id'
	);

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

	// HELPER
	public function castValue($value, $cast = 'auto')
	{
		switch ($cast)
		{
			case 'auto' :
				if ((string) ((int) ($value)) === $value)
				{
					$value = (int) $value;
				}
				elseif ((string) ((float) ($value)) === $value)
			{
					$value = (float) $value;
				}
				break;

			case 'bool' :
			case 'boolean' :
				$value = (bool) $value;
				break;

			case 'int' :
			case 'integer' :
				$value = (int) $value;
				break;

			case 'float' :
			case 'double' :
				$value = (float) $value;
				break;

			case 'string' :
			case 'text' :
				$value = (string) $value;
				break;

			default :
				// TODO: error handling?
				break;
		}
		return $value;
	}

	public function translateData($row)
	{
		if ($row['groupLabel'] == 'NULL')
		{
			$row['groupLabel'] = NULL;
		}
		if ($row['participantLabel'] == 'NULL')
		{
			$row['participantLabel'] = NULL;
		}
		if ($row['stepgroupLabel'] == 'NULL')
		{
			$row['stepgroupLabel'] = NULL;
		}
		if ($row['stepgroupLoop'] == 0)
		{
			$row['stepgroupLoop'] = NULL;
		}

		if (!is_null($row['value_bool']))
		{
			$row['value'] = (bool) ($row['value_bool']);
		}
		elseif (!is_null($row['value_int']))
		{
			$row['value'] = (int) ($row['value_int']);
		}
		elseif (!is_null($row['value_double']))
		{
			$row['value'] = (float) ($row['value_double']);
		}
		elseif (!is_null($row['value_string']))
		{
			$row['value'] = $row['value_string'];
		}
		elseif (!is_null($row['value_serialized']))
		{
			$row['value'] = unserialize($row['value_serialized']);
		}

		unset ($row['value_bool']);
		unset ($row['value_int']);
		unset ($row['value_double']);
		unset ($row['value_string']);
		unset ($row['value_serialized']);

		return $row;
	}


	public function fetchRowByNameAndContext($name, $treatmentId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$select = $this->select();

		$select->where('name = ?', $name);
		$select->where('treatmentId = ?', $treatmentId);

		if (!is_null($groupLabel))
		{
			if (!is_null($participantLabel))
			{
				throw new Exception('SoPHIE Variable can only be set with a groupLabel or a participantLabel');
			}

			$select->where('groupLabel = ?', $groupLabel);
			$select->where('participantLabel = "NULL"');
		}
		elseif (!is_null($participantLabel))
		{
			$select->where('groupLabel = "NULL"');
			$select->where('participantLabel = ?', $participantLabel);
		}
		else
		{
			$select->where('groupLabel = 0');
			$select->where('participantLabel = "NULL"');
		}

		if (!is_null($stepgroupLabel))
		{
			$select->where('stepgroupLabel = ?', $stepgroupLabel);
			if (!is_null($stepgroupLoop))
			{
				$select->where('stepgroupLoop = ?', $stepgroupLoop);
			}
			else
			{
				$select->where('stepgroupLoop = 0');
			}
		}
		else
		{
			if (!is_null($stepgroupLoop))
			{
				throw new Exception('SoPHIE Variable can not be set with a stepgroupLoop but without a stepgroupLabel');
			}

			$select->where('stepgroupLabel = "NULL"');
			$select->where('stepgroupLoop = 0');
		}

		$result = $select->query();
		if (!$row = $result->fetch())
		{
			return null;
		}

		$row = $this->translateData($row);
		return $row;
	}

	public function fetchRowById($id)
	{
		$select = $this->select();

		$select->where('id = ?', $id);

		$result = $select->query();
		if (!$row = $result->fetch())
		{
			return null;
		}

		return $this->translateData($row);
	}


	public function fetchAllByTreatmentId($treatmentId, $order = null)
	{
		if (is_null($treatmentId))
		{
			throw new Exception('No $treatmentId given.');
		}

		$select = $this->select();
		$select->where('treatmentId IN (?)', $treatmentId);

		if (!is_null($order))
		{
			$select->order($order);
		}
		
		$result = $select->query();
		$rows = $result->fetchAll();

		foreach ($rows as &$row)
		{
			$row = $this->translateData($row);
		}

		return $rows;
	}

	public function setValueByNameAndContext($name, $value, $treatmentId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$data = array ();
		$data['name'] = $name;
		$data['treatmentId'] = $treatmentId;

		if (!is_null($groupLabel))
		{
			if (!is_null($participantLabel))
			{
				throw new Exception('SoPHIE Variable can only be set with a groupLabel or a participantLabel');
			}

			$data['groupLabel'] = $groupLabel;
			$data['participantLabel'] = 'NULL';
		}
		elseif (!is_null($participantLabel))
		{
			$data['groupLabel'] = 'NULL';
			$data['participantLabel'] = $participantLabel;
		}
		else
		{
			$data['groupLabel'] = 'NULL';
			$data['participantLabel'] = 'NULL';
		}

		if (!is_null($stepgroupLabel))
		{
			$data['stepgroupLabel'] = $stepgroupLabel;
			if (!is_null($stepgroupLoop))
			{
				$data['stepgroupLoop'] = $stepgroupLoop;
			}
			else
			{
				$data['stepgroupLoop'] = 0;
			}
		}
		else
		{
			if (!is_null($stepgroupLoop))
			{
				throw new Exception('SoPHIE Variable can not be set with a stepgroupLoop but without a stepgroupLabel');
			}

			$data['stepgroupLabel'] = 'NULL';
			$data['stepgroupLoop'] = 0;
		}

		// is_null -> delete value
		if (is_null($value))
		{
			$select = $this->deleteWhere($data);
			return;
		}

		$data['value_bool'] = new Zend_Db_Expr('NULL');
		$data['value_int'] = new Zend_Db_Expr('NULL');
		$data['value_double'] = new Zend_Db_Expr('NULL');
		$data['value_string'] = new Zend_Db_Expr('NULL');
		$data['value_serialized'] = new Zend_Db_Expr('NULL');

		// is_bool
		if (is_bool($value))
		{
			$data['value_bool'] = $value ? 1 : 0;
		}

		// is_int
		elseif (is_int($value))
		{
			$data['value_int'] = $value;
		}

		// is_float
		elseif (is_float($value))
		{
			$data['value_double'] = $value;
		}

		// is_string
		elseif (is_string($value))
		{
			$data['value_string'] = $value;
		}

		// is_object / is_array -> serialize
		elseif (is_object($value) || is_array($value))
		{
			$data['value_serialized'] = serialize($value);
		}

		// is_resource -> throw exception
		elseif (is_resource($value))
		{
			throw new Exception('Resources can not be saved as SoPHIE variables');
		}

		else
		{
			throw new Exception('Unkown variable type can not be saved as SoPHIE variables');
		}

		$this->replace($data);
	}


	public function import($treatmentId, $data)
	{
		$context = strtolower(array_shift($data));
		$personContext = substr($context, 0, 1);
		$proceduralContext = substr($context, 1);

		$name = array_shift($data);
		$value = array_shift($data);

		$groupLabel = null;
		$participantLabel = null;

		if ($personContext == 'g')
		{
			$groupLabel = array_shift($data);
		}
		elseif ($personContext == 'p')
		{
			$participantLabel = array_shift($data);
		}

		$stepgroupLabel = null;
		$stepgroupLoop = null;

		if ($proceduralContext == 'sg')
		{
			$stepgroupLabel = array_shift($data);
		}
		elseif ($proceduralContext == 'sl')
		{
			$stepgroupLabel = array_shift($data);
			$stepgroupLoop = array_shift($data);
		}

		// cast value
		if (sizeof($data) > 0)
		{
			$cast = array_shift($data);
		}
		else
		{
			$cast = 'auto';
		}
		$value = $this->castValue($value, $cast);

		return $this->setValueByNameAndContext($name, $value, $treatmentId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
	}
}