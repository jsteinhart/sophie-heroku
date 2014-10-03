<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

abstract class Sophie_Steptype_Sync_Abstract_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{
	public function __construct()
	{
		parent::__construct();
		$this->options[ self :: TIMER ][ self :: ENABLED ] = false;
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		// TODO: add steptype attribute configuration
		return $config;
	}

	public function renderContent()
	{
		if ($this->getAttributeRuntimeValue('contentHeadline') != '' || $this->getAttributeRuntimeValue('contentBody') != '')
		{
			return parent::renderContent();
		}

		$view = $this->getView();
		$content = '';

		// assemble content
		$content .= '<div id="cheader" style="padding: 200px; text-align: center;">';
			$content .= '<div class="cheadline"></div>';
			$content .= '<div class="cheadtext">';
				$content .= '<img src="/_media/ajax-loader.gif" />';
			$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public function renderForm()
	{
		$view = $this->getView();

		//$view->headScript()->appendScript("function ReloadSync ()\n{\n  window.location.href='" . $this->getFrontUrl() . "';\n}\nwindow.setTimeout('ReloadSync()', 2000);");
		return '';
	}

	public function process()
	{
		return false;
	}

	/////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////

	public function adminGetTabs()
	{
		$tabs = parent::adminGetTabs();
		$tabs[] = array ('id'=>'script', 'title'=>'Script', 'order'=>200);
		return $tabs;
	}

	public function adminScriptTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('script');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Script',
				'dijitParams' => array(
					'title' => 'Script',
				),
			));
			$form->addSubForm($subForm, 'script');
		}

		$order = 100;
		$syncScript = $subForm->createElement('CodemirrorTextarea', 'syncScript',
			array(
				'label'=>'Sync Script',
				'trim'=>'true',
				'order'=>$order,
				'toolbar' => new Sophie_Toolbar_CodeMirror_Php($this),
			), array());
		$syncScript->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $syncScript->getJsInstance() . '.getValue(), \'syncScriptSanitizerMessages\', \'html\');');
		$syncScript->setValue($this->getAttributeValue('syncScript'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'script-ContentPane\'), \'onShow\', function() { ' . $syncScript->getJsInstance() . '.refresh(); } );');

		$order += 100;

		$syncScriptValid = $subForm->createElement('StaticHtml', 'syncScriptValidator', array('label'=>'', 'trim'=>'true', 'order'=>$order), array());

		$syncScriptSanitizerCheck = true;
		try
		{
			$sanitizer = $this->getPhpSanitizer();
			$syncScriptSanitizerCheck = $sanitizer->isValid('<?php ' . $this->getAttributeValue('syncScript') . ' ?>');
		}
		catch(Exception $e)
		{
			$syncScriptSanitizerCheck = false;
		}

		$syncScriptValidContent = '<div id="syncScriptSanitizerMessages" class="error"';
		if (!$syncScriptSanitizerCheck)
		{
			$syncScriptValidContent .= '>';
			$syncScriptValidContent .= '<strong>Sanitizer Warning</strong><br />';
			$syncScriptValidContent .= nl2br($view->escape(implode(', ', $sanitizer->getMessages())));
		}
		else
		{
			$syncScriptValidContent .= ' style="display:none;">';
		}
		$syncScriptValidContent .= '</div>';
		$syncScriptValid->setValue($syncScriptValidContent);

		$order += 100;

		$submit = $subForm->createElement('submit', 'scriptSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($syncScript, $syncScriptValid, $submit));
	}

	public function adminScriptTabProcess($parameters)
	{
		$this->setAttributeValue('syncScript', $parameters['syncScript']);
	}
}