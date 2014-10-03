<?php
namespace Sfwsysadmin\Model;

class User extends \Symbic_Singleton
{
	protected $idColumn = 'id';
	protected $nameColumn = 'name';
	protected $loginColumn = 'login';
	protected $passwordColumn = 'password';
	protected $emailColumn = 'email';

	public function translateColNameForSfw($colName)
	{
		switch($colName)
		{
			case $this->idColumn:
				return 'id';

			case $this->nameColumn:
				return 'name';

			case $this->loginColumn:
				return 'login';

			case $this->emailColumn:
				return 'email';

			case $this->passwordColumn:
				return 'password';

			default:
				return $colName;
		}
	}

	public function translateColNameForModel($colName)
	{
		switch($colName)
		{
			case 'id':
				return $this->idColumn;

			case 'login':
				return $this->loginColumn;

			case 'email':
				return $this->emailColumn;

			case 'password':
				return $this->passwordColumn;

			default:
				return $colName;
		}
	}

	public function translateColsForSfw($data)
	{
		$data2 = array();
		foreach ($data as $dataKey => $dataValue)
		{
			$data2[$this->translateColNameForSfw($dataKey)] = $dataValue;
		}
		return $data2;
	}

	public function translateColsForModel($data)
	{
		$data2 = array();
		foreach ($data as $dataKey => $dataValue)
		{
			$data2[$this->translateColNameForModel($dataKey)] = $dataValue;
		}
		return $data2;
	}

	public function insertUser(array $data)
	{
		$userModel = User\Table::getInstance();
		$data['password'] = md5($data['password']);
		$data = $this->translateColsForModel($data);
		return $userModel->insert($data);
	}

	public function insertUsergroup(array $data)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		return $usergroupModel->insert($data);
	}

	public function updateUserById(array $data, $userId)
	{
		$userModel = User\Table::getInstance();
		$db = $userModel->getAdapter();
		if (isset($data['password']))
		{
			$data['password'] = md5($data['password']);
		}
		$data = $this->translateColsForModel($data);
		return $userModel->update($data, $db->quoteIdentifier($this->idColumn) . ' = ' . $db->quote($userId));
	}

	public function updateUsergroupById(array $data, $usergroupId)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		$db = $usergroupModel->getAdapter();
		return $usergroupModel->update($data, 'id = ' . $db->quote($usergroupId));
	}

	public function fetchAllUsersOrderByColumn($orderColName = null)
	{
		if ($orderColName === null)
		{
			$orderColName = $this->loginColumn;
		}
		else
		{
			$orderColName = $this->translateColNameForModel($orderColName);
		}

		$userModel = User\Table::getInstance();
		$select = $userModel->select();
		$select->order($orderColName);

		$result = $userModel->fetchAll($select)->toArray();
		$result2 = array();
		foreach ($result as $data)
		{
			$result2[] = $this->translateColsForSfw($data);
		}
		return $result2;
	}

	public function fetchAllUsergroupsOrderByColumn($orderColName = null)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		if ($orderColName === null)
		{
			$orderColName = 'name';
		}

		$select = $usergroupModel->select();
		$select->order($orderColName);

		$result = $usergroupModel->fetchAll($select)->toArray();
		return $result;
	}

	public function fetchUserById($userId)
	{
		$userModel = User\Table::getInstance();
		$db = $userModel->getAdapter();
		$row = $userModel->fetchRow($db->quoteIdentifier($this->idColumn) . ' = ' . $db->quote($userId));
		if ($row === false)
		{
			return false;
		}
		return $row->toArray();
	}

	public function fetchUsergroupById($usergroupId)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		$db = $usergroupModel->getAdapter();
		$row = $usergroupModel->fetchRow('id = ' . $db->quote($usergroupId));
		if ($row === false)
		{
			return false;
		}
		return $row->toArray();
	}

	public function getRoleSelect()
	{
		return array('user' => 'User', 'admin' => 'Administrator');
	}

	public function getUsergroupSelect()
	{
		$usergroupModel = Usergroup\Table::getInstance();
		$usergroups = $usergroupModel->fetchAll()->toArray();
		$usergroupSelect = array();
		foreach ($usergroups as $usergroup)
		{
			$usergroupSelect[$usergroup['id']] = $usergroup['name'];
		}
		return $usergroupSelect;
	}

	public function getUserSelect()
	{
		$userModel = User\Table::getInstance();
		$users = $userModel->fetchAll()->toArray();
		$userSelect = array();
		foreach ($users as $user)
		{
			$userSelect[$user['id']] = $user['name'] . '(' . $user['login'] . ')';
		}
		return $userSelect;
	}

	public function getUserUsergroupUsergroupIdByUserId($userId)
	{
		$userModel = User\Table::getInstance();
		$userUsergroupModel = User\Usergroup\Table::getInstance();
		$db = $userUsergroupModel->getAdapter();
		$usergroups = $userUsergroupModel->fetchAll($db->quoteIdentifier('userId') . ' = ' . $db->quote($userId));
		$usergroupIds = array();
		foreach ($usergroups as $usergroup)
		{
			$usergroupIds[] = $usergroup->usergroupId;
		}
		return $usergroupIds;
	}

	public function userLoginExists($login)
	{
		$userModel = User\Table::getInstance();
		return $userModel->columnValueExists($this->loginColumn, $login);
	}

	public function groupNameExists($name)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		return $usergroupModel->columnValueExists('name', $name);
	}

	public function userEmailExists($email)
	{
		$userModel = User\Table::getInstance();
		return $userModel->columnValueExists($this->emailColumn, $email);
	}

	public function setUserUsergroupsById($userId, $usergroupIds)
	{
		$userUsergroupModel = User\Usergroup\Table::getInstance();
		$db = $userUsergroupModel->getAdapter();
		if (sizeof($usergroupIds) > 0)
		{
			foreach($usergroupIds as $usergroupId)
			{
				$userUsergroupModel->replace(array('userId'=> $userId, 'usergroupId' => $usergroupId));
			}
			$userUsergroupModel->delete($db->quoteIdentifier('userId') . ' = ' . $db->quote($userId) . ' AND ' . $db->quoteIdentifier('usergroupId') . ' NOT IN (' . $db->quote($usergroupIds) . ')');
		}
		else
		{
			$userUsergroupModel->delete($db->quoteIdentifier('userId') . ' = ' . $db->quote($userId));
		}
	}

	public function setUsergroupUsersById($usergroupId, $userIds)
	{
		$userUsergroupModel = User\Usergroup\Table::getInstance();
		$db = $userUsergroupModel->getAdapter();
		if (sizeof($userIds) > 0)
		{
			foreach($userIds as $userId)
			{
				$userUsergroupModel->replace(array('userId'=> $userId, 'usergroupId' => $usergroupId));
			}
			$userUsergroupModel->delete($db->quoteIdentifier('usergroupId') . ' = ' . $db->quote($usergroupIds) . ' AND ' . $db->quoteIdentifier('userId') . ' NOT IN (' . $db->quote($userId) . ')');
		}
		else
		{
			$userUsergroupModel->delete($db->quoteIdentifier('usergroupId') . ' = ' . $db->quote($usergroupId));
		}
	}

	public function getNewUserMessageById($userId, $unencryptedPassword, $actionController)
	{
		$userModel = User\Table::getInstance();
		$user = $userModel->fetchUserById($userId);
		if ($user === false)
		{
			throw new \Exception('User not found when creating new user message');
		}
		$user = $this->translateColsForSfw($user);
		
		$text = '';

		$text .= "A user account has been created for you.\n\n";
		$text .= "Account details\n---\n";

		$text .= "Name: " .  $user['name'] . "\n";
		$text .= "Login: " . $user['login'] . "\n";
		$text .= "Password: " . $unencryptedPassword . "\n";

		$text .= "\nPlease change your password after the next login.\n";

		$messageContents = array(
			'subject' => 'Welcome to your new User Account',
			'bodyText' => $text
		);
		return $messageContents;
	}

	public function getUserUpdateMessageById($userId, $unencryptedPassword, $actionController)
	{
		$user = $this->fetchUserById($userId);
		if ($user === false)
		{
			throw new Exception('User not found when creating user update message');
		}
		$user = $this->translateColsForSfw($user);
		
		$text = '';

		$text .= "Account details\n---\n";

		$text .= "Name: " .  $user['name'] . "\n";
		$text .= "Login: " . $user['login'] . "\n";
		if (!empty($unencryptedPassword))
		{
			$text .= "New Password: " . $unencryptedPassword . "\n";
			$text .= "\nPlease change your password after the next login.\n";
		}
		else
		{
			$text .= "Password: -- unchanged --\n";
		}

		$messageContents = array(
			'subject' => 'Your account has been updated',
			'bodyText' => $text
		);
		return $messageContents;
	}

	public function activateUserById($userId)
	{
		$userModel = User\Table::getInstance();
		$db = $userModel->getAdapter();
		$userModel->update(array($this->translateColNameForModel('active') => '1'), $db->quoteIdentifier($this->translateColNameForModel('id')) . ' = ' . $db->quote($userId));
	}

	public function deactivateUserById($userId)
	{
		$userModel = User\Table::getInstance();
		$db = $userModel->getAdapter();
		$userModel->update(array($this->translateColNameForModel('active') => '0'), $db->quoteIdentifier($this->translateColNameForModel('id')) . ' = ' . $db->quote($userId));

	}

	public function deleteUserById($userId)
	{
		$userModel = User\Table::getInstance();
		$db = $userModel->getAdapter();
		return $userModel->delete($db->quoteIdentifier($this->translateColNameForModel('id')) . ' = ' . $db->quote($userId));
	}

	public function deleteUsergroupById($usergroupId)
	{
		$usergroupModel = Usergroup\Table::getInstance();
		$db = $usergroupModel->getAdapter();
		return $usergroupModel->delete($db->quoteIdentifier('id') . ' = ' . $db->quote($usergroupId));
	}
}