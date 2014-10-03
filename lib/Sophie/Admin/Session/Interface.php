<?php
class Sophie_Admin_Session_Interface extends Sophie_Admin_Session_Interface_Abstract
{
	public function init()
	{
		// $this->initOverviewTab();
		$this->initMonitorTab();
		$this->initParticipantsTab();
		$this->initVariablesTab();
		$this->initReportsTab();
		$this->initDebugTab();
		$this->initLogTab();

		$eventManager = Zend_Registry::get('Zend_EventManager');
		$eventManager->trigger('sophie_admin_session_interface_inittab', null, array( 'sophie_admin_session_interface' => $this ) );
	}

	private function initOverviewTab()
	{
		$sessionOverviewTab = $this->contentPane('sessionOverviewTab', '', array (
			'href' => $this->url(array (
				'action' => 'overview'
			)
		), 'preload' => 'false', 'refreshOnShow' => 'true', 'preventCache' => 'true', 'title' => '<img src="/_media/Icons/folder.png" border="0" alt="Overview" /> Overview'));
		$this->addTab($sessionOverviewTab);
	}

	private function initMonitorTab()
	{
		$view = $this->getView();

		$sessionMonitorTab = new Sophie_Admin_Session_Interface_Tab();
		$sessionMonitorTab->setId('sessionMonitorTab');
		$sessionMonitorTab->setParams(
			array(
				'title' => '<img src="/_media/Icons/monitor.png" alt="Monitor" class="icon" /> Monitor'
				)
			);
		$sessionMonitorTab->setPriority(100);
		$sessionMonitorTab->setContent(file_get_contents(BASE_PATH . '/modules/expadmin/views/scripts/monitor/index.phtml'));
		$this->addTab($sessionMonitorTab);
	}

	private function initParticipantsTab()
	{
		$sessionParticipantTabContainer = new Sophie_Admin_Session_Interface_Tab_Container();
		$sessionParticipantTabContainer->setId('sessionParticipantTab');
		$sessionParticipantTabContainer->setParams(
			array(
				'title' => '<img src="/_media/Icons/group.png" alt="Participants" class="icon" /> Participants'
			)
		);
		$sessionParticipantTabContainer->setPriority(200);
		$this->addTab($sessionParticipantTabContainer);

		$sessionParticipantListTab = new Sophie_Admin_Session_Interface_Tab_Href();
		$sessionParticipantListTab->setId('sessionParticipantListTab');
		$sessionParticipantListTab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'participant',
					'action' => 'list'
					)),
				'title' => 'List'
			)
		);
		$sessionParticipantListTab->setPriority(100);
		$sessionParticipantTabContainer->addTab($sessionParticipantListTab);

		$sessionParticipantEditAllTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionParticipantEditAllTab');
		$sessionParticipantEditAllTab->setId('sessionParticipantEditAllTab');
		$sessionParticipantEditAllTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'participant',
					'action' => 'editall'
				)),
				'title' => 'Edit All'
			)
		);
		$sessionParticipantEditAllTab->setPriority(200);
		$sessionParticipantTabContainer->addTab($sessionParticipantEditAllTab);

		$sessionParticipantEditTypeTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionParticipantEditTypeTab');
		$sessionParticipantEditTypeTab->setId('sessionParticipantEditTypeTab');
		$sessionParticipantEditTypeTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'participant',
					'action' => 'edittype'
				)),
				'title' => 'Edit by Participant Type'
			)
		);
		$sessionParticipantEditTypeTab->setPriority(300);
		$sessionParticipantTabContainer->addTab($sessionParticipantEditTypeTab);

/*
		$sessionParticipantAddTypeTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionParticipantAddTab');
		$sessionParticipantAddTypeTab->setId('sessionParticipantAddTab');
		$sessionParticipantAddTypeTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'participant',
					'action' => 'add'
				)),
				'title' => 'Add Participant'
			)
		);
		$sessionParticipantAddTypeTab->setPriority(400);
		$sessionParticipantTabContainer->addTab($sessionParticipantAddTypeTab);
*/
		$sessionGroupListTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionGroupListTab');
		$sessionGroupListTab->setId('sessionGroupListTab');
		$sessionGroupListTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'group',
					'action' => 'List'
				)),
				'title' => 'Groups'
			)
		);
		$sessionGroupListTab->setPriority(500);
		$sessionParticipantTabContainer->addTab($sessionGroupListTab);

		$sessionGroupAddTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionGroupAddTab');
		$sessionGroupAddTab->setId('sessionGroupAddTab');
		$sessionGroupAddTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'group',
					'action' => 'add'
				)),
				'title' => 'Add Groups'
			)
		);
		$sessionGroupAddTab->setPriority(600);
		$sessionParticipantTabContainer->addTab($sessionGroupAddTab);

		$sessionGroupingTab = new Sophie_Admin_Session_Interface_Tab_Href('sessionGroupingTab');
		$sessionGroupingTab->setId('sessionGroupingTab');
		$sessionGroupingTab->setParams(
			array(
				'href' => $this->getView()->url(array (
					'controller' => 'grouping',
					'action' => 'index'
				)),
				'title' => 'Grouping'
			)
		);
		$sessionGroupingTab->setPriority(700);
		$sessionParticipantTabContainer->addTab($sessionGroupingTab);

		/*
		$sessionAdminTab = $this->contentPane('sessionAdminTab', '', array (
			'href' => $this->url(array (
				'controller' => 'admin',
				'action' => 'index'
			)
		), 'preload' => 'false', 'refreshOnShow' => 'true', 'preventCache' => 'true', 'title' => '<img src="/_media/Icons/cog.png" alt="Admin" class="icon" /> Admin'));
		$sessionTabs .= $sessionAdminTab; */
	}

	private function initVariablesTab()
	{
		$tabContainer = new Sophie_Admin_Session_Interface_Tab_Container();
		$tabContainer->setId('sessionVariableTab');
		$tabContainer->setParams(
			array(
				'title' => '<img src="/_media/Icons/database_table.png" alt="Variables" class="icon" /> Variables'
			)
		);
		$tabContainer->setPriority(300);
		$this->addTab($tabContainer);

		$sessionVariableTabulateTab = new Sophie_Admin_Session_Interface_Tab_Href();
		$sessionVariableTabulateTab->setId('sessionVariableTabulateTab');
		$sessionVariableTabulateTab->setViewComponent('dojoxContentPane');
		$sessionVariableTabulateTab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'variable',
					'action' => 'tabulate'
				)),
				'executeScripts' => 'true',
				'title' => 'Tabulate'
			)
		);
		$sessionVariableTabulateTab->setPriority(100);
		$tabContainer->addTab($sessionVariableTabulateTab);

		$sessionVariableListTab = new Sophie_Admin_Session_Interface_Tab_Href();
		$sessionVariableListTab->setId('sessionVariableListTab');
		$sessionVariableListTab->setViewComponent('dojoxContentPane');
		$sessionVariableListTab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'variable',
					'action' => 'list'
				)),
				'executeScripts' => 'true',
				'title' => 'List'
			)
		);
		$sessionVariableListTab->setPriority(200);
		$tabContainer->addTab($sessionVariableListTab);

		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setId('sessionVariableHistoryListTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'variablelog',
					'action' => 'list'
				)),
				'executeScripts' => 'true',
				'title' => 'Log'
			)
		);
		$tab->setPriority(300);
		$tabContainer->addTab($tab);

		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setId('sessionVariableAddTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'variable',
					'action' => 'add'
				)),
				'executeScripts' => 'true',
				'title' => 'Set',
				'refreshOnShow' => 'false'
			)
		);
		$tab->setPriority(500);
		$tabContainer->addTab($tab);

		$sessionVariableImportTab = new Sophie_Admin_Session_Interface_Tab_Href();
		$sessionVariableImportTab->setId('sessionVariableImportTab');
		$sessionVariableImportTab->setParams(
			array (
				'href' => $this->getView()->url(array (
					'controller' => 'variable',
					'action' => 'import'
				)),
				'title' => 'Import',
				'refreshOnShow' => 'false'
			)
		);
		$sessionVariableImportTab->setPriority(600);
		$tabContainer->addTab($sessionVariableImportTab);
	}

	private function initReportsTab()
	{
		$tabContainer = new Sophie_Admin_Session_Interface_Tab_Container();
		$tabContainer->setId('sessionReportTab');
		$tabContainer->setParams(
			array(
				'title' => '<img src="/_media/Icons/chart_bar.png" alt="Reports" class="icon" /> Reports'
			)
		);
		$tabContainer->setPriority(400);
		$this->addTab($tabContainer);
	
		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setId('sessionCodesReportTab');
		$tab->setParams(array(
				'href' => $this->getView()->url(array( 'controller' => 'report', 'action' => 'codes')),
				'title' => 'Participant Codes'
			));
		$tab->setPriority(100);
		$tabContainer->addTab($tab);

		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setId('sessionPayoffsReportTab');
		$tab->setParams(array(
				'href' => $this->getView()->url(array( 'controller' => 'report', 'action' => 'payoffs')),
				'title' => 'Participant Payoffs'
			));
		$tab->setPriority(200);
		$tabContainer->addTab($tab);
		
		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setId('sessionTreatmentReportTab');
		$tab->setParams(array(
				'href' => $this->getView()->url(array( 'controller' => 'report', 'action' => 'treatment')),
				'title' => 'Treatment Reports'
			));
		$tab->setPriority(300);
		$tabContainer->addTab($tab);
	}

	private function initDebugTab()
	{
		$tab = new Sophie_Admin_Session_Interface_Tab_Href();
		$tab->setViewComponent('dojoxContentPane');
		$tab->setId('sessionDebugTab');
		$tab->setParams(
			array(
				'href' => $this->getView()->url(
					array(
						'controller' => 'debug',
						'action' => 'index'
					)),
				'title' => '<img src="/_media/Icons/eye.png" alt="Debug" class="icon" /> Debug'
				)
			);
		$tab->setPriority(500);
		$this->addTab($tab);
	}

	private function initLogTab()
	{
		$sessionLogTab = new Sophie_Admin_Session_Interface_Tab_Href();
		$sessionLogTab->setId('sessionLogTab');
		$sessionLogTab->setViewComponent('dojoxContentPane');
		$sessionLogTab->setParams(
			array(
				'href' => $this->getView()->url(
					array(
						'controller' => 'log',
						'action' => 'index'
					)),
				'executeScripts' => 'true',
				'title' => '<img src="/_media/Icons/application_view_list.png" alt="Log" class="icon" /> Log',
				'refreshOnShow' => 'false'
				)
			);
	
		$sessionLogTab->setPriority(600);
		$this->addTab($sessionLogTab);
	}
}