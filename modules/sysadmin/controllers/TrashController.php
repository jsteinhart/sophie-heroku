<?php
class Sysadmin_TrashController extends Symbic_Controller_Action
{
	public function preDispatch()
	{
		$adminRight = Symbic_User_Session::getInstance()->hasRight('admin');
		if (!$adminRight)
		{
			$this->_error('No right to access this page');
			return;
		}

		$this->view->breadcrumbs = array(
			'home' => 'sfwsysadmin',
			array(
				'url' => $this->view->url(array('module' => 'sysadmin', 'controller' => 'trash', 'action' => ''), 'default', true),
				'title' => 'Trash',
				'small' => 'Trash:',
				'name' => 'Overview'
			)
		);
	}

	public function indexAction()
	{
		$experimentTable = Sophie_Db_Experiment::getInstance();
		$treatmentTable = Sophie_Db_Treatment::getInstance();
		$sessionTable = Sophie_Db_Session::getInstance();
		
		$this->view->deletedExperiments = $experimentTable->fetchAll('state = "deleted"')->toArray();
		$this->view->deletedTreatments = $treatmentTable->fetchAll('state = "deleted"')->toArray();
		$this->view->deletedSessions = $sessionTable->fetchAll('state = "deleted"')->toArray();		
	}

	public function restoreAction()
	{
		$id = $this->_getParam('id', null);
		if (is_null($id))
		{
			$this->_error('Missing id parameter');
			return;
		}

		$objectType = $this->_getParam('objectType', null);
		if (is_null($objectType))
		{
			$this->_error('Missing objectType parameter');
			return;
		}
		
		switch ($objectType)
		{
			case 'experiment':
				$objectTable = Sophie_Db_Experiment::getInstance();
				break;
			case 'treatment':
				$objectTable = Sophie_Db_Treatment::getInstance();
				break;
			case 'session':
				$objectTable = Sophie_Db_Session::getInstance();
				break;
			default:
				$this->_error('Unsupported objectType');
				return;
		}

		// new state by object type
		switch ($objectType)
		{
			case 'experiment':
				$newObjectState = 'active';
				break;
			case 'treatment':
				$newObjectState = 'used';
				break;
			case 'session':
				$newObjectState = 'finished';
				break;
		}

		
		// fetch object
		if ($id == 'all')
		{
			$objectTable->update(array('state' => $newObjectState), 'state = "deleted"');
		}
		else
		{
			$object = $objectTable->find($id)->current();
			if (is_null($object))
			{
				$this->_error('Object does not exist');
				return;
			}
			
			// check object state is "deleted"
			if ($object->state != 'deleted')
			{
				$this->_error('Object has not been deleted');
				return;		
			}
		
			$object->state = $newObjectState;
			$object->save();
		}
		
		$this->_helper->json(array('message' => ucfirst($objectType) . ' has been restored'));	
	}

	public function eraseAction()
	{
		$id = $this->_getParam('id', null);
		if (is_null($id))
		{
			$this->_error('Missing id parameter');
			return;
		}

		$objectType = $this->_getParam('objectType', null);
		if (is_null($objectType))
		{
			$this->_error('Missing objectType parameter');
			return;
		}
		
		switch ($objectType)
		{
			case 'experiment':
				$objectTable = Sophie_Db_Experiment::getInstance();
				break;
			case 'treatment':
				$objectTable = Sophie_Db_Treatment::getInstance();
				break;
			case 'session':
				$objectTable = Sophie_Db_Session::getInstance();
				break;
			default:
				$this->_error('Unsupported objectType');
				return;
		}
		
		// fetch object
		if ($id == 'all')
		{
			$objectTable->delete('state = "deleted"');
		}
		else
		{
			$object = $objectTable->find($id)->current();
			if (is_null($object))
			{
				$this->_error('Object does not exist');
				return;
			}
			
			// check object state is "deleted"
			if ($object->state != 'deleted')
			{
				$this->_error('Object has not been deleted');
				return;		
			}
			
			// really delete object
			$object->delete();
		}
			
		$this->_helper->json(array('message' => ucfirst($objectType) . ' permanently erased'));
	}
	
}