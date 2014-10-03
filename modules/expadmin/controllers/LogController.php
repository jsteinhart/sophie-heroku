<?php
class Expadmin_LogController extends Symbic_Controller_Action
{
	public function init()
	{
	}

	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0)
		{
			$this->_error('Missing parameter sessionId');
			return;
		}

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $this->session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}
	}

	public function indexAction()
	{
		$db = Zend_Registry::get('db');
		$logModel = Sophie_Db_Session_Log :: getInstance();

		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		if (is_null($treatment))
		{
			$this->_error('Selected treatment does not exist!');
			return;
		}

		$limit = (int)$this->_getParam('limit', 25);
		$page = (int)$this->_getParam('page', 1);
		$order = $this->_getParam('order', 'DESC');
		if (!in_array($order, array('ASC', 'DESC')))
		{
			$order = 'DESC';
		}

		$filterTypes = $this->_getParam('filterTypes', null);
		
		if (!is_array($filterTypes))
		{
			$filterTypes = array('error', 'warning', 'notice');
		}

		$filterType = ' AND type IN (' . $db->quote($filterTypes) . ')';
		
		$count = $db->fetchOne('SELECT count(*) FROM sophie_session_log WHERE sessionId = ' . $this->session->id . $filterType);

		if ($page < 1)
		{
			$page = 1;
		}
		$maxPage = ceil($count / $limit);
		if ($page > 1 && $page > $maxPage)
		{
			$page = $maxPage;
		}
		$offset = $limit * ($page - 1);

		$logs = $logModel->fetchAll('sessionId = ' . $this->session->id . $filterType, 'microtime ' . $order, $limit, $offset);

		$this->view->filterTypes = $filterTypes;
		$this->view->order = $order;
		$this->view->offset = $offset;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->count = $count;
		$this->view->logs = $logs;

		$this->view->treatment = $treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function clearAction()
	{
		Sophie_Db_Session_Log::getInstance()->delete('sessionId = ' . $this->session->id);

		$data  = array ('sessionId' => $this->session->id,
					    'microtime' => microtime(true),
					    'content'   => 'Log cleared',
						'type'      => 'notice');

		Sophie_Db_Session_Log::getInstance()->insert($data);

		$this->_helper->json(array('message'=>'Logs cleared'));
	}
}
