<?php
/**
 *
 */
class Symbic_Task_Runner extends Symbic_Task_Autoload
{

	/**
	 * @var Symbic_Task_Job_Db
	 */
	protected $jobModel = null;

	/**
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 *
	 * @var array
	 */
	protected $tasks = array();

	/**
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * @var Symbic_Base_Mutex
	 */
	protected $mutex = null;

	/**
	 *
	 * @param array $options
	 */
	final public function __construct($options = array())
	{
		parent :: __construct($options);

		// get job model
		$this->jobModel = Symbic_Task_Job_Db :: getInstance();

		// set tasks
		if (isset($options['tasks']) && is_array($options['tasks']))
		{
			$this->tasks = $options['tasks'];
			unset($options['tasks']);
		}

		// set groups
		if (isset($options['groups']) && is_array($options['groups']))
		{
			$this->groups = $options['groups'];
			unset($options['groups']);
		}

		// set options
		$this->options = $options;
	}

	final public function runJob($jobId)
	{
		$rows = $this->jobModel->find($jobId);
		if (count($rows) === 0)
		{
			echo PHP_EOL;
			echo 'Error: Job ' . $jobId . ' not found.' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}
		$job = $rows->current();
		$jobId = $job->id;

		if (!empty($job->startDate))
		{
			echo PHP_EOL;
			echo 'Error: Job ' . $jobId . ' already started.' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}

		if ($job->retryCount >= $job->retryMax)
		{
			echo PHP_EOL;
			echo 'Error: Retry counter (' . $job->retryCount . ') of job ' . $jobId . ' already reached maximum (' . $job->retryMax . ').' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}

		$job->startDate = new Zend_Db_Expr('NOW()');
		$job->retryCount++;

		$job->save();

		$taskName = $job->task;
		$taskParameters = (empty($job->parameters)) ? array() : json_decode($job->parameters, true);
		$taskOptions = (empty($job->options)) ? array() : json_decode($job->options, true);

		$startMicrotime = microtime(true);
		$result = $this->_runTask($taskName, $taskParameters, $taskOptions, $jobId);
		$finishMicrotime = microtime(true);

		if ($result)
		{
			$job->finishDate = new Zend_Db_Expr('NOW()');
			$job->jobDuration = $finishMicrotime - $startMicrotime;
		}
		else
		{
			$job->startDate = null;
			$job->finishDate = null;
			$job->jobDuration = null;
		}
		$job->save();

		return $result;
	}

	/**
	 *
	 * @param string $taskName
	 * @param array $taskParameters
	 * @return boolean
	 */
	final public function runTask($taskName, array $taskParameters = array(), array $taskOptions = array())
	{
		$jobId = $this->jobModel->insert(array(
			'task' => $taskName,
			'parameters' => $taskParameters,
			'options' => $taskOptions,
			'sync' => 1,
			'creationDate' => new Zend_Db_Expr('NOW()'),
			'startDate' => new Zend_Db_Expr('NOW()'),
			'finishDate' => null,
			'jobDuration' => null,
			'retryMax' => 0,
			'retryCount' => 0
		));

		$startMicrotime = microtime(true);
		$result = $this->_runTask($taskName, $taskParameters, $taskOptions, $jobId);
		$finishMicrotime = microtime(true);

		if ($result)
		{
			$jobUpdateData = array(
				'finishDate' => new Zend_Db_Expr('NOW()'),
				'jobDuration' => $finishMicrotime - $startMicrotime,
			);
		}
		else
		{
			$jobUpdateData = array(
				'startDate' => null,
				'finishDate' => null,
				'jobDuration' => null,
			);
		}
		$this->jobModel->update($jobUpdateData, array(
			'id = ?' => $jobId
		));

		return $result;
	}


	/**
	 *
	 * @param string $taskName
	 * @param array $taskParameters
	 * @return boolean
	 */
	final private function _runTask($taskName, array $taskParameters, array $taskOptions, $jobId = null)
	{
		// default task options
		$taskOptions = array_replace_recursive(array(
			'adapter' => 'embedded',
			'adapterOptions' => array(),
			'name' => $taskName,
			'mutex' => false,
			'mutexName' => null
		), $taskOptions);

		if (isset($this->tasks[$taskName]))
		{
			$configuredTask = true;
			$taskOptions = array_replace_recursive($taskOptions, $this->tasks[$taskName]);
		}
		else
		{
			$configuredTask = false;
		}

		if (isset($this->options['runOnlyConfiguredTasks']) && $this->options['runOnlyConfiguredTasks'] == true)
		{
			echo PHP_EOL;
			echo 'Unconfigured Task Error: Task "' . $taskName . '" is not configured.' . PHP_EOL . PHP_EOL;
			return false;
		}

		// init logging:
		$logger = new Symbic_Task_Log($jobId);
		$logger->startOutputLogger();
		$logger->startErrorLogger();
		$logger->startExceptionLogger();

		// init profiler
		//$stopwatch = new Symfony\Component\Stopwatch\Stopwatch();

		// init mutex lock:
		if ($taskOptions['mutex'] === true)
		{
			if (is_null($this->mutex))
			{
				// init mutex if necessary
				$this->mutex = new Symbic_Base_Mutex( $this->options );
			}

			$mutexName = $taskOptions['mutexName'];
			if (empty($mutexName))
			{
				$mutexName = $taskOptions['name'];
			}

			if (!$this->mutex->acquireLock($mutexName))
			{
				echo PHP_EOL;
				echo 'Task "' . $taskName . '" requires mutex locking which cannot be acquired.' . PHP_EOL . PHP_EOL;
				return false;
			}
		}

		$adapterClass = 'Symbic_Task_Adapter_' . ucfirst($taskOptions['adapter']);
		$adapter = new $adapterClass($taskOptions['adapterOptions']);

		$taskName = $taskOptions['name'];
		unset($taskOptions['name']);

		//$stopwatch->start('task');
		$taskSuccess = $adapter->run($taskName, $taskParameters, $taskOptions);
		//$stopwatch->stop('task');

		if ($taskOptions['mutex'] === true)
		{
			$this->mutex->releaseLock($mutexName);
		}

		// TODO: notify profiler
		$logger->stopExceptionLogger();
		$logger->stopErrorLogger();
		$logger->stopOutputLogger();

		return ($taskSuccess !== false);
	}

	/**
	 *
	 * @param type $groupName
	 * @return boolean
	 */
	final public function runGroup($groupName)
	{
		if (!isset($this->groups[$groupName]))
		{
			echo PHP_EOL;
			echo 'Error: Task Group "' . $groupName . '" not found.' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}

		if (!is_array($this->groups[$groupName]) || sizeof($this->groups[$groupName]) == 0)
		{
			echo PHP_EOL;
			echo 'Error: Invalid or empty Task Group "' . $groupName . '" configuration.' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}

		// default group options
		$groupOptions = array(
			'abortOnFailedTask' => false
		);
		$groupOptions = array_replace_recursive($groupOptions, $this->groups[$groupName]);

		if (!isset($groupOptions['tasks']) || !is_array($groupOptions['tasks']) || sizeof($groupOptions['tasks']) == 0)
		{
			echo PHP_EOL;
			echo 'Error: Task Group "' . $groupName . '" configuration contains no tasks.' . PHP_EOL;
			echo PHP_EOL;
			return false;
		}

		$totalTaskNumber = sizeof($groupOptions['tasks']);
		$executedTaskNumber = 0;
		$successfulTaskNumber = 0;
		$hasFailedTask = false;

		foreach ($groupOptions['tasks'] as $taskKey => $task)
		{
			if ($hasFailedTask && $groupOptions['abortOnFailedTask'])
			{
				echo PHP_EOL;
				echo 'Warning: Aborting group execution on failed task.' . PHP_EOL;
				echo PHP_EOL;
				break;
			}

			// set defaults for task parameters and options
			$taskParameters = array();
			$taskOptions = array();

			if (is_string($task))
			{
				$taskName = $task;
			}
			else
			{
				if (!isset($task['name']))
				{
					echo PHP_EOL;
					echo 'Error: Invalid task definition array format in task group "' . $groupName . '" configuration.' . PHP_EOL;
					echo PHP_EOL;
					$hasFailedTask = true;
					continue;
				}

				if (isset($task['options']))
				{
					$taskOptions = array_replace_recursive($taskOptions, $task['options']);
				}
				if (isset($task['parameters']))
				{
					$taskParameters = array_replace_recursive($taskParameters, $task['parameters']);
				}
			}

			// TODO: implement task isolation level: fork for each task
			// TODO: implement parallelity for tasks in group

			$executedTaskNumber++;

			echo PHP_EOL;
			echo 'Info: Executing task ' . $executedTaskNumber . '/' . $totalTaskNumber . ' in group "' . $groupName . '"' . PHP_EOL;

			$taskSuccess = $this->runTask($taskName, $taskParameters, $taskOptions);

			if ($taskSuccess)
			{
				$successfulTaskNumber++;
			}
			else
			{
				$hasFailedTask = true;
				continue;
			}
		}

		echo PHP_EOL;
		echo 'Info: Executed ' . $executedTaskNumber . ' out of ' . $totalTaskNumber . ' tasks  in group "' . $groupName . '" with ' . ($totalTaskNumber - $successfulTaskNumber) . ' failing tasks' . PHP_EOL;

		return ($totalTaskNumber == $successfulTaskNumber);
	}

	final public function runOpenJobs()
	{
		$db = $this->jobModel->getAdapter();
		$select = $db
			->select()
			->from(array('job' => $this->jobModel->_name), array('id'))
			->where('job.startDate IS NULL')
			->where('job.finishDate IS NULL')
			->where('job.retryMax > job.retryCount')
			// add some buffer for repeated jobs:
			->where('job.creationDate < NOW() - INTERVAL (POW(2, job.retryCount) - 1) * 15 MINUTE');
		$jobs = $select->query()->fetchAll();

		$totalJobNumber = sizeof($jobs);
		$executedJobNumber = 0;
		$successfulJobNumber = 0;

		foreach ($jobs as $job)
		{
			$executedJobNumber++;
			if ($this->runJob($job['id']))
			{
				$successfulJobNumber++;
			}
		}

		echo PHP_EOL;
		echo 'Info: Executed ' . $executedJobNumber . ' out of ' . $totalJobNumber . ' jobs with ' . ($totalJobNumber - $successfulJobNumber) . ' failing jobs' . PHP_EOL;

		return ($totalJobNumber == $successfulJobNumber);
	}

}