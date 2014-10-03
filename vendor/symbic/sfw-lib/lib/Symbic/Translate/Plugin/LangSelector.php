<?php
class Symbic_Translate_Plugin_LangSelector 
extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request){
		$lang = $request->getParam('lang', null);
        
        $translate = Zend_Registry::get('Zend_Translate');
        $locale = Zend_Registry::get('Zend_Locale');
         
        if (!is_null($lang) && !empty($lang) && $translate->isAvailable($lang))
        {
        	$locale->setLocale($lang);
        	$translate->setLocale($locale);
        }
        
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        $router->setGlobalParam('lang', $locale->getLanguage());
	}
	
	public function dispatchLoopShutdown()
	{
		$locale = Zend_Registry::get('Zend_Locale');
		$view = Zend_Registry::get('Zend_View');
		$view->headMeta()->appendHttpEquiv('content-language', $locale->getLanguage());
	}
}