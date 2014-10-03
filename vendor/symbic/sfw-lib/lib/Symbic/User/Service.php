<?php
class Symbic_User_Service extends Symbic_Singleton
{
	const MODEL_FAILURE = 'MODEL_FAILURE';

	//LOGIN CONSTANTS

	const LOGIN_SUCCESSFUL = 'LOGIN_SUCCESSFUL';
	const LOGIN_USER_UNKNOWN = 'LOGIN_USER_UNKNOWN';
	const LOGIN_WRONG_PASSWORD = 'LOGIN_WRONG_PASSWORD';
	const LOGIN_USER_DEACTIVATED = 'LOGIN_USER_DEACTIVATED';

	const FETCH_SUCCESSFUL = 'FETCH_SUCCESSFUL';
	const FETCH_USER_DEACTIVATED = 'FETCH_USER_DEACTIVATED';
	const FETCH_USER_UNKNOWN = 'FETCH_USER_UNKNOWN';

	const TOKEN_VALID = 'TOKEN_VALID';
	const TOKEN_EXPIRED = 'TOKEN_EXPIRED';
	const TOKEN_INVALID = 'TOKEN_INVALID';

	//REGISTER CONSTANTS
	const REGISTER_LOGIN_MISSING = 'REGISTER_LOGIN_MISSING';
	const REGISTER_LOGIN_EXISTS = 'REGISTER_LOGIN_EXISTS';
	const REGISTER_EMAIL_MISSING = 'REGISTER_EMAIL_MISSING';
	const REGISTER_EMAIL_EXISTS = 'REGISTER_EMAIL_EXISTS';
	const REGISTER_PASSWORD_EMPTY = 'REGISTER_PASSWORD_EMPTY';
	const REGISTER_INSERT_FAILED = 'REGISTER_INSERT_FAILED';
	const REGISTER_FAILED = 'REGISTER_FAILED';
	const REGISTER_SUCCESSFUL = 'REGISTER_SUCCESSFUL';
	const REGISTER_CONFIRM_FETCH_FAILED = 'REGISTER_CONFIRM_FETCH_FAILED';
	const REGISTER_CONFIRM_NO_CODE_FOUND = 'REGISTER_CONFIRM_NO_CODE_FOUND';
	const REGISTER_CONFIRM_UPDATE_FAILED = 'REGISTER_CONFIRM_UPDATE_FAILED';
	const REGISTER_CONFIRM_SUCCESSFULL = 'REGISTER_CONFIRM_SUCCESSFULL';
	const REGISTER_CONFIRM_FAILED = 'REGISTER_CONFIRM_FAILED';

	protected $options = array();

	protected $lastLoginByLoginAndPasswordStatus = null;
	protected $lastLoginByLoginAndPasswordModel = null;

	protected $lastFetchUserByLoginStatus = null;
	protected $lastFetchUserByLoginModel = null;

	protected $lastCreateUserStatus = null;

	protected $lastConfirmUserStatus = null;

	protected $models = array();
	protected $registerModel = null;

	protected function factory($modelId, $modelConfig)
	{
		if (!is_array($modelConfig))
		{
			throw Exception('Model factory expects an array $modelConfig parameter');
		}
		
		if (!isset($modelConfig['class']))
		{
			throw Exception('Model factory expects an array $modelConfig parameter');
		}

		$modelClass = $modelConfig['class'];
		unset($modelConfig['class']);
		
		$model = new $modelClass($this, $modelId, $modelConfig);
		return $model;
	}

	public function setOptions(array $options)
	{
		$this->options = $options;
	}

	/**
	 * factory method to retrun a specific model given by modelid
	 * @param  string $modelId the modelname defined in options
	 * @return class
	 */
	public function getModel($modelId)
	{
		if (!isset($this->models[$modelId]))
		{
			if (!isset($this->options['models']))
			{
				throw new Exception('No User Model defined');
			}
			if (!isset($this->options['models'][$modelId]))
			{
				throw new Exception('Unkown User Model requested: ' . $modelId);
			}
			try
			{
				$this->models[$modelId] = $this->factory($modelId, $this->options['models'][$modelId]);
			}
			catch (Exception $e)
			{
				throw new Exception('User Model Exception for model: ' . $modelId);
			}
		}
		return $this->models[$modelId];
	}

	/**
	 * factory method to return a registermodel defined by specific option tag
	 * @return class
	 */
	public function getRegisterModel()
	{
		if (empty($this->registerModel))
		{
			if (!isset($this->options['registerModel']))
			{
				throw new Exception('No Register Model defined');
			}
			try
			{
				$this->registerModel = $this->factory('registerModel', $this->options['registerModel']);
			}
			catch (Exception $e)
			{
				throw new Exception('Register Model Exception for model: ' . $modelId);
			}
		}
		return $this->registerModel;
	}

	/**
	 * login a user by login and password by an user session class
	 * @param  string              $login       the loginname
	 * @param  string              $password    the password
	 * @param  Symbic_User_Session $sessionUser the session where the user is logged in
	 * @return bool                           true if user is logged in
	 */
	public function loginByLoginAndPassword($login, $password, Symbic_User_Session $sessionUser)
	{
		$this->lastLoginByLoginAndPasswordStatus = null;
		$this->lastLoginByLoginAndPasswordModel = null;

		if (!isset($this->options['models']) || !is_array($this->options['models']))
		{
			throw new Exception('No User Model defined');
		}

		foreach ($this->options['models'] as $modelId => $modelOptions)
		{
			$model = $this->getModel($modelId);

			if (!method_exists($model, 'loginUserByLoginAndPassword'))
			{
				throw new Exception('Invalid User Model defined');
			}

			try {
				$loginResult = $model->loginUserByLoginAndPassword($login, $password, $sessionUser);
			}
			catch (Exception $e)
			{
				$this->lastLoginByLoginAndPasswordStatus = $loginResult;
				$this->lastLoginByLoginAndPasswordModel = $model;
				return false;
			}

			if ($loginResult === self::LOGIN_SUCCESSFUL)
			{
				$this->lastLoginByLoginAndPasswordStatus = $loginResult;
				$this->lastLoginByLoginAndPasswordModel = $model;
				return true;
			}

			if (
				$loginResult === self::MODEL_FAILURE &&
				isset($modelOptions['failOnModelFailure']) &&
				$modelOptions['failOnModelFailure'] == true
			)
			{
				$this->lastLoginByLoginAndPasswordStatus = self::MODEL_FAILURE;
				$this->lastLoginByLoginAndPasswordModel = $model;
				return false;
			}

			if (
				$loginResult === self::LOGIN_WRONG_PASSWORD ||
				$loginResult === self::LOGIN_USER_DEACTIVATED
			)
			{
				$this->lastLoginByLoginAndPasswordStatus = $loginResult;
				$this->lastLoginByLoginAndPasswordModel = $model;
				return false;
			}
		}

		$this->lastLoginByLoginAndPasswordModel = null;
		$this->lastLoginByLoginAndPasswordStatus = self::LOGIN_USER_UNKNOWN;
		return false;
	}

	public function getLastLoginByLoginAndPasswordStatus()
	{
		return $this->lastLoginByLoginAndPasswordStatus;
	}

	public function getLastLoginByLoginAndPasswordModel()
	{
		return $this->lastLoginByLoginAndPasswordModel;
	}

	/**
	 * fetches an user by login name from userModels
	 * @param  string $login the loginname
	 * @return array        userdata or null
	 */
	public function fetchUserByLogin($login)
	{
		$this->lastFetchUserByLoginStatus = null;
		$this->lastFetchUserByLoginStatusModel = null;

		if (!isset($this->options['models']) || !is_array($this->options['models']))
		{
			throw new Exception('No User Model defined');
		}

		foreach ($this->options['models'] as $modelId => $modelOptions)
		{
			$model = $this->getModel($modelId);

			if (!method_exists($model, 'fetchUserByLogin'))
			{
				throw new Exception('Invalid User Model defined');
			}

			try {
				$result = $model->fetchUserByLogin($login);
			}
			catch (Exception $e)
			{
				$this->lastFetchUserByLoginStatus = self::MODEL_FAILURE;
				$this->lastFetchUserByLoginStatusModel = $model;
				return null;
			}

			if (is_array($result))
			{
				$this->lastFetchUserByLoginStatus = self::FETCH_SUCCESSFUL;
				$this->lastFetchUserByLoginStatusModel = $model;
				return $result;
			}
		}

		$this->lastFetchUserByLoginStatus = self::FETCH_USER_UNKNOWN;
		return null;
	}

	/**
	 * fetches an active user by login name from userModels
	 * @param  string $login the loginname
	 * @return array        userdata or null
	 */
	public function fetchActiveUserByLogin($login)
	{
		$this->lastFetchUserByLoginStatus = null;
		$this->lastFetchUserByLoginStatusModel = null;

		if (!isset($this->options['models']) || !is_array($this->options['models']))
		{
			throw new Exception('No User Model defined');
		}

		foreach ($this->options['models'] as $modelId => $modelOptions)
		{
			$model = $this->getModel($modelId);

			$fetchMethod = 'fetchActiveUserByLogin';
			if (!method_exists($model, $fetchMethod))
			{
				$fetchMethod = 'fetchUserByLogin';
				if (!method_exists($model, $fetchMethod))
				{
					throw new Exception('Invalid User Model defined');
				}
			}

			try {
				$result = $model->$fetchMethod($login);
			}
			catch (Exception $e)
			{
				$this->lastFetchUserByLoginStatus = self::MODEL_FAILURE;
				$this->lastFetchUserByLoginStatusModel = $model;
				return null;
			}

			if ($result === self::FETCH_USER_DEACTIVATED)
			{
				$this->lastFetchUserByLoginStatus = $result;
				$this->lastFetchUserByLoginStatusModel = $model;
				return null;
			}

			if (is_array($result))
			{
				$this->lastFetchUserByLoginStatus = self::FETCH_SUCCESSFUL;
				$this->lastFetchUserByLoginStatusModel = $model;
				return $result;
			}
		}

		$this->lastFetchUserByLoginStatus = self::FETCH_USER_UNKNOWN;
		return null;
	}


	/**
	 * returns the status of the Last excecuted createUser call
	 * @return const status
	 */
	public function getlastCreateUserStatus()
	{
		return $this->lastCreateUserStatus;
	}

	/**
	 * calls the createUser function of the registration model
	 * @param  array  $data the user Data
	 * @return int       inserted userId if successfull
	 */
	public function createUser(array $data)
	{
		$this->lastCreateUserStatus = null;

		if (empty($this->options['registerModel']))
		{
			throw new Exception('No User Model for registration defined');
		}

		$model = $this->getRegisterModel();

		try
		{
			$result = $model->createUser($data);
		}
		catch (Exception $e)
		{
			$this->lastCreateUserStatus = $e->getMessage();
			return null;
		}

		$this->lastCreateUserStatus = self::REGISTER_SUCCESSFUL;
		return $result;
	}


	/**
	 * returns the last Status of confirm User request
	 * @return const status
	 */
	public function getLastConfirmUserStatus()
	{
		return $this->lastConfirmUserStatus;
	}

	/**
	 * Confirms a user code in the RegisterModel
	 * @param  String $code                   the confirmation code
	 * @param  bool $loginAfterConfirmation boolean to login after confirmation
	 * @return bool                         true if confirmation successfull
	 */
	public function confirmUser($code)
	{
		$this->lastConfirmUserStatus = null;
		$model = $this->getRegisterModel();

		try
		{
			$userData = $model->confirmUser($code);
		}
		catch (Exception $e)
		{
			$this->lastConfirmUserStatus = $e->getMessage();
			return false;
		}
		if(empty($userData))
		{
			$this->lastConfirmUserStatus = self::REGISTER_CONFIRM_FAILED;
			return false;
		}

		$this->lastConfirmUserStatus = self::REGISTER_CONFIRM_SUCCESSFULL;
		return true;
	}
}