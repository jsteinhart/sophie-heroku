<?php
class Symbic_Db_Table_Existence extends Symbic_Db_Table_AbstractTable
{
	protected $tableExists = false;

	public function __construct()
	{
		$instance = parent :: __construct();

		$sql = 'SHOW TABLES LIKE ' . $this->getAdapter()->quote($this->_name);
		$this->tableExists = $this->getAdapter()->query($sql)->rowCount();

		return $instance;
	}

	public function replace(array $data)
	{
		return ($this->tableExists) ? parent :: replace($data) : null;
	}

	public function insert(array $data)
	{
		return ($this->tableExists) ? parent :: insert($data) : null;
	}

	public function update(array $data, $where)
	{
		return ($this->tableExists) ? parent :: update($data, $where) : null;
	}
}
