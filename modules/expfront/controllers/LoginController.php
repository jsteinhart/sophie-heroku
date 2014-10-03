<?php
class Expfront_LoginController extends Symbic_Controller_Action
{
	public function init()
	{
		$this->_session = new Zend_Session_Namespace('expfront');
	}

	public function preDispatch()
	{
		$config = Zend_Registry::get('config');
		
		$defaultLayoutTheme = $config['systemConfig']['sophie']['expfront']['defaultLayoutTheme'];
		$defaultLayoutDesign = $config['systemConfig']['sophie']['expfront']['defaultLayoutDesign'];

		$layoutPath = BASE_PATH . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sophie' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $defaultLayoutTheme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts';

		$layoutHelper = $this->getHelper('layout')->getLayoutInstance();
		$layoutHelper->setLayoutPath($layoutPath);
		$layoutHelper->setLayout($defaultLayoutDesign);
	}

	public function indexAction()
	{
		// TODO: add brute force defence, number of failed logins per IP and rate of login attemps

		$form = $this->getForm('Login_Code');
		$form->setAction($this->view->url());

		if ($this->_hasParam('participantCode'))
		{
			if ($form->isValid($this->_getAllParams()))
			{
				$values = $form->getValues();

				$this->_session->unsetAll();

				$sessionParticipantTable = Sophie_Db_Session_Participant :: getInstance();
				$participant = $sessionParticipantTable->fetchRow($sessionParticipantTable->select()->where('code = ?', $values['participantCode']));

				if (is_null($participant))
				{
					$form->getElement('participantCode')->addError('Code does not exist');
				}
				else
				{
					Sophie_Db_Session_Log :: log($participant->sessionId, 'Participant ' . $participant->label . ' logged in');

					if (isset($values['participantExternalData']))
					{
						if (is_array($values['participantExternalData']))
						{
							$values['participantExternalData'] = json_encode($values['participantExternalData']);
						}
						
						if ($values['participantExternalData'] != '')
						{
							$participant->externalData = $values['participantExternalData'];
							Sophie_Db_Session_Log :: log($participant->sessionId, 'Participant ' . $participant->label . ' external data set to \'' . $values['participantExternalData'] . '\'');
						}
					}
					
					$participant->httpSession = Zend_Session::getId();
					$participant->save();
					
					$this->_session->participant = $participant->toArray();
					$this->_session->participantId = $participant->id;

					$this->_helper->getHelper('Redirector')->gotoRoute(array (
						'module' => 'expfront',
						'controller' => 'step',
						'action' => 'index'
					), 'default', true);
					return;
				}
			}
		}
		$this->view->form = $form;
	}

	public function logoutAction()
	{
		if (!isset ($this->_session->participant))
		{
			$this->_error('Zur Zeit ist kein Teilnehmer angemeldet, sodass keine Abmeldung stattfinden kann.');
			return;
		}

		Sophie_Db_Session_Log::log($this->_session->participant['sessionId'], 'Participant ' . $this->_session->participant['label'] . ' logged out');

		$this->_session->unsetAll();
		$this->_helper->getHelper('Redirector')->gotoRoute(array (
			'module' => 'expfront',
			'controller' => 'index',
			'action' => 'index'
		), 'default', true);
		return;
	}
}