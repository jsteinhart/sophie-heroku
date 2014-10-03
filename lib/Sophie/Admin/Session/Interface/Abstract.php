<?php
abstract class Sophie_Admin_Session_Interface_Abstract {

	private $view;
	private $tabs;
	private $session;

	public function _construct()
	{
		//$this->init();
	}

	public function setView(Zend_View_Abstract $view)
	{
		$this->view = $view;
	}

	public function getView()
	{
		return $this->view;
	}

	public function setSession($session)
	{
		$this->session = $session;
	}

	public function getSession()
	{
		return $this->session;
	}

	// INIT

	public function init()
	{
	}

	// TABS

	public function addTab(Sophie_Admin_Session_Interface_Tab_Abstract $tab)
	{
		$this->tabs[] = $tab;
	}

	public function getTabs()
	{
		return $this->tabs;
	}

	public function sortTabs()
	{
		usort($this->tabs, function($a, $b)
		{
			return $a->getPriority() > $b->getPriority();
		});
	}

	// RENDER

	public function renderSidebar()
	{
		$sidebarContent = $this->getView()->partial('session/detailsSidebar.phtml',
				array (
					'session' => $this->getSession()
				)
			);
		$sidebarContentPane = $this->getView()->contentPane('sessionSidebar', $sidebarContent, array (
			'region' => 'left',
			'splitter' => true,
			'style' => 'width: 250px;'
		));
		return $sidebarContentPane;
	}

	public function renderTabContainer()
	{
		$tabsContent = '';

		$this->sortTabs();
		$tabs = $this->getTabs();

		foreach ($tabs as $tab)
		{
			//echo '<pre>';
			//print_r($tab);
			//echo '</pre>';
			$tab->setView($this->getView());
			$tabsContent .= $tab->render();
		}

		$tabContainer = $this->getView()->tabContainer('sessionDetailsTabContainer', $tabsContent, array (
			'region' => 'center'
		), array (
			'style' => 'width: 100%; height: 100%; border: 1px'
		));

		return $tabContainer;
	}

	public function render()
	{
		$sessionDetailsMain = $this->renderSidebar();
		$sessionDetailsMain .=  $this->renderTabContainer();

		return $this->getView()->borderContainer('sessionDetailsMain', $sessionDetailsMain, array (
			'design' => 'headline',
		), array (
			'style' => 'width: 100%; height: 500px',
		));
	}
}