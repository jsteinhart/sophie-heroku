<?php
class Expdesigner_ReportController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $reportId = null;

	private $experiment = null;
	private $treatment = null;
	private $report = null;

	public function preDispatch()
	{
		$this->reportId = $this->_getParam('reportId', null);
		if ($this->reportId)
		{
			$this->report = Sophie_Db_Treatment_Report :: getInstance()->find($this->reportId)->current();
			if (is_null($this->report))
			{
				$this->_error('Selected report does not exist!');
				return;
			}
			$this->treatmentId = $this->report->treatmentId;
		}
		else
		{
			$this->treatmentId = $this->_getParam('treatmentId', null);
		}

		if (empty ($this->reportId) && empty ($this->treatmentId))
		{
			$this->_error('Paramater reportId or treatmentId missing!');
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
		if (!$acl->autoCheckAcl('experiment', $this->experiment->id, 'sophie_experiment'))
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
				'anchor' => 'tab_treatmentReportTab'
			)
		);

	}

	public function indexAction()
	{
		$model = Sophie_Db_Treatment_Report :: getInstance();
		$reports = $model->getReportsByTreatmentId($this->treatmentId);
		$this->view->reports = $reports;

		$this->view->treatmentId = $this->treatmentId;
		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$form = $this->getForm('Report_Add');
		$form->populate(array('treatmentId' => $this->treatmentId));

		$nameElement = $form->getElement('name');
		$nameValidator = new \Sophie_Validate_Treatment_Report_Name();
		$nameValidator->treatmentId = $this->treatment->id;
		$nameValidator->setUniqueCheck(true);
		$nameElement->addValidator($nameValidator);
		
		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$data = array_intersect_key($values, array(
					'id' => '',
					'treatmentId' => '',
					'name' => '',
					'type' => '',
					'definition' => '',
				));
				$model = Sophie_Db_Treatment_Report :: getInstance();
				$reportId = $model->insert($data);

				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'report',
					'action' => 'edit',
					'treatmentId' => $this->treatment['id'],
					'reportId' => $reportId
				)));
				return;
			}
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Report',
			'small' => 'Report:',
			'name' => 'Add Report'
		);

		$definitionElement = $form->getElement('definition');
		$definitionElement->setAttrib('onchange','expdesigner.updateTreatmentReportDefinitionSanitizerResults(' . $this->treatment->id . ', ' . $definitionElement->getJsInstance() . '.getValue(), \'definitionSanitizerMessages\', \'php\')');

		$this->view->form = $form;
	}

	public function editAction()
	{
		if (is_null($this->report))
		{
			$this->_error('Selected report does not exist!');
			return;
		}

		$form = $this->getForm('Report_Edit');
		$form->setAction($this->view->url());
		$formData = $this->report->toArray();
		$form->setDefaults($formData);

		$nameElement = $form->getElement('name');
		$nameValidator = new \Sophie_Validate_Treatment_Report_Name();
		$nameValidator->treatmentId = $this->treatment->id;
		$nameValidator->reportId = $this->report->id;
		$nameValidator->setUniqueCheck(true);
		$nameElement->addValidator($nameValidator);

		$db = Zend_Registry::get('db');
		$nameElement = $form->getElement('name');
		$dbRecordValidator = new Zend_Validate_Db_NoRecordExists(
			array(
				'table' => 'sophie_treatment_report',
				'field' => 'name',
				'exclude' => 'treatmentId = ' . $this->treatment->id . ' AND name <> ' . $db->quote($this->report->name)
				)
		);
		$nameElement->addValidator($dbRecordValidator, true);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$oldValues = $this->report->toArray();
				$data = array_intersect_key($values, array(
					'id' => '',
					'treatmentId' => '',
					'name' => '',
					'type' => '',
					'definition' => '',
				));
				$this->report->setFromArray($data);
				$this->report->save();

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Saved changes to Report: ' . print_r($oldValues, true) . ' => ' .  print_r($this->report->toArray(), true));

				$this->_helper->flashMessenger('Saved changes to Report');

				$this->_helper->getHelper('Redirector')->setPrependBase('')->gotoUrl($this->_helper->getHelper('Url')->url(array (
					'module' => 'expdesigner',
					'controller' => 'report',
					'action' => 'edit',
					'treatmentId' => $this->treatment['id'],
					'reportId' => $this->report->id
				)));
				return;
			}
		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Edit Report',
			'small' => 'Report:',
			'name' => 'Edit Report'
		);

		$this->view->treatment = $this->treatment->toArray();

		$definitionSanitizerCheck = true;
		$definitionValidatorElement = $form->getElement('definitionValidator');

		if ($formData['definition'] != '')
		{
			try {
				$sanitizer = new Sophie_Validate_PHPCode();
				$definitionSanitizerCheck = $sanitizer->isValid('<?php ' . $formData['definition'] . ' ?>');
			}
			catch(Exception $e)
			{
				$definitionSanitizerCheck = false;
			}
		}

		if (!$definitionSanitizerCheck)
		{
			$definitionValidContent = '<div id="definitionSanitizerMessages" class="error">';
			$definitionValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$definitionValidContent .= nl2br($this->view->escape(implode("\n", $sanitizer->getMessages())));
			$definitionValidContent .= '</div>';
			$definitionValidatorElement->setValue($definitionValidContent);
		}

		$definitionElement = $form->getElement('definition');
		$definitionElement->setAttrib('onchange','expdesigner.updateTreatmentReportDefinitionSanitizerResults(' . $this->treatment->id . ', ' . $definitionElement->getJsInstance() . '.getValue(), \'definitionSanitizerMessages\', \'php\')');

		$this->view->form = $form;
	}


	public function deleteAction()
	{
		if (is_null($this->report))
		{
			$this->_error('Selected report does not exist!');
			return;
		}

		$this->report->delete();

		$this->_helper->json(array (
			'message' => 'Report deleted'
		));
	}

	public function checkcodeAction()
	{
		$code = $this->_getParam('code', '');

		$errors = array();
		$notices = array();

		if ($code != '')
		{
			$code = '<?php ' . $code;

			$validate = new Sophie_Validate_PHPCode();
			
			$valid = $validate->isValid($code);
			
			if ($valid !== true)
			{
				$errors = $validate->getMessages();
			}
		}

		$return = array();
		$return['errors'] = $errors;
		$return['notices'] = $notices;
		$this->_helper->json($return);
	}
}