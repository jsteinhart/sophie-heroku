<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Form_Abstract_1_0_0');

class Sophie_Steptype_Form_Input_Number_1_0_0_Steptype extends Sophie_Steptype_Form_Abstract_1_0_0_Steptype
{
	public function __construct()
	{
		parent::__construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['formNotEmpty'] = array(
			'group' => 'Processing',
			'title' => 'Not Empty',
			'validatorRegExp' => '/(1|0)/'
		);
		$config['formNumberType'] = array(
			'group' => 'Processing',
			'title' => 'Number Type',
			'validatorRegExp' => '/(float|int)/'
		);
		$config['formMinValue'] = array(
			'group' => 'Processing',
			'title' => 'Processing: Min. Value',
			'validatorType' => 'numeric'
		);
		$config['formMinExclude'] = array(
			'group' => 'Processing',
			'title' => 'Min. Value Exclude',
			'validatorRegExp' => '/(1|0)/'
		);
		$config['formMaxValue'] = array(
			'group' => 'Processing',
			'title' => 'Max. Value',
			'validatorType' => 'numeric'
		);
		$config['formMaxExclude'] = array(
			'group' => 'Processing',
			'title' => 'Max. Value Exclude',
			'validatorRegExp' => '/(1|0)/'
		);
		$config['formDivisibleBy'] = array(
			'group' => 'Processing',
			'title' => 'Value divisible by',
			'validatorType' => 'numeric'
		);
		$config['formFloatPrecision'] = array(
			'group' => 'Processing',
			'title' => 'Float Precision',
			'validatorType' => 'integer'
		);
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

		return $config;
	}

	public function adminSetDefaultValues()
	{
		parent::adminSetDefaultValues();

		$this->setAttributeValue('formType', 'text');
		$this->setAttributeValue('formNumberType', 'float');
		$this->setAttributeValue('formFloatPrecision', 2);
		$this->setAttributeValue('formMinExclude', 0);
		$this->setAttributeValue('formMaxExclude', 0);
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
			$this->_processMessage = $translate->_('Please enter a value');
			return false;
		}

		$numberType = $this->getAttributeRuntimeValue('formNumberType');
		if ($numberType == 'float')
		{
			$validateFloat = new Zend_Validate_Float($this->getLocale());

			if (!$validateFloat->isValid($value))
			{
				$this->_processMessage = $translate->_('The input is not a valid floating-point number');
				return false;
			}

			$numberValue = Zend_Locale_Format::getFloat($value, array('locale'=>$this->getLocale()));

			$formFloatPrecision = $this->getAttributeRuntimeValue('formFloatPrecision');
			if ($formFloatPrecision != '')
			{
				$formFloatPrecision = (int) $formFloatPrecision;
				$formFloatPrecisionMult = pow(10, $formFloatPrecision);

				if (abs(round($numberValue, $formFloatPrecision) - $numberValue) * $formFloatPrecisionMult > 1E-10)
				{
					$this->_processMessage = $translate->_('Floating-point number has too many fractial positions');
					return false;
				}
			}
		}
		else
		{
			$validateInt = new Zend_Validate_Int($this->getLocale());

			if (!$validateInt->isValid($value))
			{
				$this->_processMessage = $translate->_('The input is not a valid integer number');
				return false;
			}

			$numberValue = (int) Zend_Locale_Format::getNumber($value, array('locale'=>$this->getLocale()));
		}

		$minValue = $this->getAttributeRuntimeValue('formMinValue');
		if ($minValue != '')
		{
			if ($numberType == 'float')
			{
				$minValue = (float)$minValue;
			}
			else
			{
				$minValue = (int)$minValue;
			}

			$minExclude = $this->getAttributeRuntimeValue('formMinExclude');
			if ($minExclude == 1 && ! $numberValue <= $minValue)
			{
				$this->_processMessage = str_replace('#1#', $minValue, $translate->_('Value must be greated than #1#'));
				return false;
			}
			elseif($minExclude == 0 && $numberValue < $minValue)
			{
				$this->_processMessage = str_replace('#1#', $minValue, $translate->_('Minimum value of input is #1#'));
				return false;
			}
		}

		$maxValue = $this->getAttributeRuntimeValue('formMaxValue');
		if ($maxValue != '')
		{
			if ($numberType == 'float')
			{
				$maxValue = (float)$maxValue;
			}
			else
			{
				$maxValue = (int)$maxValue;
			}

			$maxExclude = $this->getAttributeRuntimeValue('formMaxExclude');
			if ($maxExclude == 1 && ! $numberValue >= $maxValue)
			{
				$this->_processMessage = str_replace('#1#', $maxValue, $translate->_('Value must be smaller than #1#'));
				return false;
			}
			elseif($maxExclude == 0 && $numberValue > $maxValue)
			{
				$this->_processMessage = str_replace('#1#', $maxValue, $translate->_('Maximum value of input is #1#'));
				return false;
			}
		}

		$divisibleBy = $this->getAttributeRuntimeValue('formDivisibleBy');
		if ($divisibleBy != '')
		{
			if ($numberType == 'float')
			{
				$divisibleBy = (float)$divisibleBy;
			}
			else
			{
				$divisibleBy = (int)$divisibleBy;
			}

			if ($divisibleBy !== 0)
			{
				$division = $numberValue / $divisibleBy;
				if ($division - floor($division) > 0)
				{
					$this->_processMessage = str_replace('#1#', $divisibleBy, $translate->_('The value should be divisable by #1#'));
					return false;
				}
			}
		}

		$formVariableName = $this->getAttributeRuntimeValue('formVariableName');
		if ($formVariableName != '')
		{
			$variableApi = $this->getContext()->getApi('variable');
			$setterFunction = 'set' . $this->getAttributeRuntimeValue('formVariableContext');
			$variableApi->$setterFunction($formVariableName, $numberValue);
		}

		$this->_processFaultyValue = null;
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
		$formNotEmpty = $subForm->createElement('Checkbox', 'formNotEmpty', array('label'=>'Not Empty', 'trim'=>'true', 'order'=>$order), array());
		$formNotEmpty->setValue($this->getAttributeValue('formNotEmpty'));

		$order += 100;
		$formNumberType = $subForm->createElement('Select', 'formNumberType', array('label'=>'Number Type', 'trim'=>'true', 'order'=>$order), array());
		$formNumberType->setValue($this->getAttributeValue('formNumberType'));
		$formNumberTypeOptions = array();
		$formNumberTypeOptions['float'] = 'Floating-Point Number';
		$formNumberTypeOptions['int'] = 'Integer Number';
		$formNumberType->setMultiOptions($formNumberTypeOptions);

		$order += 100;
		$formMinValue = $subForm->createElement('TextInput', 'formMinValue', array('label'=>'Min. Value', 'trim'=>'true', 'order'=>$order), array());
		$formMinValue->setValue($this->getAttributeValue('formMinValue'));

		$order += 100;
		$formMinExclude = $subForm->createElement('Checkbox', 'formMinExclude', array('label'=>'Min. Value Exclude', 'trim'=>'true', 'order'=>$order), array());
		$formMinExclude->setValue($this->getAttributeValue('formMinExclude'));

		$order += 100;
		$formMaxValue = $subForm->createElement('TextInput', 'formMaxValue', array('label'=>'Max. Value', 'trim'=>'true', 'order'=>$order), array());
		$formMaxValue->setValue($this->getAttributeValue('formMaxValue'));

		$order += 100;
		$formMaxExclude = $subForm->createElement('Checkbox', 'formMaxExclude', array('label'=>'Max. Value Exclude', 'trim'=>'true', 'order'=>$order), array());
		$formMaxExclude->setValue($this->getAttributeValue('formMaxExclude'));

		$order += 100;
		$formDivisibleBy = $subForm->createElement('TextInput', 'formDivisibleBy', array('label'=>'Value divisible by', 'trim'=>'true', 'order'=>$order), array());
		$formDivisibleBy->setValue($this->getAttributeValue('formDivisibleBy'));

		$order += 100;
		$formFloatPrecision = $subForm->createElement('NumberSpinner', 'formFloatPrecision', array('label'=>'Float Precision', 'trim'=>'true', 'order'=>$order), array());
		$formFloatPrecision->setValue($this->getAttributeValue('formFloatPrecision'));

		$order += 100;
//		$view->dojo()->requireModule('dojo.data.ItemFileReadStore');
//		$view->dojo()->prependOnLoad('function() { window.variableComboBoxStore = new dojo.data.ItemFileReadStore({ url: "' . $view->url(array('module'=>'expdesigner', 'controller'=>'variables', 'action'=>'listknown', 'treatmentId'=>$this->treatment->id)) . '", urlPreventCache: true} ); }');
//		$formVariableName = $subForm->createElement('ComboBox', 'formVariableName', array('label'=>'Variable Name', 'trim'=>'true', 'order'=>$order, 'store'=>'window.variableComboBoxStore'), array());
		$formVariableName = $subForm->createElement('TextInput', 'formVariableName', array('label'=>'Variable Name', 'trim'=>'true', 'order'=>$order, 'required'=>'true'), array());
		$formVariableName->setValue($this->getAttributeValue('formVariableName'));

		$order += 100;
		$formVariableContext = $subForm->createElement('VariableContextSelect', 'formVariableContext', array('label'=>'Variable Context', 'trim' => 'true', 'order'=>$order, 'required'=>'true'), array());
		$formVariableContext->setValue($this->getAttributeValue('formVariableContext'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'processingSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));

		$subForm->addElements(array($formNotEmpty, $formNumberType, $formMinValue, $formMinExclude, $formMaxValue, $formMaxExclude, $formDivisibleBy, $formFloatPrecision, $formVariableName, $formVariableContext, $submit));
	}

	public function adminProcessingTabProcess($parameters)
	{
		$this->setAttributeValue('formNotEmpty', $parameters['formNotEmpty']);
		$this->setAttributeValue('formNumberType', $parameters['formNumberType']);
		$this->setAttributeValue('formMinValue', $parameters['formMinValue']);
		$this->setAttributeValue('formMinExclude', $parameters['formMinExclude']);
		$this->setAttributeValue('formMaxValue', $parameters['formMaxValue']);
		$this->setAttributeValue('formMaxExclude', $parameters['formMaxExclude']);
		$this->setAttributeValue('formDivisibleBy', $parameters['formDivisibleBy']);
		$this->setAttributeValue('formFloatPrecision', $parameters['formFloatPrecision']);

		$this->setAttributeValue('formVariableName', $parameters['formVariableName']);
		$this->setAttributeValue('formVariableContext', $parameters['formVariableContext']);
	}

}