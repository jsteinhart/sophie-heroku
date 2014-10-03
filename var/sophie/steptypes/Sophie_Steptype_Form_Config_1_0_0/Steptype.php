<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

class Sophie_Steptype_Form_Config_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{
    private $_processMessage = array();
    private $_questions = null;
    private $_form = null;

    private $allowedElementTypes = array('submit', 'text', 'radio', 'textarea', 'hidden', 'select', 'multiselect', 'multiCheckbox');

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
        $this->setAttributeValue('contentHeadline', 'Please answer the following questions.');
        $this->setAttributeValue('contentBody', '');
        $this->setAttributeValue('formConfig', "test.type = \"text\"\ntest.options.label = \"Test\"\ntest.options.required = true\nsubmit.type=\"submit\"");
        $this->setAttributeValue('formDecoratorSet', 'Zend_Form_Default');
    }

    public function getStepRenderer($reset = false)
    {
        if (is_null($this->stepRenderer) || $reset) {
            parent::getStepRenderer($reset = false);
        }

        $this->stepRenderer->setLocalVar('form', $this->getForm());

        return $this->stepRenderer;
    }

    public function getFormConfig($formConfigAttrib = null)
    {
        $formConfigIni = '';

        $formConfigIni .= "[form]\n";
        $formConfigIni .= 'form.action="' . $this->getFrontUrl() . '"' . "\n";
        $formConfigIni .= 'form.method="POST"' . "\n";
        $formConfigIni .= 'form.accept-charset = "utf-8"' . "\n";

        $formConfigIni .= 'form.elements.contextChecksum.type="hidden"' . "\n";
        $formConfigIni .= 'form.elements.contextChecksum.options.value="' . $this->getContext()->getChecksum() . '"' . "\n";

        if (is_null($formConfigAttrib)) {
            $formConfigAttrib = $this->getAttributeRuntimeValue('formConfig');
        }

        $formConfigAttribs = explode("\n", $formConfigAttrib);
        foreach ($formConfigAttribs as $formConfigAttribsLine) {
            $formConfigIni .= 'form.elements.' . $formConfigAttribsLine . "\n";
        }

        try {
            $formConfig = new Symbic_Config_Ini_String($formConfigIni, 'form');
        } catch (Exception $e) {
            return false;
        }

        return $formConfig;
    }

    private function getForm()
    {
        if (is_null($this->_form)) {
            $formConfig = $this->getFormConfig();
            if ($formConfig === false) {
                throw new Exception('Invalid form config');
            }

            $formDecoratorSet = $this->getAttributeRuntimeValue('formDecoratorSet');
            if ($formDecoratorSet != '' && $formDecoratorSet != 'Zend_Form') {
                $this->_form = new Symbic_Form($formConfig->form);
                $this->_form->setDecoratorset($formDecoratorSet);
            } else {
                $this->_form = new Symbic_Form_Standard($formConfig->form);
            }

            $this->_form->setTranslator($this->getTranslate());
        }
        return $this->_form;
    }

    public function renderForm()
    {
        $form = $this->getForm();
        return $form->render();
    }


    public function process()
    {
        $form = $this->getForm();

        if ($form->isValid($_POST)) {
            $formValues = $form->getValues();

            $variableApi = $this->getContext()->getApi('variable');

            foreach ($formValues as $formValuesKey => $formValuesValue) {
                if ($formValuesKey == 'contextChecksum') {
                    continue;
                }
                if (is_array($formValuesValue)) {
                    $formValuesValue = implode(',', $formValuesValue);
                }
                $variableApi->setPE($formValuesKey, $formValuesValue);
            }
            return parent::process();
        }

        return false;
    }

    //////////////////////////////////////////

    public function adminGetTabs()
    {
        $tabs = parent::adminGetTabs();
        $tabs[] = array('id' => 'form', 'title' => 'Form', 'order' => 300);
        return $tabs;
    }

    public function adminFormTabInit()
    {
        $view = $this->getView();
        $form = $this->adminGetForm();
        $subForm = $form->getSubForm('form');
        if (is_null($subForm)) {
            $subForm = $form->createSubForm();
            $subForm->setAttribs(array(
                'legend' => 'Form',
                'dijitParams' => array(
                    'title' => 'Form',
                ),
            ));
            $form->addSubForm($subForm, 'form');
        }

        $order = 0;

        $order += 100;
        $formConfig = $subForm->createElement('Textarea', 'formConfig', array('label' => 'Config', 'trim' => 'true', 'order' => $order), array());
        $formConfig->setValue($this->getAttributeValue('formConfig'));
        $subForm->addElement($formConfig);

        $order += 100;
        $formDecoratorSet = $subForm->createElement('Select', 'formDecoratorSet', array('label' => 'Decorators', 'trim' => 'true', 'order' => $order), array());
        $formDecoratorSet->setValue($this->getAttributeValue('formDecoratorSet'));
        $formDecoratorSetOptions = array();
        $formDecoratorSetOptions['Zend_Form'] = 'Render as Definitionlist';
        $formDecoratorSetOptions['Symbic_Form_Decoratorset_Table'] = 'Render as Table';
        //$formDecoratorSetOptions['Symbic_Dojo_Form_Decoratorset_Table'] = 'Render as Table with Dojo Fields';
        $formDecoratorSet->setMultiOptions($formDecoratorSetOptions);
        $subForm->addElement($formDecoratorSet);

        $order += 100;
        $submit = $subForm->createElement('submit', 'formSave', array('label' => 'Save', 'order' => $order, 'ignore' => 'true'));
        $subForm->addElement($submit);
    }

    public function adminFormTabValidate($subForm, $parameters)
    {
        $subFormValid = true;

        $formConfig = $this->getFormConfig($parameters['formConfig']);
        $formConfigElement = $subForm->getElement('formConfig');

        if ($formConfig === false) {
            $subForm->markAsError();
            $formConfigElement->addError('Config could not be parsed');
            return false;
        }

        $formConfigArray = $formConfig->form->toArray();
        if (!isset($formConfigArray['elements']) && is_array($formConfigArray['elements'])) {
            $subForm->markAsError();
            $formConfigElement->addError('Config could not be parsed');
            return false;
        }


        foreach ($formConfigArray['elements'] as $formConfigElementConfig) {
            if (!isset($formConfigElementConfig['type'])) {
                $subForm->markAsError();
                $formConfigElement->addError('Each element has to have a type field');
                return false;
            }

            if (!in_array($formConfigElementConfig['type'], $this->allowedElementTypes)) {
                $subForm->markAsError();
                $formConfigElement->addError('Element type "' . $formConfigElementConfig['type'] . '" is not allowed');
                return false;
            }
        }

        return true;
    }

    public function adminFormTabProcess($parameters)
    {
        $this->setAttributeValue('formConfig', $parameters['formConfig']);
        $this->setAttributeValue('formDecoratorSet', $parameters['formDecoratorSet']);
        //$this->setAttributeValue('formProcess', $parameters['formProcess']);
    }

}