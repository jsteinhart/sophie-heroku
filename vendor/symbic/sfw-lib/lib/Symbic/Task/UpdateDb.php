<?php
class Symbic_Task_UpdateDb extends Symbic_Task_AbstractTask
{
	public function run(array $parameters = array())
	{
		echo date('Y-m-d H:i:s') . ': Starting DB updates...' . PHP_EOL;
		
		// use parameter to set update class name but fallback to Application_Contrib_Updates
		if (isset($parameters['updatesClass']))
		{
			$updatesClassName = $parameters['updatesClass'];
		}
		else
		{
			$updatesClassName = 'Application_Contrib_Updates';
		}

		if (!class_exists($updatesClassName, false))
		{

			if (!isset($parameters['autoloadUpdatesClass']) || $parameters['autoloadUpdatesClass'] === false)
			{
				// use parameter to set update class file but BASE_PATH . '/contrib/Updates.php'
				if (isset($parameters['updatesClassFile']))
				{
					$updatesClassFile = $parameters['updatesClassFile'];
				}
				else
				{
					if (!defined('BASE_PATH'))
					{
						echo 'Error: ' . __CLASS__ . ' task updatesFile fallback mechanism requires BASE_PATH to be set' . PHP_EOL . PHP_EOL;
						return false;
					}
					$updatesClassFile = BASE_PATH . '/contrib/Updates.php';
				}

				if (!file_exists($updatesClassFile))
				{
					echo 'Error: ' . __CLASS__ . ' task could not include ' . $updateClassFile . PHP_EOL . PHP_EOL;
					return false;
				}
			
				require_once($updatesClassFile);
			}
		}

		$updater = new $updatesClassName();

		$adapter = $updater->getAdapter();
		if ($adapter === null || !$adapter instanceof Zend_Db_Adapter_Abstract)
		{
			$db = Zend_Registry::get('db');
			if (!$db instanceof Zend_Db_Adapter_Abstract)
			{
				echo 'Error: ' . __CLASS__ . ' task requires the default adapter for Zend_Db_Table to be set or a Zend_Db_Adapter instance to be present under the key db within the Zend_Registry' . PHP_EOL . PHP_EOL;
				return false;
			}
			$updater->setAdapter($db);
		}

		if (!method_exists($updater, 'runUpdates'))
		{
			echo 'Error: ' . __CLASS__ . ' task requires ' . $updateClassFile . ' to be a class implementing runUpdates()' . PHP_EOL . PHP_EOL;
			return false;
		}
		
		$updater->verbose = !isset($parameters['verbose']) || $parameters['verbose'] !== false;
		
		$updater->runUpdates();

		echo date('Y-m-d H:i:s') . ': DB updates finished!' . PHP_EOL . PHP_EOL;
	}
}