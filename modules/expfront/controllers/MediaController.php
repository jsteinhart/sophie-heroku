<?php
class Expfront_MediaController extends Symbic_Controller_Action
{
	public function init()
	{
		$this->_session = new Zend_Session_Namespace('expfront');
	}

	public function indexAction()
	{
		if ( isset($this->_session->participantId) )
		{

			$db = Zend_Registry::get('db');

		}
	}
}