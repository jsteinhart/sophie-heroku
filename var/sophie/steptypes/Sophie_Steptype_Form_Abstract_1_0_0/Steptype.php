<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

abstract class Sophie_Steptype_Form_Abstract_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{

	public $_processMessage = array();
	public $_processValues = array();
	public $_processFaultyValue = null;

	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['formHeadline'] = array(
			'group' => 'Form',
			'title' => 'Form Headline'
		);
		$config['formLabel'] = array(
			'group' => 'Form',
			'title' => 'Field Label'
		);
		$config['formDefaultValue'] = array(
			'group' => 'Form',
			'title' => 'Field Default Value'
		);
		$config['formAutofocus'] = array(
			'group' => 'Form',
			'title' => 'Autofocus',
			'validatorRegExp' => '/(1|0)/'
		);
		$config['formContentBefore'] = array(
			'group' => 'Form',
			'title' => 'Content Before Form',
		);
		$config['formContentBetween'] = array(
			'group' => 'Form',
			'title' => 'Content Between Form',
		);
		$config['formContentAfter'] = array(
			'group' => 'Form',
			'title' => 'Content After Form',
		);
		return $config;
	}

	public function adminSetDefaultValues()
	{
		$this->setAttributeValue('formVariableName', 'input');
		$this->setAttributeValue('formVariableContext', 'PSL');
		$this->setAttributeValue('formNotEmpty', '1');
		$this->setAttributeValue('formTrimWhitespace', '1');
		$this->setAttributeValue('formAutofocus', '1');
	}

	public function renderFormContent()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();

		$content = '';

		$formType = $this->getAttributeRuntimeValue('formType');
		if (is_null($formType) || $formType == '')
		{
			$formType = 'text';
		}
		$formTypeHelper = 'form' . ucfirst($formType);

		$formDefaultValue = $this->getAttributeRuntimeValue('formDefaultValue');
		if (!is_null($this->_processFaultyValue))
		{
			$formDefaultValue = $this->_processFaultyValue;
		}
		$formFieldAttributesJson = $this->getAttributeRuntimeValue('formFieldAttributesJson');
		if (!empty($formFieldAttributesJson))
		{
			$formFieldAttributes = Zend_Json::decode($formFieldAttributesJson, true);
		}
		else
		{
			$formFieldAttributes = array();
		}

		if (!array_key_exists('id', $formFieldAttributes))
		{
			$formFieldAttributes['id'] = 'form_field';
		}

		$formMaxLength = $this->getAttributeRuntimeValue('formMaxLength');
		if ($formMaxLength !== '')
		{
			$formFieldAttributes['maxlength'] = $formMaxLength;
		}

		if ($formType == 'textarea')
		{
			$formTextareaRows = $this->getAttributeRuntimeValue('formTextareaRows');
			$formTextareaCols = $this->getAttributeRuntimeValue('formTextareaCols');
			if ($formTextareaRows != '')
			{
				$formFieldAttributes['rows'] = $formTextareaRows;
			}
			if ($formTextareaCols != '')
			{
				$formFieldAttributes['cols'] = $formTextareaCols;
			}
		}

		$formShowLength = $this->getAttributeRuntimeValue('formShowLength');
		if ($formMaxLength !== '' && $formShowLength !== '')
		{
			$maxlengthOptions = array(
				'alwaysShow' => ($formShowLength === 'always'),
				'threshold' => max(10, ceil($formMaxLength * .1)),
				'message' => $this->getTranslate()->_('%charsTyped% / %charsTotal%')
			);
			$formFieldAttributes['showMaxlength'] = $maxlengthOptions;
		}

		$formOptionsJson = $this->getAttributeRuntimeValue('formOptionsJson');
		if (!empty($formOptionsJson))
		{
			$formOptions = Zend_Json::decode($formOptionsJson, true);
			if ($formType == 'radio')
			{
				$formRadioSeparator = $this->getAttributeRuntimeValue('formRadioSeparator');
				if ($formRadioSeparator == '')
				{
					$formRadioSeparator = '<br />';
				}
				$content .= $view->$formTypeHelper('form_field', $formDefaultValue, $formFieldAttributes, $formOptions, $formRadioSeparator);
			}
			elseif ($formType == 'radioList')
			{
				$content .= $view->$formTypeHelper('form_field', $formDefaultValue, $formFieldAttributes, $formOptions);
			}
			else
			{
				$content .= $view->$formTypeHelper('form_field', $formDefaultValue, $formFieldAttributes, $formOptions);
			}
		}
		else
		{
			$content .= $view->$formTypeHelper('form_field', $formDefaultValue, $formFieldAttributes);
		}
		return $content;
	}

	public function renderForm()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();

		$content = '<form action="' . $this->getFrontUrl() . '" method="POST"  id="formStepAction" name="stepaction" autocomplete="off">';
		$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());

		// new div for old layout
		$content .= '<div id="caction">';

			$formHeadline = $stepRender->render($this->getAttributeRuntimeValue('formHeadline'));
			if ($formHeadline != '')
			{
				$content .= '<div class="cactionhead">' . $formHeadline . '</div>';
			}

			$content .= '<div class="cactionform">';

				// RENDER CONTENT BEFORE
				$formContentBefore = $stepRender->render($this->getAttributeRuntimeValue('formContentBefore'));
				if ($formContentBefore != '')
				{
					$content .= $formContentBefore;
				}

				// RENDER ERROR MESSAGES
				if (is_string($this->_processMessage) && $this->_processMessage != '')
				{
					$content .= '<div class="formError">' . $this->_processMessage . '</div>';
				}
				elseif (is_array($this->_processMessage) && sizeof($this->_processMessage) > 0)
				{
					$content .= '<div class="formError"><ul><li>' . implode('</li><li>', $this->_processMessage) .	'</li></ul></div>';
				}

				// RENDER FORM LABEL
				$formLabel = $this->getAttributeRuntimeValue('formLabel');
				if ($formLabel != '')
				{
					$content .= '<label class="formLabel" for="form_field">' . $formLabel . '</label> ';
				}

				// RENDER CONTENT BETWEEN
				$formContentBetween = $stepRender->render($this->getAttributeRuntimeValue('formContentBetween'));
				if ($formContentBetween != '')
				{
					$content .= $formContentBetween;
				}

				// RENDER FORM CONTENT
				$content .= $this->renderFormContent();

				// RENDER CONTENT AFTER
				$formContentAfter = $stepRender->render($this->getAttributeRuntimeValue('formContentAfter'));
				if ($formContentAfter!='')
				{
					$content .= $formContentAfter;
				}

				// RENDER SUBMIT BUTTON
				$content .= ' <input name="NextStep" type="submit" value="' . $translate->_('Submit ...') . '">';
			$content .= '</div>';

		$content .= '</div>';
		$content .= '</form>';

		$formAutofocus = $this->getAttributeRuntimeValue('formAutofocus');
		if ($formAutofocus == '1')
		{
			$this->view->jsOnLoad()->appendScript('var forms = document.forms || [];
			for(var i = 0; i < forms.length; i++){
				for(var j = 0; j < forms[i].length; j++){
					if(!forms[i][j].readonly != undefined && forms[i][j].type != "hidden" && forms[i][j].disabled != true && forms[i][j].style.display != \'none\'){
						forms[i][j].focus();
						return;
					}
				}
			}');
		}

		return $content;
	}

	public function process()
	{
		return parent::process();
	}

	///////////////////////////////////////

	public function adminGetTabs()
	{
		$tabs = parent::adminGetTabs();
		$tabs[] = array('id'=>'form', 'title'=>'Form', 'order'=>200);
		$tabs[] = array('id'=>'processing', 'title'=>'Processing', 'order'=>300);
		return $tabs;
	}

	public function adminFormTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('form');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Form',
				'dijitParams' => array(
					'title' => 'Form',
				),
			));
			$form->addSubForm($subForm, 'form');
		}

		$order = 100;
		$formHeadline = $subForm->createElement('TextInput', 'formHeadline', array('label'=>'Form Headline', 'trim'=>'true', 'order'=>$order), array());
		$formHeadline->setValue($this->getAttributeValue('formHeadline'));

		$order += 100;
		$formLabel = $subForm->createElement('TextInput', 'formLabel', array('label'=>'Field Label', 'trim'=>'true', 'order'=>$order), array());
		$formLabel->setValue($this->getAttributeValue('formLabel'));

		$order += 100;
		$formDefaultValue = $subForm->createElement('TextInput', 'formDefaultValue', array('label'=>'Field Default Value', 'trim'=>'true', 'order'=>$order), array());
		$formDefaultValue->setValue($this->getAttributeValue('formDefaultValue'));

		$order += 100;
		$formAutofocus = $subForm->createElement('Checkbox', 'formAutofocus', array('label'=>'Autofocus', 'trim'=>'true', 'order'=>$order), array());
		$formAutofocus->setValue($this->getAttributeValue('formAutofocus'));

		$order += 100;
		$formContentBefore = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentBefore', array('label'=>'Content Before Form', 'trim'=>'true', 'order'=>$order), array());
		$formContentBefore->setValue($this->getAttributeValue('formContentBefore'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentBefore->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$formContentBetween = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentBetween', array('label'=>'Content Between Label and Field', 'trim'=>'true', 'order'=>$order), array());
		$formContentBetween->setValue($this->getAttributeValue('formContentBetween'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentBetween->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$formContentAfter = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentAfter', array('label'=>'Content After Form', 'trim'=>'true', 'order'=>$order), array());
		$formContentAfter->setValue($this->getAttributeValue('formContentAfter'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentAfter->getJsInstance() . '.refresh(); } );');

		/////////////
		$order += 100;

		$formTabsValid = $subForm->createElement('StaticHtml', 'formTabsValidator',
		 array('label'=>'',
				'trim'=>'true',
			   'order'=> $order), array());

		$formTabsSanitizerCheck = true;

		try
		{
			$sanitizer = $this->getPhpSanitizer();
			$formTabsSanitizerCheck = $sanitizer->isValid($this->getAttributeValue('formContentHeadline'));
			$formTabsSanitizerCheck = $sanitizer->isValid($this->getAttributeValue('formContentBefore')) && $formTabsSanitizerCheck;
			$formTabsSanitizerCheck = $sanitizer->isValid($this->getAttributeValue('formContentBetween')) && $formTabsSanitizerCheck;
			$formTabsSanitizerCheck = $sanitizer->isValid($this->getAttributeValue('formContentAfter')) && $formTabsSanitizerCheck;
		}
		catch(Exception $e)
		{
			$formTabsSanitizerCheck = false;
		}
		$formTabsValidContent = '';

		if (!$formTabsSanitizerCheck)
		{
			$formTabsValidContent .= '<div class="error"><strong>Sanitizer Warning</strong><br />';
			$formTabsValidContent .= nl2br($view->escape(implode(', ', $sanitizer->getMessages()))) . '</div>';
		}
		$formTabsValid->setValue($formTabsValidContent);
		/////////////

		$order += 100;
		$submit = $subForm->createElement('submit', 'formSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));

		$subForm->addElements(array($formLabel, $formDefaultValue, $formAutofocus, $formHeadline, $formContentBefore, $formContentBetween, $formContentAfter, $formTabsValid, $submit));
	}

	public function adminFormTabProcess($parameters)
	{
		$this->setAttributeValue('formHeadline', $parameters['formHeadline']);
		$this->setAttributeValue('formContentBefore', $parameters['formContentBefore']);
		$this->setAttributeValue('formContentBetween', $parameters['formContentBetween']);
		$this->setAttributeValue('formContentAfter', $parameters['formContentAfter']);

		$this->setAttributeValue('formLabel', $parameters['formLabel']);
		$this->setAttributeValue('formDefaultValue', $parameters['formDefaultValue']);

		$this->setAttributeValue('formAutofocus', $parameters['formAutofocus']);
	}

	abstract public function adminProcessingTabInit();

	abstract public function adminProcessingTabProcess($parameters);

}