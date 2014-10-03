<?php
class Symbic_Task_Adapter_Exec extends Symbic_Task_Adapter_Abstract
{
	public function run($taskName, array $taskParameters, array $taskOptions)
	{
		$builder = new Symfony\Component\Process\ProcessBuilder();


		if (isset($taskOptions['workingDirectory']))
		{
			$builder->setWorkingDirectory($taskOptions['workingDirectory']);
		}

		if (isset($taskOptions['environmentVariables']) && is_array($taskOptions['environmentVariables']))
		{
			$builder->addEnvironmentVariables($taskOptions['environmentVariables']);
		}

		if (isset($taskOptions['stdin']))
		{
			$builder->setInput($taskOptions['stdin']);
		}

		if (isset($taskOptions['timeout']))
		{
			$builder->setTimeout($taskOptions['timeout']);
		}

		if (isset($taskOptions['arguments']))
		{
			// TODO: setArguments ...
		}

		foreach ($taskParameters as $paramName => $paramValue)
		{
			if ($paramValue === true)
			{
				$builder->setArgument($paramName);
			}
			else
			{
				$builder->setOption($paramName, $paramValue);
			}
		}

		$builder->setPrefix($taskName);

		$process = $builder->getProcess();
		
		// TODO: log raw command line command
		// $command = $process->getCommandLine();

		try
		{
			$process->run();
		}
		catch (RuntimeException $e)
		{
			// Timeout reached
			return false;
		}
		
		// $process->getOutput();
		// $process->getErrorOutput();
		return $process->isSuccessful();
	}
}