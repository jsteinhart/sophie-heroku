<?php
class Expfront_IndexController extends Symbic_Controller_Action
{
	public function init()
	{
		$this->_session = new Zend_Session_Namespace('expfront');
	}

	public function indexAction()
	{
		if ( isset($this->_session->participantId) )
		{
			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'expfront',
				'controller' => 'step',
				'action' => 'index'
			), 'default', true);
			return;
		}
		else
		{
			$this->_helper->getHelper('Redirector')->gotoRoute(array (
				'module' => 'expfront',
				'controller' => 'login',
				'action' => 'index'
			), 'default', true);
			return;
		}

	}
}