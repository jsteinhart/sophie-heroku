<?php
class Expdesigner_ParameterController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $parameterName = null;

	private $experiment = null;
	private $treatment = null;
	private $parameter = null;

	public function preDispatch()
	{
		$this->treatmentId = $this->_getParam('treatmentId', null);
		if (empty($this->treatmentId))
		{
			$this->_error('Parameter treatmentId missing!');
			return;
		}

		$this->treatment = Sophie_Db_Treatment :: getInstance()->find($this->treatmentId)->current();
		if (is_null($this->treatment))
		{
			$this->_error('Selected treatment does not exist!');
			return;
		}
		$this->experiment = $this->treatment->findParentRow('Sophie_Db_Experiment');
		$this->experimentId = $this->experiment->id;

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('experiment',  $this->experiment->id, 'sophie_experiment'))
		{
			$this->_error('Access denied.');
			return;
		}
		
		$this->parameterName = $this->_getParam('parameterName', null);
		if (!empty($this->parameterName))
		{
			$this->parameter = Sophie_Db_Treatment_Parameter :: getInstance()->find($this->treatmentId, $this->parameterName)->current();
			if (is_null($this->parameter))
			{
				$this->_error('Selected parameter does not exist!');
				return;
			}
		}

		$this->view->breadcrumbs = array(
			'home' => 'expdesigner',
			'experiment' => array(
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array(
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentDataTab'
			)
		);
		if ($this->parameter) {
			$this->view->breadcrumbs['parameter'] = array(
				'treatmentId' => $this->treatment->id,
				'name' => $this->parameter->name
			);
		}
	}

	public function listAction()
	{
		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'));
		$this->types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type :: getInstance()->select()->order('label'));

		$this->parameters = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Parameter', null, Sophie_Db_Treatment_Parameter :: getInstance()->select()->order('name'));

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->stepgroups = $this->stepgroups->toArray();
		$this->view->types = $this->types->toArray();

		$this->view->parameters = $this->parameters->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$form = $this->getForm('Parameter_Add');
		$form->setAction($this->view->url());
		$form->setDefaults(array (
			'treatmentId' => $this->treatment->id
		));

		$nameElement = $form->getElement('name');
		$nameElement->addValidator(new Zend_Validate_Db_NoRecordExists(
		    array(
		        'table' => 'sophie_treatment_parameter',
		        'field' => 'name',
		        'exclude' => 'treatmentId = ' . $this->treatment->id
		        )
    		), true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				Sophie_Db_Treatment_Parameter :: getInstance()->insert(array (
					'name' => $values['name'],
					'value' => $values['value'],
					'treatmentId' => $this->treatment->id
				));

				$newParameter = Sophie_Db_Treatment_Parameter :: getInstance()->find($this->treatment->id, $values['name'])->current();
				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Parameter added: ' . print_r($newParameter->toArray(), true));

				$this->_helper->flashMessenger('New Parameter added');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true);
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Parameter',
			'small' => 'Parameter:',
			'name' => 'Add Parameter'
		);
	}

	public function editAction()
	{
		if (is_null($this->parameter))
		{
			$this->_error('Parameter parameterName missing!');
			return;
		}

		$form = $this->getForm('Parameter_Edit');
		$form->setAction($this->view->url());
		$formData = $this->parameter->toArray();
		$formData['treatmentId'] = $this->treatment->id;
		$formData['parameterName'] = $this->parameter->name;
		$form->setDefaults($formData);

		$db = Zend_Registry::get('db');
		$nameElement = $form->getElement('name');
		$dbRecordValidator = new Zend_Validate_Db_NoRecordExists(
		    array(
		        'table' => 'sophie_treatment_parameter',
		        'field' => 'name',
		        'exclude' => 'treatmentId = ' . $this->treatment->id . ' AND name <> ' . $db->quote($this->parameter->name)
		        )
    		);
    	$nameElement->addValidator($dbRecordValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$oldValues = $this->parameter->toArray();
				$this->parameter->setFromArray($values);
				$this->parameter->save();

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Saved changes to Parameter: ' . print_r($oldValues, true) . ' => ' .  print_r($this->parameter->toArray(), true));

				$this->_helper->flashMessenger('Saved changes to Parameter');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true);
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment;
		$this->view->parameter = $this->parameter->toArray();
		$this->view->form = $form;
	}

	public function deleteAction()
	{
		if (is_null($this->parameter))
		{
			$this->_error('Parameter parameterName missing!');
			return;
		}

		$oldValues = $this->parameter->toArray();

		if ($this->parameter->delete())
		{
			Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Parameter deleted: ' . print_r($oldValues, true));
			$this->_helper->json(array('message' => 'Parameter deleted'));
		}
		else
		{
			$this->_helper->json(array('error' => 'Parameter not deleted'));
		}
	}

}