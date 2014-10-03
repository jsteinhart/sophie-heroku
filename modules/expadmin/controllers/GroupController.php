<?php
class Expadmin_GroupController extends Symbic_Controller_Action
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

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session)) {
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session', $this->session->id, 'sophie_session')) {
			$this->_error('Access denied.');
			return;
		}

		$popup = $this->_getParam('popup', false);
		if ($popup) {
			$this->_helper->layout->setLayout('popup');
		}
	}

	public function listAction()
	{
		$treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->view->treatment = $treatment->toArray();
		$this->view->session = $this->session->toArray();
		$groupSelect = Sophie_Db_Session_Group::getInstance()->select();
		$groupSelect->where('sessionId = ?', $this->session->id);
		$groupSelect->order('number');
		$this->view->groups = $groupSelect->query()->fetchAll();
		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$form = $this->getForm('Group_Add');
		$form->setAction('javascript:expadmin.sessionGroupAdd()');

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues($_POST);
				$values['sessionId'] = $this->session->id;

				$sessionService = Sophie_Service_Session::getInstance();
				$sessionService->addGroups($values['sessionId'], $values['groupNumber']);

				Sophie_Db_Session_Log :: getInstance()->log($this->session->id, 'Added ' . $values['groupNumber'] . ' Group');
				$this->_helper->json(array(
					'message' => 'Added ' .$values['groupNumber'] . ' Groups'
				));
				return;
			}
			else
			{
				$this->_helper->json(array(
					'type' => 'error',
					'message' => 'Creating Groups failed '
				));
			}
		}

		$this->view->session = $this->session->toArray();
		$this->view->form = $form;
		$this->_helper->layout->disableLayout();
	}
}