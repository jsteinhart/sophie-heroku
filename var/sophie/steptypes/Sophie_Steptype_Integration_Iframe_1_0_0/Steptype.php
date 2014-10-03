<?php
$steptypeFactory = Sophie_Steptype_Factory::getSystemInstance();
$steptypeFactory->load('Sophie_Steptype_Info_1_0_0');

class Sophie_Steptype_Integration_Iframe_1_0_0_Steptype extends Sophie_Steptype_Info_1_0_0_Steptype
{
	
	public function __construct()
	{
		parent :: __construct();
		$this->translatePaths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translate';
	}

	protected function getSteptypeAttributeConfigurations()
	{
		$config = parent :: getSteptypeAttributeConfigurations();
		$config['iframeUrl'] = array(
			'group' => 'IFrame',
			'title' => 'Url',
		);
		return $config;
	}

	public function render()
	{
		$content = $this->renderContent();
		
		$iframeUrl = $this->getAttributeRuntimeValue('iframeUrl');
		$content .= '<iframe id="sophieIntegrationIframe" class="sophieIntegrationIframe" src="' . $iframeUrl . '"></iframe>';
		$content .= $this->renderForm();
		return $content;
	}

	/////////////////////////////////////////////////
	// ADMIN TABS
	/////////////////////////////////////////////////
	
	public function adminSetDefaultValues()
	{
		$this->setAttributeValue('iframeUrl', 'http://URL');
	}
	
	public function adminGetTabs()
	{
		$tabs = parent :: adminGetTabs();
		$tabs[] = array ('id'=>'iframe', 'title'=>'IFrame', 'order'=>150);
		return $tabs;
	}

	public function adminIframeTabInit()
	{
		$view = $this->getView();
		$form = $this->adminGetForm();
		$subForm = $form->getSubForm('iframe');
		if (is_null($subForm))
		{
			$subForm = $form->createSubForm();
			$subForm->setAttribs(array(
				'legend' => 'IFrame',
				'dijitParams' => array(
					'title' => 'IFrame',
				),
			));
			$form->addSubForm($subForm, 'iframe');
		}

		$order = 0;

		$order += 100;
		$iframeUrl = $subForm->createElement('TextInput', 'iframeUrl', array('label'=>'IFrame Url', 'trim'=>'true', 'order'=>$order), array());
		$iframeUrl->setValue($this->getAttributeValue('iframeUrl'));

		$order += 100;
		$submit = $subForm->createElement('submit', 'redirectSave', array('label'=>'Save', 'order'=>$order, 'ignore'=>'true'));
		$subForm->addElements(array($iframeUrl, $submit));
	}

	public function adminRedirectTabProcess($parameters)
	{
		$this->setAttributeValue('iframeUrl', $parameters['iframeUrl']);
	}

}