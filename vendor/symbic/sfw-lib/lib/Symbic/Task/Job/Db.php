<?php

/*

CREATE TABLE IF NOT EXISTS `symbic_task_job` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task` varchar(255) NOT NULL,
  `parameters` text COMMENT 'json',
  `options` text COMMENT 'json',
  `sync` tinyint(1) unsigned NOT NULL COMMENT 'run from cli: 1, run from queue: 0',
  `creationDate` datetime NOT NULL,
  `startDate` datetime DEFAULT NULL,
  `finishDate` datetime DEFAULT NULL,
  `jobDuration` float DEFAULT NULL COMMENT 'seconds, see PHP''s microtime(true)',
  `retryMax` smallint(3) unsigned NOT NULL,
  `retryCount` smallint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task` (`task`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

*/

class Symbic_Task_Job_Db extends Symbic_Db_Table_Existence
{
	// CONFIG
	public $_name = 'symbic_task_job';
	public $_primary = 'id';

	private function parseData(array $data)
	{
		foreach (array('parameters', 'options') as $key)
		{
			if (empty($data[$key]))
			{
				$data[$key] = null;
			}
			else if (is_array($data[$key]))
			{
				$data[$key] = json_encode($data[$key]);
			}
		}
		return $data;
	}

	public function replace(array $data)
	{
		$data = $this->parseData($data);
		return parent :: replace($data);
	}

	public function insert(array $data)
	{
		$data = $this->parseData($data);
		return parent :: insert($data);
	}

	public function update(array $data, $where)
	{
		$data = $this->parseData($data);
		return parent :: update($data, $where);
	}
}