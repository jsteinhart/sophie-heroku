<?php

class Symbic_Task_Manager extends Symbic_Task_Autoload
{
	protected $jobModel = null;
	protected $logModel = null;

	protected $options = array();
	protected $tasks = array();

	final public function __construct($options = array())
	{
		parent :: __construct($options);
		// get models
		$this->jobModel = Symbic_Task_Job_Db :: getInstance();
		$this->logModel = Symbic_Task_Log_Db :: getInstance();

		$this->options = $options;

		if (isset($this->options['tasks']) && is_array($this->options['tasks']))
		{
			$this->tasks = $this->options['tasks'];
		}
	}

	public function queueManually($taskName, $retryMax = 3)
	{
		$tasks = $this->getTasksFromConfig();
		if (!isset($tasks[$taskName]) || !$tasks[$taskName]['manuallyQueueable'])
		{
			return false;
		}
		return $this->jobModel->insert(array(
			'task' => $taskName,
			'sync' => 0,
			'creationDate' => new Zend_Db_Expr('NOW()'),
			'startDate' => null,
			'finishDate' => null,
			'jobDuration' => null,
			'retryMax' => $retryMax,
			'retryCount' => 0
		));
	}

	public function getTasksFromConfig()
	{
		$result = array();
		foreach ($this->tasks as $task)
		{
			$result[ $task['name'] ] = array(
				'fromConfig' => true,
				'task' => $task['name'],
				'manuallyQueueable' => !empty($task['manuallyQueueable']),
				'maxId' => null,
				'maxCreationDateTS' => null,
				'maxStartDateTS' => null,
				'maxFinishDateTS' => null,
			);
		}
		return $result;
	}

	public function getTasks()
	{
		$result = $this->getTasksFromConfig();

		$db = $this->jobModel->getAdapter();
		$select = $db
			->select()
			->from(
				array('job' => $this->jobModel->_name),
				array(
					'task',
					'maxJobId' => new Zend_Db_Expr('MAX(job.id)'),
					'maxCreationDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(MAX(job.creationDate))'),
					'maxStartDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(MAX(job.startDate))'),
					'maxFinishDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(MAX(job.finishDate))'),
				)
			)
			->group('job.task');

		$tasks = $select->query()->fetchAll();

		foreach ($tasks as $task)
		{
			$taskName = $task['task'];
			if (isset($this->tasks[$taskName]))
			{
				// preconfigured task, overwrite:
				$taskName = $this->tasks[$taskName]['name'];
				$task['task'] = $taskName;
			}

			if (isset($result[ $taskName ]))
			{
				$result[ $taskName ] = array_merge($result[ $taskName ], $task);
			}
			else
			{
				$result[ $taskName ] = $task;
			}
		}

		$this->parseAndCompleteTasks($result);
		ksort($result);

		return $result;
	}

	private function parseAndCompleteTasks(&$tasks, $getLogSummary = true)
	{
		$taskValidationCache = array();

		foreach ($tasks as &$task)
		{
			// add unset values:
			if (!isset($task['fromConfig']))
			{
				$task['fromConfig'] = false;
			}
			if (!isset($task['manuallyQueueable']))
			{
				$task['manuallyQueueable'] = false;
			}

			if (isset($this->tasks[$task['task']]))
			{
				// preconfigured task, overwrite:
				$task['task'] = $this->tasks[$task['task']]['name'];
			}

			// get human readable name and description from tasks:
			$task['name'] = $task['task'];
			$task['description'] = '';
			$task['valid'] = true;

			$taskName = $task['task'];
			if (isset($taskValidationCache[$taskName]))
			{
				if (is_array($taskValidationCache[$taskName]))
				{
					$task['name'] = $taskValidationCache[$taskName]['name'];
					$task['description'] = $taskValidationCache[$taskName]['description'];
				}
				else
				{
					$task['valid'] = false;
				}
			}
			else
			{
				try
				{
					$options = array();
					$instance = new $taskName($options);
					$task['name'] = $instance->getTaskName();
					$task['description'] = $instance->getTaskDescription();
					$taskValidationCache[$taskName] = array(
						'name' => $task['name'],
						'description' => $task['description']
					);
				}
				catch (Exception $e)
				{
					$task['valid'] = false;
					$taskValidationCache[$taskName] = false;
				}
			}

			$task['log'] = array();
			if ($getLogSummary && !empty($task['maxJobId']))
			{
				// get status from log
				$db = $this->logModel->getAdapter();
				$select = $db
					->select()
					->from(
						array('log' => $this->logModel->_name),
						array(
							'type',
							'count' => new Zend_Db_Expr('COUNT(*)'),
						)
					)
					->where('log.jobId = ?', $task['maxJobId'])
					->group('log.type');

				$task['log'] = $select->query()->fetchAll();
			}
		}
	}

	public function getTaskAliases($taskName)
	{
		$result = array();

		// search configured tasks for aliases:
		// (both directions: check config alias and configured task class name)
		foreach ($this->tasks as $configAlias => $configTask)
		{
			if ($taskName == $configAlias)
			{
				$result[ $configTask['name'] ] = $configTask['name'];
			}
			if ($taskName == $configTask['name'])
			{
				$result[ $configAlias ] = $configAlias;
			}
		}

		// if an alias was found: add the queried task name to the aliases
		if (!empty($result))
		{
			$result[ $taskName ] = $taskName;
		}

		return $result;
	}

	public function getJobHistory($taskName, $limit = 50, $offset = 0)
	{
		$aliases = $this->getTaskAliases($taskName);
		if (empty($aliases))
		{
			// the queried task was not found in config
			// add it nevertheless, it might not have been configured
			$aliases[ $taskName ] = $taskName;
		}

		$limit = (int)$limit;
		if ($limit <= 0)
		{
			$limit = 50;
		}

		$db = $this->jobModel->getAdapter();
		$select = $db
			->select()
			->from(
				array('job' => $this->jobModel->_name),
				array(
					'*',
					'creationDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(job.creationDate)'),
					'startDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(job.startDate)'),
					'finishDateTS' => new Zend_Db_Expr('UNIX_TIMESTAMP(job.finishDate)'),
				)
			)
			->where('job.task IN (?)', $aliases)
			->order('job.id DESC')
			->limit($limit, (int)$offset);

		$sql = $select->__toString();

		if (strtoupper(substr($sql, 0, 6)) != 'SELECT')
		{
			throw new Exception('Unexpected SELECT Query: ' . $sql);
		}
		$sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, 7);
		$history = $db->query($sql)->fetchAll();

		$foundRows = $db->query('SELECT FOUND_ROWS() AS foundRows')->fetchAll();
		$foundRows = $foundRows[0]['foundRows'];

		$this->parseAndCompleteTasks($history, false /* do not get log summary */);

		foreach ($history as &$job)
		{
			// get status from log
			$select = $db
				->select()
				->from(
					array('log' => $this->logModel->_name)
				)
				->where('log.jobId = ?', $job['id'])
				->order('log.date ASC');
			$job['log'] = $select->query()->fetchAll();
		}

		return array(
			'history' => $history,
			'length' => $foundRows
		);
	}

}