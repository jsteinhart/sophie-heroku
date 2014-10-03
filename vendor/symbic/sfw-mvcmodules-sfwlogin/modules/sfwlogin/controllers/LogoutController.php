<?php
class Sfwlogin_LogoutController extends Symbic_Controller_Action
{
	private $userSession;
	private $moduleConfig;

	public function init()
	{
		$this->userSession = $this->getUserSession();
		$this->moduleConfig = $this->getModuleConfig();
	}

	protected function gotoPostLogoutRoute()
	{
		if (!isset($this->moduleConfig['login']['redirectRouteLogout']) || !isset($this->moduleConfig['login']['redirectRouteLogout']['values']))
		{
			$values = array();
		}
		else
		{
			$values = (array)$this->moduleConfig['login']['redirectRouteLogout']['values'];
		}
		if (!isset($this->moduleConfig['login']['redirectRouteLogout']) || !isset($this->moduleConfig['login']['redirectRouteLogout']['name']))
		{
			$route = 'default';
		}
		else
		{
			$route = (string)$this->moduleConfig['login']['redirectRouteLogout']['name'];
		}
		$this->_helper->getHelper('Redirector')->gotoRoute($values, $route);
	}

	public function indexAction()
	{
		if (!$this->userSession->isLoggedIn())
		{
			$this->_error('Logout failed. You are not logged in!');
			return;
		}

		if (!isset($this->moduleConfig['logoutSessionHandling']) || $this->moduleConfig['logoutSessionHandling'] == 'logoutUserSession')
		{
			$this->userSession->logout();
		}
		elseif ($this->moduleConfig['logoutSessionHandling'] == 'destroySession')
		{
			Zend_Session :: destroy();
		}
		else
		{
			throw new Exception('Unsupported Logout Session Handling');
		}

		if (isset($this->moduleConfig['postLogoutTasks']) && is_array($this->moduleConfig['postLogoutTasks']))
		{
			foreach ($this->moduleConfig['postLogoutTasks'] as $postLogoutTask)
			{
				switch ($postLogoutTask['type'])
				{
					case 'include':
						if (file_exists($postLogoutTask['file']))
						{
							include $postLogoutTask['file'];
						}
						else
						{
							throw new Exception('postLogoutTask include file not found');
						}
						break;

					default:
						throw new Exception('Unknown postLogoutTask found');
				}
			}
		}

		$this->gotoPostLogoutRoute();
	}
}