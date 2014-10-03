<?php
class Sfwsysadmin_TaskController extends Symbic_Controller_Action
{
	private $moduleConfig;
	private $module;

	private $tasksConfig;

	public function init()
	{
		$this->moduleConfig = $this->getModuleConfig();
		$this->module = $this->getModule();
		if (!$this->module->isAllowed('task'))
		{
			$this->_error('Access to this component is disallowed');
			return;
		}

		// load tasks config
		$this->tasksConfig = array();
		$configDir = APPLICATION_PATH . '/configs/';
		if (file_exists($configDir))
		{

			$configFile = $configDir . DIRECTORY_SEPARATOR . 'tasks.default.php';
			if (file_exists($configFile))
			{
				$this->tasksConfig = array_replace_recursive($this->tasksConfig, include($configFile));
			}

			$configFile = $configDir . DIRECTORY_SEPARATOR . 'tasks.php';
			if (file_exists($configFile))
			{
				$this->tasksConfig = array_replace_recursive($this->tasksConfig, include($configFile));
			}
		}

		$this->view->breadcrumbs = array(
			array(
				'url' => $this->view->url(array('controller' => 'index', 'action' => 'index')),
				'title' => 'Administration',
				'small' => 'Home:',
				'name' => 'Administration'
			),
			array(
				'url' => $this->view->url(array('controller' => 'task', 'action' => 'index')),
				'title' => 'Tasks',
				'small' => 'Tasks:',
				'name' => 'Overview'
			)
		);

	}

	/**
	 * Get all tasks from the db and return it to the view
	 */
	public function indexAction()
	{
		$manager = new Symbic_Task_Manager($this->tasksConfig);

		$this->view->tasks = $manager->getTasks();
		/*
		$taskModel = $this->getModelSingleton('Task');
		$result = $taskModel->fetchAll()->toArray();
		$this->view->result = $result;
		*/
	}

	public function queueAction()
	{
		$task = $this->_getParam('task', null);
		if (is_null($task))
		{
			$this->_error('Missing parameter');
			return;
		}

		$manager = new Symbic_Task_Manager($this->tasksConfig);
		$manager->queueManually($task);

		$this->_helper->flashMessenger($this->view->T('Task queued'));

		$this->_helper->getHelper('Redirector')->gotoRoute(array (
			'module' => 'sfwsysadmin',
			'controller' => 'task',
			'action' => 'index'
		), 'default', true);
		return;
	}

	public function historyAction()
	{
		$task = $this->_getParam('task', null);
		if (is_null($task))
		{
			$this->_error('Missing parameter');
			return;
		}
		$manager = new Symbic_Task_Manager($this->tasksConfig);
		$history = $manager->getJobHistory($task);
		$this->view->history = $history['history'];
		$this->view->historyLength = $history['length'];

		if (!empty($history['history']))
		{
			$firstTask = reset($history['history']);
			$this->view->breadcrumbs[] = array(
				'title' => 'Task',
				'small' => 'Task:',
				'name' => $firstTask['name']
			);
		}
	}

	// TODO: runAction
	// TODO: addAction
	// TODO: editAction
	// TODO: deleteAction
	// TODO: activateAction
	// TODO: deactivateAction
	// TODO: detailsAction
}