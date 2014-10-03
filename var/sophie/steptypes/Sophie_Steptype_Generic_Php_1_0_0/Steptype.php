<?php
class Sophie_Steptype_Generic_Php_1_0_0_Steptype extends Sophie_Steptype_Abstract
{
	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		// TODO: add steptype attribute configuration
		return $config;
	}

	public function render()
	{
		$stepRender = $this->getStepRenderer();
		return $stepRender->render($this->getAttributeRuntimeValue('contentBody'));
	}

	public function process()
	{
		$stepId = $this->getContext()->getStepId();
		$sessionId = $this->getContext()->getSessionId();
		$participantLabel = $this->getContext()->getParticipantLabel();

		$processScript = $this->getAttributeRuntimeValue('processScript');
		if (!empty ($processScript))
		{
			$sandbox = $this->getScriptSandbox();
			$return = $sandbox->run($processScript);

			$sandboxOutput = $sandbox->getEvalOutput();
			$sandbox->clearEvalOutput();
			if ($sandboxOutput != '')
			{
				Sophie_Db_Session_Log :: log($sessionId, 'Process script output for step ' . $stepId . ' and participant ' . $participantLabel . ': ' . $sandboxOutput);
			}

			if ($return === false)
			{
				// TODO: transfer postDefinedLocalVar to view object?
				return false;
			}
		}

		$processApi = $this->getContext()->getApi('process');
		$processApi->transferParticipantToNextStep();
		return true;
	}

	public function ajaxProcess()
	{
		$stepId = $this->getContext()->getStepId();
		$sessionId = $this->getContext()->getSessionId();
		$participantLabel = $this->getContext()->getParticipantLabel();

		$processScript = $this->getAttributeRuntimeValue('ajaxProcessScript');
		if (!empty ($processScript))
		{
			$sandbox = $this->getScriptSandbox();

			$return = $sandbox->run($processScript);

			$sandboxOutput = $sandbox->getEvalOutput();
			$sandbox->clearEvalOutput();
			if ($sandboxOutput != '')
			{
				Sophie_Db_Session_Log :: log($sessionId, 'Ajax Process script output for step ' . $stepId . ' and participant ' . $participantLabel . ': ' . $sandboxOutput);
			}

			return $return;
		}
		return array (
			'error' => 'No ajax process defined.'
		);
	}

	///////////////////////////////////////////////////////////////////

	public function adminSetDefaultValues()
	{
		$attributes = array('contentBody', 'processScript', 'ajaxProcessScript');
		foreach ($attributes as $attribute)
		{
			$filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . $attribute . '.default';
			if (!file_exists($filename))
			{
				continue;
			}
			$this->setAttributeValue($attribute, file_get_contents($filename));
		}
	}

	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array ('id'=>'content', 'title'=>'Content', 'order'=>100);
		$tabs[] = array('id'=>'process', 'title'=>'Process', 'order'=>200);
		$tabs[] = array('id'=>'ajaxProcess', 'title'=>'Ajax Process', 'order'=>300);
		return $tabs;
	}

	public function adminContentTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('form');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Content',
				'dijitParams' => array(
					'title' => 'Content',
				),
			));
			$form->addSubForm($subForm, 'content');
		}

		$order = 100;

		$contentBodyValue = $this->getAttributeValue('contentBody');
		$elementOptions = array('label'=>'Content', 'trim'=>'true', 'order'=>$order, 'toolbar' => new Sophie_Toolbar_CodeMirror_Html($this));
		$contentBody = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'contentBody', $elementOptions, array());
		$contentBody->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $contentBody->getJsInstance() . '.getValue(), \'contentBodySanitizerMessages\', \'html\');');
		$contentBody->setValue($contentBodyValue);

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'content-ContentPane\'), \'onShow\', function() { ' . $contentBody->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$contentBodyValid = $subForm->createElement('StaticHtml', 'contentBodyValidator', array('label'=>'', 'trim'=>'true', 'order'=>$order), array());
		$contentBodyValidContent = '';

		$contentBodySanitizerCheck = true;
		try {
			$sanitizer = $this->getPhpSanitizer();
			$contentBodySanitizerCheck = $sanitizer->isValid($this->getAttributeValue('contentBody'));
		}
		catch(Exception $e)
		{
			$contentBodySanitizerCheck = false;
		}

		$contentBodyValidContent = '<div id="contentBodySanitizerMessages" class="alert alert-danger"';
		if (!$contentBodySanitizerCheck)
		{
			$contentBodyValidContent .= '>';
			$contentBodyValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$contentBodyValidContent .= nl2br($view->escape(implode(', ', $sanitizer->getMessages())));
		}
		else
		{
			$contentBodyValidContent .= ' style="display:none;">';
		}
		$contentBodyValidContent .= '</div>';
		$contentBodyValid->setValue($contentBodyValidContent);

		$order += 100;
		$submit = $subForm->createElement('submit', 'formSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($contentBody, $contentBodyValid, $submit));
	}

	public function adminContentTabProcess($parameters)
	{
		$this->setAttributeValue('contentBody', $parameters['contentBody']);
	}

	public function adminProcessTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('process');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Process',
				'dijitParams' => array(
					'title' => 'Process',
				),
			));
			$form->addSubForm($subForm, 'process');
		}

		$order = 100;

		$processScript = $subForm->createElement('CodemirrorTextarea', 'processScript', array (
			'label' => 'Process Script',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Php($this),
		), array ());
		$processScript->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $processScript->getJsInstance() . '.getValue(), \'processScriptSanitizerMessages\', \'php\');');
		$processScript->setValue($this->getAttributeValue('processScript'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'process-ContentPane\'), \'onShow\', function() { ' . $processScript->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$processScriptValid = $subForm->createElement('StaticHtml', 'processScriptValidator', array (
			'label' => '',
			'trim' => 'true',
			'order' => $order
		), array ());

		$processScriptSanitizerCheck = true;
		try {
			$sanitizer = $this->getPhpSanitizer();
			$processScriptSanitizerCheck = $sanitizer->isValid('<?php ' . $this->getAttributeValue('processScript') . '?>');
		}
		catch(Exception $e)
		{
			$processScriptSanitizerCheck = false;
		}

		$processScriptValidContent = '<div id="processScriptSanitizerMessages" class="alert alert-danger"';
		if (!$processScriptSanitizerCheck)
		{
			$processScriptValidContent .= '>';
			$processScriptValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$processScriptValidContent .= nl2br($view->escape(implode("\n", $sanitizer->getMessages())));
		}
		else
		{
			$processScriptValidContent .= ' style="display:none;">';
		}
		$processScriptValidContent .= '</div>';
		$processScriptValid->setValue($processScriptValidContent);


		$order += 100;
		$submit = $subForm->createElement('submit', 'processSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($processScript, $processScriptValid, $submit));
	}

	public function adminProcessTabProcess($parameters)
	{
		$this->setAttributeValue('processScript', $parameters['processScript']);
	}

	public function adminAjaxProcessTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('ajaxProcess');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Ajax Process',
				'dijitParams' => array(
					'title' => 'Ajax Process',
				),
			));
			$form->addSubForm($subForm, 'ajaxProcess');
		}

		$order = 100;

		$ajaxProcessScript = $subForm->createElement('CodemirrorTextarea', 'ajaxProcessScript', array (
			'label' => 'Ajax Process Script',
			'order' => $order,
			'toolbar' => new Sophie_Toolbar_CodeMirror_Php($this),
		), array ());
		$ajaxProcessScript->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $ajaxProcessScript->getJsInstance() . '.getValue(), \'ajaxProcessScriptSanitizerMessages\', \'php\');');
		$ajaxProcessScript->setValue($this->getAttributeValue('ajaxProcessScript'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'ajaxProcess-ContentPane\'), \'onShow\', function() { ' . $ajaxProcessScript->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$ajaxProcessScriptValid = $subForm->createElement('StaticHtml', 'ajaxProcessScriptValidator', array (
			'label' => '',
			'trim' => 'true',
			'order' => $order
		), array ());


		$ajaxProcessScriptSanitizerCheck = true;
		try {
			$sanitizer = $this->getPhpSanitizer();
			$ajaxProcessScriptSanitizerCheck = $sanitizer->isValid('<?php ' . $this->getAttributeValue('ajaxProcessScript') . ' ?>');
		}
		catch(Exception $e)
		{
			$ajaxProcessScriptSanitizerCheck = false;
		}

		$ajaxProcessScriptValidContent = '<div id="ajaxProcessScriptSanitizerMessages" class="alert alert-danger"';
		if (!$ajaxProcessScriptSanitizerCheck)
		{
			$ajaxProcessScriptValidContent .= '>';
			$ajaxProcessScriptValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$ajaxProcessScriptValidContent .= nl2br($view->escape(implode("\n", $sanitizer->getMessages())));
		}
		else
		{
			$ajaxProcessScriptValidContent .= ' style="display:none;">';
		}
		$ajaxProcessScriptValidContent .= '</div>';
		$ajaxProcessScriptValid->setValue($ajaxProcessScriptValidContent);


		$order += 100;
		$submit = $subForm->createElement('submit', 'ajaxProcessSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($ajaxProcessScript, $ajaxProcessScriptValid, $submit));
	}

	public function adminAjaxProcessTabProcess($parameters)
	{
		$this->setAttributeValue('ajaxProcessScript', $parameters['ajaxProcessScript']);
	}

}