<?php
/*
 * SQL dump for database tables in dbtable.sql
 */
class Symbic_User_Model_Dbtable
{
	protected $userService;
	protected $modelId;
	protected $options;
	protected $userTable;
	protected $usergroupTable;
	protected $userUsergroupTable;
	protected $userPasswordTokenTable;

	public function __construct($userService, $modelId, $options = array())
	{
		$this->userService = $userService;
		$this->modelId = $modelId;
		if (!is_array($options))
		{
			throw Exception(__class__ . ' expects $options parameter to be an array');
		}
		$this->options = $options;
	}

	/**
	 * returns a Symbic_Db_Table Instance of the User Table
	 * @return Symbic_Db_Table refering on the User table
	 */
	protected function getUserTable()
	{
		if (is_null($this->userTable))
		{
			if (!isset($this->options['userDbtableOptions']))
			{
				$this->options['userDbtableOptions'] = array(
					'name' => 'system_user'
				);
			}
			$this->userTable = new Symbic_Db_Table($this->options['userDbtableOptions']);
		}
		return $this->userTable;
	}

	/**
	 * returns a Symbic_Db_Table Instance of the UserGroup Table
	 * @return Symbic_Db_Table refering on the UserGroup table
	 */
	protected function getUsergroupTable()
	{
		if (is_null($this->usergroupTable))
		{
			if (!isset($this->options['usergroupDbtableOptions']))
			{
				$this->options['usergroupDbtableOptions'] = array(
					'name' => 'system_usergroup'
				);
			}
			$this->usergroupTable = new Symbic_Db_Table($this->options['usergroupDbtableOptions']);
		}
		return $this->usergroupTable;
	}

	/**
	 * returns a Symbic_Db_Table Instance of the UserUserGroup Table
	 * @return Symbic_Db_Table refering on the UserUserGroup table
	 */
	protected function getUserUsergroupTable()
	{
		if (is_null($this->userUsergroupTable))
		{
			if (!isset($this->options['userUsergroupDbtableOptions']))
			{
				$this->options['userUsergroupDbtableOptions'] = array(
					'name' => 'system_user_usergroup',
					'primary' => array('userId', 'usergroupId')
				);
			}
			$this->userUsergroupTable = new Symbic_Db_Table($this->options['userUsergroupDbtableOptions']);
		}
		return $this->userUsergroupTable;
	}
	/**
	 * returns a Symbic_Db_Table Instance of the PasswordToken Table
	 * @return Symbic_Db_Table refering on the PasswordToken table
	 */
	protected function getUserPasswordTokenTable()
	{
		if (is_null($this->userPasswordTokenTable))
		{
			if (!isset($this->options['userPasswordTokenDbtableOptions']))
			{
				$this->options['userPasswordTokenDbtableOptions'] = array(
					'name' => 'system_user_password_token',
					'primary' => array('userId', 'token')
				);
			}
			$this->userPasswordTokenTable = new Symbic_Db_Table($this->options['userPasswordTokenDbtableOptions']);
		}
		return $this->userPasswordTokenTable;
	}

	/**
	 * returns the active state of a user array
	 * @param  array   $user the user "session" array
	 * @return boolean true if user is active
	 */
	public function isUserDeactivated(array $user)
	{
		return isset($user['active']) && $user['active'] != 1;
	}
	
	// FUNCTIONS FOR LOGIN AND SESSION REFRESH
	/**
	 * fetch user data from database and fill session with informations
	 * @param  string              $login       the login name
	 * @param  string              $password    password
	 * @param  Symbic_User_Session $sessionUser session to be filled
	 * @return const                           constant success / rror message
	 */
	public function loginUserByLoginAndPassword($login, $password, Symbic_User_Session $sessionUser)
	{
		$userServiceClass = get_class($this->userService);

		try {
			$user = $this->fetchUserByLogin($login);

			if (is_null($user))
			{
				return $userServiceClass::LOGIN_USER_UNKNOWN;
			}

			$passwordValid = false;
			if (!isset($user['passwordType']) || $user['passwordType'] == 'md5')
			{
				$passwordValid = (md5($password) == $user['password']);
			}
			else if ($user['passwordType'] == 'password_hash')
			{
				$passwordValid = password_verify($password, $user['password']);
			}
			else
			{
				throw new Exception('Unknown password type');
			}

			if (!$passwordValid)
			{
				return $userServiceClass::LOGIN_WRONG_PASSWORD;
			}

			if ($this->isUserDeactivated($user))
			{
				return $userServiceClass::LOGIN_USER_DEACTIVATED;
			}

			$id = $user['id'];
			$login = $user['login'];

			// TODO: use a config flag to activate this
			// $encryptedPasswordMethod = 'md5'
			// $encryptedPassword = md5($user['password']);
			// $password = $user['password'];

			unset($user['id']);
			unset($user['login']);
			unset($user['password']);

			$sessionUser->set($id, $login, $this->modelId, $user);

			$this->setUserLastLoginById($id);

			return $userServiceClass::LOGIN_SUCCESSFUL;
		}
		catch (Exception $e)
		{
			return $userServiceClass::MODEL_FAILURE;
		}
	}
	/**
	 * updates data in a userSession
	 * @param  Symbic_User_Session $userSession UserSession class
	 * @return const              constant success/ error message
	 */
	public function refreshUserSession($userSession)
	{
		$userSessionClass = get_class($userSession);
		if (!$userSession->isLoggedIn())
		{
			return $userSessionClass::REFRESH_NO_USER;
		}

		$id = $userSession->getId();
		$user = $this->fetchUserById($id);

		if (isset($user['active']) && $user['active'] != 1)
		{
			return $userSessionClass::REFRESH_USER_DEACTIVATED;
		}

		$id = $user['id'];
		$login = $user['login'];

		// TODO: use a config flag to activate this
		// $encryptedPasswordMethod = 'md5'
		// $encryptedPassword = md5($user['password']);
		// $password = $user['password'];

		unset($user['id']);
		unset($user['login']);
		unset($user['password']);

		$userSession->update($id, $login, $user);

		return $userSessionClass::REFRESH_SUCCESSFUL;
	}

	/**
	 * fetches use by login name from database
	 * @param  string $login loginName
	 * @return array         Array of userinformations
	 */
	public function fetchUserByLogin($login)
	{
		return $this->getUserTable()->fetchUniqueByColumnValue('login', $login);
	}


	public function fetchUserById($id)
	{
		return $this->getUserTable()->fetchUniqueByColumnValue('id', $id);
	}

	/**
	 * Updates lastLogin row by userId
	 * @param int $userId
	 * @return boolean true if successfull
	 */
	public function setUserLastLoginById($userId)
	{
		$userTable = $this->getUserTable();
		try
		{
			$userTable->update(
				array(
					'lastLogin' => new Zend_Db_Expr('NOW()')
				),
				'id = ' . $userTable->getAdapter()->quote($userId)
			);
		}
		catch (Exception $e)
		{
			return false;
		}
		return true;
	}

	// EXTENDED USER RIGHTS
	/**
	 * returns an array of userroles the user inherits
	 * @param  int $userId
	 * @return array
	 */
	public function getUserroleIdsByUserId($userId)
	{
		$user = $this->fetchUserById($userId);
		if (!empty($user['role']))
		{
			// 1 and 2 means admin and regular user
			if ($user['role'] == 'admin')
			{
				return array(1, 2);
			}
			// 2 means regular user
			else
			{
				return array(2);
			}
		}
		return array(2);
	}

	public function getRightsByUserId($userId)
	{
		return array();
	}

	public function getRightsByUserroleId($userroleId)
	{
		if ($userroleId == 1)
		{
			return array('admin');
		}
		else
		{
			return array();
		}
	}

	public function getRightsByUserIdAndUserroleIds($userId, array $userroleIds = array())
	{
		$rights = $this->getRightsByUserId($userId);
		foreach ($userroleIds as $userroleId)
		{
			$rights = array_merge($rights, $this->getRightsByUserroleId($userroleId));
		}
		return $rights;
	}

	// USERGROUPS
	public function getUsergroupIdsByUserId()
	{
		$select = $this->getUserUsergroupTable()->select();
		$select->where('userId = ?', $userId);
		$userUsergroups = $select->query()->fetchAll();
		$ids = array();
		foreach ($userUsergroups as $userUsergroup)
		{
			$ids[] = $userUsergroup['usergroupId'];
		}
		return $ids;
	}

	// FUNCTIONS FOR FORGOT PASSWORD
	public function fetchActiveUserByLogin($login)
	{
		$userServiceClass = get_class($this->userService);
		$user = $this->fetchUserByLogin($login);
		if (!is_null($user))
		{
			if ($this->isUserDeactivated($user))
			{
				return $userServiceClass::FETCH_USER_DEACTIVATED;
			}
		}
		return $user;
	}

	public function setPasswordById($userId, $newPassword)
	{
		try
		{
			// TODO: use password hashing adapter
			// TODO: set passwordType => 'md5'
			$table = $this->getUserTable();
			$table->update(
					array(
						'password' => md5($newPassword),
					),
					'id = ' . $table->getAdapter()->quote($userId)
				);
		}
		catch (Exception $e)
		{
			return false;
		}
		return true;
	}

	public function generateForgotPasswordTokenById($userId, $validUntilTimestamp, array $options = array())
	{
		$tokenTable = $this->getUserPasswordTokenTable();
		$data = array(
				'userId' => $userId,
				'created' => time()
		);
		
		if ($validUntilTimestamp === 'neverExpire')
		{
			$data['validUntil'] = new Zend_Db_Expr('NULL');
		}
		else
		{
			$data['validUntil'] = $validUntilTimestamp;
		}

		if (empty($options['token']))
		{
			$data['token'] = md5(uniqid(rand(1, 100000)));
		}
		else
		{
			$data['token'] = $options['token'];
		}

		$tokenTable->insert($data);

		return $data['token'];
	}

	public function checkForgotPasswordToken($token, $userLogin)
	{
		$userServiceClass = get_class($this->userService);
		$tokenTable = $this->getUserPasswordTokenTable();
		$tokenRow = $tokenTable->select()->where('token = ?', $token)->query()->fetch();

		if (is_null($tokenRow) || $tokenRow['state'] !== 'unused')
		{		
			return $userServiceClass::TOKEN_INVALID;
		}
		
		$user = $this->fetchUserByLogin($userLogin);
		if (!is_array($user) || $user['login'] !== $userLogin || $tokenRow['userId'] !== $user['id'])
		{
			return $userServiceClass::TOKEN_INVALID;
		}
		
		if ($tokenRow['validUntil'] < time())
		{
			return $userServiceClass::TOKEN_EXPIRED;
		}
		
		return $userServiceClass::TOKEN_VALID;
	}

	public function setForgotPasswordTokenState($token)
	{
		$tokenTable = $this->getUserPasswordTokenTable();
		$tokenTable->update(array('state' => 'used'), $tokenTable->getAdapter()->quoteInto('token = ?', $token));
	}

	public function invalidateUnusedForgotPasswordTokensByLogin($userLogin)
	{
		$user = $this->fetchUserByLogin($userLogin);
		if (!is_array($user) || empty($user['id']))
		{
			throw new Exception('Unknown user when invalidating password reste token');
		}
		$tokenTable = $this->getUserPasswordTokenTable();
		$tokenTable->update(array('state' => 'used'), $tokenTable->getAdapter()->quoteInto('userId = ?', $user['id']));
	}

	// FUNCTIONS FOR REGISTER

	/**
	 * checks if an login is already given
	 * @param  string $login the login name you are looking for
	 * @return boolean        true if login name exists
	 */
	public function loginExists($login)
	{
		return $this->getUserTable()->columnValueExists('login', $login);
	}
	/**
	 * checks if an email is already given
	 * @param  string $login the email you are looking for
	 * @return boolean        true if email exists
	 */
	public function emailExists($email)
	{
		return $this->getUserTable()->columnValueExists('email', $email);
	}

	/**
	 * creates a user with an data array
	 * @param  array  $data the user data
	 * @return int     the inserted userId
	 */
	public function createUser(array $data)
	{
		$userServiceClass = get_class($this->userService);
		$data['active'] = 0;

		if (!is_array($data))
		{
			$data = array();
		}

		if (!isset($data['login']))
		{
			throw new Exception($userServiceClass::REGISTER_LOGIN_MISSING);
		}

		if ($this->loginExists($data['login']))
		{
			throw new Exception($userServiceClass::REGISTER_LOGIN_EXISTS);
		}

		if(!isset($data['email']))
		{
			throw new Exception($userServiceClass::REGISTER_EMAIL_MISSING);
		}

		if($this->emailExists($data['email']))
		{
			throw new Exception($userServiceClass::REGISTER_EMAIL_EXISTS);
		}

		if (!isset($data['password']))
		{
			throw new Exception($userServiceClass::REGISTER_PASSWORD_EMPTY);
		}

		// TODO: validate password pattern
		// TODO: configure encryption method of password
		$data['password'] = md5($data['password']);

		// TODO: check additional fields to add to user row

		try
		{
			$userId = $this->getUserTable()->insert($data);
		}
		catch (Exception $e)
		{
			throw new Exception($userServiceClass::REGISTER_INSERT_FAILED, null ,$e);
		}

		// TODO: enhance error handling
		if (is_null($userId))
		{
			throw new Exception($userServiceClass::REGISTER_FAILED);
		}

		return $userId;
	}

	/**
	 * confirms a user code by resetting code and set user active
	 * @param  string $code code you are looking for
	 * @return array       data array of found user
	 */
	public function confirmUser($code)
	{
		$userServiceClass = get_class($this->userService);
		$table = $this->getUserTable();

		try
		{
			$found = $this->getUserTable()->fetchUniqueByColumnValue('confirmCode', $code);
		}
		catch (Exception $e)
		{
			throw new Exception($userServiceClass::REGISTER_CONFIRM_FETCH_FAILED, null ,$e);
		}

		if(!empty($found))
		{
			try
			{
				$table->update(
						array(
							'confirmCode' => new Zend_Db_Expr('NULL'),
							'active' => 1,
							'role' => 'user',
						),
						'id = ' . $table->getAdapter()->quote($found['id'])
					);
			}
			catch (Exception $e)
			{
				throw new Exception($userServiceClass::REGISTER_CONFIRM_UPDATE_FAILED, null ,$e);
			}
		}
		else
		{
			throw new Exception($userServiceClass::REGISTER_CONFIRM_NO_CODE_FOUND);
		}
		return $found;
	}

	public function fetchUserByMail($email)
	{
		$userServiceClass = get_class($this->userService);

		$user = $this->getUserTable()->fetchAllByColumnValue('email', $email);

		return $user;
	}
}