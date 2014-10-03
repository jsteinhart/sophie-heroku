<?php
class Expdesigner_LogController extends Symbic_Controller_Action
{

	public function indexAction()
	{
		$db = Zend_Registry::get('db');
		$logModel = Sophie_Db_Treatment_Log :: getInstance();

		$treatmentId = $this->_getParam('treatmentId', null);
		if (is_null($treatmentId))
		{
			$this->_error('Missing parameter');
			return;
		}

		$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
		if (is_null($treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $treatment->experimentId, 'sophie_experiment'))
		{
			$this->_error('Access denied.');
			return;
		}

		$limit = (int)$this->_getParam('limit', 25);
		$page = (int)$this->_getParam('page', 1);
		$order = $this->_getParam('order', 'DESC');
		if (!in_array($order, array('ASC', 'DESC')))
		{
			$order = 'DESC';
		}

		$filterType = $this->_getParam('filterType', '');
		if (!in_array($filterType, array('error', 'warn', 'notice', 'event')))
		{
			$filterType = '';
		}
		else
		{
			$filterType = ' AND type = ' . $db->quote($filterType);
		}

		$count = $db->fetchOne('SELECT count(*) FROM sophie_treatment_log WHERE treatmentId = ' . $treatment->id);

		$this->view->count = $count;

		if ($count > 0)
		{

			if ($page < 1)
			{
				$page = 1;
			}
			if ($page > ceil($count / $limit))
			{
				$page = ceil($count / $limit);
			}
			$offset = $limit * ($page - 1);

			$logs = $logModel->fetchAll('treatmentId = ' . $treatment->id . $filterType, 'microtime ' . $order, $limit, $offset);

			$this->view->logs = $logs->toArray();
			$this->view->limit = $limit;
			$this->view->offset = $offset;
			$this->view->order = $order;
			$this->view->page = $page;
		}

		$this->view->treatment = $treatment->toArray();
		$this->_helper->layout->disableLayout();
	}

	public function clearAction()
	{
		if ($this->_hasParam('treatmentId'))
		{
			$treatmentId = $this->_getParam('treatmentId');
			$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
			if (is_null($treatment))
			{
				$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
				return;
			}

			Sophie_Db_Treatment_Log :: getInstance()->delete('treatmentId = ' . $treatment->id);

			$data  = array ('treatmentId' => $treatment->id,
							  'microtime' => microtime(true),
							  'content'   => 'Log cleared',
							  'type'      => 'note');

			Sophie_Db_Treatment_Log :: getInstance()->insert($data);

		}
		else
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->_helper->json(array (
			'message' => 'Logs cleared'
		));
	}

	public function disableAction()
	{
		if ($this->_hasParam('treatmentId'))
		{
			$treatmentId = $this->_getParam('treatmentId');
			$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
			if (is_null($treatment))
			{
				$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
				return;
			}

			if($treatment->loggingEnabled == 0)
			{
				$this->_helper->json(array (
					'message' => 'Logs already disabled'
			));
				return;
			}
			Sophie_Db_Treatment :: getInstance()->update(array('loggingEnabled' => 0), Sophie_Db_Treatment :: getInstance()->getAdapter()->quoteInto('id = ?', $treatmentId));

			$data  = array ('treatmentId' => $treatment->id,
							  'microtime' => microtime(true),
							  'content'   => 'Log disabled',
							  'type'      => 'note');

			Sophie_Db_Treatment_Log :: getInstance()->insert($data);

		}
		else
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->_helper->json(array (
			'message' => 'Logs disabled'
		));
	}

	public function enableAction()
	{
		if ($this->_hasParam('treatmentId'))
		{
			$treatmentId = $this->_getParam('treatmentId');
			$treatment = Sophie_Db_Treatment :: getInstance()->find($treatmentId)->current();
			if (is_null($treatment))
			{
				$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
				return;
			}

			if($treatment->loggingEnabled == 1)
			{
				$this->_helper->json(array (
					'message' => 'Logs already enabled'
			));
				return;
			}
			Sophie_Db_Treatment :: getInstance()->update(array('loggingEnabled' => 1), Sophie_Db_Treatment :: getInstance()->getAdapter()->quoteInto('id = ?', $treatmentId));

			$data  = array ('treatmentId' => $treatment->id,
							  'microtime' => microtime(true),
							  'content'   => 'Log enabled',
							  'type'      => 'note');

			Sophie_Db_Treatment_Log :: getInstance()->insert($data);

		}
		else
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->_helper->json(array (
			'message' => 'Logs enabled'
		));
	}
}