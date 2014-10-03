<?php
class Expadmin_DebugController extends Symbic_Controller_Action
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
		$participantSelect = Sophie_Db_Session_Participant :: getInstance()->select();
		$participantSelect->where('sessionId = ?', $this->session->id);
		$participantSelect->order('number');
		$participants = Sophie_Db_Session_Participant :: getInstance()->fetchAll($participantSelect);

		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$typeSelect = Sophie_Db_Treatment_Type :: getInstance()->select();
		$typeSelect->where('treatmentId = ?', $treatment->id);
		//$typeSelect->order('label');
		$types = Sophie_Db_Treatment_Type :: getInstance()->fetchAll($typeSelect);

		$this->view->types = $types->toArray();
		$this->view->participants = $participants->toArray();
		$this->view->session = $this->session->toArray();

		// METHOD subdomain: ${code}-${number}.hostname/url
		// METHOD domainPrefix: ${code}-${number}-hostname/url/
		// TODO: use codePlacement from config or default to subdomain;
		$this->view->codePlacement = 'subdomain';

		// TODO: use serverHost from config or default to $_SERVER['HTTP_HOST'];
		$this->view->serverHost = $_SERVER['HTTP_HOST'];

		$this->view->url = $this->view->url(array('module' => 'expfront', 'controller' => 'login', 'action' => 'index'));
		if ($this->view->codePlacement == 'domainPrefix' && array_key_exists('HTTPS', $_SERVER))
		{
			$this->view->protocol = 'https';
		}
		else
		{
			$this->view->protocol = 'http';
		}

		$this->_helper->layout->disableLayout();
	}

	public function consoleAction()
	{
		$active = $this->_getParam('active', 0);
		if (!in_array($active, array(0, 1)))
		{
			$active = 0;
		}
		$this->session->debugConsole = $active;
		$this->session->save();

		if ($active == 0)
		{
			$this->_helper->json(array('message'=>'Debug Console deactivated'));
		}
		else
		{
			$this->_helper->json(array('message'=>'Debug Console activated'));
		}
	}

	public function deletesyncAction()
	{
		$variableDb = Sophie_Db_Session_Variable::getInstance();
		$variableDb->delete('sessionId = ' . $this->session->id . ' AND name LIKE "__stepsync_%"');

		$this->_helper->json(array('message'=>'Deleted all Sync Variables'));
	}

	public function treatmentcacheclearAction()
	{
		$cache = Zend_Registry :: get('cache');
		$cachePrefix = 'sophie_sessionCache_' . $this->session->id . '_';

		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$cacheKey = $cachePrefix . 'treatment_' . $treatment->id;
		$cache->remove($cacheKey);

		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		foreach ($stepgroups as $stepgroup)
		{
			$cacheKey = $cachePrefix . 'treatmentStepgroup_' . $stepgroup->id;
			$cache->remove($cacheKey);

			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step');
			foreach ($steps as $step)
			{
				$cacheKey = $cachePrefix . 'treatmentStep_' . $step->id;
				$cache->remove($cacheKey);
				$cacheKey = $cachePrefix . 'treatmentStepAttributes_' . $step->id;
				$cache->remove($cacheKey);
			}
		}
		
		$this->_helper->json(array('message'=>'Treatment Cache cleared'));
	}

	public function treatmentcacheprefillAction()
	{
		$cache = Zend_Registry::get('cache');
		$db = Zend_Registry::get('db');

		$cachePrefix = 'sophie_sessionCache_' . $this->session->id . '_';

		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$cacheKey = $cachePrefix . 'treatment_' . $treatment->id;
		$treatmentData = $treatment->toArray();
		$cache->save($treatmentData, $cacheKey);

		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		foreach ($stepgroups as $stepgroup)
		{
			$cacheKey = $cachePrefix . 'treatmentStepgroup_' . $stepgroup->id;
			$stepgroupData = $stepgroup->toArray();
			$cache->save($stepgroupData, $cacheKey);

			$steps = $stepgroup->findDependentRowset('Sophie_Db_Treatment_Step');
			foreach ($steps as $step)
			{
				$cacheKey = $cachePrefix . 'treatmentStep_' . $step->id;
				$stepData = $step->toArray();
				$cache->save($stepData, $cacheKey);

				$cacheKey = $cachePrefix . 'treatmentStepAttributes_' . $step->id;
				$stepAttributes = (array)$db->fetchPairs('SELECT name, value FROM sophie_treatment_step_eav WHERE stepID = ' . $db->quote($step->id));
				$cache->save($stepAttributes, $cacheKey);
			}
		}
		
		$this->_helper->json(array('message'=>'Treatment Cache prefilled'));
	}
}