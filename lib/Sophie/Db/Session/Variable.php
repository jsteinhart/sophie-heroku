<?php
class Sophie_Db_Session_Variable extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_variable';
	public $_primary = 'id';

	public $_referenceMap = array (
		'Session' => array (
			'columns' => array (
				'sessionId'
			),
			'refTableClass' => 'Sophie_Db_Session',
			'refColumns' => array (
				'id'
			)
		)
	);

	protected $deleteOnNullValue = true;

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

	public function deleteWhere($data)
	{

		if (sizeof($data) > 0)
		{
			$db = Zend_Registry :: get('db');
			$sql = 'DELETE FROM `' . $this->_name . '` WHERE ';

			$isfirst = true;
			foreach ($data as $key => $value)
			{
				if (!$isfirst)
					$sql .= ' AND ';

				if (is_null($value))
				{
					$sql .= $db->quoteIdentifier($key) . ' IS NULL';
				}
				else
				{
					$sql .= $db->quoteIdentifier($key) . ' = ' . $db->quote($value);
				}

				$isfirst = false;
			}
			$db->getConnection()->exec($sql);
		}
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

		if (array_key_exists('value_bool', $row) && !is_null($row['value_bool']))
		{
			$row['value'] = (bool) ($row['value_bool']);
		}
		elseif (array_key_exists('value_int', $row) && !is_null($row['value_int']))
		{
			$row['value'] = (int) ($row['value_int']);
		}
		elseif (array_key_exists('value_double', $row) && !is_null($row['value_double']))
		{
			$row['value'] = (float) ($row['value_double']);
		}
		elseif (array_key_exists('value_string', $row) && !is_null($row['value_string']))
		{
			$row['value'] = $row['value_string'];
		}
		elseif (array_key_exists('value_serialized', $row) && !is_null($row['value_serialized']))
		{
			$row['value'] = unserialize($row['value_serialized']);
		}
		else
		{
//			throw new Exception('Unknown type of value for SoPHIE Variable');
		}

		unset ($row['value_bool']);
		unset ($row['value_int']);
		unset ($row['value_double']);
		unset ($row['value_string']);
		unset ($row['value_serialized']);

		return $row;
	}

	// FUNCTIONS
	public function fetchValueByNameAndContext($name, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$row = $this->fetchRowByNameAndContext($name, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		if (is_null($row))
		{
			return null;
		}
		else
		{
			return $row['value'];
		}
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


	public function fetchRowByNameAndContext($name, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$select = $this->select();

		$select->where('name = ?', $name);
		$select->where('sessionId = ?', $sessionId);

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

	public function fetchAllByNameAndContext($name = null, $excludeSystemVariables = false, $sessionId = null, $types = null, $stepgroupLabel = null, $order = null, $andWhere = null)
	{
		$select = $this->select();

		if (!is_null($name))
		{
			if (is_string($name))
			{
				$name = array( $name );
			}
			if (!is_array($name))
			{
				throw new Exception('$name must be a string or an array!');
			}
			if (sizeof($name) > 0)
			{
				$select->where('name IN (?)', $name);
			}
		}

		if ($excludeSystemVariables)
		{
			$select->where('NOT name LIKE "\_\_%"');
		}

		if (!is_null($sessionId))
		{
			$select->where('sessionId IN (?)', $sessionId);
		}

		if (!is_null($types))
		{
			if (is_string($types))
			{
				$types = array (
					$types
				);
			}

			if (!is_array($types))
			{
				throw new Exception('Filtering variables by type can only by done using a filter string or an array');
			}

			$typesWhere = array ();
			foreach ($types as $type)
			{
				$type = strtolower($type);

				switch ($type)
				{
					case 'ee':
						$typesWhere[] = '(groupLabel = "NULL" AND participantLabel = "NULL" AND stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'es':
						$typesWhere[] = '(groupLabel = "NULL" AND participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'esl':
						$typesWhere[] = '(groupLabel = "NULL" AND participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND NOT stepgroupLoop = 0)';
						break;

					case 'ge':
						$typesWhere[] = '(NOT groupLabel = "NULL" AND participantLabel = "NULL" AND stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'gs':
						$typesWhere[] = '(NOT groupLabel = "NULL" AND participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'gsl':
						$typesWhere[] = '(NOT groupLabel = "NULL" AND participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND NOT stepgroupLoop = 0)';
						break;

					case 'pe':
						$typesWhere[] = '(groupLabel = "NULL" AND NOT participantLabel = "NULL" AND stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'ps':
						$typesWhere[] = '(groupLabel = "NULL" AND NOT participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND stepgroupLoop = 0)';
						break;

					case 'psl':
						$typesWhere[] = '(groupLabel = "NULL" AND NOT participantLabel = "NULL" AND NOT stepgroupLabel = "NULL" AND NOT stepgroupLoop = 0)';
						break;

					default:
						throw new Exception('Filtering variable type unkown: ' . $type);
						break;
				}
			}

			if (sizeof($typesWhere) > 0)
			{
				$select->where(join($typesWhere, ' OR '));
			}
		}

		if (!is_null($stepgroupLabel))
		{
			$select->where('stepgroupLabel IN (?)', $stepgroupLabel);
		}

		if (!is_null($order))
		{
			$select->order($order);
		}

		if (!is_null($andWhere))
		{
			$select->where($andWhere);
		}

		$result = $select->query();
		$rows = $result->fetchAll();

		foreach ($rows as & $row)
		{
			$row = $this->translateData($row);
		}

		return $rows;
	}

	/*
	 * Note: 'NULL' vs. new Zend_Db_Expr('NULL')
	 * Some fields will get the default string 'NULL' whereas others will get
	 * the NULL values as default value.
	 * Background: The variable is identified by a set of fields including some
	 * context fields (groupLabel, participantLabel, stepgroupLabel, stepgroupLoop).
	 * To use this set (with sessionId and the variable's name) as identifyer
	 * there exists a UNIQUE KEY (made of these fields). A value set containing
	 * one or more NULL values won't be added to this KEY and therefore won't match.
	 * To work around this issue the corresponding labels are set to the string
	 * 'NULL'.
	 */
	protected function createData($name, $value, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$data = array ();
		$data['name'] = $name;
		$data['sessionId'] = $sessionId;

		if (!is_null($groupLabel))
		{
			if (!is_null($participantLabel))
			{
				throw new Exception('SoPHIE Variable can only be set with a groupLabel or a participantLabel');
			}

			$data['groupLabel'] = $groupLabel;
			$data['participantLabel'] = 'NULL';	// see above
		}
		elseif (!is_null($participantLabel))
		{
			$data['groupLabel'] = 'NULL';
			$data['participantLabel'] = $participantLabel;
		}
		else
		{
			$data['groupLabel'] = 'NULL';		// see above
			$data['participantLabel'] = 'NULL';	// see above
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

			$data['stepgroupLabel'] = 'NULL';	// see above
			$data['stepgroupLoop'] = 0;
		}

		// is_null -> delete value
		if (is_null($value))
		{
			if ($this->deleteOnNullValue)
			{
				$select = $this->deleteWhere($data);
			}
			return null;
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
		return $data;
	}

	public function setValueByNameAndContext($name, $value, $sessionId, $groupLabel = null, $participantLabel = null, $stepgroupLabel = null, $stepgroupLoop = null)
	{
		$data = $this->createData($name, $value, $sessionId, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);
		if (is_array($data))
		{
			$this->replace($data);
		}
	}

	public function fetchAllDistinctNames($sessionId)
	{
		$select = $this->getAdapter()->select();
		$select->from(array('v'=>$this->_name), array('name'));
		$select->distinct();
		$select->where('sessionId = ?', $sessionId);
		$select->order('name');
		return $select->query()->fetchAll();
	}

	public function import($sessionId, $data)
	{
		$data2 = array ();
		$data2['sessionId'] = $sessionId;

		$context = strtolower(array_shift($data));
		$personContext = substr($context, 0, 1);
		$proceduralContext = substr($context, 1);

		$data2['name'] = array_shift($data);
		$value = array_shift($data);

		$data2['groupLabel'] = 'NULL';
		$data2['participantLabel'] = 'NULL';

		if ($personContext == 'g')
		{
			$data2['groupLabel'] = array_shift($data);
		}
		elseif ($personContext == 'p')
		{
			$data2['participantLabel'] = array_shift($data);
		}

		$data2['stepgroupLabel'] = 'NULL';
		$data2['stepgroupLoop'] = 'NULL';

		if ($proceduralContext == 'sg')
		{
			$data2['stepgroupLabel'] = array_shift($data);
		}
		elseif ($proceduralContext == 'sl')
		{
			$data2['stepgroupLabel'] = array_shift($data);
			$data2['stepgroupLoop'] = array_shift($data);
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

		$data2['value_bool'] = new Zend_Db_Expr('NULL');
		$data2['value_int'] = new Zend_Db_Expr('NULL');
		$data2['value_double'] = new Zend_Db_Expr('NULL');
		$data2['value_string'] = new Zend_Db_Expr('NULL');
		$data2['value_serialized'] = new Zend_Db_Expr('NULL');

		// is_bool
		if (is_bool($value))
		{
			$data2['value_bool'] = $value ? 1 : 0;
		}

		// is_int
		elseif (is_int($value))
		{
			$data2['value_int'] = $value;
		}

		// is_float
		elseif (is_float($value))
		{
			$data2['value_double'] = $value;
		}

		// is_string
		elseif (is_string($value))
		{
			$data2['value_string'] = $value;
		}

		// is_object / is_array -> serialize
		elseif (is_object($value) || is_array($value))
		{
			$data2['value_serialized'] = serialize($value);
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

		$this->replace($data2);
	}
}