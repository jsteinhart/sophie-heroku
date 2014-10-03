<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Form_Input_Text_1_0_0');

class Sophie_Steptype_Form_Input_Textarea_1_0_0_Steptype extends Sophie_Steptype_Form_Input_Text_1_0_0_Steptype
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

		$this->setAttributeValue('formType', 'textarea');
		$this->setAttributeValue('formTextareaRows', '15');
		$this->setAttributeValue('formTextareaCols', '40');
		$this->setAttributeValue('formContentAfter', '<br /><br />');
	}

	///////////////////////////////////////

	public function adminFormTabInit()
	{

		parent::adminFormTabInit();

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

		$order = 110;
		$formTextareaRows = $subForm->createElement('NumberSpinner', 'formTextareaRows', array('label'=>'Textarea Rows', 'trim'=>'true', 'order'=>$order, 'required'=>'true'), array());
		$formTextareaRows->setValue($this->getAttributeValue('formTextareaRows'));
		$subForm->addElement($formTextareaRows);

		$order = 120;
		$formTextareaCols = $subForm->createElement('NumberSpinner', 'formTextareaCols', array('label'=>'Textarea Cols', 'trim'=>'true', 'order'=>$order, 'required'=>'true'), array());
		$formTextareaCols->setValue($this->getAttributeValue('formTextareaCols'));
		$subForm->addElement($formTextareaCols);
	}

	public function adminFormTabProcess($parameters)
	{
		$this->setAttributeValue('formTextareaRows', $parameters['formTextareaRows']);
		$this->setAttributeValue('formTextareaCols', $parameters['formTextareaCols']);

		return parent::adminFormTabProcess($parameters);
	}

}