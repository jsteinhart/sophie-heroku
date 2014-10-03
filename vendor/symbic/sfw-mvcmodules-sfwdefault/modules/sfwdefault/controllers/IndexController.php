<?php
class Sfwdefault_IndexController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['defaultAction']))
		{
			$this->_error('No default action defined');
			return;
		}

		// default to handling action as redirectToRoute
		if (!isset($moduleConfig['defaultAction']['type']))
		{
			$type = 'forward';
		}
		else
		{
			$type = $moduleConfig['defaultAction']['type'];
		}
		
		switch ($type)
		{
			case 'forward':
				$action = $moduleConfig['defaultAction']['action'];
				$controller = $moduleConfig['defaultAction']['controller'];
				$module = $moduleConfig['defaultAction']['module'];
				$this->_forward($action, $controller, $module);
				return;

			case 'redirectToRoute':
				if (!isset($moduleConfig['defaultAction']['route']) || !isset($moduleConfig['defaultAction']['route']['name']))
				{
					$this->_error('Default redirectRoute action is incompletely defined');
					return;
				}
				
				if (isset($moduleConfig['defaultAction']['route']['params']))
				{
					if (!is_array($moduleConfig['defaultAction']['route']['params']))
					{
						$this->_error('Default redirectRoute params is incorrectly defined');
						return;
					}
					$routeParams = $moduleConfig['defaultAction']['route']['params'];
				}
				else
				{
					$routeParams = array();
				}
				
				$this->_helper->getHelper('Redirector')->gotoRoute($routeParams, $moduleConfig['defaultAction']['route']['name'], true);
				return;
				
			default:
				$this->_error('No default action defined');
				return;
		}
	}
}