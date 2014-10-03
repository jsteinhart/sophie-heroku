<?php
class Symbic_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	public $_session = null;

	public function __construct(array $options = array())
	{
		$this->_session = new Zend_Session_Namespace('system');
	}

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$config = Zend_Registry::get('config');
		if ($config['systemConfig']['acl']['active'])
		{
			$redirectToAuth = false;
			if ($config['systemConfig']['acl']['requireAuthDefault'])
			{
				if (!isset($this->_session->user) &&
					!$this->isException($request->getModuleName(), $request->getControllerName(), $request->getActionName()))
				{
					$redirectToAuth = true;
				}
			}
			else
			{
				if ($this->isException($request->getModuleName(), $request->getControllerName(), $request->getActionName()) &&
					!isset($this->_session->user) )
				{
					$redirectToAuth = true;
				}
			}

			if ($redirectToAuth === true)
			{
				$this->redirectToAuth($request);
			}
		}
	}
	
	protected function redirectToAuth(Zend_Controller_Request_Abstract $request)
	{
		$isDenied = $request->getParam('acl_handler');
		if ($isDenied == 'denied')
		{
			throw new Exception('acl controller plugin loop');
		}
		else
		{
			$config = Zend_Registry::get('config');
			$request->setParam('acl_handler', 'denied')
					->setParam('message', 'You need to be authenticated.')
					->setModuleName($config['systemConfig']['acl']['requireAuthHandler']['module'])
					->setControllerName($config['systemConfig']['acl']['requireAuthHandler']['controller'])
					->setActionName($config['systemConfig']['acl']['requireAuthHandler']['action'])
					->setDispatched(false);
		}
	}

	protected function isException ($module, $controller, $action)
	{
		$requestKey1 = $module . '_' . $controller . '_' . $action;
		$requestKey2 = $module . '_' . $controller . '_*';
		$requestKey3 = $module . '_*';

		$config = Zend_Registry::get('config');
		$exceptions = $config['systemConfig']['acl']['requireAuthExceptions'];
		return ( in_array($requestKey1, $exceptions) ||
				in_array($requestKey2, $exceptions) ||
				in_array($requestKey3, $exceptions) );
	}

	protected function hasIdentity ()
	{
		// solution based on Zend_Auth
		$auth = Zend_Auth :: getInstance();
		return $auth->hasIdentity();
	}
}