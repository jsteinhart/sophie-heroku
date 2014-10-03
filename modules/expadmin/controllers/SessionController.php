<?php
class Expadmin_SessionController extends Symbic_Controller_Action
{
	private $userId;

	public function init()
	{
		$this->userId = Symbic_User_Session::getInstance()->getId();
	}

	public function preDispatch()
	{
		$this->view->breadcrumbs = array (
			'home' => 'expadmin'
		);
	}

	public function indexAction()
	{
		// TODO: move the following code as hasExperiment()/hasTreatment to model
		// TODO: do not only check if experiments exist, but if treatment exist
		// fetch list of experiments to show note when none exist
		$experimentModel = Sophie_Db_Experiment :: getInstance();
		$overviewSelect = $experimentModel->getOverviewSelect();

		// allow admin to see everything
		$adminMode = (boolean)$this->_getParam('adminMode', false);
		$adminRight = Symbic_User_Session::getInstance()->hasRight('admin');

		if ($adminMode && $adminRight)
		{
			$overviewSelect->order(array('experiment.name'));
		}
		else
		{
			System_Acl::getInstance()->addSelectAcl($overviewSelect, 'experiment');
			$overviewSelect->order(array('experiment.name', 'acl.rule'));
		}
		$overviewSelect->group(array('experiment.id'));
		$this->view->hasTreatment = sizeof($overviewSelect->query()->fetchAll()) > 0;

		// fetch sessions list
		$sessionModel = Sophie_Db_Session :: getInstance();
		$db = $sessionModel->getAdapter();
		$overviewSelect = $sessionModel->getOverviewSelect();

		// add filters from form
		$filterExperimentId = $this->_getParam('filterExperimentId', null);
		if (!is_null($filterExperimentId))
		{
			$overviewSelect->where('experiment.id = ?', $filterExperimentId);
		}

		$filterState = $this->_getParam('filterState', null);
		if (!is_null($filterState))
		{
			$overviewSelect->where('session.state = ?', $filterState);
			$overviewSelect->where('session.state != ?', 'deleted');
		}
		else
		{
			$overviewSelect->where('session.state != ?', 'terminated');
			$overviewSelect->where('session.state != ?', 'archived');
			$overviewSelect->where('session.state != ?', 'deleted');
		}

		// allow admin to see everything
		$adminMode = (boolean)$this->_getParam('adminMode', false);
		$adminRight = Symbic_User_Session::getInstance()->hasRight('admin');

		if ($adminMode && $adminRight)
		{
			$overviewSelect->order(array (
				'session.name'
			));
		}
		else
		{
			System_Acl :: getInstance()->addSelectAcl($overviewSelect, 'session');
			$overviewSelect->order(array (
				'session.name',
				'acl.rule'
			));
		}

		$overviewSelect->group(array (
			'session.id'
		));

		$this->view->sessions = $overviewSelect->query()->fetchAll();
		$this->view->adminMode = $adminMode;
		$this->view->adminRight = $adminRight;
	}

	public function addAction()
	{
		// TODO: move the following code as hasExperiment()/hasTreatment to model
		// fetch list of experiments to show note when none exist
		$experimentModel = Sophie_Db_Experiment :: getInstance();
		$overviewSelect = $experimentModel->getOverviewSelect();

		// allow admin to see everything
		$adminMode = (boolean)$this->_getParam('adminMode', false);
		$adminRight = Symbic_User_Session::getInstance()->hasRight('admin');

		if ($adminMode && $adminRight)
		{
			$overviewSelect->order(array('experiment.name'));
		}
		else
		{
			System_Acl::getInstance()->addSelectAcl($overviewSelect, 'experiment');
			$overviewSelect->order(array('experiment.name', 'acl.rule'));
		}
		$overviewSelect->group(array('experiment.id'));
		$this->view->hasTreatment = sizeof($overviewSelect->query()->fetchAll()) > 0;

		// prepare interface to add session
		$sessiontypeId = $this->_getParam('sessiontypeId', null);
		$sessiontypeModel = new Sophie_Db_Treatment_Sessiontype();
		if (! is_null($sessiontypeId))
		{
			$sessiontype = $sessiontypeModel->find($sessiontypeId)->current($sessiontypeId);
			if (is_null($sessiontype))
			{
				$this->_helper->FlashMessenger('Select sessiontype not found');
			}
			else
			{
				$treatment = $sessiontype->findParentRow('Sophie_Db_Treatment');
				$experiment = $treatment->findParentRow('Sophie_Db_Experiment');
				$this->view->sessiontypeTreeSelect = "['sessiontypeRoot', 'experiment" . $experiment->id . "', 'treatment" . $treatment->id . "', 'sessiontype" . $sessiontypeId . "']";
				$this->view->sessiontypeTreeSelectSessiontypeId = $sessiontypeId;
			}
		}
		else
		{
			$treatmentId = $this->_getParam('treatmentId', null);
			if (!is_null($treatmentId))
			{
				$treatmentModel = new Sophie_Db_Treatment();
				$treatment = $treatmentModel->find($treatmentId)->current();
				if (is_null($treatment))
				{
					$this->_helper->FlashMessenger('Select treatment not found');
				}
				else
				{
					$experiment = $treatment->findParentRow('Sophie_Db_Experiment');
					$this->view->sessiontypeTreeSelect = "['sessiontypeRoot', 'experiment" . $experiment->id . "', 'treatment" . $treatmentId . "']";
				}
			}
			else
			{
				$experimentId = $this->_getParam('experimentId', null);
				if (! is_null($experimentId))
				{
					$this->view->sessiontypeTreeSelect = "['sessiontypeRoot', 'experiment" . $experimentId . "']";
				}
			}

		}

		$this->view->breadcrumbs[] = array (
			'title' => 'Create Session',
			'small' => 'Session:',
			'name' => 'Create'
		);
	}

	public function sessiontypesAction()
	{
		$db = Zend_Registry::get('db');

		$sessiontypeTree = array ();

		$experimentModel = Sophie_Db_Experiment :: getInstance();
		$treatmentModel = Sophie_Db_Treatment :: getInstance();

		$overviewSelect = $experimentModel->getOverviewSelect();

		System_Acl::getInstance()->addSelectAcl($overviewSelect, 'experiment');
		$overviewSelect->group(array('experiment.id'));
		$overviewSelect->order(array('experiment.name', 'acl.rule'));

		$experiments = $overviewSelect->query()->fetchAll();

		foreach ($experiments as $experiment)
		{
			$experimentData = array ();
			$experimentData['id'] = 'experiment' . $experiment['id'];
			$experimentData['type'] = 'experiment';
			$experimentData['experimentId'] = $experiment['id'];
			$experimentData['label'] = $experiment['name'];
			$experimentData['children'] = array ();

			$treatments = $treatmentModel->fetchAll('state <> "deleted" AND experimentId = ' . $experiment['id']);
			foreach ($treatments as $treatment)
			{
				$treatmentSessiontypeNumber = 0;
				$sessiontypes = $treatment->findDependentRowset('Sophie_Db_Treatment_Sessiontype');

				$treatmentData = array ();
				$treatmentData['id'] = 'treatment' . $treatment->id;
				$treatmentData['type'] = 'treatment';
				$treatmentData['treatmentId'] = $treatment->id;
				$treatmentData['label'] = $treatment->name;
				$treatmentData['children'] = array ();

				$sessiontypeData = array ();
				$sessiontypeData['treatmentId'] = $treatment->id;
				$sessiontypeData['id'] = 'dynamicSession' . $treatment->id;
				$sessiontypeData['type'] = 'dynamicSession';
				$sessiontypeData['label'] = 'Dynamic Session';

				$treatmentData['children'][] = $sessiontypeData;

				foreach ($sessiontypes as $sessiontype)
				{
					if ($sessiontype->state != 'deleted')
					{
						$sessiontypeData = array ();
						$sessiontypeData['id'] = 'sessiontype' . $sessiontype->id;
						$sessiontypeData['type'] = 'sessiontype';
						$sessiontypeData['sessiontypeId'] = $sessiontype->id;
						$sessiontypeData['label'] = $sessiontype->name;

						$treatmentData['children'][] = $sessiontypeData;
					}
				}

				$experimentData['children'][] = $treatmentData;
			}

			if (sizeof($experimentData['children']) > 0)
			{
				$sessiontypeTree[] = $experimentData;
			}
		}

		$data = new Zend_Dojo_Data('id', $sessiontypeTree);
		$data->setLabel('label');
		echo $data->toJson();

		exit;
	}

	public function add2Action()
	{
		$this->_helper->layout->disableLayout();
		if (!$this->_hasParam('sessiontypeId'))
		{
			$this->_error('Please select a sessiontype');
			$this->_forward('add');
			return;
		}

		$sessiontypeModel = new Sophie_Db_Treatment_Sessiontype();
		$sessiontypeId = $this->_getParam('sessiontypeId');
		$sessiontype = $sessiontypeModel->find($sessiontypeId)->current();
		if (is_null($sessiontype))
		{
			$this->_helper->FlashMessenger('Select sessiontype does not exist');
			$this->_forward('add');
			return;
		}

		$treatment = $sessiontype->findParentRow('Sophie_Db_Treatment');
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$this->view->sessiontype = $sessiontype->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();
	}

	public function add3Action()
	{
		if (!$this->_hasParam('sessiontypeId'))
		{
			$this->_helper->FlashMessenger('Please select a sessiontype');
			$this->_forward('add');
			return;
		}

		$sessiontypeModel = new Sophie_Db_Treatment_Sessiontype();
		$sessiontypeId = $this->_getParam('sessiontypeId', null);
		$sessiontype = $sessiontypeModel->find($sessiontypeId)->current();
		if (is_null($sessiontype))
		{
			$this->_helper->FlashMessenger('Selected sessiontype does not exist');
			$this->_forward('add');
			return;
		}

		// validate group definition:
		if ($sessiontype->participantMgmt == 'static' && !$sessiontypeModel->checkGroupDefinition($sessiontypeId))
		{
			$this->_helper->FlashMessenger('<strong>The grouping of the selected sessiontype is flawed:</strong><br />' . $sessiontypeModel->lastGroupDefinitionError);
			$this->_forward('add');
			return;
		}

		$treatment = $sessiontype->findParentRow('Sophie_Db_Treatment');
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$form = $this->getForm('Session_Add3');
		$form->setAction($this->view->url());

		$session = new Zend_Session_Namespace('system');
		$formData = array (
			'ownerId' => Symbic_User_Session::getInstance()->getId()
		);
		$form->setDefaults($formData);

		if ($this->getRequest()->isPost())
		{

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if (isset($values['userAccess']))
				{
					$userAccess = $values['userAccess'];
					unset ($values['userAccess']);
				}
				else
				{
					$userAccess = array();
				}
				if (isset($values['usergroupAccess']))
				{
					$usergroupAccess = $values['usergroupAccess'];
					unset ($values['usergroupAccess']);
				}
				else
				{
					$usergroupAccess = array();
				}

				try
				{
					$db = Sophie_Db_Session :: getInstance()->getAdapter();
					$db->beginTransaction();

					$id = Sophie_Db_Session :: getInstance()->insert(array (
						'treatmentId' => $sessiontype->treatmentId,
						'sessiontypeId' => $sessiontype->id,
						'participantMgmt' => $sessiontype->participantMgmt,
						'name' => $values['name'],
						'description' => $values['description'],
						'ownerId' => $this->userId,
						'debugConsole' => $values['debugConsole'],
						'cacheTreatment' => $values['cacheTreatment'],
						'created' => new Zend_Db_Expr('NOW()'
					), 'state' => 'created'));


					//SessionLog ->Session created
					$newSession = Sophie_Db_Session :: getInstance()->find($id)->current();
					Sophie_Db_Session_Log :: log($id, 'Session created', null, print_r($newSession->toArray(), true));

					$acl = System_Acl :: getInstance();
					$acl->setAccessForRoles('session', $id, 'user', $userAccess);
					$acl->setAccessForRoles('session', $id, 'usergroup', $usergroupAccess);

					$sessionService = Sophie_Service_Session :: getInstance();
					$sessionService->initParticipantsWithCode($id);

					if ($sessiontype->participantMgmt == 'static')
					{
						$sessionService->initStaticGrouping($id);
					}

					////////////////////////////////////////////
					// import entity attribute values
					////////////////////////////////////////////
					$sessionEavTable = Sophie_Db_Session_Eav :: getInstance();
					$treatmentEavs = Sophie_Db_Treatment_Eav :: getInstance()->getAll($sessiontype->treatmentId);
					foreach ($treatmentEavs as $attribute => $value)
					{
						$sessionEavTable->replace(array('sessionId' => $id, 'name' => $attribute, 'value' => $value));
					}

					////////////////////////////////////////////
					// import variables
					////////////////////////////////////////////
					$sessionVariableTable = Sophie_Db_Session_Variable :: getInstance();

					// import treatment variables
					$treatmentVariables = Sophie_Db_Treatment_Variable::getInstance()->fetchAllByTreatmentId($sessiontype->treatmentId, array('groupLabel', 'participantLabel', 'stepgroupLabel', 'stepgroupLoop', 'name'));
					foreach ($treatmentVariables as $treatmentVariable)
					{
						$sessionVariableTable->setValueByNameAndContext($treatmentVariable['name'], $treatmentVariable['value'], $id, $treatmentVariable['groupLabel'], $treatmentVariable['participantLabel'], $treatmentVariable['stepgroupLabel'], $treatmentVariable['stepgroupLoop']);
					}

					// import sessiontype variables
					$sessiontypeVariables = Sophie_Db_Treatment_Sessiontype_Variable::getInstance()->fetchAllBySessiontypeId($sessiontype->id, array('groupLabel', 'participantLabel', 'stepgroupLabel', 'stepgroupLoop', 'name'));
					foreach ($sessiontypeVariables as $sessiontypeVariable)
					{
						$sessionVariableTable->setValueByNameAndContext($sessiontypeVariable['name'], $sessiontypeVariable['value'], $id, $sessiontypeVariable['groupLabel'], $sessiontypeVariable['participantLabel'], $sessiontypeVariable['stepgroupLabel'], $sessiontypeVariable['stepgroupLoop']);
					}

					////////////////////////////////////////////
					// import parameters
					////////////////////////////////////////////
					$sessionParameterTable = Sophie_Db_Session_Parameter :: getInstance();

					// import treatment parameters
					$treatmentParameters = Sophie_Db_Treatment_Parameter::getInstance()->fetchAllByTreatmentId($sessiontype->treatmentId);
					foreach ($treatmentParameters as $treatmentParameter)
					{
						$sessionParameterTable->replace(array('sessionId'=>$id, 'name'=>$treatmentParameter['name'], 'value'=>$treatmentParameter['value']));
					}

					// import sessiontype parameters
					$sessiontypeParameters = Sophie_Db_Treatment_Sessiontype_Parameter::getInstance()->fetchAllBySessiontypeId($sessiontype->id);
					foreach ($sessiontypeParameters as $sessiontypeParameter)
					{
						$sessionParameterTable->replace(array('sessionId'=>$id, 'name'=>$sessiontypeParameter['name'], 'value'=>$sessiontypeParameter['value']));
					}

					////////////////////////////////////////////
					// run setup script
					////////////////////////////////////////////

					if (!empty($treatment->setupScript))
					{
						$context = new Sophie_Context();
						$context->setPersonContextLevel('none');
						$context->setProcessContextLevel('treatment');
						$context->setSession($newSession->toArray());

						$sandbox = new Sophie_Script_Sandbox();
						$sandbox->setContext($context);
						$sandbox->setLocalVars($context->getStdApis());
						$sandbox->setThrowOriginalException(true);

						try
						{
							Sophie_Eval_Error_Handler :: $context = $context;
							Sophie_Eval_Error_Handler :: $script = 'Session Setup Script';
							set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));
							$sandbox->run($treatment->setupScript);
							restore_error_handler();
						}
						catch (Exception $e)
						{
							$this->_error('Running session setup script failed. The session might be broken.');
							return;
						}
					}

					$db->commit();

					// Forward to session page
					$this->_helper->FlashMessenger('Session created');
					$this->_helper->getHelper('Redirector')->gotoRoute(array (
						'module' => 'expadmin',
						'controller' => 'session',
						'action' => 'details',
						'sessionId' => $id
					), 'default', true);
					return;
				}
				catch (Exception $e)
				{
					$db->rollBack();
					$this->_error('An error occured while initalizing the session: ' . $e->getMessage());
					return;
				}
			}
		}

		$this->view->sessiontype = $sessiontype->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Create Session',
			'small' => 'Session:',
			'name' => 'Create'
		);
	}

	public function adddynamic2Action()
	{
		$this->_helper->layout->disableLayout();
		if (!$this->_hasParam('treatmentId'))
		{
			$this->_error('Please select a treatment');
			return;
		}

		$treatmentModel = new Sophie_Db_Treatment();
		$treatmentId = $this->_getParam('treatmentId');
		$treatment = $treatmentModel->find($treatmentId)->current();
		if (is_null($treatment))
		{
			$this->_error('Selected treatment does not exist');
			return;
		}
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();
	}

	public function adddynamic3Action()
	{
		if (!$this->_hasParam('treatmentId'))
		{
			$this->_helper->FlashMessenger('Please select a treatment');
			$this->_forward('add');
			return;
		}

		$treatmentModel = new Sophie_Db_Treatment();
		$treatmentId = $this->_getParam('treatmentId');
		$treatment = $treatmentModel->find($treatmentId)->current();
		if (is_null($treatment))
		{
			$this->_helper->FlashMessenger('Select treatment does not exist');
			$this->_forward('add');
			return;
		}
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$form = $this->getForm('Session_Adddynamic3');
		$form->setAction($this->view->url());

		$formData = array (
			'ownerId' => Symbic_User_Session::getInstance()->getId()
		);
		$form->setDefaults($formData);

		if ($this->getRequest()->isPost())
		{
			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				if (isset($values['userAccess']))
				{
					$userAccess = $values['userAccess'];
					unset ($values['userAccess']);
				}
				else
				{
					$userAccess = array();
				}
				if (isset($values['usergroupAccess']))
				{
					$usergroupAccess = $values['usergroupAccess'];
					unset ($values['usergroupAccess']);
				}
				else
				{
					$usergroupAccess = array();
				}

				$id = Sophie_Db_Session :: getInstance()->insert(array (
					'treatmentId' => $treatment->id,
					'sessiontypeId' => new Zend_Db_Expr('NULL'),
					'participantMgmt' => 'dynamic',
					'name' => $values['name'],
					'description' => $values['description'],
					'ownerId' => $this->userId,
					'debugConsole' => $values['debugConsole'],
					'cacheTreatment' => $values['cacheTreatment'],
					'created' => new Zend_Db_Expr('NOW()'
				), 'state' => 'created'));


				//SessionLog ->Session created
				$newSession = Sophie_Db_Session :: getInstance()->find($id)->current();
				Sophie_Db_Session_Log :: log($id, 'Session created', null, print_r($newSession->toArray(), true));

				$acl = System_Acl :: getInstance();
				$acl->setAccessForRoles('session', $id, 'user', $userAccess);
				$acl->setAccessForRoles('session', $id, 'usergroup', $usergroupAccess);

				try
				{
					$sessionService = Sophie_Service_Session :: getInstance();
					//$sessionService->initParticipantsWithCode($id);

					////////////////////////////////////////////
					// import entity attribute values
					////////////////////////////////////////////
					$sessionEavTable = Sophie_Db_Session_Eav :: getInstance();
					$treatmentEavs = Sophie_Db_Treatment_Eav :: getInstance()->getAll($treatment->id);
					foreach ($treatmentEavs as $attribute => $value)
					{
						$sessionEavTable->replace(array('sessionId' => $id, 'name' => $attribute, 'value' => $value));
					}

					////////////////////////////////////////////
					// import variables
					////////////////////////////////////////////
					$sessionVariableTable = Sophie_Db_Session_Variable :: getInstance();

					// import treatment variables
					$treatmentVariables = Sophie_Db_Treatment_Variable::getInstance()->fetchAllByTreatmentId($treatment->id, array('groupLabel', 'participantLabel', 'stepgroupLabel', 'stepgroupLoop', 'name'));
					foreach ($treatmentVariables as $treatmentVariable)
					{
						$sessionVariableTable->setValueByNameAndContext($treatmentVariable['name'], $treatmentVariable['value'], $id, $treatmentVariable['groupLabel'], $treatmentVariable['participantLabel'], $treatmentVariable['stepgroupLabel'], $treatmentVariable['stepgroupLoop']);
					}

					////////////////////////////////////////////
					// import parameters
					////////////////////////////////////////////
					$sessionParameterTable = Sophie_Db_Session_Parameter :: getInstance();

					// import treatment parameters
					$treatmentParameters = Sophie_Db_Treatment_Parameter::getInstance()->fetchAllByTreatmentId($treatment->id);
					foreach ($treatmentParameters as $treatmentParameter)
					{
						$sessionParameterTable->replace(array('sessionId'=>$id, 'name'=>$treatmentParameter['name'], 'value'=>$treatmentParameter['value']));
					}

					////////////////////////////////////////////
					// run setup script
					////////////////////////////////////////////

					if (!empty($treatment->setupScript))
					{
						$context = new Sophie_Context();
						$context->setPersonContextLevel('none');
						$context->setProcessContextLevel('treatment');
						$context->setSession($newSession->toArray());

						$sandbox = new Sophie_Script_Sandbox();
						$sandbox->setContext($context);
						$sandbox->setLocalVars($context->getStdApis());
						$sandbox->setThrowOriginalException(true);

						try
						{
							Sophie_Eval_Error_Handler :: $context = $context;
							Sophie_Eval_Error_Handler :: $script = 'Session Setup Script';
							set_error_handler(array('Sophie_Eval_Error_Handler', 'errorHandler'));
							$sandbox->run($treatment->setupScript);
							restore_error_handler();
						}
						catch (Exception $e)
						{
							$this->_error('Running session setup script failed. The session might be broken.');
							return;
						}
					}

					// Forward to session page
					$this->_helper->FlashMessenger('Session created');
					$this->_helper->getHelper('Redirector')->gotoRoute(array (
						'module' => 'expadmin',
						'controller' => 'session',
						'action' => 'details',
						'sessionId' => $id
					), 'default', true);
					return;
				}
				catch (Exception $e)
				{
					$this->_error('An error occured while initalizing the session: ' . $e->getMessage());
					return;
				}
			}
		}

		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs[] = array (
			'title' => 'Create Session',
			'small' => 'Session:',
			'name' => 'Create'
		);
	}

	public function editAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		if ($session->sessiontypeId)
		{
			$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');
		}
		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		$form = $this->getForm('Session_Edit');
		$form->setAction($this->view->url());
		$formData = array ();
		$formData['name'] = $session->name;
		$formData['description'] = $session->description;
		$formData['ownerId'] = $session->ownerId;
		$formData['sessionId'] = $session->id;
		$form->setDefaults($formData);

		$acl = System_Acl :: getInstance();

		$userAccess = $acl->getAccessForRoleClass('session', $session->id, 'user');
		if ($userAccessElement = $form->getElement('userAccess'))
		{
			$userAccessElement->setValue($userAccess);
		}

		$usergroupAccess = $acl->getAccessForRoleClass('session', $session->id, 'usergroup');
		if ($usergroupAccessElement = $form->getElement('usergroupAccess'))
		{
			$usergroupAccessElement->setValue($usergroupAccess);
		}

		if ($this->getRequest()->isPost())
		{

			if ($form->isValid($_POST))
			{
				$values = $form->getValues();

				$userAccess = (isset($values['userAccess'])) ? $values['userAccess'] : array($this->userId => $this->userId);
				unset($values['userAccess']);
				$usergroupAccess = (isset($values['usergroupAccess'])) ? $values['usergroupAccess'] : array();
				unset($values['usergroupAccess']);

				unset ($values['sessionId']);

				$session->setFromArray($values);
				$session->save();

				$acl->setAccessForRoles('session', $session->id, 'user', $userAccess);
				$acl->setAccessForRoles('session', $session->id, 'usergroup', $usergroupAccess);

				$message = 'Session updated';

				$this->_helper->FlashMessenger($message);

				Sophie_Db_Session_Log :: log($session->id, $message, 'notice');

				$this->_helper->getHelper('Redirector')->gotoRoute(array (
					'module' => 'expadmin',
					'controller' => 'session',
					'action' => 'details',
					'sessionId' => $session->id
				), 'default', true);
				return;
			}
		}

		$sessionHeadName = $experiment->name . ': ' . $treatment->name;
		$this->view->session = $session->toArray();

		if ($session->sessiontypeId)
		{
			$sessionHeadName .= ': ' . $sessiontype->name;
			$this->view->sessiontype = $sessiontype->toArray();
		}

		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();
		$this->view->form = $form;

		$this->view->breadcrumbs['session'] = array (
			'sessionId' => $sessionId,
			'name' => $sessionHeadName
		);

		$this->view->breadcrumbs[] = array (
			'title' => 'Edit Session',
			'small' => 'Session:',
			'name' => 'Edit'
		);

	}

	public function detailsAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if (is_null($sessionId) || $sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}
		if ($session->state == 'deleted')
		{
			$this->_error('Selected session is deleted!');
			return;
		}
		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');

		if (!empty($session->sessiontypeId))
		{
			$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');
			$this->view->sessiontype = $sessiontype->toArray();
		}

		$this->view->session = $session->toArray();
		$this->view->treatment = $treatment->toArray();
		$this->view->experiment = $experiment->toArray();

		$name = $experiment->name . ': ' . $treatment->name;
		if (!empty($sessiontype->name))
		{
			$name .= ': ' . $sessiontype->name;
		}
		else
		{
			$name .= ': Dynamic Session';
		}
		$this->view->breadcrumbs['session'] = array (
			'sessionId' => $sessionId,
			'name' => $name
		);
	}

	public function detailsdataAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if (is_null($sessionId) || $sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}
		if ($session->state == 'deleted')
		{
			$this->_error('Selected session is deleted!');
			return;
		}
		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$treatment = $session->findParentRow('Sophie_Db_Treatment');
		$experiment = $treatment->findParentRow('Sophie_Db_Experiment');
		$stepgroups = $treatment->findDependentRowset('Sophie_Db_Treatment_Stepgroup', null, Sophie_Db_Treatment_Stepgroup::getInstance()->select()->order('position'));

		$steps = array();
		$stepTable = Sophie_Db_Treatment_Step::getInstance();
		foreach ($stepgroups as $stepgroup)
		{
			$stepgroupSteps = $stepTable->fetchAllByStepgroupIdJoinParticipantTypesAndSteptype($stepgroup->id);
			$steps = array_merge($steps, $stepgroupSteps);
		}

		// get participant types
		$types = Sophie_Db_Treatment_Type::getInstance()->fetchAll(Sophie_Db_Treatment_Type::getInstance()->select()->where('treatmentId = ?', $treatment->id));

		$data = array();

		$data['experiment']	= $experiment->toArray();
		$data['treatment'] = $treatment->toArray();
		if (!empty($session->sessiontypeId))
		{
			$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');
			$data['sessiontype'] = $sessiontype->toArray();
		}
		$data['stepgroups'] = $stepgroups->toArray();
		$data['steps'] = $steps;
		$data['types'] = $types->toArray();

		$data['session'] = $session->toArray();


		$this->_helper->json($data);
	}

	public function deleteAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if ($sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}
		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$sessionName = $session->name;

		try
		{
			//$session->delete();
			$session->state = 'deleted';
			$session->save();

			$message = 'Session ' . $sessionName . ' deleted';

			Sophie_Db_Session_Log :: log($session->id, $message, 'notice');
		}
		catch (Exception $e)
		{
			Sophie_Db_Session_Log :: log($session->id, 'error while deleting session', 'error', print_r($e, true));
		}
		$this->_helper->json(array('message'=>$message));
	}

	public function setstateAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if (is_null($sessionId) || $sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}
		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $this->session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		if (!$this->_hasParam('state'))
		{
			$this->_error('Missing state parameter!');
			return;
		}

		$state = $this->_getParam('state');
		if (!in_array($state, array (
				'running',
				'paused',
				'finished',
				'archived'
			)))
		{
			$this->_error('Invalid state parameter: ' . $state);
			return;
		}

		$this->session->state = $state;
		$this->session->save();

		$message = 'State set to ' . $state;

		Sophie_Db_Session_Log :: log($this->session->id, $message, 'notice');

		$this->_helper->json(array (
			'message' => $message
		));
	}

	// HELPER FUNCTION, MEANT FOR TESTING
	public function initializeAction()
	{
		if (!$this->_hasParam('sessionId'))
		{
			$this->_helper->FlashMessenger('Please select a session');
			$this->_forward('index');
			return;
		}

		$sessionId = $this->_getParam('sessionId');
		$session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($session))
		{
			$this->_helper->FlashMessenger('Please select a session');
			$this->_forward('index');
			return;
		}
		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$sessiontype = $session->findParentRow('Sophie_Db_Treatment_Sessiontype');

		if ($sessiontype->style == 'static')
		{
			$sessionService = Sophie_Service_Session :: getInstance();
			$sessionService->initParticipantsWithCode($session->id);
			$sessionService->initStaticGrouping($session->id, json_decode($sessiontype->groupDefinitionJson, true));
			//Exp_Service_Session_Variable_Import :: getInstance()->import($id, $this->treatment->initVariables);
		}
	}

	public function setAction()
	{
		$sessionId = $this->_getParam('sessionId', 0);
		if (is_null($sessionId) || $sessionId == 0)
		{
			$this->_error('Missing parameter');
			return;
		}

		$this->session = Sophie_Db_Session :: getInstance()->find($sessionId)->current();
		if (is_null($this->session))
		{
			$this->_error('Selected session does not exist!');
			return;
		}

		$acl = System_Acl :: getInstance();
		if (!$acl->autoCheckAcl('session',  $this->session->id, 'sophie_session'))
		{
			$this->_error('Access denied.');
			return;
		}

		$data = $this->getAllParams();

		$updates = array();
		foreach ($data as $dataKey => $dataValue)
		{
			switch ($dataKey)
			{
				case 'module':
				case 'controller':
				case 'action':
				case 'sessionId':
					break;

				case 'cacheTreatment':
					if ($dataValue !== '0' && $dataValue !== '1')
					{
						$this->_error('Invalid treatment cache setting: ' . $dataValue);
						return;
					}

					if ($dataValue == $this->session->cacheTreatment)
					{
						if ($dataValue === '0')
						{
							$this->_error('Session Treatment Cache is already set to inactive');
							return;
						}
						else
						{
							$this->_error('Session Treatment Cache is already set to active');
							return;
						}
					}

					$updates['cacheTreatment'] = $dataValue;

					if ($dataValue === '0')
					{
						$message = 'Session Treatment Cache deactivated';
					}
					else
					{
						$message = 'Session Treatment Cache activated';
					}
					break;

				case 'debugConsole':
					if ($dataValue !== '0' && $dataValue !== '1')
					{
						$this->_error('Invalid debug console setting: ' . $dataValue);
						return;
					}

					if ($dataValue == $this->session->debugConsole)
					{
						if ($dataValue === '0')
						{
							$this->_error('Debug Console is already inactive');
							return;
						}
						else
						{
							$this->_error('Debug Console is already active');
							return;
						}
					}

					$updates['debugConsole'] = $dataValue;

					if ($dataValue === '0')
					{
						$message = 'Debug Console deactivated';
					}
					else
					{
						$message = 'Debug Console activated';
					}
					break;

				case 'participantMgmt':
					if (!in_array($dataValue, array (
									'static',
									'dynamic',
								)))
					{
						$this->_error('Invalid participant mgmt console setting: ' . $dataValue);
						return;
					}

					if ($dataValue == $this->session->participantMgmt)
					{
						$this->_error('Session Participant Management is already ' . $dataValue);
						return;
					}

					$updates['participantMgmt'] = $dataValue;

					$message = 'Participant Management set to ' . $dataValue;
					break;

				default:
					$this->_error('Tried to set unknown session setting: ' . $dataKey . ' => ' . $dataValue);
					return;
			}

		}

		if (sizeof($updates) === 0)
		{
			$this->_error('Nothing to update');
			return;
		}

		$this->session->setFromArray($updates);
		$this->session->save();

		Sophie_Db_Session_Log :: log($this->session->id, $message, 'notice');

		$this->_helper->json(array (
			'message' => $message
		));
	}

	public function deletedAction()
	{
	}
}