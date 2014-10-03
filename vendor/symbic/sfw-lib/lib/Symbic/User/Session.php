<?php
class Symbic_User_Session extends Symbic_Singleton
{
	const REFRESH_NO_MODEL_ATTACHED = 'REFRESH_NO_MODEL_ATTACHED';
	const REFRESH_NO_USER = 'REFRESH_NO_USER';
	const REFRESH_USER_DEACTIVATED = 'REFRESH_USER_DEACTIVATED';
	const REFRESH_SUCCESSFUL = 'REFRESH_SUCCESSFUL';
	const SESSION_NAMESPACE = 'symbicUserSession';

	protected $userService;
	protected $session;
	protected $encryptedPasswordMethods;

	protected function init()
	{
		$this->encryptedPasswordMethods = array('md5');
		$this->userService = Symbic_User_Service::getInstance();
	}

	// SESSION INTERFACE
	protected function getSession()
	{
		if (is_null($this->session))
		{
			$this->session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
		}
		return $this->session;
	}

	// MODEL INTERFACE
	public function getModel()
	{
		if (!$this->hasModelId())
		{
			return null;
		}
		return $this->userService->getModel($this->getModelId());
	}

	// LOGIN / LOGOUT / REFRESH
	public function set($id, $login, $modelId, array $data = null, $encryptedPassword = null, $encryptedPasswordMethod = 'md5', $password = null)
	{
		if ($this->isLoggedIn())
		{
			// TODO: alternative throw exception here
			$this->logout();
		}

		$this->setModelId($modelId);

		$this->clearUserrolesCache();
		$this->clearUsergroupsCache();
		$this->clearRightsCache();

		$this->update($id, $login, $data, $encryptedPassword, $encryptedPasswordMethod, $password);
	}

	public function update($id = null, $login = null, array $data = null, $encryptedPassword = null, $encryptedPasswordMethod = null, $password = null)
	{
		if (!is_null($id))
		{
			$this->setId($id);
		}
		if (!is_null($login))
		{
			$this->setLogin($login);
		}
		if (!is_null($data))
		{
			$this->setData($data);
		}
		if (!is_null($encryptedPassword))
		{
			$this->setEncryptedPassword($encryptedPassword);
		}
		if (!is_null($encryptedPasswordMethod))
		{
			if (!in_array($encryptedPasswordMethod, $this->encryptedPasswordMethods))
			{
				throw new Exception('Unsupported password encryption method');
			}
			$this->setEncryptedPasswordMethod($encryptedPasswordMethod);
		}
		if (!is_null($password))
		{
			$this->setPassword($password);
		}
	}

	public function refresh()
	{
		$this->clearUserrolesCache();
		$this->clearUsergroupsCache();
		$this->clearRightsCache();

		$model = $this->getModel();
		if (!is_null($model))
		{
			return $model->refreshUserSession($this);
		}
		else
		{
			return self::REFRESH_NO_MODEL_ATTACHED;
		}
	}

	public function isLoggedIn()
	{
		return $this->hasId();
	}

	public function logout()
	{
		$session = $this->getSession();
		$session->unsetAll();
	}

	// GET AND SET DATA
	protected function setId($id)
	{
		$session = $this->getSession();
		$session->id = $id;
	}

	public function getId()
	{
		$session = $this->getSession();
		if (!empty($session->id))
		{
			return $session->id;
		}
		else
		{
			return null;
		}
	}

	public function hasId()
	{
		$session = $this->getSession();
		return (!empty($session->id));
	}

	protected function setLogin($login)
	{
		$session = $this->getSession();
		$session->login = $login;
	}

	public function getLogin()
	{
		$session = $this->getSession();
		if (!empty($session->login))
		{
			return $session->login;
		}
		else
		{
			return null;
		}
	}

	public function hasLogin()
	{
		$session = $this->getSession();
		return (!empty($session->login));
	}

	protected function setModelId($modelId)
	{
		$session = $this->getSession();
		$session->modelId = $modelId;
		return $this;
	}

	public function getModelId()
	{
		$session = $this->getSession();
		if (!empty($session->modelId))
		{
			return $session->modelId;
		}
		else
		{
			return null;
		}
	}

	public function hasModelId()
	{
		$session = $this->getSession();
		return (!empty($session->modelId));
	}

		protected function setData(array $data)
	{
		$session = $this->getSession();
		$session->data = $data;
	}

	public function getData()
	{
		$session = $this->getSession();
		if (isset($session->data) && is_array($session->data))
		{
			return $session->data;
		}
		else
		{
			return array();
		}
	}

	public function hasData()
	{
		$session = $this->getSession();
		return (isset($session->data) && is_array($session->data));
	}

	public function getDataEntry($entryKey)
	{
		if (!$this->hasDataEntry($entryKey))
		{
			return null;
		}
		$session = $this->getSession();
		return $session->data->$entryKey;
	}

	public function hasDataEntry($entryKey)
	{
		$session = $this->getSession();
		if (!isset($session->data))
		{
			return false;
		}
		if (!isset($session->data->$entryKey))
		{
			return false;
		}
		return true;
	}

	protected function setEncryptedPassword($encryptedPassword)
	{
		$session = $this->getSession();
		$session->encryptedPassword = $encryptedPassword;
	}

	protected function getEncryptedPassword()
	{
		$session = $this->getSession();
		if (!empty($session->encryptedPassword))
		{
			return $session->encryptedPassword;
		}
		else
		{
			return null;
		}
	}

	public function hasEncryptedPassword()
	{
		$session = $this->getSession();
		return (!empty($session->encryptedPassword));
	}

	protected function setEncryptedPasswordMethod($encryptedPasswordMethod)
	{
		$session = $this->getSession();
		$session->encryptedPasswordMethod = $encryptedPasswordMethod;
	}

	protected function getEncryptedPasswordMethod()
	{
		$session = $this->getSession();
		if (!empty($session->encryptedPasswordMethod))
		{
			return $session->encryptedPasswordMethod;
		}
		else
		{
			return null;
		}
	}

	public function encryptedPasswordEquals($password)
	{
		if (!$this->hasEncryptedPassword())
		{
			throw new Exception('No encrypted password available for comparison');
		}
		$encryptedPassword = $this->getEncryptedPassword();
		$encryptedPasswordMethod = $this->getEncryptedPasswordMethod();
		if ($encryptedPasswordMethod == 'md5')
		{
				return md5($password) == $encryptedPassword;
		}
		throw new Exception('Unsupported password encryption method');
	}

	protected function setPassword($password)
	{
		$session = $this->getSession();
		$session->password = $password;
	}

	protected function getPassword()
	{
		$session = $this->getSession();
		if (!empty($session->password))
		{
			return $session->password;
		}
		else
		{
			return null;
		}
	}

	public function hasPassword()
	{
		$session = $this->getSession();
		return (!empty($session->password));
	}

	// USERROLES
	public function clearUserrolesCache()
	{
		$session = $this->getSession();
		if(isset($session->userroleIds))
		{
			unset($session->userroleIds);
		}
	}

	public function getUserroleIds($cacheInSession = true)
	{
		$session = $this->getSession();
		if ($cacheInSession && isset($session->userroleIds))
		{
			return $session->userroleIds;
		}

		$model = $this->getModel();
		$userroleIds = $model->getUserroleIdsByUserId($this->getId());
		if ($cacheInSession)
		{
			$session->userroleIds = $userroleIds;
		}
		return $userroleIds;
	}

	public function hasUserroleId($userroleId, $cacheInSession = true)
	{
		$userroleIds = $this->getUserroleIds($cacheInSession);
		return in_array($userroleId, $userroleIds);
	}

	// RIGHTS
	public function clearRightsCache()
	{
		$session = $this->getSession();
		if(isset($session->rightIds))
		{
			unset($session->rightIds);
		}
	}

	public function getRights($cacheInSession = true)
	{
		if (!$this->isLoggedIn())
		{
			return array();
		}

		$session = $this->getSession();
		if ($cacheInSession && isset($session->rights))
		{
			return $session->rights;
		}

		$model = $this->getModel();
		$rights = $model->getRightsByUserIdAndUserroleIds($this->getId(), $this->getUserroleIds($cacheInSession));
		if ($cacheInSession)
		{
			$session->rights = $rights;
		}
		return $rights;
	}

	public function hasRight($right, $cacheInSession = true)
	{
		$rights = $this->getRights($cacheInSession);
		return in_array($right, $rights);
	}

	// USERGROUPS
	public function clearUsergroupsCache()
	{
		$session = $this->getSession();
		if(isset($session->usergroupIds))
		{
			unset($session->usergroupIds);
		}
	}

	public function getUsergroupIds($cacheInSession = true)
	{
		$session = $this->getSession();
		if ($cacheInSession && isset($session->usergroupIds))
		{
			return $session->usergroupIds;
		}

		$model = $this->getModel();
		$usergroupIds = $model->getUsergroupIdsByUserId($this->getId());
		if ($cacheInSession)
		{
			$session->usergroupIds = $usergroupIds;
		}
		return $usergroupIds;
	}

	public function hasUsergroupId($usergroupId, $cacheInSession = true)
	{
		$usergroupIds = $this->getUsergroupIds($cacheInSession);
		return in_array($usergroupId, $usergroupIds);
	}
}