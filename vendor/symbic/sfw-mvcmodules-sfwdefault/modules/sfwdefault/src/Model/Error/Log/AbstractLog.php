<?php
namespace Sfwdefault\Model\Error\Log;

class AbstractLog
{
	protected $logTableName;
	protected $logTable;
	protected $logFile;
	protected $options;

	protected function normalizePath($path)
	{
		$path = str_replace('\\', '/', $path);
		if (defined('BASE_PATH'))
		{
			$basePath = str_replace('\\', '/', BASE_PATH . DIRECTORY_SEPARATOR);

			if (strpos($path, $basePath) === 0)
			{
				$path = substr($path, strlen($basePath));
			}
			elseif (strpos($path, 'phar://' . $basePath) === 0)
			{
				$path = substr($path, strlen('phar://' . $basePath));
			}
		}
		return $path;
	}

	protected function getLogTable()
	{
		if ($this->logTable === null)
		{
			$this->logTable = new \Symbic_Db_Table(array(
				'name' => $this->logTableName
				)
			);
		}
		return $this->logTable;
	}

	protected function write($data)
	{
		$table = $this->getLogTable();
		return $table->insert($data);
	}
}