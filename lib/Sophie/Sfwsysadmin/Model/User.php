<?php
class Sophie_Sfwsysadmin_Model_User extends \Sfwsysadmin\Model\User
{
	public function deleteUserById($userId)
	{
		$aclModel = System_Db_Acl :: getInstance();
		$aclModel->delete('roleClass = "user" AND roleId = ' . $aclModel->getAdapter()->quote($userId));

		return parent::deleteUserById($userId);
	}

	public function deleteUsergroupById($usergroupId)
	{
		$aclModel = System_Db_Acl :: getInstance();
		$aclModel->delete('roleClass = "usergroup" AND roleId = ' . $aclModel->getAdapter()->quote($usergroupId));

		return parent::deleteUsergroupById($usergroupId);
	}
}