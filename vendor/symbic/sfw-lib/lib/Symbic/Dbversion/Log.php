<?php
class Symbic_Dbversion_Log extends Symbic_Db_Table_Abstract
{
	public $_name = 'system_db_version_log';
	public $_primary = 'id';

	static public function log($version, $statement, $status)
	{
		$table = self::getInstance();
		$table->insert(array('microtime'=>microtime(true),'version' => $version, 'statement'=>$statement, 'status'=> $status));
	}
}