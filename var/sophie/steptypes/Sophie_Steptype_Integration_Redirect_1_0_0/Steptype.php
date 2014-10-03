<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

class Sophie_Steptype_Integration_Redirect_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{
	
	public function __construct()
	{
		parent :: __construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['redirectUrl'] = array(
			'group' => 'Redirect',
			'title' => 'Url',
		);
		$config['redirectType'] = array(
			'group' => 'Redirect',
			'title' => 'Type',
		);
		return $config;
	}

	public function render()
	{
		$redirectType = $this->getAttributeRuntimeValue('redirectType');
		if ($redirectType == 'http')
		{
		  $redirectUrl = $this->getAttributeRuntimeValue('redirectUrl');
		  header('Location: ' . $redirectUrl);
		  exit;
		}

		$content = $this->renderContent();
		$content .= $this->renderForm();
		return $content;
	}

	public function renderForm()
	{
		$view = $this->getView();

		$redirectUrl = $this->getAttributeRuntimeValue('redirectUrl');

		$content .= '
<script type="text/javascript">
//<!--
window.setTimeout(\'window.location.href= ' . json_encode($redirectUrl) . '\', 100);
//-->
</script>';

		return $content;
	}

	public function process()
	{
		return false;
	}

	/////////////////////////////////////////////////
	// ADMIN TABS
	/////////////////////////////////////////////////
	
	public function adminSetDefaultValues()
	{
		$this->setAttributeValue('redirectUrl', 'http://URL');
		$this->setAttributeValue('redirectType', 'http');
	}
	
	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array ('id'=>'redirect', 'title'=>'Redirect', 'order'=>150);
		return $tabs;
	}

	public function adminRedirectTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('redirect');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'Redirect',
				'dijitParams' => array(
					'title' => 'Redirect',
				),
			));
			$form->addSubForm($subForm, 'redirect');
		}

		$order = 100;
		$redirectType = $subForm->createElement('Select', 'redirectType', array('label'=>'Redirect Type', 'trim'=>'true', 'order'=>$order), array());
		$redirectType->setValue($this->getAttributeValue('redirectType'));
		$redirectTypeOptions= array();
		$redirectTypeOptions['http'] = 'HTTP';
		$redirectTypeOptions['js'] = 'JavaScript';
		$redirectType->setMultiOptions($redirectTypeOptions);
		
		$order += 100;
		$redirectUrl = $subForm->createElement('TextInput', 'redirectUrl', array('label'=>'Redirect Url', 'trim'=>'true', 'order'=>$order), array());
		$redirectUrl->setValue($this->getAttributeValue('redirectUrl'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'redirectSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($redirectType, $redirectUrl, $submit));
	}

	public function adminRedirectTabProcess($parameters)
	{
		$this->setAttributeValue('redirectType', $parameters['redirectType']);
		$this->setAttributeValue('redirectUrl', $parameters['redirectUrl']);
	}

}