<?php
class Sophie_Session_Transaction
{
	private $sessionId = null;
	private $db = null;

	public function __construct($sessionId)
	{
		$this->sessionId = $sessionId;
	}

	public function getDb()
	{
		if (is_null($this->db))
		{
			$this->db = Zend_Registry :: get('db');
		}
		return $this->db;
	}

	public function begin()
	{
		$db = $this->getDb();
		$db->beginTransaction();

		// cp. http://dev.mysql.com/doc/refman/5.1/en/innodb-deadlocks.html
		// set update to lock auxiliary “semaphore” table
		$db->query('UPDATE sophie_session SET lastLock = NOW() WHERE id=' . $db->quote($this->sessionId));
	}

	public function commit()
	{
		$db = $this->getDb();
		$db->commit();
	}

	public function rollBack()
	{
		$db = $this->getDb();
		$db->rollBack();
	}
}