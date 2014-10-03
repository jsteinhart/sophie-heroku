<?php
class Symbic_Application_Resource_User_Service extends Zend_Application_Resource_ResourceAbstract
{
	public $_explicitType = 'userService';

	/**
	 * @var Symbic_User_Service
	 */
	protected $_userService = null;

	/**
	 * Defined by Zend_Application_Resource_Resource
	 *
	 * @return Symbic_User_Service
	 */
	public function init()
	{
		return $this->getUserService();
	}

	/**
	 * Attach Symbic_User_Service
	 *
	 * @param  Symbic_User_Service $userService
	 * @return Symbic_User_Service
	 */
	public function setUserService(Symbic_User_Service $userService)
	{
		$this->_userService = $userService;
		return $this;
	}

	/**
	 * Retrieve moduleManager object
	 *
	 * @return Symbic_User_Service
	 */
	public function getUserService()
	{
		if (null === $this->_userService)
		{
			$userService = Symbic_User_Service::getInstance();
			$userService->setOptions($this->getOptions());
			$this->setUserService($userService);
		}
		return $this->_userService;
	}
}
