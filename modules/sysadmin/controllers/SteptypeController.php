<?php
class Sysadmin_SteptypeController extends Symbic_Controller_Action
{
	public function preDispatch()
	{
		if (!Symbic_User_Session::getInstance()->hasRight('admin'))
		{
			$this->_error('You do not have the right to access this page');
			return;
		}

		$this->view->breadcrumbs = array(
			'home' => 'sfwsysadmin',
			array(
				'url' => $this->view->url(array('module' => 'sysadmin', 'controller' => 'steptype', 'action' => ''), 'default', true),
				'title' => 'Steptypes',
				'small' => 'Steptypes:',
				'name' => 'Overview'
			)
		);
	}

	public function indexAction()
	{
		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		$basepaths = $steptypeFactory->getBasepaths();
		$defaultBasepath = $steptypeFactory->getDefaultBasepath();
		$this->view->basepaths = $basepaths;
		$this->view->defaultBasepath = $defaultBasepath;

		$steptypes = Sophie_Db_Steptype :: getInstance()->fetchAll();
		$this->view->steptypes = $steptypes->toArray();
	}

	public function addAction()
	{
		die('not yet implemented');

		$form = $this->getForm('Steptype_Add');
		$form->setAction($this->view->url());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$installer = new Symbic_Installer();
				$installer->downloadPackageXml($values['package'], 'http://sophie-svn.local/packages');
				$installer->downloadPackageVersionsXml($values['package'], 'http://sophie-svn.local/packages');
				$installer->downloadPackageVersionArchiv($values['package'], '1.0.0', 'http://sophie-svn.local/packages');
				$installer->extractPackageVersionArchiv($values['package'], '1.0.0');

				// TODO: add package stepgroup to stepgroup config
				die('package installed');
			}
		}

		$this->view->form = $form;
	}

	public function installAction()
	{
		$steptypeId = $this->_getParam('steptypeId', null);
		if (is_null($steptypeId))
		{
			$this->_error('No steptypeId passed');
			return;
		}

		$steptype = Sophie_Db_Steptype :: getInstance()->find($steptypeId)->current();

		if (is_null($steptype))
		{
			$this->_helper->flashMessenger('Selected steptype does not exist');

			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'sysadmin',
				'controller' => 'steptype',
				'action' => 'index'
			), 'default', true);
			return;
		}

		if ($steptype->isInstalled == 1)
		{
			$this->_helper->flashMessenger('Selected steptype is already installed');

			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'sysadmin',
				'controller' => 'steptype',
				'action' => 'index'
			), 'default', true);
			return;
		}
		else
		{
			$steptype->isInstalled = 1;
			$steptype->save();
			$this->_helper->flashMessenger('Steptype installed');

			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'sysadmin',
				'controller' => 'steptype',
				'action' => 'index'
			), 'default', true);
			return;
		}
	}

	public function activateAction()
	{
		$steptypeId = $this->_getParam('steptypeId', null);
		if (is_null($steptypeId))
		{
			$this->_error('No steptypeId passed');
			return;
		}

		$steptype = Sophie_Db_Steptype :: getInstance()->find($steptypeId)->current();

		if (is_null($steptype))
		{
			$this->_error('Selected steptype does not exist');
			return;
		}

		$steptype->isActive = 1;
		$steptype->save();

		$this->_helper->flashMessenger('Steptype activated');

		$this->_helper->getHelper('Redirector')->gotoRoute(array (
			'module' => 'sysadmin',
			'controller' => 'steptype',
			'action' => 'index'
		), 'default', true);
		return;
	}

	public function deactivateAction()
	{
		$steptypeId = $this->_getParam('steptypeId', null);
		if (is_null($steptypeId))
		{
			$this->_error('No steptypeId passed');
			return;
		}

		$steptype = Sophie_Db_Steptype :: getInstance()->find($steptypeId)->current();

		if (is_null($steptype))
		{
			$this->_error('Selected steptype does not exist');
			return;
		}

		$steptype->isActive = 0;
		$steptype->save();

		$this->_helper->flashMessenger('Steptype deactivated');

		$this->_helper->getHelper('Redirector')->gotoRoute(array (
			'module' => 'sysadmin',
			'controller' => 'steptype',
			'action' => 'index'
		), 'default', true);
		return;
	}

	public function refreshAction()
	{
		$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
		$basepaths = $steptypeFactory->getBasepaths();
		$defaultBasepath = $steptypeFactory->getDefaultBasepath();
		$this->view->basepaths = $basepaths;
		$this->view->defaultBasepath = $defaultBasepath;		
	
		$this->view->breadcrumbs[] = array(
			'title' => 'Refresh Steptypes',
			'small' => 'Steptypes:',
			'name' => 'Refresh'
		);

		$form = $this->getForm('Steptype_Refresh');
		$form->setAction($this->view->url());

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$results = array ();
				$steptypeDbModel = Sophie_Db_Steptype :: getInstance();

				// cleanup by purging all data from the steptype table first
				if ($values['procedure'] === 'purgeAndReload')
				{
					$results[] = array (
						'type' => 'message',
						'message' => 'Purged all steptypes from the database.'
					);
					$steptypeDbModel->delete('1');
				}

				// scan steptype basepath for packages
				$steptypesHandledByScan = array ();

				$basepaths = $steptypeFactory->getBasepaths();
				$basepaths[] = $steptypeFactory->getDefaultBasepath();

				foreach ($basepaths as $steptypePackagesBasePath)
				{
					if (!file_exists($steptypePackagesBasePath) || !is_dir($steptypePackagesBasePath))
					{
					  continue;
					}
					$steptypePackagesBasePathEntries = scandir($steptypePackagesBasePath);
					foreach ($steptypePackagesBasePathEntries as $steptypePackagesBasePathEntry)
					{
						$steptypePackagePath = $steptypePackagesBasePath . DIRECTORY_SEPARATOR . $steptypePackagesBasePathEntry;

						// do not read system directories and special dirs like .svn
						if (substr($steptypePackagesBasePathEntry, 0, 1) == '.' || !is_dir($steptypePackagePath))
						{
							continue;
						}

						// set the package name according to the directory name
						$steptypePackageSystemName = $steptypePackagesBasePathEntry;

						if (isset($steptypesHandledByScan[$steptypePackageSystemName]))
						{
							$results[] = array (
								'type' => 'message',
								'message' => 'Steptype ' . $steptypePackageSystemName . ' already installed from a higher priority basepath'
							);
							continue;
						}
						
						// read package information from package.xml
						$steptypePackageInfoFilePath = $steptypePackagePath . DIRECTORY_SEPARATOR . 'package.xml';
						if (!file_exists($steptypePackageInfoFilePath))
						{
							continue;
						}

						try
						{
							$steptypePackageInfoFileXml = new Zend_Config_Xml($steptypePackageInfoFilePath);
							$steptypePackageInfo = $steptypePackageInfoFileXml->toArray();
						}
						catch (Exception $e)
						{
							continue;
						}

						// try to fetch steptype information from database
						$steptypeDbRow = $steptypeDbModel->fetchRow($steptypeDbModel->getAdapter()->quoteInto('systemName = ?', $steptypePackageSystemName));

						// insert new steptype info if non exists
						if (is_null($steptypeDbRow))
						{
							$steptypeData = array ();
							$steptypeData['systemName'] = $steptypePackageSystemName;
							$steptypeData['name'] = $steptypePackageInfo['name'];
							$steptypeData['version'] = $steptypePackageInfo['version'];
							$steptypeData['description'] = $steptypePackageInfo['description'];
							$steptypeData['category'] = $steptypePackageInfo['category'];
							$steptypeData['author'] = $steptypePackageInfo['author'];
							$steptypeData['authorEmail'] = $steptypePackageInfo['authorEmail'];
							$steptypeData['website'] = $steptypePackageInfo['website'];
							//$steptypeData['dependencies'] = $steptypePackageInfo['dependencies'];
							$steptypeData['isAbstract'] = $steptypePackageInfo['isAbstract'];
							$steptypeData['isInstalled'] = 1;
							$steptypeData['isActive'] = 1;
							$steptypeData['isBroken'] = 0;

							$steptypeDbModel->insert($steptypeData);

							$results[] = array (
								'type' => 'message',
								'message' => 'Added Steptype ' . $steptypePackageSystemName
							);

							$steptypesHandledByScan[$steptypePackageSystemName] = 'added';
						}

						// update existing steptype info
						else
						{
							$results[] = array (
								'type' => 'message',
								'message' => 'Steptype ' . $steptypePackageSystemName . ' already exists. Doing nothing here.'
							);

							$steptypesHandledByScan[$steptypePackageSystemName] = 'noChange';
						}
					}
				}
					
				// handle steptypes with non existing packages
				$results[] = array (
					'type' => 'headline',
					'message' => 'Handling steptypes defined but without packages found'
				);

				$dbSteptypes = $steptypeDbModel->fetchAll();
				foreach ($dbSteptypes as $dbSteptype)
				{
					if (!array_key_exists($dbSteptype->systemName, $steptypesHandledByScan))
					{

						if ($values['procedure'] === 'updateAndPurge')
						{
							$results[] = array (
								'type' => 'message',
								'message' => 'Package for Steptype ' . $dbSteptype->systemName . ' not found. Deleted entry.'
							);
							$dbSteptype->delete();
						}
						else
						{
							$dbSteptype->isBroken = 1;
							$dbSteptype->isActive = 0;
							$dbSteptype->save();
						}
					}
				}

				// check steptype dependencies
				/*$results[] = array (
					'type' => 'headline',
					'message' => 'Check steptype dependencies'
				);

				$dbSteptypes = $steptypeDbModel->fetchAll();
				foreach ($dbSteptypes as $dbSteptype)
				{
					// ???
				}*/

				$this->view->results = $results;
			}
		}

		$this->view->form = $form;
	}
}