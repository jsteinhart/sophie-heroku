<?php
class Symbic_Controller_Plugin_Prerequisite extends Zend_Controller_Plugin_Abstract
{
	public $_session = null;
	public $interferedRequest = false;

	public function __construct(array $options = array())
	{
		$this->_session = new Zend_Session_Namespace('system');
	}

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if (!isset($this->_session->user))
		{
			return;
		}

		if ($this->interferedRequest)
		{
			return;
		}

		if (!isset($this->_session->symbicControllerPluginPrerequisite))
		{
			return;
		}

		$prerequisites = $this->_session->symbicControllerPluginPrerequisite;
		if (!is_array($prerequisites) || sizeof($prerequisites) == 0)
		{
			return;
		}

		$prerequisite = array_shift($prerequisites);

		$request->setParam('symbicControllerPluginPrerequisiteInterference', true);
		$this->interferedRequest = true;
		$request->setModuleName($prerequisite['module']);
		$request->setControllerName($prerequisite['controller']);
		$request->setActionName($prerequisite['action']);
		if (isset($prerequisite['params']))
		{
			foreach ($prerequisite['params'] as $paramName => $paramValue)
			{
				$request->setParam($paramName, $paramValue);
			}
		}
		$request->setDispatched(false);
	}
}