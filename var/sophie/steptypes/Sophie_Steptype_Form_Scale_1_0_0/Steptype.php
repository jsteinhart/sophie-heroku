<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Form_Abstract_1_0_0');

class Sophie_Steptype_Form_Scale_1_0_0_Steptype extends Sophie_Steptype_Form_Abstract_1_0_0_Steptype
{
	public function __construct()
	{
		parent :: __construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		// TODO: add steptype attribute configuration
		return $config;
	}

	public function adminSetDefaultValues()
	{
		parent :: adminSetDefaultValues();

		$this->setAttributeValue('formType', 'scale');
		$this->setAttributeValue('formVariableName', 'scale');
		$this->setAttributeValue('formScaleFrom', '1');
		$this->setAttributeValue('formScaleTo', '7');
		$this->setAttributeValue('formScaleStepsize', '1');
	}

	public function getAnswers()
	{
		$scaleFrom = (int)$this->getAttributeRuntimeValue('formScaleFrom');
		$scaleTo = (int)$this->getAttributeRuntimeValue('formScaleTo');
		if ($scaleTo < $scaleFrom)
		{
			$scaleTo = $scaleFrom;
		}
		$scaleStepsize = (int)$this->getAttributeRuntimeValue('formScaleStepsize');

		$answers = array ();

		for ($i = $scaleFrom; $i <= $scaleTo; $i += $scaleStepsize)
		{
			$answers[$i] = '';
		}
		return $answers;
	}

	public function renderForm()
	{
		$view = $this->getView();
		$translate = $this->getTranslate();
		$stepRender = $this->getStepRenderer();

		$content = '<form action="' . $this->getFrontUrl() . '" method="POST"  id="formStepAction" name="stepaction">';
		$content .= $view->formHidden('contextChecksum', $this->getContext()->getChecksum());

		// new div for old layout
		$content .= '<div id="caction">';

		$formHeadline = $stepRender->render($this->getAttributeRuntimeValue('formHeadline'));
		if ($formHeadline != '')
		{
			$content .= '<div class="cactionhead">' . $formHeadline . '</div>';
		}

		$content .= '<div class="cactionform">';

		// CONTENT BEFORE
		$formContentBefore = $stepRender->render($this->getAttributeRuntimeValue('formContentBefore'));
		if ($formContentBefore != '')
		{
			$content .= $formContentBefore;
		}

		if (is_string($this->_processMessage) && $this->_processMessage != '')
		{
			$content .= '<div class="formError">' . $this->_processMessage . '</div>';
		}
		elseif (is_array($this->_processMessage) && sizeof($this->_processMessage) > 0)
		{
			$content .= '<div class="formError"><ul><li>' . implode('</li><li>', $this->_processMessage) . '</li></ul></div>';
		}

		// LABEL
		$formLabel = $this->getAttributeRuntimeValue('formLabel');
		if ($formLabel != '')
		{
			$content .= '<label class="formLabel" for="form_field">' . $this->getAttributeRuntimeValue('formLabel') . '</label> ';
		}

		// CONTENT BETWEEN
		$formContentBetween = $stepRender->render($this->getAttributeRuntimeValue('formContentBetween'));
		if ($formContentBetween != '')
		{
			$content .= $formContentBetween;
		}

		///// SCALE

		$scaleFrom = $this->getAttributeRuntimeValue('formScaleFrom');
		$scaleTo = $this->getAttributeRuntimeValue('formScaleTo');
		$scaleStepsize = $this->getAttributeRuntimeValue('formScaleStepsize');
		$scaleShow = $this->getAttributeRuntimeValue('formScaleShow');
		$scaleCaptionLeft = $this->getAttributeRuntimeValue('formScaleCaptionLeft');
		$scaleCaptionRight = $this->getAttributeRuntimeValue('formScaleCaptionRight');
		$scaleCaptionCenter = $this->getAttributeRuntimeValue('formScaleCaptionCenter');
		$scaleCaptionPosition = $this->getAttributeRuntimeValue('formScaleCaptionPosition');
		$scaleWidth = $this->getAttributeRuntimeValue('formScaleWidth');

		$steps = floor(($scaleTo - $scaleFrom +1) / $scaleStepsize);
		if ($scaleCaptionPosition == 'onSides')
		{
			$steps += 2;
		}
		$tdStepSite = 100 / $steps;

		$content .= '<table';
		if ($scaleWidth != '')
			$content .= ' style="width:' . $scaleWidth . '"';
		$content .= '>';
		$content .= '<tr>';

		if ($scaleCaptionPosition == 'onSides')
		{
			$content .= '<td><center>' . $scaleCaptionLeft . '</center></td>';
		}

		$content .= '<td width="' . $tdStepSite . '%" align="center">' . $view->formRadio('form_field', $this->getAttributeRuntimeValue('formDefaultValue'), array (), $this->getAnswers(), '</td><td width="' . $tdStepSite . '%" align="center">') . '</td>';

		if ($scaleCaptionPosition == 'onSides')
		{
			$content .= '<td><center>' . $scaleCaptionRight . '</center></td>';
		}

		$content .= '</tr>';

		if ($scaleShow == 1)
		{
			$content .= '<tr>';

			if ($scaleCaptionPosition == 'onSides')
			{
				$content .= '<td></td>';
			}

			for ($i = $scaleFrom; $i <= $scaleTo; $i += $scaleStepsize)
			{
				$content .= '<td><center>' . $i . '</center></td>';
			}

			if ($scaleCaptionPosition == 'onSides')
			{
				$content .= '<td></td>';
			}

			$content .= '</tr>';
		}

		if ($scaleCaptionPosition == 'below')
		{
			$content .= '<tr>';
			$content .= '<td><center>' . $scaleCaptionLeft . '</center></td>';
			$centerCaptionSet = false;
			for ($i = ($scaleFrom + $scaleStepsize); $i <= ($scaleTo - $scaleStepsize); $i += $scaleStepsize)
			{
				if (!$centerCaptionSet && $i >= ($scaleFrom +floor(($scaleTo - $scaleFrom +1) / 2)))
				{
					$centerCaptionSet = true;
					$content .= '<td><center>' . $scaleCaptionCenter . '</center></td>';
				}
				else
				{
					$content .= '<td></td>';
				}
			}
			$content .= '<td><center>' . $scaleCaptionRight . '</center></td>';
			$content .= '</tr>';
		}

		$content .= '</table>';

		// CONTENT AFTER
		$formContentAfter = $stepRender->render($this->getAttributeRuntimeValue('formContentAfter'));
		if ($formContentAfter != '')
		{
			$content .= $formContentAfter;
		}

		$content .= ' <input name="NextStep" type="submit" value="' . $translate->_('Submit ...') . '">';
		$content .= '</div>';

		$content .= '</div>';
		$content .= '</form>';

		return $content;
	}

	public function process()
	{
		$translate = $this->getTranslate();
		$value = $this->getController()->getRequest()->getParam('form_field', null);
		$this->_processMessage = array ();

		$formOptions = $this->getAnswers();

		// check if value is in options
		if (is_array($formOptions) && !array_key_exists($value, $formOptions))
		{
			$this->_processMessage = $translate->_('Please select one of the given options.');
			return false;
		}

		$formVariableName = $this->getAttributeRuntimeValue('formVariableName');
		if ($formVariableName != '')
		{
			$variableApi = $this->getContext()->getApi('variable');
			$setterFunction = 'set' . $this->getAttributeRuntimeValue('formVariableContext');
			$variableApi-> $setterFunction ($formVariableName, $value);
		}

		return parent :: process();
	}

	///////////////////////////////////////

	public function adminFormTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('form');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Form',
				'dijitParams' => array (
					'title' => 'Form',

				),

			));
			$form->addSubForm($subForm, 'form');
		}

		$order = 100;
		$formHeadline = $subForm->createElement('TextInput', 'formHeadline', array (
			'label' => 'Form Headline',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formHeadline->setValue($this->getAttributeValue('formHeadline'));
		$subForm->addElement($formHeadline);

		$order += 100;
		$formLabel = $subForm->createElement('TextInput', 'formLabel', array (
			'label' => 'Field Label',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formLabel->setValue($this->getAttributeValue('formLabel'));
		$subForm->addElement($formLabel);

		$order += 100;
		$formDefaultValue = $subForm->createElement('TextInput', 'formDefaultValue', array (
			'label' => 'Field Default Value',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formDefaultValue->setValue($this->getAttributeValue('formDefaultValue'));
		$subForm->addElement($formDefaultValue);

		$order += 100;
		$formScaleFrom = $subForm->createElement('NumberSpinner', 'formScaleFrom', array (
			'label' => 'Scale From',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleFrom->setValue($this->getAttributeValue('formScaleFrom'));
		$subForm->addElement($formScaleFrom);

		$order += 100;
		$formScaleTo = $subForm->createElement('NumberSpinner', 'formScaleTo', array (
			'label' => 'Scale To',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleTo->setValue($this->getAttributeValue('formScaleTo'));
		$subForm->addElement($formScaleTo);

		$order += 100;
		$formScaleStepsize = $subForm->createElement('NumberSpinner', 'formScaleStepsize', array (
			'label' => 'Scale Stepsize',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleStepsize->setValue($this->getAttributeValue('formScaleStepsize'));
		$subForm->addElement($formScaleStepsize);

		/*$order += 100;
		$formScaleShow = $subForm->createElement('Checkbox', 'formScaleShow', array (
			'label' => 'Show Number Legend',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleShow->setValue($this->getAttributeValue('formScaleShow'));
		$subForm->addElement($formScaleShow);*/

		$order += 100;
		$formScaleShow = $subForm->createElement('Select', 'formScaleShow', array (
			'label' => 'Number Legend',
			'trim' => 'true',
			'order' => $order,
			'multiOptions'=>array('0'=>'Do not show', '1'=>'Show')
		), array ());
		$formScaleShow->setValue($this->getAttributeValue('formScaleShow'));
		$subForm->addElement($formScaleShow);

		$order += 100;
		$formScaleCaptionPosition = $subForm->createElement('Select', 'formScaleCaptionPosition', array (
			'label' => 'Scale Caption',
			'trim' => 'true',
			'order' => $order,
			'multiOptions'=>array(''=>'None', 'onSides'=>'on the Sides', 'below'=>'Below')
		), array ());
		$formScaleCaptionPosition->setValue($this->getAttributeValue('formScaleCaptionPosition'));
		$subForm->addElement($formScaleCaptionPosition);

		$order += 100;
		$formScaleCaptionLeft = $subForm->createElement('TextInput', 'formScaleCaptionLeft', array (
			'label' => 'Scale Caption Left',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleCaptionLeft->setValue($this->getAttributeValue('formScaleCaptionLeft'));
		$subForm->addElement($formScaleCaptionLeft);

		$order += 100;
		$formScaleCaptionCenter = $subForm->createElement('TextInput', 'formScaleCaptionCenter', array (
			'label' => 'Scale Caption Center',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleCaptionCenter->setValue($this->getAttributeValue('formScaleCaptionCenter'));
		$subForm->addElement($formScaleCaptionCenter);

		$order += 100;
		$formScaleCaptionRight = $subForm->createElement('TextInput', 'formScaleCaptionRight', array (
			'label' => 'Scale Caption Right',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleCaptionRight->setValue($this->getAttributeValue('formScaleCaptionRight'));
		$subForm->addElement($formScaleCaptionRight);

		$order += 100;
		$formScaleWidth = $subForm->createElement('TextInput', 'formScaleWidth', array (
			'label' => 'Scale Width (CSS)',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formScaleWidth->setValue($this->getAttributeValue('formScaleWidth'));
		$subForm->addElement($formScaleWidth);

		$order += 100;
		$formContentBefore = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentBefore', array (
			'label' => 'Content Before Form',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formContentBefore->setValue($this->getAttributeValue('formContentBefore'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentBefore->getJsInstance() . '.refresh(); } );');
		$subForm->addElement($formContentBefore);

		$order += 100;
		$formContentAfter = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentAfter', array (
			'label' => 'Content After Form',
			'trim' => 'true',
			'order' => $order
		), array ());
		$formContentAfter->setValue($this->getAttributeValue('formContentAfter'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentAfter->getJsInstance() . '.refresh(); } );');
		$subForm->addElement($formContentAfter);

		$order += 100;
		$submit = $subForm->createElement('submit', 'formSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));
		$subForm->addElement($submit);
	}

	public function adminFormTabProcess($parameters)
	{
		$this->setAttributeValue('formHeadline', $parameters['formHeadline']);
		$this->setAttributeValue('formLabel', $parameters['formLabel']);
		$this->setAttributeValue('formContentBefore', $parameters['formContentBefore']);
		$this->setAttributeValue('formContentAfter', $parameters['formContentAfter']);
		$this->setAttributeValue('formDefaultValue', $parameters['formDefaultValue']);

		$this->setAttributeValue('formScaleFrom', $parameters['formScaleFrom']);
		$this->setAttributeValue('formScaleTo', $parameters['formScaleTo']);
		$this->setAttributeValue('formScaleStepsize', $parameters['formScaleStepsize']);
		$this->setAttributeValue('formScaleShow', $parameters['formScaleShow']);
		$this->setAttributeValue('formScaleCaptionLeft', $parameters['formScaleCaptionLeft']);
		$this->setAttributeValue('formScaleCaptionRight', $parameters['formScaleCaptionRight']);
		$this->setAttributeValue('formScaleCaptionCenter', $parameters['formScaleCaptionCenter']);
		$this->setAttributeValue('formScaleCaptionPosition', $parameters['formScaleCaptionPosition']);
		$this->setAttributeValue('formScaleWidth', $parameters['formScaleWidth']);
	}

	public function adminProcessingTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('processing');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array (
				'legend' => 'Processing',
				'dijitParams' => array (
					'title' => 'Processing',

				),

			));
			$form->addSubForm($subForm, 'processing');
		}

		$order = 100;
		//		$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
		//		$view->dojo()->prependOnLoad('function() { window.variableComboBoxStore = new dojo.data.ItemFileReadStore({ url: "' . $view->url(array('module'=>'expdesigner', 'controller'=>'variables', 'action'=>'listknown', 'treatmentId'=>$this->treatment->id)) . '", urlPreventCache: true} ); }');
		//		$formVariableName = $subForm->createElement('ComboBox', 'formVariableName', array('label'=>'Variable Name', 'trim'=>'true', 'order'=>$order, 'store'=>'window.variableComboBoxStore'), array());
		$formVariableName = $subForm->createElement('TextInput', 'formVariableName', array (
			'label' => 'Variable Name',
			'trim' => 'true',
			'order' => $order,
			'required' => 'true'
		), array ());
		$formVariableName->setValue($this->getAttributeValue('formVariableName'));

		$order += 100;
		$formVariableContext = $subForm->createElement('VariableContextSelect', 'formVariableContext', array (
			'label' => 'Variable Context',
			'trim' => 'true',
			'order' => $order,
			'required' => 'true',
		), array ());
		$formVariableContext->setValue($this->getAttributeValue('formVariableContext'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'processingSave', array (
			'label' => 'Save',
			'order' => $order,
			'ignore' => 'true'
		));

		$subForm->addElements(array (
			$formVariableName,
			$formVariableContext,
			$submit
		));
	}

	public function adminProcessingTabProcess($parameters)
	{
		$this->setAttributeValue('formVariableName', $parameters['formVariableName']);
		$this->setAttributeValue('formVariableContext', $parameters['formVariableContext']);
	}

}