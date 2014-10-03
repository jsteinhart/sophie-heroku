<?php
class Symbic_Task_Adapter_JsonRpc extends Symbic_Task_Adapter_Abstract
{
	public function run($taskName, array $taskParameters, array $taskOptions)
	{
		if (!isset($taskOptions['transport']))
		{
			return false;
		}

		// use http connection
		elseif ($taskOptions['transport'] == 'http')
		{
			if (!isset($taskOptions['url']))
			{
				return false;
			}
			$connection = Tivoka\Client::connect($taskOptions['url']);
		}

		// use tcp connection
		elseif ($taskOptions['transport'] == 'tcp')
		{
			if (!isset($taskOptions['host']) || !isset($taskOptions['port']))
			{
				return false;
			}
			$connection = Tivoka\Client::connect(array('host' => $taskOptions['host'], 'port' => $taskOptions['port']));
		}
		
		// only tcp and http supported
		else
		{
			return false;
		}
		
		try
		{
			$request = $connection->sendRequest($taskName, $taskParameters);
		}
		catch (Exception $e)
		{
			return false;
		}
		return true;
	}
}