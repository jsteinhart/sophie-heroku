<?php
class Expdesigner_VariableController extends Symbic_Controller_Action
{
	private $experimentId = null;
	private $treatmentId = null;
	private $variableName = null;

	private $experiment = null;
	private $treatment = null;
	private $variable = null;

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
		
		$this->variableId = $this->_getParam('variableId', null);
		if (!empty($this->variableId))
		{
			$this->variable = Sophie_Db_Treatment_Variable::getInstance()->find($this->variableId)->current();
			if (is_null($this->variable))
			{
				$this->_error('Selected variable does not exist!');
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
		if (!is_null($this->variable)) {
			$this->view->breadcrumbs['variable'] = array(
				'treatmentId' => $this->treatment->id,
				'name' => $this->variable->name
			);
		}
	}

	public function listAction()
	{
		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup :: getInstance()->select()->order('position'));
		$this->types = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Type', null, Sophie_Db_Treatment_Type :: getInstance()->select()->order('label'));

		$this->variables = Sophie_Db_Treatment_Variable::getInstance()->fetchAllByTreatmentId($this->treatment->id, array('groupLabel', 'participantLabel', 'stepgroupLabel', 'stepgroupLoop', 'name'));

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->stepgroups = $this->stepgroups->toArray();
		$this->view->types = $this->types->toArray();

		$this->view->variables = $this->variables;

		$outputFormat = $this->getParam('outputFormat');
		if ($outputFormat == 'csv')
		{
			$this->_helper->viewRenderer('list-' . $outputFormat);
			$this->getResponse()->setHeader('Content-Type', 'text/csv');
			$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_list_treatment' . $this->treatment->id . '.csv');
		}

		elseif ($outputFormat == 'xlsx')
		{
			$this->_helper->viewRenderer('list-' . $outputFormat);

			$this->getResponse()->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_list_treatment' . $this->treatment->id . '.xlsx');
		}

		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$form = $this->getForm('Variable_Add');
		$form->setAction($this->view->url());
		$form->setDefaults(array (
			'treatmentId' => $this->treatment->id
		));

		if ($this->getRequest()->isPost())
		{
			if (isset($_POST['personContext']))
			{
				if ($_POST['personContext'] == 'e')
				{
					$form->getElement('participantLabel')->setRequired(false);
					$form->getElement('groupLabel')->setRequired(false);
				}
				elseif ($_POST['personContext'] == 'g')
				{
					$form->getElement('participantLabel')->setRequired(false);
				}
				elseif ($_POST['personContext'] == 'p')
				{
					$form->getElement('groupLabel')->setRequired(false);
				}
			}

			if (isset($_POST['proceduralContext']))
			{
				if ($_POST['proceduralContext'] == 'e')
				{
					$form->getElement('stepgroupLabel')->setRequired(false);
					$form->getElement('stepgroupLoop')->setRequired(false);
				}
				elseif ($_POST['proceduralContext'] == 'sg')
				{
					$form->getElement('stepgroupLoop')->setRequired(false);
				}
			}

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$groupLabel = NULL;
				$participantLabel = NULL;

				if ($values['personContext'] == 'g')
				{
					$groupLabel = $values['groupLabel'];
				}
				elseif ($values['personContext'] == 'p')
				{
					$participantLabel = $values['participantLabel'];
				}

				$stepgroupLabel = NULL;
				$stepgroupLoop = NULL;
				if ($values['proceduralContext'] == 'sg' || $values['proceduralContext'] == 'sl')
				{
					$stepgroupLabel = $values['stepgroupLabel'];
				}
				if ($values['proceduralContext'] == 'sl')
				{
					$stepgroupLoop = $values['stepgroupLoop'];
				}

				$variableModel = Sophie_Db_Treatment_Variable::getInstance();
				$values['value'] = $variableModel->castValue($values['value']);
				
				$variableModel->setValueByNameAndContext($values['name'], $values['value'], $this->treatment->id, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Variable added: ' . print_r(array($values['name'], $values['value'], $this->treatment->id, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop), true));

				$this->_helper->flashMessenger('New variable added');

				$this->_helper->redirector->setPrependBase('')->gotoUrl($this->view->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true) . '#tab_treatmentDataVariableTab');
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Add Variable',
			'small' => 'Variable:',
			'name' => 'Add Variable'
		);
	}

	public function editAction()
	{
		if (is_null($this->variable))
		{
			$this->_error('Parameter variable missing!');
			return;
		}

		$form = $this->getForm('Variable_Edit');
		$form->setAction($this->view->url());

		$variableModel = Sophie_Db_Treatment_Variable::getInstance();
		$translatedVariable = $variableModel->translateData($this->variable->toArray());

		$formData = $translatedVariable;
		$formData['treatmentId'] = $this->treatment->id;

		if (!is_null($translatedVariable['groupLabel']))
		{
			$formData['personContext'] = 'g';
		}
		elseif (!is_null($translatedVariable['participantLabel']))
		{
			$formData['personContext'] = 'p';
			$formData['groupLabel'] = '';
		}
		else
		{
			$formData['personContext'] = 'e';
			$formData['participantLabel'] = '';
			$formData['groupLabel'] = '';
		}

		if (!is_null($translatedVariable['stepgroupLabel']) && is_null($translatedVariable['stepgroupLoop']))
		{
			$formData['proceduralContext'] = 'sg';
			$formData['stepgroupLoop'] = '';
		}
		elseif (!is_null($translatedVariable['stepgroupLabel']) && !is_null($translatedVariable['stepgroupLoop']))
		{
			$formData['proceduralContext'] = 'sl';
		}
		else
		{
			$formData['proceduralContext'] = 'e';
			$formData['stepgroupLabel'] = '';
			$formData['stepgroupLoop'] = '';
		}

		$form->setDefaults($formData);

		if ($this->getRequest()->isPost())
		{
			if (isset($_POST['personContext']))
			{
				if ($_POST['personContext'] == 'e')
				{
					$form->getElement('participantLabel')->setRequired(false);
					$form->getElement('groupLabel')->setRequired(false);
				}
				elseif ($_POST['personContext'] == 'g')
				{
					$form->getElement('participantLabel')->setRequired(false);
				}
				elseif ($_POST['personContext'] == 'p')
				{
					$form->getElement('groupLabel')->setRequired(false);
				}
			}

			if (isset($_POST['proceduralContext']))
			{
				if ($_POST['proceduralContext'] == 'e')
				{
					$form->getElement('stepgroupLabel')->setRequired(false);
					$form->getElement('stepgroupLoop')->setRequired(false);
				}
				elseif ($_POST['proceduralContext'] == 'sg')
				{
					$form->getElement('stepgroupLoop')->setRequired(false);
				}
			}

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$oldValues = $this->variable->toArray();
				$values['value'] = $variableModel->castValue($values['value']);

				$groupLabel = NULL;
				$participantLabel = NULL;

				if ($values['personContext'] == 'g')
				{
					$groupLabel = $values['groupLabel'];
				}
				elseif ($values['personContext'] == 'p')
				{
					$participantLabel = $values['participantLabel'];
				}

				$stepgroupLabel = NULL;
				$stepgroupLoop = NULL;
				if ($values['proceduralContext'] == 'sg' || $values['proceduralContext'] == 'sl')
				{
					$stepgroupLabel = $values['stepgroupLabel'];
				}
				if ($values['proceduralContext'] == 'sl')
				{
					$stepgroupLoop = $values['stepgroupLoop'];
				}

				$this->variable->delete();
				$variableModel->setValueByNameAndContext($values['name'], $values['value'], $this->treatment->id, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop);

				Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Variable changed from ' . print_r(array($oldValues), true) . ' => ' . print_r(array($values['name'], $values['value'], $this->treatment->id, $groupLabel, $participantLabel, $stepgroupLabel, $stepgroupLoop), true));

				$this->_helper->flashMessenger('Saved changes to Variable');

				$this->_helper->redirector->setPrependBase('')->gotoUrl($this->view->url(array (
					'module' => 'expdesigner',
					'controller' => 'treatment',
					'action' => 'details',
					'treatmentId' => $this->treatment['id']
				), 'default', true) . '#tab_treatmentDataVariableTab');
				return;
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment;
		$this->view->variable = $this->variable->toArray();
		$this->view->form = $form;
	}

	public function deleteAction()
	{
		if (is_null($this->variable))
		{
			$this->_error('Parameter variableId missing!');
			return;
		}

		$oldValues = $this->variable->toArray();

		if ($this->variable->delete())
		{
			Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Variable deleted: ' . print_r($oldValues, true));
			$this->_helper->json(array('message' => 'Variable deleted'));
		}
		else
		{
			$this->_helper->json(array('error' => 'Variable not deleted'));
		}
	}

	public function deleteallAction()
	{
		$variableTable = Sophie_Db_Treatment_Variable::getInstance();
		$numRows = $variableTable->delete($variableTable->getAdapter()->quoteInto('treatmentId = ?', $this->treatment->id));
		
		Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Deleted all Variables in Treatment. ' . $numRows . ' Varables affected.');
		$this->_helper->json(array('message' => $numRows . ' Variables deleted'));
	}
	
	public function importAction()
	{
		$form = $this->getForm('Variable_Import');
		$form->setAction($this->view->url());
		$formData = array('treatmentId' => $this->treatment->id);
		$form->setDefaults($formData);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();
				$variableContentElement = $form->getElement('variableContent');

				if (!empty($values['variableContent']))
				{

					if (!empty($values['csvDelimiter']))
					{
						$csvDelimiter = $values['csvDelimiter'];
					}
					else
					{
						$csvDelimiter = ';';
					}

					$csvEnclosure = '"';
					$csvEscape = '\\';

					$values['variableContent'] = str_replace("\r\n", "\n", $values['variableContent']);
					$values['variableContent'] = str_replace("\r", "\n", $values['variableContent']);

					$variables = explode("\n", $values['variableContent']);

					$variableModel = Sophie_Db_Treatment_Variable::getInstance();

					$variableImportFailure = false;
					$variableImportData = array();
					foreach ($variables as $variableContent)
					{
						if (empty($variableContent))
						{
							continue;
						}

						// TODO: add backward compatibility by using explode for php v < 5.3
						$variableData = str_getcsv ( $variableContent, $csvDelimiter, $csvEnclosure, $csvEscape);

						switch (strtolower($variableData[0]))
						{

							case 'ee':
								if (sizeof($variableData) < 3 || sizeof($variableData) > 4)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "ee" expects 2 or 3 parameters (name, value, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'es':
								if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "es" expects 3 or 4 parameters (name, value, stepgroupLabel, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'esl':
								if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "esl" expects 4 or 5 parameters (name, value, stepgroupLabel, stepgroupLoop, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'ge':
								if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "ge" expects 3 or 4 parameters (name, value, groupLabel, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'gs':
								if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "gs" expects 4 or 5 parameters (name, value, groupLabel, stepgroupLabel, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'gsl':
								if (sizeof($variableData) < 6 || sizeof($variableData) > 7)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "gsl" expects 5 or 6 parameters (name, value, groupLabel, stepgroupLabel, stepgroupLoop, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'pe':
								if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "pe" expects 3 or 4 parameters (name, value, participantLabel, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'ps':
								if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "ps" expects 4 or 5 parameters (name, value, participantLabel, stepgroupLabel, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							case 'psl':
								if (sizeof($variableData) < 6 || sizeof($variableData) > 7)
								{
									$variableImportFailure = true;
									$variableContentElement->addError('Variable type "psl" expects 5 or 6 parameters (name, value, participantLabel, stepgroupLabel, stepgroupLoop, [type]): ' . implode(';', $variableData));
									continue;
								}
								break;

							default:
								$variableImportFailure = true;
								$variableContentElement->addError('Unkown variable type ' . $variableData[0]);
								continue;
								break;
						}

						// disallow emtpy variable name
						if (empty($variableData[1]))
						{
							$variableImportFailure = true;
							$variableContentElement->addError('Empty variable name not allowed');
							continue;
						}

						$variableImportData[] = $variableData;
					}

					if (!$variableImportFailure)
					{
						foreach ($variableImportData as $variableData)
						{
							$variableModel->import($this->treatment->id, $variableData);
						}

						Sophie_Db_Treatment_Log :: getInstance()->log($this->treatment->id, 'Imported ' . sizeof($variableImportData) . ' variables');
						$this->_helper->flashMessenger('Imported ' . sizeof($variableImportData) . ' variables');

						$this->_helper->redirector->setPrependBase('')->gotoUrl($this->view->url(array (
							'module' => 'expdesigner',
							'controller' => 'treatment',
							'action' => 'details',
							'treatmentId' => $this->treatment['id']
						), 'default', true) . '#tab_treatmentDataTab');
						return;
					}
				}
				else
				{
					$variableContentElement->addError('No variables to import');
				}
			}
		}

		$this->view->experiment = $this->experiment->toArray();
		$this->view->treatment = $this->treatment;
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Import Variables',
			'small' => 'Variables:',
			'name' => 'Import Variables'
		);
	}

}