<?php
class Sfwsystem_HeartbeatController extends Symbic_Controller_Action
{
	public function indexAction()
	{
		// TODO: do not increase session timeout
		session_write_close();

		$response = array('heartbeat'=>'success');

		$userSession = $this->getUserSession();
		if ( $userSession->isLoggedIn() )
		{
			$response['sessionUserLogin'] = $userSession->getLogin();
			$response['sessionTimeout'] = 2;
		}
		$this->_helper->json($response);
	}
}