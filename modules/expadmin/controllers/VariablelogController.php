<?php
class Expadmin_VariablelogController extends Symbic_Controller_Action
{

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
		if (!$acl->autoCheckAcl('session', $this->session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$this->sessiontype = $this->session->findParentRow('Sophie_Db_Treatment_Sessiontype');
		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');

		$popup = $this->_getParam('popup', false);
		if ($popup)
		{
			$this->_helper->layout->setLayout('popup');
			$this->popup = 1;
		}
	}

	public function listAction()
	{
		$variableModel = Sophie_Db_Session_Variable_Log :: getInstance();

		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		$names = $variableModel->fetchAllDistinctNames($this->session->id);

		$variableListForm = $this->getForm('Variable_Listing');
		$variableListForm->setAction($this->view->url(array (
			'action' => 'list',
			'popup' => '1'
		)));

		$winName = 'Variables' . md5(uniqid());
		$variableListForm->setAttrib('target', $winName);
		$variableListForm->setAttrib('onsubmit', 'win("' . $winName . '")');

		$stepgroupLabelsField = $variableListForm->getElement('filterStepgroupLabels');
		$stepgroupLoopsField = $variableListForm->getElement('filterStepgroupLoops');

		$stepgroupMaxLoops = 1;
		$stepgroupLabels = array();
		foreach ($this->stepgroups as $stepgroup)
		{
			$stepgroupLabels[$stepgroup['label']] = $stepgroup['position'] . '. ' . $stepgroup['name'] . ' (' . $stepgroup['label'] . ')';
			if ($stepgroup['loop'] > $stepgroupMaxLoops)
			{
				$stepgroupMaxLoops = $stepgroup['loop'];
			}
		}
		asort($stepgroupLabels);
		$stepgroupLabelsField->addMultiOptions($stepgroupLabels);

		for ($i = 1; $i <= $stepgroupMaxLoops; $i++)
		{
			$stepgroupLoopsField->addMultiOption($i, $i);
		}

		$namesField = $variableListForm->getElement('filterNames');
		foreach ($names as $name)
		{
			$namesField->addMultiOption($name['name'], $name['name']);
		}

		if ($this->getRequest()->isPost())
		{
			if ($variableListForm->isValid($_POST))
			{
				$formValues = $variableListForm->getValues();

				// get variable list
				$filterVariables = $formValues['filterNames'];
				$variablesOrder = array (
					'stepgroupLabel',
					'stepgroupLoop',
					'groupLabel',
					'participantLabel',
					'name'
				);

				$variables = $variableModel->fetchAllByNameAndContext($filterVariables, $formValues['filterSystemVariables'] == 1, $this->session->id, $formValues['filterVariableTypes'], $formValues['filterStepgroupLabels'], $variablesOrder);

				// prepare participant codes list
				if ($formValues['includeParticipantCodes'])
				{
					$participantTable = Sophie_Db_Session_Participant :: getInstance();
					$participants = $participantTable->fetchAllBySession($this->session->id);
					$participantCodes = array ();
					foreach ($participants as $participant)
					{
						$participantCodes[$participant->label] = $participant->code;
					}
					$this->view->participantCodes = $participantCodes;
				}

				$this->view->variables = $variables;
				$this->_helper->viewRenderer('list-' . $formValues['outputFormat']);

				if ($formValues['outputFormat'] == 'csv')
				{
					$this->getResponse()->setHeader('Content-Type', 'text/csv');
					$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_list_session' . $this->session->id . '.csv');
					$this->_helper->layout->disableLayout();
				}

				elseif ($formValues['outputFormat'] == 'xlsx')
				{
					$this->getResponse()->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_list_session' . $this->session->id . '.xlsx');
					$this->_helper->layout->disableLayout();
				}

				return;
			}
		}

		$this->view->variableListForm = $variableListForm;

		$this->view->treatment = $this->treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}

}