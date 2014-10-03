<?php
namespace Sfwdefault\Model\Error\Log;

class Message {

	protected $logTable;
	protected $logFile;
	protected $options;

	protected function getLogTable()
	{
		if ($this->logTable === null)
		{
			$this->logTable = new \Symbic_Db_Table(array('name' => 'system_log_error_message'));
		}
		return $this->logTable;
	}

	protected function write($data)
	{
		$table = $this->getLogTable();
		$table->insert($data);
	}

	public function log($data)
	{
		$this->write($data);
	}
}