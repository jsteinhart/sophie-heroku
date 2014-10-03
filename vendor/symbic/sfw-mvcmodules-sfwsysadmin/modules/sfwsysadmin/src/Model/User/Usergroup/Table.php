<?php
namespace Sfwsysadmin\Model\User\Usergroup;

class Table extends \Symbic_Db_Table_Abstract
{
	public $_name = 'system_user_usergroup';
	public $_primary = array('userId', 'usergroupId');
}