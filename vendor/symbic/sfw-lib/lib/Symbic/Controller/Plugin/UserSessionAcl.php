<?php
class Symbic_Controller_Plugin_UserSessionAcl extends Zend_Controller_Plugin_Abstract
{
	private $userSession = null;

	private function getUserSession()
	{
		if (is_null($this->userSession))
		{
			$this->userSession = Symbic_User_Session::getInstance();
		}
		return $this->userSession;
	}

	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$config = Zend_Registry::get('config');
		$aclConfig = $config['systemConfig']['acl'];
		
		if (!$aclConfig['active'])
		{
			return;
		}

		$userSession = $this->getUserSession();
		if ($userSession->isLoggedIn())
		{
			return;
		}

		$requestKey1 = $request->getModuleName() . '_' . $request->getControllerName() . '_' . $request->getActionName();
		$requestKey2 = $request->getModuleName() . '_' . $request->getControllerName() . '_*';
		$requestKey3 = $request->getModuleName() . '_*';

		$isException = (
				in_array($requestKey1, $aclConfig['requireAuthExceptions']) ||
				in_array($requestKey2, $aclConfig['requireAuthExceptions']) ||
				in_array($requestKey3, $aclConfig['requireAuthExceptions'])
			);
		
		if (
			// case 1: authentication is required by default and this is no exception
			($aclConfig['requireAuthDefault'] && !$isException) || 
			// case 2: authentication is not required by default but this is an exception
			(!$aclConfig['requireAuthDefault'] && $isException)
		)
		{
			$this->redirectToAuth($request);
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

}