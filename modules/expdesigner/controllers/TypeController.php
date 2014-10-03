<?php
class Expdesigner_TypeController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $typeLabel = null;

	private $experiment = null;
	private $treatment = null;
	private $type = null;

	public function preDispatch()
	{
		$this->treatmentId = $this->_getParam('treatmentId', null);
		if (empty ($this->treatmentId))
		{
			$this->_error('Paramater treatmentId missing!');
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
		
		$this->view->breadcrumbs = array (
			'home' => 'expdesigner',
			'experiment' => array (
				'id' => $this->experiment->id,
				'name' => $this->experiment->name
			),
			'treatment' => array (
				'id' => $this->treatment->id,
				'name' => $this->treatment->name,
				'anchor' => 'tab_treatmentParticipantTab'
			)
		);

		$this->typeLabel = $this->_getParam('typeLabel', null);
		if (!empty ($this->typeLabel))
		{
			$this->type = Sophie_Db_Treatment_Type :: getInstance()->find($this->treatment->id, $this->typeLabel)->current();

			if (is_null($this->type))
			{
				$this->_error('Selected type does not exist or does not belong to selected treatment!');
				return;
			}
		}
	}

	public function listAction()
	{
		$this->types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type :: getInstance()->select()->order('label'));

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->types = $this->types->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function selectAction()
	{
		$this->types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type :: getInstance()->select()->order('label'));

		$select = array();
		foreach ($this->types as $type)
		{
			$select[] = array('value' => $type->label, 'text' => $type->name);
		}
		$this->_helper->json($select);
	}

	public function addAction()
	{
		$form = $this->getForm('Type_Add');
		$form->setAction($this->view->url());

		$labelElement = $form->getElement('label');
		$labelValidator = new Sophie_Validate_Treatment_Type_Label();
		$labelValidator->treatmentId = $this->treatment->id;
		$labelValidator->setUniqueCheck(true);
		$labelElement->addValidator($labelValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$data = array (
					'treatmentId' => $this->treatment->id,
					'label' => $values['label'],
					'name' => $values['name'],
					'description' => $values['description'],
					'icon' => $values['icon']
				);
				$id = Sophie_Db_Treatment_Type :: getInstance()->insert($data);

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Type added: ' . print_r($data, true));

				$this->_helper->flashMessenger('New type added');

				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment->id
				)) . '#tab_treatmentParticipantTab');
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Add Participant Type',
			'small' => 'Participant Type:',
			'name' => 'Add Type'
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
	}

	public function editAction()
	{
		if (is_null($this->type))
		{
			$this->_error('Missing typeLabel parameter');
			return;
		}

		$form = $this->getForm('Type_Edit');
		$form->setAction($this->view->url());

		$formData = $this->type->toArray();
		$formData['typeLabel'] = $this->type->label;
		$form->setDefaults($formData);

		$labelElement = $form->getElement('label');
		$labelValidator = new Sophie_Validate_Treatment_Type_Label();
		$labelValidator->treatmentId = $this->treatment->id;
		if (isset($_POST['label']) && $this->type->label != $_POST['label'])
		{
			$labelValidator->setUniqueCheck(true);
		}
		$labelElement->addValidator($labelValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{

				$values = $form->getValues();
				unset($values['typeLabel']);
				unset($values['treatmentId']);

				$oldValues = $this->type->toArray();
				$this->type->setFromArray($values);
				$this->type->save();

				// TODO: cascade???
				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Changes to type saves: ' . print_r($oldValues, true) . ' => ' . print_r($values, true));

				$this->_helper->flashMessenger('Type changes saved');

				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment->id
				), null, true) . '#tab_treatmentParticipantTab');
				return;
			}
		}

		$this->view->breadcrumbs['__own'] = array (
			'title' => 'Edit Participant Type',
			'small' => 'Participant Type:',
			'name' => $this->type->name
		);

		$this->view->form = $form;
		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->type = $this->type->toArray();
	}

	public function deleteAction()
	{
		if (is_null($this->type))
		{
			$this->_error('Missing typeLabel parameter');
			return;
		}

		// TODO: cascade???
		$typeData = $this->type->toArray();
		$this->type->delete();
		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Type deleted: ' . print_r($typeData, true));

		$this->_helper->json(array('message' => 'Type deleted'));
	}
}