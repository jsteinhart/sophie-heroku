<?php
class Sfwsysadmin_DbversionController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		if (!$this->getModule()->isAllowed('dbversion'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		// get dbversion infos
		
		// TODO: get $dbVersionClass & $dbVersionClassPath from config
		$dbVersionClass = 'Application_Contrib_Updates';
		$dbVersionClassPath = BASE_PATH . '/contrib/Updates.php';
		
		// TODO: if not use autoloading for $dbVersionClass
		if (!class_exists($dbVersionClass))
		{
			if (!file_exists($dbVersionClassPath))
			{
				$this->_error('Db Version Class does not exist');
				return;
			}
			require_once($dbVersionClassPath);
		}

		$this->view->currentVersion = $dbVersion->getCurrentDbVersion();
		$this->view->latestVersion = $dbVersion->getLatestDbVersion();
		
		if (method_exists($dbVersion, 'getVersionHistory'))
		{
			$this->view->history = $dbVersion->getVersionHistory();
		}

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'Db Version',
					'small' => 'Db Version:',
					'name' => 'Overview'
				)
			);
	}
}