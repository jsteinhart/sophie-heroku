<?php
namespace Application
{
	class Bootstrap extends \Symbic_Application_Bootstrap
	{
		protected function _initMyView()
		{
			parent::_initMyView();
			
			$this->bootstrap('view');
			$view	 = $this->getResource('view');

			$helperLoader = \Symbic_View_Loader_Helper::getInstance();
			$helperLoader->setMap('FormParticipantTypeSelect', 'Sophie_View_Helper_FormParticipantTypeSelect');
			$helperLoader->setMap('FormVariableContextMultiSelect', 'Sophie_View_Helper_FormVariableContextMultiSelect');
			$helperLoader->setMap('FormVariableContextSelect', 'Sophie_View_Helper_FormVariableContextSelect');

			$view->headTitle('SoPHIE');

			$headLink = $view->headLink();
			$headLink->headLink(array(
				'rel'	 => 'shortcut icon',
				'href'	 => '/_media/favicon.ico'), 'append');
		}
		
		protected function _initMyForm()
		{
			$elementLoader = \Symbic_Form_Loader_Element::getInstance();
			$elementLoader->setMap('ParticipantTypeSelect', 'Sophie_Form_Element_ParticipantTypeSelect');
			$elementLoader->setMap('VariableContextMultiSelect', 'Sophie_Form_Element_VariableContextMultiSelect');
			$elementLoader->setMap('VariableContextSelect', 'Sophie_Form_Element_VariableContextSelect');
		}
	}
}