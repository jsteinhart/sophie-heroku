<?php
namespace Sfwinstaller\Installer\Runner;

class Standard
{
	protected $db;
	protected $dbSchemaFile;
	protected $dbDataFile;
	
	public function __construct()
	{
		$this->dbSchemaFile = BASE_PATH . DIRECTORY_SEPARATOR . 'contrib' . DIRECTORY_SEPARATOR . 'install.sql';
		$this->dbDataFile = BASE_PATH . DIRECTORY_SEPARATOR . 'contrib' . DIRECTORY_SEPARATOR . 'data.sql';
	}
	
	public function run($values)
	{
		$this->dbInit($values);
		
		$this->dbStartTransaction();
		if (isset($values['dbconfig']) && isset($values['dbconfig']['populateSchema']) && $values['dbconfig']['populateSchema'] === '1' && file_exists($this->dbSchemaFile))
		{
			$this->dbPopulateSchema($values);
		}
		if (isset($values['dbconfig']) && isset($values['dbconfig']['populateData']) && $values['dbconfig']['populateData'] === '1' && file_exists($this->dbDataFile))
		{
			$this->dbPopulateData($values);
		}
	
		if (isset($values['adminuser']))
		{
			if (empty($values['adminuser']['skip']) || $values['adminuser']['skip'] !== '1')
			{
				$this->dbCreateAdmin($values);
			}
		}

		$this->dbCommitTransaction();

		$this->writeApplicationConfig($values);
	}

	protected function dbInit($values)
	{
		try
		{
			$this->db = new \PDO('mysql:host=' . $values['dbconfig']['host'] . ';dbname='.$values['dbconfig']['dbname'], $values['dbconfig']['username'], $values['dbconfig']['password']);
			$this->db->exec("SET CHARACTER SET utf8");
			$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		catch(Exception $e)
		{
			throw new \Exception('Database connection failed: ' . $e->getMessage());
		}
	}
	
	protected function dbStartTransaction()
	{
		$this->db->beginTransaction();
	}
	
	protected function dbPopulateSchema($values)
	{
		try {
			$structureSql = file_get_contents($this->dbSchemaFile);
		}
		catch(Exception $e)
		{
			throw new \Exception('Reading schema sql file failed: ' . $e->getMessage());
		}
		try {
			$this->db->exec($structureSql);
		}
		catch(Exception $e)
		{
			throw new \Exception('Applying schema sql failed: ' . $e->getMessage());
		}
	}

	protected function dbPopulateData($values)
	{
		try {
			$dataSql = file_get_contents($this->dbDataFile);
		}
		catch(Exception $e)
		{
			throw new \Exception('Reading data sql file failed: ' . $e->getMessage());
		}
		try {
			$this->db->exec($dataSql);
		}
		catch(Exception $e)
		{
			throw new \Exception('Applying data sql failed: ' . $e->getMessage());
		}
	}
	
	protected function dbAdminSQLQuery($adminuserValues)
	{
		$sql = "REPLACE INTO `system_user` (`login`, `password`, `name`, `email`, `role`) VALUES (" . $this->db->quote($adminuserValues['username']) . ", " . $this->db->quote(md5($adminuserValues['password'])) . ", " . $this->db->quote($adminuserValues['name']) . ", " . $this->db->quote($adminuserValues['email']) . ", 'admin')";
		
		return $sql;
	}
	
	protected function dbCreateAdmin($values)
	{
		// create admin user
		try {
			$adminUserCreateSql = $this->dbAdminSQLQuery($values['adminuser']);
			$this->db->exec($adminUserCreateSql);
			if ($this->db->errorCode() != 0)
			{
				throw new \Exception(print_r($this->db->errorCode(), true));
			}
		}
		catch(Exception $e)
		{
			throw new \Exception('Creating admin user failed: ' . $e->getMessage());
		}
	}

	protected function dbCommitTransaction()
	{
		try
		{
			$this->db->commit();
		}
		catch(Exception $e)
		{
			throw new \Exception('Committing database failed: ' . $e->getMessage());
		}
	}

	protected function writeApplicationConfig($values)
	{
		$configDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;

		$configTemplateFile = $configDir . 'application_template.php';
		$configFile = $configDir . 'application.php';
		
		// compose config from installer
		$installerConfig = array();
		$installerConfig['resources'] = array();
		$installerConfig['systemConfig'] = array();

		// add db config
		if (isset($values['dbconfig']['populateSchema']))
		{
			unset($values['dbconfig']['populateSchema']);
		}
		$installerConfig['resources']['db'] =
			array(
				'adapter' => 'Pdo_Mysql',
				'params' => $values['dbconfig']
			);
		
		// write mailconfig 
		$installerConfig['resources']['mail'] =
			array(
				'transport' => array(
					'register' => true,
					'type' => $values['mailconfig']['type']
				),
				'defaultFrom' => array(
					'email' => $values['mailconfig']['defaultEmail'],
					'name' => 'Admin'
				),
				'defaultReplyTo' => array(
					'email' => $values['mailconfig']['defaultEmail'],
					'name' => 'Admin'
				)
			);

		if($installerConfig['resources']['mail']['transport']['type'] == 'file')
		{
			$installerConfig['resources']['mail']['transport']['path'] = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .'log';
		}
		elseif ($installerConfig['resources']['mail']['transport']['type'] == 'smtp')
		{
			$installerConfig['resources']['mail']['transport']['host'] = $values['mailconfig']['host'];
			$installerConfig['resources']['mail']['transport']['port'] = $values['mailconfig']['port'];

			if ($values['mailconfig']['auth'] != '')
			{
				$installerConfig['resources']['mail']['transport']['auth'] = $values['mailconfig']['auth'];
			}

			if ($values['mailconfig']['username'] != '')
			{
				$installerConfig['resources']['mail']['transport']['username'] = $values['mailconfig']['username'];
			}

			if ($values['mailconfig']['password'] != '')
			{
				$installerConfig['resources']['mail']['transport']['password'] = $values['mailconfig']['password'];
			}

			if ($values['mailconfig']['ssl'] != '')
			{
				$installerConfig['resources']['mail']['transport']['ssl'] = $values['mailconfig']['ssl'];
			}
		}
		
		$installerConfig['systemConfig']['admin'] = array(
				'email' => $values['adminuser']['email'],
				'name' => $values['adminuser']['name']
			);
		
		
		if (file_exists($configTemplateFile))
		{
			$installerConfig = array_replace_recursive((array)require($configTemplateFile), $installerConfig);
		}

		$installerConfig = var_export($installerConfig, true);
		$installerConfig = str_replace('\'' . addslashes(BASE_PATH), 'BASE_PATH . \'', $installerConfig);

		
		try
		{
			file_put_contents($configFile, '<?php return ' . $installerConfig . ';');
		}
		catch(Exception $e)
		{
			throw new Exception('Writing application config file failed: ' . $e->getMessage());
		}
	}

}