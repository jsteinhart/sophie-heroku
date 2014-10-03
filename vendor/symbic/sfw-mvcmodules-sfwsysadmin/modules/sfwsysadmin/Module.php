<?php
class Sfwsysadmin_Module extends \Symbic_Module_Standard
{
	public function isActiveComponent($componentName)
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['components']))
		{
			return false;
		}
		if (!isset($moduleConfig['components'][$componentName]))
		{
			return false;
		}
		if (!isset($moduleConfig['components'][$componentName]['active']))
		{
			return false;
		}
		return $moduleConfig['components'][$componentName]['active'] == 1;
	}

	public function isAllowed($componentName)
	{
		if (!$this->isActiveComponent($componentName))
		{
			return false;
		}

		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['components'][$componentName]['requiredRight']))
		{
			$requireRight = $moduleConfig['defaultRequiredRight'];
		}
		else
		{
			$requireRight = $moduleConfig['components'][$componentName]['requiredRight'];
		}

		$userSession = $this->getUserSession();
		return $userSession->hasRight($requireRight);
	}

	public function getActiveComponents()
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['components']))
		{
			return array();
		}

		$activeComponents = array();
		foreach ($moduleConfig['components'] as $componentName => $componentDetails)
		{
			if ($componentName != 'dashboard' && $this->isAllowed($componentName))
			{
				$activeComponents[$componentName] = $componentDetails;
			}
		}
		return $activeComponents;
	}

	public function getComponentConfig($component)
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['components']) || !isset($moduleConfig['components'][$component]))
		{
			throw new Exception('Component does not exist: ' . $component);
		}
		
		if (isset($moduleConfig['components'][$component]['config']))
		{
			return $moduleConfig['components'][$component]['config'];
		}
		
		return array();
	}

	public function getUserModel()
	{
		$moduleConfig = $this->getModuleConfig();
		if (!isset($moduleConfig['models']) || !$moduleConfig['models']['user'])
		{
			$moduleConfig['models']['user'] = '\Sfwsysadmin\Model\User';
		}

		$userModelClass = $moduleConfig['models']['user'];
		return $userModelClass::getInstance();
	}
}