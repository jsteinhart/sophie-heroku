<?php
class Symbic_Controller_Plugin_Ssl extends Zend_Controller_Plugin_Abstract
{
	// alternatively place in: dispatchLoopStartup(...)
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		// do not unenforce ssl
		if ($request->isSecure())
		{
			return;
		}

		// do not enforce POST REQUESTS
		if ($request->isPost())
		{
			return;
		}

		$config = Zend_Registry::get('config');
		if (!isset($config['systemConfig']) || !isset($config['systemConfig']['ssl']))
		{
			throw new Exception('Ssl Controller Plugin Configuration is missing');
		}

		$config = $config['systemConfig']['ssl'];

		if (empty($config['active']))
		{
			return;
		}

		$redirect = false;

		// require as default
		if ($config['requireDefault'] && !$this->isException($request->getModuleName(), $request->getControllerName(), $request->getActionName()))
		{
			$redirect = true;
		}

		// not require as default
		elseif ($this->isException($request->getModuleName(), $request->getControllerName(), $request->getActionName()))
		{
			$redirect = true;
		}

		if ($redirect === true)
		{
			if (empty($config['hostname']))
			{
				$server = $request->getServer();
				$hostname = $server['HTTP_HOST'];
			}
			else
			{
				$hostname = $config['hostname'];
			}

			$url = Zend_Controller_Request_Http::SCHEME_HTTPS . "://" . $hostname . $request->getRequestUri();

			$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->setGoToUrl($url);
			$redirector->redirectAndExit();
		}
	}

	protected function isException ($module, $controller, $action)
	{
		$requestKey1 = $module . '_' . $controller . '_' . $action;
		$requestKey2 = $module . '_' . $controller . '_*';
		$requestKey3 = $module . '_*';

		$config = Zend_Registry::get('config');
		$exceptions = $config['systemConfig']['ssl']['requireExceptions'];
		return ( in_array($requestKey1, $exceptions) ||
				in_array($requestKey2, $exceptions) ||
				in_array($requestKey3, $exceptions) );
	}
}