<?php
abstract class Symbic_Db_Table_AbstractTable extends Zend_Db_Table_Abstract
{
	// TODO: replace singleton code with trait
	//use \Symbic_Base_SingletonTrait;

	// SINGLETON
	final public static function getInstance()
	{
		static $instances = array();
		$instanceName = get_called_class();

		if (!isset($instances[$instanceName]))
		{
			$instances[$instanceName] = new $instanceName();
		}
		return $instances[$instanceName];
	}

	// prevent user from cloning a singleton
	final private function __clone()
	{
	}

	// mysql replace
	// TODO: check SQL standard compatibility
	// TODO: implement check if adapter is of type mysql
	public function replace(array $data)
	{
		if (!count($data))
		{
			return;
		}
		$db = $this->getAdapter();
		$sql = 'REPLACE ' . $db->quoteIdentifier($this->_name) . ' ';
		$keysql = '';
		$valuesql = '';

		$isfirst = true;
		foreach ($data as $key => $value)
		{
			if (!$isfirst) $keysql .= ', ';
			if (!$isfirst) $valuesql .= ', ';

			$keysql .= $db->quoteIdentifier($key);
			$valuesql .= $db->quote($value);

			$isfirst=false;
		}
		$sql.= '(' . $keysql . ') VALUES (' . $valuesql . ')';
		$db->getConnection()->query($sql);
	}

	public function getCount()
	{
		$db = $this->getAdapter();
		if ($this->_schema !== null)
		{
			$quotedName = $db->quoteIdentifier(array($this->_schema, $this->_name));
		}
		else
		{
			$quotedName = $db->quoteIdentifier($this->_name);
		}
		return $db->fetchOne('SELECT count(*) FROM ' . $quotedName);
	}

	public function getColumnValueCount($column, $value, $columnExclude = null, $valueExclude = null)
	{
		$db = $this->getAdapter();

		$sql = 'SELECT count(*) FROM ';
		if ($this->_schema !== null)
		{
			$sql .= $db->quoteIdentifier(array($this->_schema, $this->_name));
		}
		else
		{
			$sql .= $db->quoteIdentifier($this->_name);
		}
		$sql .= ' WHERE ' . $db->quoteIdentifier($column);

		if ($value === null)
		{
			$sql .= ' IS NULL';
		}
		else
		{
			$sql .= ' = ' . $db->quote($value);
		}

		if ($columnExclude !== null)
		{
			$sql .= ' AND ' . $db->quoteIdentifier($columnExclude);

			if ($valueExclude === null)
			{
				$sql .= ' IS NULL';
			}
			else
			{
				$sql .= ' = ' . $db->quote($valueExclude);
			}
		}

		return $db->fetchOne($sql);
	}

	public function columnValueExists($column, $value, $columnExclude = null, $valueExclude = null)
	{
		return ($this->getColumnValueCount($column, $value, $columnExclude, $valueExclude) > 0);
	}

	public function fetchAllByColumnValue($columnName, $columnValue)
	{
		$db = $this->getAdapter();
		return $this->fetchAll($db->quoteIdentifier($columnName) . ' = ' . $db->quote($columnValue));
	}

	public function fetchUniqueByColumnValue($columnName, $columnValue)
	{
		$rows = $this->fetchAllByColumnValue($columnName, $columnValue);
		if (sizeof($rows) == 0)
		{
			return null;
		}
		if (sizeof($rows) > 1)
		{
			throw new Exception('Row is not unique for column ' . $columnName);
		}
		return $rows[0]->toArray();
	}
}
