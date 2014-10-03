<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Sync_Abstract_1_0_0');

class Sophie_Steptype_Sync_Admin_1_0_0_Steptype extends Sophie_Steptype_Sync_Abstract_1_0_0_Steptype
{

	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		/*
		// WARNING: syncnote MUST NOT be set by runtime initialization, because the
		// corresponding script is executed for participants and in PSL context only.
		// Therefore the dynamically set value won't be available in expadmin!
		$config['syncnote'] = array(
			'group' => 'Sync Note',
			'title' => 'Sync Note',
		);
		*/
		return $config;
	}

	public function runAdminProcess()
	{
		$sessionId = $this->getContext()->getSessionId();
		$stepId = $this->getContext()->getStepId();
		$stepLabel = $this->getContext()->getStepLabel();
		$stepgroupLabel = $this->getContext()->getStepgroupLabel();
		$stepgroupLoop = $this->getContext()->getStepgroupLoop();

		$processApi = $this->getContext()->getApi('process');

		if ($this->getContext()->getApi('variable')->getESL('__stepsync_' . $stepId) == 'sync')
		{
			$processApi->transferEveryoneToNextStep();
			Sophie_Db_Session_Log::log($sessionId, 'Sync step ' . $stepLabel . ' already sync');
			return;
		}

		if ($this->getContext()->getApi('variable')->getESL('__stepsyncRun_' . $stepId) == 'sync')
		{
			Sophie_Db_Session_Log :: log($sessionId, 'Trying to process admin sync step ' . $stepLabel);

			$syncScript = $this->getAttributeRuntimeValue('syncScript');
			if (!empty($syncScript))
			{
				$sandbox = new Sophie_Script_Sandbox();
				$sandbox->setContext($this->getContext());
				//$sandbox->setLocalVar('controller', $this->getController());
				$sandbox->setLocalVars($this->getContext()->getStdApis());

				$return = $sandbox->run($syncScript);

				$sandboxOutput = $sandbox->getEvalOutput();
				$sandbox->clearEvalOutput();
				if ($sandboxOutput != '')
				{
					if (strlen($sandboxOutput) > 100)
					{
						$sandboxOutputShort = substr($sandboxOutput, 0, 90) . '...';
					}
					else
					{
						$sandboxOutputShort = $sandboxOutput;
						$sandboxOutput = null;
					}
					Sophie_Db_Session_Log::log($sessionId, 'Sync script output for step ' . $stepLabel . ': ' . $sandboxOutputShort, null, $sandboxOutput);
				}

				if ($return === false)
				{
					Sophie_Db_Session_Log::log($sessionId, 'Sync script for step ' . $stepLabel . ' returned false, will not set sync', 'debug');
					return;
				}
			}

			Sophie_Db_Session_Log::log($sessionId, 'Set sync step ' . $stepLabel);
			$this->getContext()->getApi('variable')->setESL('__stepsync_' . $stepId, 'sync');

			$processApi->transferEveryoneToNextStep();
		}
	}

	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array ('id'=>'syncnote', 'title'=>'Sync Note', 'order'=>210);
		return $tabs;
	}

	public function adminSyncnoteTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('syncnote');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Sync Note',
				'dijitParams' => array(
					'title' => 'Sync Note',
				),
			));
			$form->addSubForm($subForm, 'syncnote');
		}

		$order = 0;

		$order += 100;

		$adminSyncNote = $subForm->createElement('CodemirrorTextarea', 'adminSyncNote', array('label'=>'Sync Note', 'trim'=>'true', 'order'=>$order), array());
		$adminSyncNote->setValue($this->getAttributeValue('adminSyncNote'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'syncnote-ContentPane\'), \'onShow\', function() { ' . $adminSyncNote->getJsInstance() . '.refresh(); } );');

		$order += 100;

		$submit = $subForm->createElement('submit', 'syncnoteSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($adminSyncNote, $submit));
	}

	public function adminSyncnoteTabProcess($parameters)
	{
		$this->setAttributeValue('adminSyncNote', $parameters['adminSyncNote']);
	}

}