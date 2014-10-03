<?php
class Sfwsysadmin_InstallerController extends Symbic_Controller_Action
{
	protected $installersvnPath;
	protected $installerPath;

	public function init()
	{
		$this->installersvnPath = BASE_PATH . '/install-svn.php';
		$this->installerPath = BASE_PATH . '/install.php';
	}

	public function installsvnAction()
	{
		if (!$this->getModule()->isAllowed('installerInstallsvn'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		if (!file_exists($this->installersvnPath))
		{
			$this->_error('You are not running an SVN Version or no svn installer script available.');
			return;
		}

		$form = $this->getForm('Installer_Installsvn');
		$config = $this->getModule()->getComponentConfig('installerInstallsvn');

		if (!empty($config['username']))
		{
			$form->setDefaults(array('svnUsername' => $config['username']));
		}
		
		if (!empty($config['password']))
		{
			$form->setDefaults(array('svnPassword' => $config['password']));
		}
		
		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$this->view->run = true;
			}
		}
		$this->view->form = $form;

		$this->view->breadcrumbs = array(
			array(
				'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
				'title' => 'Administration',
				'small' => 'Home:',
				'name' => 'Administration'
			),
			array(
				'title' => 'Installer',
				'small' => 'Installer:',
				'name' => 'Install from SVN'
			)
		);
	}

	public function runinstallsvnAction()
	{
		if (!$this->getModule()->isAllowed('installerInstallsvn'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->_helper->layout->disableLayout();

		if (!file_exists($this->installersvnPath))
		{
			$this->_error('You are not running an SVN Version or no svn installer script available.');
			return;
		}

		$form = $this->getForm('Installer_Installsvn');

		if (!$form->isValid($this->getAllParams()))
		{
			$this->_error('Invalid request to runinstaller');
			return;
		}

		$values = $form->getValues();

		// php install-svn.php [svn username] [svn password] [operation] force
		$execCommand = 'php';
		$execCommand .= ' ' . escapeshellarg($this->installersvnPath);
		$execCommand .= ' ' . escapeshellarg($values['svnUsername']);
		$execCommand .= ' ' . escapeshellarg($values['svnPassword']);
		$execCommand .= ' ' . escapeshellarg($values['operation']);

		if ($values['force'] == 'force')
		{
			$execCommand .= ' force';
		}

		$this->view->message = 'Executing ' . $this->installersvnPath;
		
		//echo $execCommand;
		exec($execCommand, $output, $returnVal);
		$this->view->output = $output;
	}

	public function installAction()
	{
		if (!$this->getModule()->isAllowed('installerInstall'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		if (!file_exists($this->installerPath))
		{
			$this->_error('No installer script available.');
			return;
		}

		$form = $this->getForm('Installer_Install');
		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$this->view->run = true;
			}
		}
		$this->view->form = $form;

		$this->view->breadcrumbs = array(
			'home' => 'sfwsysadmin',
			array(
				'title' => 'Installer',
				'small' => 'Installer:',
				'name' => 'Execute Installer'
			)
		);
	}

	public function runinstallAction()
	{
		if (!$this->getModule()->isAllowed('installerInstall'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->_helper->layout->disableLayout();

		if (!file_exists($this->installerPath))
		{
			$this->_error('No installer script available.');
			return;
		}

		$form = $this->getForm('Installer_Install');

		if (!$form->isValid($this->getAllParams()))
		{
			$this->_error('Invalid request to runinstaller');
			return;
		}

		$values = $form->getValues();

		// php install.php [operation]
		ini_set('max_execution_time', 300);
		set_time_limit (300);

		$execCommand = 'php';
		$execCommand .= ' ' . escapeshellarg($this->installerPath);
		$execCommand .= ' ' . escapeshellarg($values['operation']);

		$this->view->message = 'Executing ' . $this->installerPath;
		
		//echo $execCommand;
		exec($execCommand, $output, $returnVal);
		$this->view->output = $output;
	}
}