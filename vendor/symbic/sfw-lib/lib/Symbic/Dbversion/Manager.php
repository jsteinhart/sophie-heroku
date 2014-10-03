<?php
/**
* Automatically update database schema using a simple versioning
*/

class Symbic_Dbversion_Manager extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'system_db_version';
	public $_primary = 'version';
	public $db;
	public $logTable;

	public $latestVersion = null;

	public function init()
	{
		$this->db = $this->getAdapter();
		$this->logTable = Symbic_Dbversion_Log::getInstance();
	}

	/**
	* Returns the current db version
	* from the local database
	*/
	public function getCurrentDbVersion()
	{
		$currentVersion = $this->fetchRow();
		if (!is_null($currentVersion))
		{
			return $currentVersion->version;
		}
		$this->insert(array(
			'version' => 0,
			'lastChange' => new Zend_Db_Expr('NOW()')
		));
		return 0;
	}

	/**
	* Returns the latest db version
	*/
	public function getLatestDbVersion()
	{
		$this->latestVersion = 0;
		$methods = get_class_methods($this);
		foreach ($methods as $method)
		{
			if (preg_match('/^getUpdatesToVersion(\d+)$/', $method, $matches))
			{
				$this->latestVersion = max($this->latestVersion, $matches[1]);
			}
		}
		return $this->latestVersion;
	}

	/**
	* 1. Get the db version number from the installed version
	* 2. Compare with latest version from update script
	* 3. If installed version is older -> Apply update
	*/
	public function runUpdates()
	{
		$currentVersion = $this->getCurrentDbVersion();
		$latestVersion  = $this->getLatestDbVersion();

		if($currentVersion == $latestVersion)
		{
			return;
		}

		while($currentVersion < $latestVersion)
		{
			$nextVersion = ($currentVersion + 1);
			$updates     = $this->getUpdatesToVersion($nextVersion);

			if($updates)
			{
				$this->applyUpdates($updates,$nextVersion);
			}

			$currentVersion = $nextVersion;
		}
		$this->finish($currentVersion);
	}

	/**
	* Apply an update
	*/
	public function applyUpdates($updates,$nextVersion)
	{
		if(is_array($updates))
		{
			foreach($updates as $sql)
			{
				if (empty($sql))
				{
					continue;
				}
				try
				{
					$this->db->query($sql);
					$this->logTable->log($nextVersion,$sql, "Update successful");
				}
				catch(Zend_Exception $e)
				{
					$this->logTable->log($nextVersion,$sql,$e->getMessage());
				}
			}
		}
	}

	/**
	* Gets the updates needed for the next verison
	*/
	public function getUpdatesToVersion($version)
	{
		if(method_exists($this,'getUpdatesToVersion' . $version))
		{
			return $this->{'getUpdatesToVersion' . $version}();
		}else
		{
			return false;
		}
	}

	/**
	* Get the version log
	*/
	public function getVersionHistory()
	{
		// TODO: implement
		return array();
	}
	
	/**
	* Update the current version number in the local installation
	*/
	private function finish($newVersion)
	{
		parent :: update(array(
			'version' => $newVersion,
			'lastChange' => new Zend_Db_Expr('NOW()')
		), $this->db->quoteInto('version < ?', $newVersion));
	}
}