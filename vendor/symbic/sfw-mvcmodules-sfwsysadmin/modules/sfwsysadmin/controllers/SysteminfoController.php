<?php
class Sfwsysadmin_SysteminfoController extends Symbic_Controller_Action
{
	public function phpinfoAction()
	{
		if (!$this->getModule()->isAllowed('systeminfoPhpinfo'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'PHP Info',
					'small' => 'Powered by PHP:',
					'name' => 'PHP Info'
				)
			);
	}

	public function phpinfo2Action()
	{
		if (!$this->getModule()->isAllowed('systeminfoPhpinfo'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$this->_helper->layout->disableLayout();
	}

	public function drivesAction()
	{
		if (!$this->getModule()->isAllowed('systeminfoDrives'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$drives = array();
		if(php_uname('s') == 'Windows NT')
		{
			for ($i = 0; $i <= 25; $i++)
			{
				$drive = chr(ord('A') + $i) . ':\\';
				if (realpath($drive) !== false)
				{
					$drives[] = $token;
				}
			}
		}
		else
		{
			$data=`mount`;
			$data=explode(' ',$data);
			foreach($data as $token)
			{
				if(substr($token,0,5) == '/dev/')
				{
					$drives[] = $token;
				}
			}
		}
		
		$details = array();
		foreach ($drives as $drive)
		{
			$details[$drive] = array(
									'total' => disk_total_space($drive),
									'free' => disk_free_space($drive)
								);
		}

		$this->view->details = $details;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'Drives',
					'small' => 'Drives:',
					'name' => 'Drives'
				)
			);
	}

	public function mysqlAction()
	{
		if (!$this->getModule()->isAllowed('systeminfoMysql'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$db = Zend_Registry::get('db');

		$this->status = $db->fetchAll('SHOW STATUS');		
		$this->variables = $db->fetchAll('SHOW VARIABLES');
		$this->innodbStatus = $db->fetchAll('SHOW ENGINE INNODB STATUS');

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'MySQL',
					'small' => 'MySQL:',
					'name' => 'MySQL'
				)
			);
	}

	public function limitsAction()
	{
		if (!$this->getModule()->isAllowed('systeminfoLimits'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		try {
			$limits = system('ulimit -a');
		}
		catch (Exception $e)
		{
			$this->_error('Executing system command to show limits failed. Alternativly use "ulimit -a" on a shell.');
		}
		$this->view->limits = $limits;
	}
}