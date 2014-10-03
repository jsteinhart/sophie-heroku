<?php
class Sfwsysadmin_logController extends Symbic_Controller_Action
{
	public function fileAction()
	{
		if (!$this->getModule()->isAllowed('logFile'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$config = $this->getModule()->getComponentConfig('logFile');
		if (!isset($config['files']) || !is_array($config['files']) || sizeof($config['files']) === 0)
		{
			$this->_error('Only configured log files can be shown, but none has been configured.');
			return;
		}

		$logFiles = array();
		foreach ($config['files'] as $logFileId => $logFileData)
		{
			if (is_string($logFileData))
			{
				$logFileData = array(
					'path' => $logFileData
				);
			}

			$logFileData['id'] = $logFileId;

			if (!is_array($logFileData) || empty($logFileData['path']))
			{
				$this->_error('Invalid log file definition ' . print_r($logFileData, true));
				return;		
			}
			
			$logFilePath = BASE_PATH . '/' . $logFileData['path'];
			if (file_exists($logFilePath))
			{
				$logFileData['size'] = filesize($logFileData['path']);
			}
			else
			{
				$logFileData['size'] = 0;
			}
			
			if (empty($logFileData['name']))
			{
				$logFileData['name'] = basename($logFileData['path']);
			}

			$logFiles[] = $logFileData;
		}
		
		$this->view->logFiles = $logFiles;
		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'title' => 'System Logs',
					'small' => 'System Logs:',
					'name' => 'Log Files'
				)
			);
	}

	public function filebrowseAction()
	{
		if (!$this->getModule()->isAllowed('logFile'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$config = $this->getModule()->getComponentConfig('logFile');
		if (!isset($config['files']) || !is_array($config['files']) || sizeof($config['files']) === 0)
		{
			$this->_error('Only configured log files can be shown, but none has been configured.');
			return;
		}
		
		$fileId = $this->_getParam('fileId', null);
		
		if (is_null($fileId))
		{
			$this->_error('fileId parameter is missing');
			return;
		}
		
		if(!isset($config['files'][$fileId]))
		{
			$this->_error('Only configured log files can be shown, but selected fileId does not exist.');
			return;
		}
		
		if (is_string($config['files'][$fileId]))
		{
			$logFileData = array(
				'path' => $config['files'][$fileId]
			);
		}
		else
		{
			if (!is_array($config['files'][$fileId]) || empty($config['files'][$fileId]['path']))
			{
				$this->_error('Invalid log file definition');
				return;			
			}
			$logFileData = $config['files'][$fileId];
		}
		
		if (empty($logFileData['name']))
		{
			$logFileData['name'] = basename($logFileData['path']);
		}
		
		$logFilePath = BASE_PATH . '/' . $logFileData['path'];
		if (!file_exists($logFilePath))
		{
			$this->error = 'The selected log file does not exist';
			return;
		}

		$offset = 0;
		$limit = 100;

		$file = new Symbic_File_LineIterator($logFileData['path']);
		$file->seek($offset);
		
		$i = 0;
		$logFileLines = array();
		
		foreach ($file as $line)
		{
			$logFileLines[] = $line;
			if ($i == $limit)
			{
				break;
			}
			$i++;
		}

		$this->view->logFileId = $fileId;
		$this->view->logFileData = $logFileData;
		$this->view->logFileLines = $logFileLines;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'url' => $this->view->url(array('action' => 'file')),
					'title' => 'System Logs',
					'small' => 'System Logs:',
					'name' => 'Browse Log Files'
				)
			);
	}
	
	public function filetailAction()
	{
		if (!$this->getModule()->isAllowed('logFile'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$config = $this->getModule()->getComponentConfig('logFile');
		if (!isset($config['files']) || !is_array($config['files']) || sizeof($config['files']) === 0)
		{
			$this->_error('Only configured log files can be shown, but none has been configured.');
			return;
		}
		
		$fileId = $this->_getParam('fileId', null);
		
		if (is_null($fileId))
		{
			$this->_error('fileId parameter is missing');
			return;
		}
		
		if(!isset($config['files'][$fileId]))
		{
			$this->_error('Only configured log files can be shown, but selected fileId does not exist.');
			return;
		}
		
		if (is_string($config['files'][$fileId]))
		{
			$logFileData = array(
				'path' => $config['files'][$fileId]
			);
		}
		else
		{
			if (!is_array($config['files'][$fileId]) || empty($config['files'][$fileId]['path']))
			{
				$this->_error('Invalid log file definition');
				return;			
			}
			$logFileData = $config['files'][$fileId];
		}
		
		if (empty($logFileData['name']))
		{
			$logFileData['name'] = basename($logFileData['path']);
		}
		
		$logFilePath = BASE_PATH . '/' . $logFileData['path'];
		if (!file_exists($logFilePath))
		{
			$logFileLines = array();
			$logFileData['size'] = 0;
			$this->error = 'The selected log file does not exist';
			return;
		}
		else
		{
			$logFileData['size'] = filesize($logFileData['path']);
			$tail = new Symbic_File_Tail();
			$logFileLines = $tail->getLines($logFileData['path'], 100);
		}

		$this->view->logFileId = $fileId;
		$this->view->logFileData = $logFileData;
		$this->view->logFileLines = $logFileLines;

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'url' => $this->view->url(array('action' => 'file')),
					'title' => 'System Logs',
					'small' => 'System Logs:',
					'name' => 'Tail Log Files'
				)
			);
	}
	
	public function filetruncateAction()
	{
		if (!$this->getModule()->isAllowed('logFile'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$config = $this->getModule()->getComponentConfig('logFile');
		if (!isset($config['files']) || !is_array($config['files']) || sizeof($config['files']) === 0)
		{
			$this->_error('Only configured log files can be shown, but none has been configured.');
			return;
		}
		
		$fileId = $this->_getParam('fileId', null);
		
		if (is_null($fileId))
		{
			$this->_error('fileId parameter is missing');
			return;
		}
		
		if(!isset($config['files'][$fileId]))
		{
			$this->_error('Only configured log files can be shown, but selected fileId does not exist.');
			return;
		}
		
		if (is_string($config['files'][$fileId]))
		{
			$logFileData = array(
				'path' => $config['files'][$fileId]
			);
		}
		else
		{
			if (!is_array($config['files'][$fileId]) || empty($config['files'][$fileId]['path']))
			{
				$this->_error('Invalid log file definition');
				return;			
			}
			$logFileData = $config['files'][$fileId];
		}
		
		if (empty($logFileData['name']))
		{
			$logFileData['name'] = basename($logFileData['path']);
		}
		
		$logFilePath = BASE_PATH . '/' . $logFileData['path'];
		if (!file_exists($logFilePath))
		{
			$this->_error('The selected log file does not exist');
			return;
		}

		$fp = fopen($logFilePath, 'w');
		fclose($fp);
		
		$this->_helper->json(array('message' => 'Emptied log file'));
	}

	public function dbAction()
	{
		if (!$this->getModule()->isAllowed('logDb'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		$logDbModel = $this->getModelSingleton('Log_Db');

		// TODO: implement an application log model
		// TODO: implement a datatable to show the log entries
		// TODO: implement a filter and search functionality

		$this->view->breadcrumbs = array(
				array(
					'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
					'title' => 'Administration',
					'small' => 'Home:',
					'name' => 'Administration'
				),
				array(
					'url' => $this->view->url(),
					'title' => 'System Logs',
					'small' => 'System Logs:',
					'name' => 'Log'
				)
			);
	}
}