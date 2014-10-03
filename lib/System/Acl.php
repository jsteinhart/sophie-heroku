<?php
class System_Acl extends Symbic_Singleton
{
	protected $sessionUser;
	protected $aclTable;

	public function init()
	{
		$this->sessionUser = Symbic_User_Session::getInstance();
		$this->aclTable = System_Db_Acl::getInstance();
	}

	public function getUsergroupIds()
	{
		return System_Db_User_Usergroup::getInstance()->getUsergroupIdsByUser($this->sessionUser->getId());
	}

	public function autoCheckAcl($resourceClass, $resourceId, $resourceTable)
	{
		if ($this->sessionUser->hasRight('admin'))
		{
			return true;
		}

		$table = new Zend_Db_Table($resourceTable);
		$table = $table->getAdapter();

		$select = $table->select();
		$select->from(array($resourceTable), array('cnt' => 'COUNT(*)'));

		$select = $this->addSelectAcl($select, $resourceClass, $resourceTable);
		$select->where($resourceTable . '.id = ?', $resourceId);
		
		$row = $table->fetchRow($select);
		return ($row['cnt']) ? true : false;
	}

	public function checkAcl($resourceClass, $resourceId, $roleClass, $roleId, $action = 'access')
	{
		if ($this->sessionUser->hasRight('admin'))
		{
			return true;
		}

		$select = $this->aclTable->select();
		$select->where('resourceClass = ?', $resourceClass);
		$select->where('resourceId = ?', $resourceId);
		$select->where('roleClass = ?', $roleId);
		$select->where('roleId = ?', $roleClass);
		$select->where('action = ?', $action);

		$access = $select->query()->fetchRow();

		return ($access !== false && $access->rule === 'allow');
	}

	public function getAclWhere($resourceClass, $resourceTable = null)
	{
		$db = $this->aclTable->getAdapter();

		if (is_null($resourceTable))
		{
			$resourceTable = $resourceClass;
		}

		$aclWhere = '';
		$aclWhere .= $db->quoteInto($resourceTable . '.ownerId = ?', $this->sessionUser->getId());

		// add sessions by system_acl table
		$aclWhere .= ' OR (acl.roleClass = "user" AND acl.roleId = ' . $db->quote($this->sessionUser->getId()) . ' AND acl.rule = "allow")';
		$usergroupIds = $this->getUsergroupIds();
		if (is_array($usergroupIds) && sizeof($usergroupIds)>0)
		{
			$aclWhere .= ' OR (acl.roleClass = "group" AND acl.roleId IN (' . join(',', $usergroupIds) . ') AND acl.rule = "allow")';
		}
		return $aclWhere;
	}

	public function addSelectAcl($select, $resourceClass, $resourceTable = null)
	{
		if (is_null($resourceTable))
		{
			$resourceTable = $resourceClass;
		}

		// add acl filters
		$select->joinLeft(
					array('acl'=>$this->aclTable->_name),
					'acl.resourceClass = "' . $resourceClass . '" AND acl.action = "access" AND ' . $resourceTable . '.id = acl.resourceId',
					array('acl_rule'=>'acl.rule')
		);

		$select->where($this->getAclWhere($resourceClass, $resourceTable));

		return $select;
	}

	public function setAccessForRoles($resourceClass, $resourceId, $roleClass, $roleIds, $action = 'access', $rule = 'allow')
	{
		$db = $this->aclTable->getAdapter();

		if (is_null($roleIds))
		{
			$roleIds = array();
		}

		$deleteOld = '';
		$deleteOld .= 'resourceClass = ' . $db->quote($resourceClass);
		$deleteOld .= ' AND resourceId = ' . $db->quote($resourceId);
		$deleteOld .= ' AND roleClass = ' . $db->quote($roleClass);
		$deleteOld .= ' AND action = ' . $db->quote($action);

		if (sizeof($roleIds) > 0)
		{
			$deleteOld .= ' AND roleId NOT IN (' .  join(',', $roleIds) . ')';
		}
		$this->aclTable->delete($deleteOld);

		foreach ($roleIds as $roleId)
		{
			$accessData = array();
			$accessData['resourceClass'] = $resourceClass;
			$accessData['resourceId'] = $resourceId;
			$accessData['roleClass'] = $roleClass;
			$accessData['roleId'] = $roleId;
			$accessData['action'] = $action;
			$accessData['rule'] = $rule;
			$this->aclTable->replace($accessData);
		}
	}

	public function getAccessForRoleClass($resourceClass, $resourceId, $roleClass, $action = 'access')
	{
		$select = $this->aclTable->select();
		$select->where('resourceClass = ?', $resourceClass);
		$select->where('resourceId = ?', $resourceId);
		$select->where('roleClass = ?', $roleClass);
		$select->where('action = ?', $action);

		$roleAccesses = $select->query()->fetchAll();

		$ids = array();
		foreach ($roleAccesses as $roleAccess)
		{
			$ids[] = $roleAccess['roleId'];
		}
		return $ids;
	}
}