<?php

class Symbic_Application_Resource_Modulemanager extends Zend_Application_Resource_ResourceAbstract
{

	public $_explicitType = 'modulemanager';

	/**
	 * @var Symbic_Application_Modulemanager
	 */
	protected $_moduleManager = null;

	/**
	 * Defined by Zend_Application_Resource_Resource
	 *
	 * @return Symbic_Application_Module_Manager
	 */
	public function init()
	{
		$bootstrap	 = $this->getBootstrap();
		$bootstrap->bootstrap('FrontController');
		$front		 = $bootstrap->getResource('FrontController');

		$moduleManager = $this->getModuleManager();
		Zend_Registry::set('moduleManager', $moduleManager);
		$moduleManager->bootstrapModules();
		return $this;
	}

	/**
	 * Attach Modulemanager
	 *
	 * @param  Symbic_Module_Manager $moduleManager
	 * @return Symbic_Module_Manager
	 */
	public function setModulemanager(Symbic_Module_Manager $moduleManager)
	{
		$this->_moduleManager = $moduleManager;
		return $this;
	}

	/**
	 * Retrieve moduleManager object
	 *
	 * @return Symbic_Module_Manager
	 */
	public function getModuleManager()
	{
		if (null === $this->_moduleManager)
		{
			$moduleManager = Symbic_Module_Manager::getInstance();
			$moduleManager->setOptions($this->getOptions());
			$moduleManager->setBootstrap($this->getBootstrap());
			$this->setModuleManager($moduleManager);
		}
		return $this->_moduleManager;
	}

}
