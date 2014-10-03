<?php
class init
{
	public function init()
	{
		$eventManager = Zend_Registry::get('Zend_EventManager');
		$eventManager->attach(
			'sophie_admin_session_interface_inittab',
			function($e)
			{
				$sessionAdminInterface = $e->getParam('sophie_admin_session_interface');
				$sessionAdminMturkTab = new Sophie_Admin_Session_Interface_Tab_Href();
				$sessionAdminMturkTab->setId('sessionSophielabsMturkTab');
				$sessionAdminMturkTab->setParams(
					array(
						'href' => $sessionAdminInterface->getView()->url(
							array(
								'module' => 'sophielabsmturk',
								'controller' => 'session',
								'action' => 'index'
							)),
						'title' => '<img src="/_media/Icons/monitor.png" alt="MTurk"> MTurk'
						)
					);
				$sessionAdminMturkTab->setPriority(350);
				$sessionAdminInterface->addTab($sessionAdminMturkTab);
			}
		);
		
		$eventManager->attach(
			'sophie_admin_session_interface_tabcontainer_VARIABLES_prerender',
			function($e)
			{
				$sessionAdminInterfaceTabcontainer = $e->getParam('sophie_admin_session_interface_tabconainer');
			
				$sessionVariableListTab = new Sophie_Admin_Session_Interface_Tab_Href();
				$sessionVariableListTab->setId($sessionAdminInterfaceTabcontainer->getId() . '2');
				$sessionVariableListTab->setParams(
					array (
						'href' => $sessionAdminInterfaceTabcontainer->getView()->url(array (
							'controller' => 'variable',
							'action' => 'list'
						)),
						'title' => 'List'
					)
				);
				$sessionAdminInterfaceTabcontainer->addTab($sessionVariableListTab);
			}
		);
	}
}