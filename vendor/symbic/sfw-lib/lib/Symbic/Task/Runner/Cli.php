<?php

/**
 *
 */
class Symbic_Task_Runner_Cli extends Symbic_Task_Runner
{
	/**
	 *
	 */
	public function outputRequiredArgsText()
	{
		echo 'Usage: run.php [group|task] <name>' . PHP_EOL;
		echo 'Runs the named group of tasks or a single task.' . PHP_EOL;
		echo PHP_EOL;
		echo 'Usage: run.php job <id>' . PHP_EOL;
		echo 'Runs the given job as defined in database table symbic_task_job.' . PHP_EOL;
		echo PHP_EOL;
		echo 'Usage: run.php openjobs' . PHP_EOL;
		echo 'Runs the queued / failed jobs from database table symbic_task_job.' . PHP_EOL;
		echo PHP_EOL;
	}

	/**
	 *
	 * @return type
	 */
	public function run()
	{
		$args = new Symbic_Cli_Args();
		if (!($args->getParamCount() == 2 || ($args->getParamCount() == 1 && $args->getParam(1) == 'openjobs')))
		{
			$this->outputRequiredArgsText();
			exit;
		}

		// TODO: register signal handler
		//pcntl_signal(SIGINT, 'sigint');
		//pcntl_signal(SIGTERM, 'sigint');

		// register tick callback to allow timeout inception

		$runType = $args->getParam(1);
		switch ($runType)
		{
			case 'task':
				$taskName = $args->getParam(2);
				// TODO: implement cli parameters to pass to task
				// TODO: implement reading parameters from STDIN
				$taskParameters = array();
				$this->runTask($taskName, $taskParameters);
				return;

			case 'group':
				$groupName = $args->getParam(2);
				$this->runGroup($groupName);
				return;

			case 'job':
				$jobId = $args->getParam(2);
				$this->runJob($jobId);
				return;

			case 'openjobs':
				$this->runOpenJobs();
				return;

			default:
				echo 'Unknown run type "' . $runType . '". Expecting "group", "task" or "job"!' . PHP_EOL;
				echo PHP_EOL;
				$this->outputRequiredArgsText();
		}
	}

}
