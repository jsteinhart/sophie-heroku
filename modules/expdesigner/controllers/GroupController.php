<?php
class Expdesigner_GroupController extends Symbic_Controller_Action
{

	public function init()
	{
		$this->session = new Zend_Session_Namespace('expdesigner');
	}

	public function preDispatch()
	{
		$treatmentId = $this->_getParam('treatmentId', 0);
		if ($treatmentId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->treatment = Sophie_Db_Treatment::getInstance()->find($treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist or does not belong to selected experiment!');
			return;
		}

		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->view->experiment = $this->experiment->toArray();

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied');
			return;
		}

	}

	public function addAction()
	{
		if ($this->_hasParam('groupName'))
		{
			$groupName = $this->_getParam('groupName');
		}
		else
		{
			$groupName = 'New group';
		}
		$id = Sophie_Db_Treatment_Group::getInstance()->insert(
								array(
									'treatmentId'=>$this->treatment['id'],
									'name' => $groupName
									));
  		$this->view->message = 'created group ' . $id;

 		$this->_helper->json($this->view->getVars());
	}

	public function editAction()
	{

		$groupId = $this->_getParam('groupId', 0);
		if ($groupId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->group = Sophie_Db_Treatment_Group::getInstance()->find($groupId)->current();

		if (is_null($this->group) || $this->group->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected group does not exist or does not belong to selected treatment!');
			return;
		}

		$this->view->treatment = $this->treatment;
		$this->view->group = $this->group->toArray();

		$this->_helper->layout->setLayout('layoutWithoutNav');

	}

	public function ajaxupdateAction()
	{

		$groupId = $this->_getParam('groupId', 0);
		if ($groupId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->group = Sophie_Db_Treatment_Group::getInstance()->find($groupId)->current();

		if (is_null($this->group) || $this->group->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected group does not exist or does not belong to selected treatment!');
			return;
		}

		$this->group->name = $this->_getParam('FORM_name', '');
		$this->group->number = $this->_getParam('FORM_number', 1);
		$this->group->active = $this->_getParam('FORM_active',1);
		$this->group->save();

		$this->_helper->json(array('message'=>'updated'));
	}

	public function deleteAction()
	{
		$groupId = $this->_getParam('groupId', 0);
		if ($groupId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->group = Sophie_Db_Treatment_Group::getInstance()->find($groupId)->current();
		if (is_null($this->group) || $this->group->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected group does not exist or does not belong to selected treatment!');
			return;
		}

		$this->group->delete();

		$this->view->message = 'deleted group';
 		$this->_helper->json($this->view->getVars());
	}

	public function ajaxstateupdateAction()
	{

		$groupId = $this->_getParam('groupId', 0);
		if ($groupId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->group = Sophie_Db_Treatment_Group::getInstance()->find($groupId)->current();

		if (is_null($this->group) || $this->group->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected group does not exist or does not belong to selected treatment!');
			return;
		}

		$this->group->active = $this->_getParam('active',1);
		$this->group->save();

		$this->_helper->json(array('message'=>'updated'));
	}
}