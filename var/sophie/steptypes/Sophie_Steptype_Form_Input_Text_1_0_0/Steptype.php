<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Form_Abstract_1_0_0');

class Sophie_Steptype_Form_Input_Text_1_0_0_Steptype extends Sophie_Steptype_Form_Abstract_1_0_0_Steptype
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

	public function adminSetDefaultValues()
	{
		parent::adminSetDefaultValues();

		$this->setAttributeValue('formType', 'text');
	}

	public function process()
	{
		$translate = $this->getTranslate();
		$value = $this->getController()->getRequest()->getParam('form_field', null);
		$this->_processMessage = null;
		$this->_processFaultyValue = $value;

		$trimWhitespace = $this->getAttributeRuntimeValue('formTrimWhitespace');
		if ($trimWhitespace == '1')
		{
			$value = trim($value);
		}

		$notEmpty = $this->getAttributeRuntimeValue('formNotEmpty');
		if ($notEmpty === '1' && $value === '')
		{
			$this->_processMessage = $translate->_('Please enter a value.');
			return false;
		}

		// TODO: add formValidation!!!
		$formValidationOptions['Everything'] = 'Everything';
		$formValidationOptions['Alpha'] = 'Text (only Alphabet)';
		$formValidationOptions['AlphaWS'] = 'Text (only Alphabet with Whitespace)';
		$formValidationOptions['Alnum'] = 'Text (only Alpha-Numerics)';
		$formValidationOptions['AlnumWS'] = 'Text (only Alpha-Numerics with Whitespace)';
		$formValidationOptions['Digits'] = 'Digits (0 - 9)';

		$validation = $this->getAttributeRuntimeValue('formValidation');
		if (!empty($validation) && $validation != 'Everything')
		{
			switch ($validation)
			{
				case 'Alpha':
					$validator = new Zend_Validate_Alpha(false);
					break;
				case 'AlphaWS':
					$validator = new Zend_Validate_Alpha(true);
					break;
				case 'Alnum':
					$validator = new Zend_Validate_Alnum(false);
					break;
				case 'AlnumWS':
					$validator = new Zend_Validate_Alnum(true);
					break;
				case 'Digits':
					$validator = new Zend_Validate_Digits();
					break;
			}
			if (!$validator->isValid($value))
			{
				$validatorMessages = $validator->getMessages();
				foreach ($validatorMessages as $validatorMessage)
				{
					$this->_processMessage = $translate->_($validatorMessage);
				}
				return false;
			}
		}

		$maxLength = $this->getAttributeRuntimeValue('formMaxLength');
		if ($maxLength != '' && strlen($value) > $maxLength)
		{
			$this->_processMessage = sprintf($translate->_('Input should not exceed %1$s characters'), $maxLength);
			return false;
		}

		$minLength = $this->getAttributeRuntimeValue('formMinLength');
		if ($minLength != '' && strlen($value) < $minLength)
		{
			$this->_processMessage = sprintf($translate->_('Input should contain at least %1$s characters'), $minLength);
			return;
		}

		$regexp = $this->getAttributeRuntimeValue('formRegexp');
		if ($regexp != '' && !preg_match ( $regexp , $value))
		{
			$this->_processMessage = $translate->_('The given input is not valid');
			return;
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
		$formValidation = $subForm->createElement('Select', 'formValidation', array('label'=>'Valid Values', 'trim'=>'true', 'order'=>$order), array());
		$formValidation->setValue($this->getAttributeValue('formValidation'));
		$formValidationOptions = array();
		$formValidationOptions['Everything'] = 'Accept everything';
		$formValidationOptions['Alpha'] = 'Text (only Alphabet)';
		$formValidationOptions['AlphaWS'] = 'Text (only Alphabet with Whitespace)';
		$formValidationOptions['Alnum'] = 'Text (only Alpha-Numerics)';
		$formValidationOptions['AlnumWS'] = 'Text (only Alpha-Numerics with Whitespace)';
		$formValidationOptions['Digits'] = 'Digits (0 - 9)';
		$formValidation->setMultiOptions($formValidationOptions);

		$order += 100;
		$formTrimWhitespace = $subForm->createElement('Checkbox', 'formTrimWhitespace', array('label'=>'Trim Whitespace', 'trim'=>'true', 'order'=>$order), array());
		$formTrimWhitespace->setValue($this->getAttributeValue('formTrimWhitespace'));

		$order += 100;
		$formNotEmpty = $subForm->createElement('Checkbox', 'formNotEmpty', array('label'=>'Not Empty', 'trim'=>'true', 'order'=>$order), array());
		$formNotEmpty->setValue($this->getAttributeValue('formNotEmpty'));

		$order += 100;
		$formMinLength = $subForm->createElement('NumberSpinner', 'formMinLength', array('label'=>'Min. Length', 'trim'=>'true', 'order'=>$order), array());
		$formMinLength->setValue($this->getAttributeValue('formMinLength'));

		$order += 100;
		$formMaxLength = $subForm->createElement('NumberSpinner', 'formMaxLength', array('label'=>'Max. Length', 'trim'=>'true', 'order'=>$order), array());
		$formMaxLength->setValue($this->getAttributeValue('formMaxLength'));

		$order += 100;
		$formShowLength = $subForm->createElement('Select', 'formShowLength', array('label'=>'Show Length', 'trim'=>'true', 'order'=>$order), array());
		$formShowLength->setValue($this->getAttributeValue('formShowLength'));
		$formShowLengthOptions = array(
			'' => 'Do not show character count',
			'auto' => 'Show character count when reaching max. length',
			'always' => 'Always show character count (required given max. length)',
		);
		$formShowLength->setMultiOptions($formShowLengthOptions);

		$order += 100;
//		$regexpOptions = array(
//			'/^[0-9]+$/' => '/^[0-9]+$/',
//			'/^[1-9][0-9]*$/' => '/^[1-9][0-9]*$/'
//		);
		$regexpValue = $this->getAttributeValue('formRegexp');
//		if (!isset($regexpOptions[$regexpValue])) {
//			$regexpOptions[$regexpValue] = $regexpValue;
//		}
//		$formRegexp = $subForm->createElement('ComboBox', 'formRegexp', array('label'=>'Regular Expression', 'trim'=>'true', 'order'=>$order, 'multiOptions' => $regexpOptions), array());
		$formRegexp = $subForm->createElement('TextInput', 'formRegexp', array('label'=>'Regular Expression', 'trim'=>'true', 'order'=>$order), array());
		$formRegexp->setValue($regexpValue);

		$order += 100;
		$formVariableName = $subForm->createElement('TextInput', 'formVariableName', array('label'=>'Variable Name', 'trim'=>'true', 'order'=>$order, 'required'=>'true'), array());
		$formVariableName->setValue($this->getAttributeValue('formVariableName'));
		//$formVariableName->setMultiOptions(array());

		$order += 100;
		$formVariableContext = $subForm->createElement('VariableContextSelect', 'formVariableContext', array('label'=>'Variable Context', 'trim' => 'true', 'order'=>$order, 'required'=>'true'), array());
		$formVariableContext->setValue($this->getAttributeValue('formVariableContext'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'processingSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));

		$subForm->addElements(array($formValidation, $formTrimWhitespace, $formNotEmpty, $formMinLength, $formMaxLength, $formShowLength, $formRegexp, $formVariableName, $formVariableContext, $submit));
	}

	public function adminProcessingTabProcess($parameters)
	{
		$this->setAttributeValue('formValidation', $parameters['formValidation']);
		$this->setAttributeValue('formTrimWhitespace', $parameters['formTrimWhitespace']);
		$this->setAttributeValue('formNotEmpty', $parameters['formNotEmpty']);
		$this->setAttributeValue('formMinLength', $parameters['formMinLength']);
		$this->setAttributeValue('formMaxLength', $parameters['formMaxLength']);
		$this->setAttributeValue('formShowLength', $parameters['formShowLength']);
		$this->setAttributeValue('formRegexp', $parameters['formRegexp']);
		$this->setAttributeValue('formVariableName', $parameters['formVariableName']);
		$this->setAttributeValue('formVariableContext', $parameters['formVariableContext']);
	}

}