<?php
class Expadmin_VariableController extends Symbic_Controller_Action
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
		$variableModel = Sophie_Db_Session_Variable :: getInstance();

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

	public function tabulateAction()
	{
		$variableModel = Sophie_Db_Session_Variable :: getInstance();

		$this->treatment = $this->session->findParentRow('Sophie_Db_Treatment');
		$this->stepgroups = $this->treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup');
		$names = $variableModel->fetchAllDistinctNames($this->session->id);

		$variableTabulateForm = $this->getForm('Variable_Tabulate');
		$variableTabulateForm->setAction($this->view->url(array (
			'module' => 'expadmin',
			'controller' => 'variable',
			'action' => 'tabulate',
			'popup' => '1'
		)));

		$winName = 'VariableTable' . md5(uniqid());
		$variableTabulateForm->setAttrib('target', $winName);
		$variableTabulateForm->setAttrib('onsubmit', 'win("' . $winName . '")');

		$stepgroupLabelsField = $variableTabulateForm->getElement('filterStepgroupLabels');
		$stepgroupLoopsField = $variableTabulateForm->getElement('filterStepgroupLoops');

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

		$namesField = $variableTabulateForm->getElement('filterNames');
		foreach ($names as $name)
		{
			$namesField->addMultiOption($name['name'], $name['name']);
		}

		if ($this->getRequest()->isPost())
		{
			if ($variableTabulateForm->isValid($_POST))
			{
				$formValues = $variableTabulateForm->getValues();

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

				$varNames = array ();
				$variableRows = array ();
				foreach ($variables as & $variable)
				{
					if (!in_array($variable['name'], $varNames))
					{
						$varNames[] = $variable['name'];
					}

					$rowKey = 'session' . $variable['sessionId'] . '_stepgroup' . $variable['stepgroupLabel'] . '_loop' . $variable['stepgroupLoop'] . '_group' . $variable['groupLabel'] . '_participant' . $variable['participantLabel'];
					if (!array_key_exists($rowKey, $variableRows) || !is_array($variableRows[$rowKey]))
					{
						$variableRows[$rowKey] = $variable;
						unset ($variableRows[$rowKey]['name']);
						unset ($variableRows[$rowKey]['value']);
					}
					$variableRows[$rowKey][$variable['name']] = $variable['value'];
				}
				unset ($variables);
				sort($varNames);

				$aggregateRows = array ();
				if (isset ($formValues['addAggregateRows']))
				{
					foreach ($formValues['addAggregateRows'] as $aggregateRow)
					{
						$aggregateRows[$aggregateRow] = array ();

						switch ($aggregateRow)
						{
							case 'min' :
								foreach ($varNames as $varName)
								{
									$value = null;
									foreach ($variableRows as $variable)
									{
										if (isset ($variable[$varName]) && (is_null($value) || $variable[$varName] < $value))
										{
											$value = $variable[$varName];
										}
									}

									if (!is_null($value))
									{
										$aggregateRows[$aggregateRow][$varName] = $value;
									}
								}
								break;

							case 'max' :
								foreach ($varNames as $varName)
								{
									$value = null;
									foreach ($variableRows as $variable)
									{
										if (isset($variable[$varName]) && (is_null($value) || $variable[$varName] > $value))
										{
											$value = $variable[$varName];
										}
									}

									if (!is_null($value))
									{
										$aggregateRows[$aggregateRow][$varName] = $value;
									}
								}
								break;

							case 'avg' :
								foreach ($varNames as $varName)
								{
									$value = 0;
									$sum = 0;
									$n = 0;
									foreach ($variableRows as $variable)
									{
										if (isset ($variable[$varName]))
										{
											if (is_numeric($variable[$varName]))
											{
												$sum += $variable[$varName];
												$n++;
											}
										}
									}

									if ($n > 0)
									{
										$value = $sum / $n;
										$aggregateRows[$aggregateRow][$varName] = $value;
									}
								}
								break;

							case 'mode' :
								foreach ($varNames as $varName)
								{
									$values = array ();
									foreach ($variableRows as $variable)
									{
										if (isset ($variable[$varName]))
										{
											if (is_numeric($variable[$varName]))
											{
												if (!isset ($values[$variable[$varName]]))
												{
													$values[$variable[$varName]] = 0;
												}
												$values[$variable[$varName]]++;
											}
										}
									}

									$maxCount = 0;
									$modeValue = array ();
									foreach ($values as $valueKey => $valueCount)
									{
										if ($valueCount > $maxCount)
										{
											$modeValue = array ();
											$modeValue[] = $valueKey;
											$maxCount = $valueCount;
										}
										elseif ($valueCount == $maxCount)
										{
											$modeValue[] = $valueKey;
										}
									}

									if (sizeof($modeValue) > 0)
									{
										sort($modeValue);
										$aggregateRows[$aggregateRow][$varName] = implode(';', $modeValue);
									}
								}
								break;

							case 'median' :
								foreach ($varNames as $varName)
								{
									$values = array ();
									foreach ($variableRows as $variable)
									{
										if (isset($variable[$varName]))
										{
											if (is_numeric($variable[$varName]))
											{
												$values[] = $variable[$varName];
											}
										}
									}

									$valueCount = sizeof($values);
									if ($valueCount > 0)
									{
										sort($values);

										$middleValuePointer = ceil($valueCount / 2) - 1;
										if ($valueCount % 2 == 0)
										{
											$middleValue = ($values[$middleValuePointer] + $values[$middleValuePointer +1]) / 2;
										}
										else
										{
											$middleValue = $values[$middleValuePointer];
										}

										$aggregateRows[$aggregateRow][$varName] = $middleValue;
									}

								}
								break;

							case 'sum' :
								foreach ($varNames as $varName)
								{
									$sum = 0;

									foreach ($variableRows as $variable)
									{
										if (isset ($variable[$varName]))
										{
											if (is_numeric($variable[$varName]))
											{
												$sum += $variable[$varName];
											}
										}
									}

									$aggregateRows[$aggregateRow][$varName] = $sum;
								}
								break;

							case 'count' :
								foreach ($varNames as $varName)
								{
									$n = 0;
									foreach ($variableRows as $variable)
									{
										if (isset ($variable[$varName]))
										{
											$n++;
										}
									}

									$aggregateRows[$aggregateRow][$varName] = $n;
								}
								break;
						}
						$this->view->aggregateRows = $aggregateRows;
					}
				}

				$this->view->varNames = $varNames;
				$this->view->variableRows = $variableRows;

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

				$this->_helper->viewRenderer('tabulate-' . $formValues['outputFormat']);

				if ($formValues['outputFormat'] == 'csv')
				{
					$this->getResponse()->setHeader('Content-Type', 'text/csv');
					$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_table_session' . $this->session->id . '.csv');
					$this->_helper->layout->disableLayout();
				}

				elseif ($formValues['outputFormat'] == 'xlsx')
				{
					$this->getResponse()->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					$this->getResponse()->setHeader('Content-disposition', 'attachment;filename=variables_table_session' . $this->session->id . '.xlsx');
					$this->_helper->layout->disableLayout();
				}

				return;
			}
		}
		$this->view->variableTabulateForm = $variableTabulateForm;

		$this->view->treatment = $this->treatment->toArray();
		$this->view->session = $this->session->toArray();

		$this->_helper->layout->disableLayout();
	}

	public function addAction()
	{
		$form = $this->getForm('Variable_Add');
		$form->setAction('javascript:expadmin.sessionTabSubmit(\'' . $this->view->url() . '\', \'ExpadminFormVariableAdd\', \'sessionVariableAddTab\')');
		$form->setDefaults(array (
			'sessionId' => $this->session->id
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

				// personContext
				if ($values['personContext'] == 'g')
				{
					$values['participantLabel'] = NULL;
				}
				elseif ($values['personContext'] == 'p')
				{
					$values['groupLabel'] = NULL;
				}
				else
				{
					$values['groupLabel'] = NULL;
					$values['participantLabel'] = NULL;
				}

				// proceduralContext
				if ($values['proceduralContext'] != 'sg' && $values['proceduralContext'] != 'sl')
				{
					$values['stepgroupLabel'] = NULL;
					$values['stepgroupLoop'] = NULL;
				}
				else
				{
					if ($values['proceduralContext'] != 'sl')
					{
						$values['stepgroupLoop'] = NULL;
					}
				}

				$variableModel = Sophie_Db_Session_Variable :: getInstance();

				$variableModel->setValueByNameAndContext($values['name'], $values['value'], $this->session->id, $values['groupLabel'], $values['participantLabel'], $values['stepgroupLabel'], $values['stepgroupLoop']);
				
				Sophie_Db_Session_Log :: log($this->session->id, 'Variable ' . $values['name'] . ' added', null, print_r(array (
					$values['name'],
					$values['value'],
					$this->session->id,
					$values['groupLabel'],
					$values['participantLabel'],
					$values['stepgroupLabel'],
					$values['stepgroupLoop']
				), true));

				$this->view->message = 'New variable added';
				
				$form->reset();
			}
		}
		
		$this->view->form = $form;
		$this->_helper->layout->disableLayout();
	}
	
	public function importAction()
	{
		// calling the form
		if (!$this->getRequest()->isPost())
		{
			$variableImportForm = $this->getForm('Variable_Import');
			$variableImportForm->setAction("javascript:expadmin.sessionVariableImport('sessionVariableImportContent');");
			$variableImportForm->setMethod('POST');

			$this->view->variableImportForm = $variableImportForm;
			$this->view->treatment = $this->treatment->toArray();
			$this->view->session = $this->session->toArray();
			$this->_helper->layout->disableLayout();
			return;
		}

		// form submitted
		$variableContent = $this->_getParam('sessionVariableImportContent', '');
		if (empty ($variableContent))
		{
			$this->_helper->json(array (
				'error' => 'Variable data is empty'
			));
			return;
		}

		$csvDelimiter = $this->_getParam('csvDelimiter', ';');
		$csvEnclosure = '"';
		$csvEscape = '\\';

		$variableContent = str_replace("\r\n", "\n", $variableContent);
		$variables = explode("\n", $variableContent);

		if (!is_array($variables))
		{
			$this->_helper->json(array (
				'error' => 'Importing variables failed'
			));
			return;
		}

		$variableModel = Sophie_Db_Session_Variable :: getInstance();

		$variableImportFailure = false;
		$variableImportErrors = array ();
		$variableImportData = array ();
		foreach ($variables as $variableContent)
		{
			if (empty ($variableContent))
			{
				continue;
			}

			// TODO: add backward compatibility by using explode for php v < 5.3
			$variableData = str_getcsv($variableContent, $csvDelimiter, $csvEnclosure, $csvEscape);

			switch (strtolower($variableData[0]))
			{

				case 'ee' :
					if (sizeof($variableData) < 3 || sizeof($variableData) > 4)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "ee" expects 2 or 3 parameters (name, value, [type])';
						continue;
					}
					break;

				case 'es' :
					if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "esg" expects 3 or 4 parameters (name, value, stepgroupLabel, [type])';
						continue;
					}
					break;

				case 'esl' :
					if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "esl" expects 4 or 5 parameters (name, value, stepgroupLabel, stepgroupLoop, [type])';
						continue;
					}
					break;

				case 'ge' :
					if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "ge" expects 3 or 4 parameters (name, value, groupLabel, [type])';
						continue;
					}
					break;

				case 'gs' :
					if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "gs" expects 4 or 5 parameters (name, value, groupLabel, stepgroupLabel, [type])';
						continue;
					}
					break;

				case 'gsl' :
					if (sizeof($variableData) < 6 || sizeof($variableData) > 7)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "gsl" expects 5 or 6 parameters (name, value, groupLabel, stepgroupLabel, stepgroupLoop, [type])';
						continue;
					}
					break;

				case 'pe' :
					if (sizeof($variableData) < 4 || sizeof($variableData) > 5)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "pe" expects 3 or 4 parameters (name, value, participantLabel, [type])';
						continue;
					}
					break;

				case 'ps' :
					if (sizeof($variableData) < 5 || sizeof($variableData) > 6)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "psg" expects 4 or 5 parameters (name, value, participantLabel, stepgroupLabel, [type])';
						continue;
					}
					break;

				case 'psl' :
					if (sizeof($variableData) < 6 || sizeof($variableData) > 7)
					{
						$variableImportFailure = true;
						$variableImportErrors[] = 'Variable type "psl" expects 5 or 6 parameters (name, value, participantLabel, stepgroupLabel, stepgroupLoop, [type])';
						continue;
					}
					break;

				default :
					$variableImportFailure = true;
					$variableImportErrors[] = 'Unkown variable type ' . $variableData[0];
					continue;
					break;
			}

			// disallow emtpy variable name
			if (empty ($variableData[1]))
			{
				$variableImportFailure = true;
				$variableImportErrors[] = 'Empty variable name not allowed';
				continue;
			}

			$variableImportData[] = $variableData;
		}

		if ($variableImportFailure)
		{
			$this->_helper->json(array (
				'message' => 'Imported variables to failed: ' . implode(',<br />',
				$variableImportErrors
			)));
			return;
		}

		foreach ($variableImportData as $variableData)
		{
			$variableModel->import($this->session->id, $variableData);
		}

		Sophie_Db_Session_Log :: log($this->session->id, 'Imported ' . sizeof($variableImportData) . ' variables');

		$this->_helper->json(array (
			'message' => 'Imported ' . sizeof($variableImportData
		) . ' variables'));
	}

	public function deleteAction()
	{
		$variableModel = Sophie_Db_Session_Variable :: getInstance();

		$deleteWhere = array ();
		$deleteWhere['sessionId'] = $this->session->id;
		$deleteWhere['groupLabel'] = $this->session->id;
		$deleteWhere['participantLabel'] = $this->session->id;
		$deleteWhere['stepgroupLabel'] = $this->session->id;
		$deleteWhere['stepgroupLoop'] = $this->session->id;
		$deleteWhere['name'] = $this->session->id;

		$variableModel->delete($deleteWhere);

		$this->_helper->json(array (
			'message' => 'Variable deleted'
		));

	}

}