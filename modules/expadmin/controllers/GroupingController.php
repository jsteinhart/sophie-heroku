<?php
class Expadmin_GroupingController extends Symbic_Controller_Action
{
	public function init()
	{
	}

	public function preDispatch()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0) {
			$this->_error('Missing parameter sessionId');
			return;
		}

		$this->session = Sophie_Db_Session::getInstance()->find($sessionId)->current();
		if (is_null($this->session)) {
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl::getInstance();
		if (!$acl->autoCheckAcl('session', $this->session->id, 'sophie_session')) {
			$this->_error('Access denied.');
			return;
		}

		$popup = $this->_getParam('popup', false);
		if ($popup) {
			$this->_helper->layout->setLayout('popup');
		}
	}

	public function indexAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$groups = $this->session->findDependentRowset('Sophie_Db_Session_Group');
		$participants = $this->session->findDependentRowset('Sophie_Db_Session_Participant');
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');

		$groupings = $this->session->findDependentRowset('Sophie_Db_Session_Participant_Group');
		$groupingsByStepgroup = array();
		foreach ($groupings as $grouping)
		{
			if (!isset($groupingsByStepgroup[$grouping['stepgroupLabel']]))
			{
				$groupingsByStepgroup[$grouping['stepgroupLabel']] = array();
			}
			if (!isset($groupingsByStepgroup[$grouping['stepgroupLabel']][$grouping['stepgroupLoop']]))
			{
				$groupingsByStepgroup[$grouping['stepgroupLabel']][$grouping['stepgroupLoop']] = array();
			}
			if (!isset($groupingsByStepgroup[$grouping['stepgroupLabel']][$grouping['stepgroupLoop']][$grouping['groupLabel']]))
			{
				$groupingsByStepgroup[$grouping['stepgroupLabel']][$grouping['stepgroupLoop']][$grouping['groupLabel']] = array();
			}

			$groupingsByStepgroup[$grouping['stepgroupLabel']][$grouping['stepgroupLoop']][$grouping['groupLabel']][] = $grouping['participantLabel'];
		}

		$this->view->session = $this->session->toArray();
		$this->view->stepgroups = $stepgroups->toArray();
		$this->view->groups = $groups->toArray();
		$this->view->participants = $participants->toArray();
		$this->view->groupingsByStepgroup = $groupingsByStepgroup;
		$this->_helper->layout->disableLayout();
	}
}