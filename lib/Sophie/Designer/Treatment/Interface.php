<?php
class Sophie_Designer_Treatment_Interface extends Sophie_Designer_Treatment_Interface_Abstract {

	public function init()
	{
		$this->initStructureTab();
		$this->initParticipantsTab();
		$this->initDataTab();
		$this->initAssetsTab();
		$this->initReportsTab();
		$this->initSessiontypesTab();
		$this->initLogTab();
		
		$eventManager = Zend_Registry::get('Zend_EventManager');
		$eventManager->trigger('sophie_designer_treatment_interface_inittab', null, array( 'sophie_designer_treatment_interface' => $this ) );
	}

	private function initStructureTab()
	{
		$view = $this->getView();
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentStructureTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
				'href' => $view->url(array('controller' => 'treatment', 'action' => 'detailsstructure')),
				'parseOnLoad' => 'true',
				'executeScripts' => 'true',
				'title' => '<img src="/_media/Icons/folder.png" alt="Structure" class="icon" /> Structure'
		));
		$tab->setPriority(100);
		$this->addTab($tab);
	}

	private function initParticipantsTab()
	{
		$view = $this->getView();
		
		$tabContainer = new Sophie_Designer_Treatment_Interface_Tab_Container();
		$tabContainer->setId('treatmentParticipantTab');
		$tabContainer->setParams(
			array(
				'title' => '<img src="/_media/Icons/group.png" alt="Participants" class="icon" /> Participants'
			)
		);
		$tabContainer->setPriority(200);
		$this->addTab($tabContainer);

		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentParticipantTypeTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
				'href' => $view->url(array('controller' => 'type', 'action' => 'list')),
				'title' => 'Types'
		));
		$tab->setPriority(100);
		$tabContainer->addTab($tab);

		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentParticipantGroupStructureTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
				'href' => $view->url(array('controller' => 'groupstructure', 'action' => 'list')),
				'title' => 'Group Structure'
		));
		$tab->setPriority(200);
		$tabContainer->addTab($tab);
	}

	private function initDataTab()
	{

		$view = $this->getView();
		/*$tabContainer = new Sophie_Designer_Treatment_Interface_Tab_Container();
		$tabContainer->setId('treatmentDataTab');
		$tabContainer->setParams(
			array(
				'title' => '<img src="/_media/Icons/table.png" alt="Data" class="icon" /> Data'
			)
		);
		$tabContainer->setPriority(300);
		$this->addTab($tabContainer);
		*/
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentDataVariableTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
				'href' => $view->url(array('controller' => 'variable', 'action' => 'list')),
				'title' => '<img src="/_media/Icons/table.png" alt="Data" class="icon" /> Variables'
		));
		//$tab->setPriority(100);
		//$tabContainer->addTab($tab);
		$tab->setPriority(300);
		$this->addTab($tab);

		/*$treatmentDataParameterTab = $this->contentPane('treatmentDataParameterTab', '', array (
			'href' => $this->url(array (
				'controller' => 'parameter',
				'action' => 'list'
			)
		), 'preload' => 'false', 'refreshOnShow' => 'true', 'preventCache' => 'true', 'title' => 'Parameters'));
		$treatmentDataTabs .= $treatmentDataParameterTab;*/
	}

	private function initAssetsTab()
	{
		$view = $this->getView();
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentAssetTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
			'href' => $view->url(array('controller' => 'asset',	'action' => 'index')),
			'title' => '<img src="/_media/Icons/picture.png" alt="Assets" class="icon" /> Assets'
		));
		$tab->setPriority(400);
		$this->addTab($tab);
	}
	
	private function initReportsTab()
	{
		$view = $this->getView();
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentReportTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
			'href' => $view->url(array ('controller' => 'report', 'action' => 'index')),
			'title' => '<img src="/_media/Icons/chart_bar.png" alt="Report" class="icon" /> Reports'
		));
		$tab->setPriority(500);
		$this->addTab($tab);
	}

	private function initSessiontypesTab()
	{
		$view = $this->getView();
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentSessiontypesTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
			'href' => $view->url(array('action' => 'detailssessiontypes')),
			'title' => '<img src="/_media/Icons/brick.png" alt="Session Types" class="icon" /> Session Types'
		));
		$tab->setPriority(600);
		$this->addTab($tab);
	}

	private function initLogTab()
	{	
		$view = $this->getView();
		$tab = new Sophie_Designer_Treatment_Interface_Tab_Href();
		$tab->setId('treatmentLogTab');
		$tab->setViewComponent('dojoxContentPane');
		$tab->setParams(array(
			'href' => $view->url(array('controller' => 'log', 'action' => 'index')),
			'title' => '<img src="/_media/Icons/application_view_list.png" alt="Log" class="icon" /> Log'
		));
		$tab->setPriority(700);
		$this->addTab($tab);
	}
}