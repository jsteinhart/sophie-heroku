<?php
namespace Sfwsysadmin\Model\Config;

class Mail extends \Symbic_Singleton
{
	private function getConfigPath()
	{
		$configDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
		$configFile = $configDir . 'application.php';
		return $configFile;
	}

	private function getConfig()
	{
		$configFile = $this->getConfigPath();
		
		if (!file_exists($configFile))
		{
			throw new Exception('Application config file not found');
		}

		return include($configFile);
	}

	private function getSection($name)
	{
		$config = $this->getConfig();

		if (isset($config['resources']) && isset($config['resources']['mail']) && is_array($config['resources']['mail'][$name]))
		{
			$mailConfig = $config['resources']['mail'][$name];
		}
		else
		{
			$mailConfig = array();
		}

		return $mailConfig;
	}

	private function writeConfig($config)
	{
		$configFile = $this->getConfigPath();

		$config = var_export($config, true);
		$config = str_replace('\'' . addslashes(BASE_PATH), 'BASE_PATH . \'', $config);
		
		try
		{
			file_put_contents($configFile, '<?php return ' . $config . ';');
		}
		catch(Exception $e)
		{
			throw new Exception('Writing application config file failed: ' . $e->getMessage());
		}
	}

	private function updateSection($name, $values)
	{
		$config = $this->getConfig();

		if (!isset($config['resources']) || !is_array($config['resources']))
		{
			$config['resources'] = array();
		}

		if (!isset($config['resources']['mail']) || !is_array($config['resources']['mail']))
		{
			$config['resources']['mail'] = array();
		}

		$config['resources']['mail'][$name] = $values;

		$this->writeConfig($config);
	}

	public function getTransport()
	{
		return $this->getSection('transport');
	}

	public function getDefaultFrom()
	{
		return $this->getSection('defaultFrom');
	}

	public function getDefaultReplyTo()
	{
		return $this->getSection('defaultReplyTo');
	}

	public function updateTransport($values)
	{
		// write mailconfig 
		$config = array(
					'register' => true,
					'type' => $values['type'],
				);

		if($values['type'] == 'file')
		{
			$config['path'] = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .'log';
		}
		elseif ($values['type'] == 'smtp')
		{
			$config['host'] = $values['host'];
			$config['port'] = $values['port'];

			if (!empty($values['auth']))
			{
				$config['auth'] = $values['auth'];
			}

			if (!empty($values['auth']) && !empty($values['username']))
			{
				$config['username'] = $values['transport']['username'];
			}

			if (!empty($values['auth']) && !empty($values['password']))
			{
				$config['password'] = $values['password'];
			}

			if (!empty($values['ssl']))
			{
				$config['ssl'] = $values['ssl'];
			}
		}

		$this->updateSection('transport', $config);
	}

	public function updateDefaultFrom($values)
	{
		$config = array(
					'email' => $values['email'],
					'name' => $values['name'],
				);

		$this->updateSection('defaultFrom', $config);
	}

	public function updateDefaultReplyTo($values)
	{
		$config = array(
					'email' => $values['email'],
					'name' => $values['name'],
				);

		$this->updateSection('defaultReplyTo', $config);
	}
}