<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Form_Abstract_1_0_0');

class Sophie_Steptype_Form_Choice_1_0_0_Steptype extends Sophie_Steptype_Form_Abstract_1_0_0_Steptype
{
	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['formVariableName'] = array(
			'group' => 'Processing',
			'title' => 'Variable Name',
			'validatorType' => 'string'
		);
		$config['formVariableContext'] = array(
			'group' => 'Processing',
			'title' => 'Variable Context',
			'validatorType' => 'context'
		);
		$config['formOptionsJson'] = array(
			'group' => 'Form',
			'title' => 'Options (JSON encoded)',
			'validatorType' => 'json'
		);

		return $config;
	}

	public function adminSetDefaultValues()
	{
		parent::adminSetDefaultValues();

		$this->setAttributeValue('formType', 'radioList');
		$this->setAttributeValue('formVariableName', 'radio');
		$this->setAttributeValue('formContentAfter', '<br /><br />');
		$this->setAttributeValue('formOptionsJson', '{"a":"Option A","b":"Option B"}');
	}

	public function process()
	{
		$translate = $this->getTranslate();
		$value = $this->getController()->getRequest()->getParam('form_field', null);
		$this->_processMessage = array();

		$formOptionsJson = $this->getAttributeRuntimeValue('formOptionsJson');
		if (!empty($formOptionsJson))
		{
			$formOptions = Zend_Json::decode($formOptionsJson, true);
		}
		else
		{
			$formOptions = array();
		}


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
			$variableApi->$setterFunction($formVariableName, $value);
		}

		return parent::process();
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

		$formOptionsJson = $this->getAttributeValue('formOptionsJson');
		if (!empty($formOptionsJson))
		{
			$formOptions = Zend_Json::decode($formOptionsJson, true);
			$formOptionsText = '';
			foreach ($formOptions as $formOptionsKey => $formOptionsValue)
			{
				$formOptionsText .= $formOptionsKey . ':' . $formOptionsValue . "\n";
			}
		}
		else
		{
			$formOptionsText = "";
		}
		$order += 100;
		$formOptions = $subForm->createElement('Textarea', 'formOptions', array('label'=>'Field Options', 'trim'=>'true', 'order'=>$order), array());
		$formOptions->setValue($formOptionsText);

		$order += 100;
		$formType = $subForm->createElement('Select', 'formType', array('label'=>'Field Type', 'trim'=>'true', 'order'=>$order, 'multiOptions'=>array('radioList'=>'Radio Buttons', 'select'=>'Select Box')), array());
		$formType->setValue($this->getAttributeValue('formType'));

		$order += 100;
		$formContentBefore = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentBefore', array('label'=>'Content Before Form', 'trim'=>'true', 'order'=>$order), array());
		$formContentBefore->setValue($this->getAttributeValue('formContentBefore'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentBefore->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$formContentAfter = $subForm->createElement('SwitchCodemirrorWysiwygTextarea', 'formContentAfter', array('label'=>'Content After Form', 'trim'=>'true', 'order'=>$order), array());
		$formContentAfter->setValue($this->getAttributeValue('formContentAfter'));
		$view->jsOnLoad()->appendScript('dojo.connect(dijit.byId(\'form-ContentPane\'), \'onShow\', function() { ' . $formContentAfter->getJsInstance() . '.refresh(); } );');

		$order += 100;
		$submit = $subForm->createElement('submit', 'formSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));

		$subForm->addElements(array($formHeadline, $formLabel, $formDefaultValue, $formOptions, $formType, $formContentBefore, $formContentAfter, $submit));
	}

	public function adminFormTabProcess($parameters)
	{
		$this->setAttributeValue('formHeadline', $parameters['formHeadline']);
		$this->setAttributeValue('formContentBefore', $parameters['formContentBefore']);
		$this->setAttributeValue('formContentAfter', $parameters['formContentAfter']);

		if (!isset($parameters['formType']))
		{
			$parameters['formType'] = 'radioList';
		}
		$this->setAttributeValue('formType', $parameters['formType']);
		$this->setAttributeValue('formDefaultValue', $parameters['formDefaultValue']);

		$formOptions1 = trim($parameters['formOptions']);
		$formOptionsArray1 = explode("\n", $formOptions1);
		$formOptionsArray2 = array();
		foreach ($formOptionsArray1 as $formOptions2)
		{
			$formOptions3 = explode(":", $formOptions2, 2);
			if (!isset($formOptions3[1]))
			{
				$formOptions3[1] = '';
			}
			$key = trim($formOptions3[0]);
			$value = trim($formOptions3[1]);
			if (empty($key) && empty($value))
			{
				// remove empty options
				continue;
			}
			$formOptionsArray2[$key] = $value;
		}
		$this->setAttributeValue('formOptionsJson', Zend_Json::encode($formOptionsArray2));
	}

	public function adminProcessingTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('processing');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Processing',
				'dijitParams' => array(
					'title' => 'Processing',
				),
			));
			$form->addSubForm($subForm, 'processing');
		}

		$order = 100;
		$formVariableName = $subForm->createElement('TextInput', 'formVariableName', array('label'=>'Variable Name', 'trim'=>'true', 'order'=>$order, 'required'=>'true'), array());
		$formVariableName->setValue($this->getAttributeValue('formVariableName'));

		$order += 100;
		$formVariableContext = $subForm->createElement('VariableContextSelect', 'formVariableContext', array('label'=>'Variable Context', 'trim' => 'true', 'order'=>$order, 'required'=>'true',), array());
		$formVariableContext->setValue($this->getAttributeValue('formVariableContext'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'processingSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));

		$subForm->addElements(array($formVariableName, $formVariableContext, $submit));
	}

	public function adminProcessingTabProcess($parameters)
	{
		$this->setAttributeValue('formVariableName', $parameters['formVariableName']);
		$this->setAttributeValue('formVariableContext', $parameters['formVariableContext']);
	}

}