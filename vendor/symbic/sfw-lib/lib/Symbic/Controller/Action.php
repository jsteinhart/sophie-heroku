<?php
class Symbic_Controller_Action extends Zend_Controller_Action
{
	static protected $_errorModule = null;
	static protected $_errorController = 'error';
	static protected $_errorAction = 'message';

	final protected function getApplication()
	{
		if (Zend_Registry::isRegistered('application'))
		{
			return Zend_Registry::get('application');
		}
		throw new Exception('Application unavailable');
	}

	final protected function getUserSession()
	{
		return Symbic_User_Session::getInstance();
	}

	final protected function getModuleManager()
	{
		if (Zend_Registry::isRegistered('moduleManager'))
		{
			return Zend_Registry::get('moduleManager');
		}
		throw new Exception('Module manager unavailable');
	}

	final protected function getModuleName()
	{
		return $this->getRequest()->getModuleName();
	}

	final protected function getModule($moduleName = null)
	{
		if ($moduleName === null)
		{
			$moduleName = $this->getModuleName();
		}
		return $this->getModuleManager()->getModule($moduleName);
	}

	final protected function getModuleConfig($moduleName = null)
	{
		if ($moduleName === null)
		{
			$moduleName = $this->getModuleName();
		}
		$module = $this->getModuleManager()->getModule($moduleName);
		return $module->getModuleConfig();
	}

	final protected function getForm($formName, $options = array(), $moduleName = null)
	{
		$module = $this->getModule($moduleName);
		return call_user_func(array($module, 'getForm'), $formName, $options);
	}

	final protected function getModel($modelName, $moduleName = null)
	{
		$module = $this->getModule($moduleName);

		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		array_unshift($args, $modelName);

		return call_user_func_array(array($module, 'getModel'), $args);
	}

	final protected function getModelSingleton($modelName, $moduleName = null)
	{
		$module = $this->getModule($moduleName);

		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		array_unshift($args, $modelName);

		return call_user_func_array(array($module, 'getModelSingleton'), $args);
	}

	final protected function getModuleDir($moduleName = null)
	{
		if (is_null($moduleName) || $moduleName == '')
		{
			$moduleName = $this->getModuleName();
		}

		$request = $this->getRequest();
		$module  = $request->getModuleName();
		$dirs    = $this->getFrontController()->getControllerDirectory();
		if (empty($module) || !isset($dirs[$module])) {
			throw new Exception('getModuleDir called for an unkown module');
		}
		$baseDir = dirname($dirs[$module]);
		return $baseDir;
	}

	final protected function getApplicationConfig()
	{
		if (Zend_Registry::isRegistered('config'))
		{
			return Zend_Registry::get('config');
		}
		throw new Exception('Application config not available');
	}

	final protected function _error($message)
	{
		if (null === self::$_errorModule)
		{
			$errorModule = Zend_Controller_Front::getInstance()->getDispatcher()->getDefaultModule();
		}
		$this->_forward(self::$_errorAction, self::$_errorController, $errorModule, array('message'=>$message));
	}
}