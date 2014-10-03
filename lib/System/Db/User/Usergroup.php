<?php
class System_Db_User_Usergroup extends Symbic_Db_Table_Abstract
{
	public $_name = 'system_user_usergroup';
	public $_primary = array('userId', 'usergroupId');

	// FUNCTIONS
	public function getUsergroupIdsByUser($userId)
	{
		$select = $this->select();
		$select->where('userId = ?', $userId);
		$userUsergroups = $select->query()->fetchAll();
		$ids = array();
		foreach ($userUsergroups as $userUsergroup)
		{
			$ids[] = $userUsergroup['usergroupId'];
		}
		return $ids;
	}
}