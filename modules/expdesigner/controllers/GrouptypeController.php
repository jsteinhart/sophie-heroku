<?php
class Expdesigner_GrouptypeController extends Symbic_Controller_Action
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
			$this->_error('Missing treatmentId parameter');
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
		
		$groupId = $this->_getParam('groupId', 0);
		if ($groupId == 0)
		{
			$this->_error('Missing groupId parameter');
			return;
		}

		$this->group = Sophie_Db_Treatment_Group::getInstance()->find($groupId)->current();
		if (is_null($this->group) || $this->group->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected group does not exist or does not belong to selected treatment!');
			return;
		}
	}

	public function addAction()
	{

		$types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type');
		if (sizeof($types)==0)
		{
			$this->view->error = 'no type available to add grouptypes';
		}
		else
		{
			$id = Sophie_Db_Treatment_Group_Type::getInstance()->insert(
								array(
									'groupId'=>$this->group->id,
									'typeId'=>$types->current()->id,
									'participants' => 1
									));
  			$this->view->message = 'created grouptype ' . $id;
		}

 		$this->_helper->json($this->view->getVars());
	}

	public function editAction()
	{

		$grouptypeId = $this->_getParam('grouptypeId', 0);
		if ($grouptypeId == 0)
		{
			$this->_error('Missing grouptypeId parameter');
			return;
		}

		$this->grouptype = Sophie_Db_Treatment_Group_Type::getInstance()->find($grouptypeId)->current();

		if (is_null($this->grouptype) || $this->grouptype->groupId != $this->group->id)
		{
			$this->_error('Selected grouptype does not exist or does not belong to selected group!');
			return;
		}

		$typeSelect = array();
		$types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type');
		foreach ($types as $type)
		{
			$typeSelect[$type->id] = $type->name;
		}
		$this->view->typeSelect = $typeSelect;
		$this->view->treatment = $this->treatment;
		$this->view->group = $this->group->toArray();
		$this->view->grouptype = $this->grouptype->toArray();

		$this->_helper->layout->setLayout('layoutWithoutNav');

	}

	public function ajaxupdateAction()
	{

		$grouptypeId = $this->_getParam('grouptypeId', 0);
		if ($grouptypeId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->grouptype = Sophie_Db_Treatment_Group_Type::getInstance()->find($grouptypeId)->current();
		if (is_null($this->grouptype) || $this->grouptype->groupId != $this->group->id)
		{
			$this->_error('Selected grouptype does not exist or does not belong to selected group!');
			return;
		}

		$type = Sophie_Db_Treatment_Type::getInstance()->find($this->_getParam('FORM_typeid', ''))->current();
		if (is_null($type) || $type->treatmentId != $this->treatment->id)
		{
			$this->_error('Selected type does not exist or does not belong to selected treatment!');
			return;
		}

		$this->grouptype->participants = $this->_getParam('FORM_participants', '');
		$this->grouptype->typeId = $this->_getParam('FORM_typeid', '');
		$this->grouptype->save();

		$this->_helper->json(array('message'=>'updated'));
	}

	public function deleteAction()
	{
		$grouptypeId = $this->_getParam('grouptypeId', 0);
		if ($grouptypeId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->grouptype = Sophie_Db_Treatment_Group_Type::getInstance()->find($grouptypeId)->current();

		if (is_null($this->grouptype) || $this->grouptype->groupId != $this->group->id)
		{
			$this->_error('Selected grouptype does not exist or does not belong to selected group!');
			return;
		}

		$this->grouptype->delete();

		$this->view->message = 'deleted grouptype';
 		$this->_helper->json($this->view->getVars());
	}
}