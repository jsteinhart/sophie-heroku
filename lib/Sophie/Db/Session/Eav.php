<?php
class Sophie_Db_Session_Eav extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'sophie_session_eav';
	public $_primary = array('sessionId', 'name');

	public $_referenceMap	 = array(
	'Step' => array(
		'columns'		=> array('sessionId'),
		'refTableClass'	=> 'Sophie_Db_Session',
		'refColumns'	=> array('id')
	));

	// FUNCTIONS
	public function insertIgnore(array $data)
	{
		return $this->_insert($data, 'ignore');
	}

	private function _insert(array $data, $type)
	{
		if ($type !== 'replace' && $type !== 'ignore')
		{
			throw new Exception('Unknown type: ' . $type);
		}

		if (count($data) > 0)
		{
			$db = $this->getAdapter();
			$sql = 'INSERT ';
			if ($type === 'ignore')
			{
				$sql .= ' IGNORE ';
			}
			$sql .= ' INTO ' . $db->quoteIdentifier($this->_name) . ' SET ';

			$valuesSql = '';
			$isfirst = true;
			foreach ($data as $key=>$value)
			{
				if (!$isfirst) $valuesSql .= ', ';

				$valuesSql .= $db->quoteIdentifier($key);
				$valuesSql .= '=';
				$valuesSql .= $db->quote($value);

				$isfirst=false;
			}

			$sql .= $valuesSql;
			if ($type === 'replace')
			{
				$sql .= ' ON DUPLICATE KEY UPDATE ';
				$sql .= $valuesSql;
			}

			$db->getConnection()->exec($sql);
		}
	}

	public function get($sessionId, $name)
	{
		$result = $this->find($sessionId, $name)->current();
		if (is_null($result))
		{
			return null;
		}
		return $result->value;
	}

	public function getAll($sessionId)
	{
		$result = array();

		$eav = $this->fetchAll(array('sessionId = ?' => $sessionId))->toArray();
		foreach ($eav as $value)
		{
			$result[ $value['name'] ] = $value['value'];
		}
		return $result;
	}
}