<?php
abstract class Symbic_Controller_Test_AbstractControllersTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
		Zend_Session::start();
    }

	public function testControllers()
	{
		$request = new Zend_Controller_Request_Http();
		$response = new Zend_Controller_Response_Http();

		$modulesDir = BASE_PATH . '/modules';
		
		foreach (scandir($modulesDir) as $module)
		{
			if ($module == '.' || $module == '..')
			{
				continue;
			}

			echo 'Scanning Module ' . $module . PHP_EOL;

			$moduleDir = $modulesDir . '/' . $module;

			if (!is_dir($moduleDir . '/controllers'))
			{
				continue;
			}

			foreach (scandir($moduleDir . '/controllers') as $controllerFilename)
			{
				if ($controllerFilename == '.' || $controllerFilename == '..')
				{
					continue;
				}

				echo '    Init Controller ' . $controllerFilename . PHP_EOL;

				$controllerFile = $moduleDir . '/controllers/' . $controllerFilename;
				
				$controllerName = substr($controllerFilename, 0, strrpos($controllerFilename, '.'));

				$controllerClass = ucfirst($module) . '_' . $controllerName;
				
				if (!class_exists($controllerClass))
				{
					require($controllerFile);
				}
				
				$controller = new $controllerClass($request, $response, array());
				$this->assertEquals(get_class($controller), $controllerClass);
			}
		}
	}
}