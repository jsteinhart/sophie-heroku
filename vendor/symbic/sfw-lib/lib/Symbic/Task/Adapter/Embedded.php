<?php
class Symbic_Task_Adapter_Embedded extends Symbic_Task_Adapter_Abstract
{
	public function run($taskName, array $taskParameters, array $taskOptions)
	{
		try
		{
			$task = new $taskName($taskOptions);
		}
		catch (Exception $e)
		{
			echo PHP_EOL;
			echo 'Error: Task "' . $taskName . '" not found.' . PHP_EOL . PHP_EOL;
			return false;
		}

		if (!$task instanceof Symbic_Task_AbstractTask)
		{
			echo PHP_EOL;
			echo 'Error: Task "' . $taskName . '" does not implement the abstract cli task.' . PHP_EOL . PHP_EOL;
			return false;
		}

		try
		{
			$task->run($taskParameters);

			// TODO: implement a timeout using register_tick_function
			/* while (!$task->run())
			  {
			  // $task->run might return false to signal continuing activity
			  // TODO: notify profiler
			  // TODO: notify logger
			  } */
		}
		catch (Exception $e)
		{
			echo PHP_EOL;
			echo 'Exception while running task ' . $taskName . ': ' . $e->getMessage() . PHP_EOL;
			echo PHP_EOL;
			echo 'File: ' . $e->getFile() . PHP_EOL;
			echo 'Line: ' . $e->getLine() . PHP_EOL;
			echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL . PHP_EOL;
			return false;
		}
	}
}