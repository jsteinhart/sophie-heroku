<?php
class Sophie_Steptype_Info_1_0_0_Steptype extends Sophie_Steptype_Abstract
{
	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['contentHeadline'] = array(
			'group' => 'Content',
			'title' => 'Headline',
		);
		$config['contentBody'] = array(
			'group' => 'Content',
			'title' => 'Body',
		);
		return $config;
	}

	public function renderContent()
	{
		$stepRender = $this->getStepRenderer();

		$contentHeadline = $stepRender->render($this->getAttributeRuntimeValue('contentHeadline'));
		$contentBody = $stepRender->render($this->getAttributeRuntimeValue('contentBody'));

		$content = '';

		if ($contentHeadline != '' || $contentBody != '')
		{
			// assemble content
			$content .= '<div id="cheader">';
				if ($contentHeadline != '')
				{
					$content .= '<div class="cheadline">' . $contentHeadline . '</div>';
				}
				if ($contentBody != '')
				{
					$content .= '<div class="cheadtext">';
						$content .= $contentBody;
					$content .= '</div>';
				}
			$content .= '</div>';
		}

		return $content;
	}

	public function renderForm()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();
		$content = '<form action="' . $this->getFrontUrl() . '" method="POST" id="formStepAction" name="stepaction">';
		$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());

		// new div for old layout
		$content .= '<div id="caction">';
			//$content .= '<div class="cactionhead"></div>';
			$content .= '<div class="cactionform">';
				$content .= '<input name="NextStep" type="submit" value="' . $translate->_('Continue ...') . '">';
			$content .= '</div>';
		$content .= '</div>';
		$content .= '</form>';

		return $content;
	}

	public function render()
	{
		$content = $this->renderContent();
		$content .= $this->renderForm();
		return $content;
	}

	///////////////////////////////////////////////////////////////////

	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array ('id'=>'content', 'title'=>'Content', 'order'=>100);
		return $tabs;
	}

	public function adminContentTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('content');
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
		
		$contentHeadline = $subForm->createElement('TextInput', 'contentHeadline', array('label'=>'Headline', 'trim'=>'true', 'order'=>$order), array());
		$contentHeadline->setValue($this->getAttributeValue('contentHeadline'));

		$order += 100;

		$contentBody = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'contentBody', array(
				'label' => 'Body',
				'trim' => 'true',
				'order' => $order,
				'toolbar' => new Sophie_Toolbar_CodeMirror_Html($this)
			)
		);
		$contentBody->setAttrib('onchange', 'expdesigner.updateStepCodeSanitizerResults(' . $this->getContext()->getStepId() . ', ' . $contentBody->getJsInstance() . '.getValue(), \'contentBodySanitizerMessages\', \'html\');');
		$contentBody->setValue($this->getAttributeValue('contentBody'));

		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'content-ContentPane\'), \'onShow\', function() { ' . $contentBody->getJsInstance() . '.refresh(); } );');

/*
		$order += 100;
		$contentBodyAjaxValid = $subForm->createElement('ContentCheck', 'contentBodyAjaxValid', array('label'=>'', 'trim'=>'true', 'order'=>$order), array());
*/

		$order += 100;

		/////////////
		$contentBodyValid = $subForm->createElement('StaticHtml', 'contentBodyValidator', array('label'=>'', 'trim'=>'true', 'order'=>$order), array());

		$contentBodySanitizerCheck = true;
		try
		{
			$sanitizer = $this->getPhpSanitizer();
			$contentBodySanitizerCheck = $sanitizer->isValid($this->getAttributeValue('contentHeadline'));
			$contentBodySanitizerCheck = $sanitizer->isValid($this->getAttributeValue('contentBody')) && $contentBodySanitizerCheck;
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
		/////////////

		$order += 100;

		$submit = $subForm->createElement('submit', 'contentSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($contentHeadline, $contentBody, $contentBodyValid, $submit));
	}

	public function adminContentTabProcess($parameters)
	{
		$this->setAttributeValue('contentHeadline', $parameters['contentHeadline']);
		$this->setAttributeValue('contentBody', $parameters['contentBody']);
	}

}