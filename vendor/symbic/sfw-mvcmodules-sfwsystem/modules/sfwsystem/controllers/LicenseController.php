<?php
class Sfwsystem_LicenseController extends Symbic_Controller_Action
{
	public function init()
	{
	}

	public function indexAction()
	{
		$licenceFile = BASE_PATH . DIRECTORY_SEPARATOR . 'LICENSE';
		if (!file_exists($licenceFile))
		{
			$this->view->license = 'License file not found. Please contact the developer for license information.';
		}
		else
		{
			$this->view->license = file_get_contents($licenceFile);
		}

		if ($this->getUserSession()->isLoggedIn())
		{
			$partLicenseDir = BASE_PATH . DIRECTORY_SEPARATOR . 'contrib' . DIRECTORY_SEPARATOR . 'license';
			$parts = array();
			if (file_exists($partLicenseDir) && is_dir($partLicenseDir) && $handle = opendir($partLicenseDir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if (substr($file, 0, 1) == '.')
					{
						continue;
					}
					$licenseDir = $partLicenseDir . DIRECTORY_SEPARATOR . $file;
					if (!is_dir($licenseDir))
					{
						continue;
					}
					
					$part = array(
						'partName' => $file,
						'partUrl' => '',
						'partAuthors' => array(),
						'licenseType' => '',
						'licenseUrl' => '',
						'licenseText' => ''
					);
					if (file_exists($licenseDir . DIRECTORY_SEPARATOR . 'LICENSE.json'))
					{
						$licenseJson = file_get_contents($licenseDir . DIRECTORY_SEPARATOR . 'LICENSE.json');
						$licenseJson = json_decode($licenseJson, true);
						if (is_array($licenseJson))
						{
							$part = array_merge($part, $licenseJson);
						}
					}
					if (file_exists($licenseDir . DIRECTORY_SEPARATOR . 'LICENSE.txt'))
					{
						$part['licenseText'] = file_get_contents($licenseDir . DIRECTORY_SEPARATOR . 'LICENSE.txt');
					}
					if (!is_array($part['partAuthors']))
					{
						$part['partAuthors'] = (empty($part['partAuthors'])) ? array() : array($part['partAuthors']);
					}
					$parts[] = $part;
				}
				closedir($handle);
			}
			usort($parts, array($this, 'sortLicenses'));
			$this->view->parts = $parts;

			//Setup Composer Components
			$composerComponents = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'installed.json';
			if(file_exists($composerComponents))
			{
				$fileString = file_get_contents($composerComponents);
				$jsonObject = json_decode($fileString, true);
				$sortMe = array();
				foreach($jsonObject as $obj)
				{
					$sortMe[$obj['name']] = $obj;
				}
				$composer = array(
					'name' => 'composer',
					'description' => 'Dependency Manager for PHP',
					'homepage' => 'http://getcomposer.org/',
					'authors' => array( array('name' => 'Nils Adermann'), array('name' => 'Jordi Boggiano') ),
					'license' => array('MIT License'),
				);
				$sortMe[ $composer['name'] ] = $composer;
				ksort($sortMe);
				$this->view->composerComponents = $sortMe;
			}


			//setup JS / CSS components
			$jsComponentsDirectory = BASE_PATH . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'components';
			$this->view->javascriptComponents = $this->generateJsComponentsLicense($jsComponentsDirectory);
		}
	}

	private function sortLicenses($a, $b)
	{
		if (!isset($a['sequence']))
		{
			$a['sequence'] = PHP_INT_MAX;
		}
		if (!isset($b['sequence']))
		{
			$b['sequence'] = PHP_INT_MAX;
		}
		if ($a['sequence'] < $b['sequence'])
		{
			return -1;
		}
		if ($a['sequence'] > $b['sequence'])
		{
			return 1;
		}
		
		return strcmp($a['partName'], $b['partName']);
	}

	private function generateJsComponentsLicense($componentsDir)
	{
		$config = $this->getModuleConfig();
		if (file_exists($componentsDir) && is_dir($componentsDir) && $handle = opendir($componentsDir))
		{
			$licenseInformations = array();
			while (false !== ($file = readdir($handle)))
			{
				if (substr($file, 0, 1) == '.')
				{
					continue;
				}
				$componentDir = $componentsDir . DIRECTORY_SEPARATOR . $file;
				if (!is_dir($componentDir))
				{
					continue;
				}

				$componentLicenseFile = $componentDir . DIRECTORY_SEPARATOR . 'license.json';


				if(!file_exists($componentLicenseFile))
				{
					$componentInformation = array(
							'name' => ucfirst($file),
							'version' => array_diff(scandir($componentDir), array('..', '.', '.htaccess', 'license.json')),
							'homepage' => '',
							'authors' => array(''),
							'license' => array(''),
						);
					if($config['generateEmptyComponentsLicense'])
					{
						if (defined('JSON_PRETTY_PRINT'))
						{
							file_put_contents($componentLicenseFile, json_encode($componentInformation,JSON_PRETTY_PRINT));
						}
						else
						{
							file_put_contents($componentLicenseFile, json_encode($componentInformation));
						}
					}
				}
				else
				{
					$componentInformation = json_decode(file_get_contents($componentLicenseFile),true);
				}
				$licenseInformations[] = $componentInformation;
			}
			closedir($handle);
			return $licenseInformations;
		}
		return array();
	}

}