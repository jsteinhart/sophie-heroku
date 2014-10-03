<?php
abstract class Sophie_Designer_Treatment_Interface_Abstract {

	private $view;
	private $tabs;
	private $treatment;

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

	public function setTreatment($treatment)
	{
		$this->treatment = $treatment;
	}

	public function getTreatment()
	{
		return $this->treatment;
	}

	// INIT

	public function init()
	{
	}

	// TABS

	public function addTab(Sophie_Designer_Treatment_Interface_Tab_Abstract $tab)
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

		$tabContainer = $this->getView()->tabContainer(
			'treatmentDefinition',
			$tabsContent,
			array (),
			array ('style' => 'width: 100%; height: 500px; border: 1px;')
		);

		return $tabContainer;
	}

	public function render()
	{
		return $this->renderTabContainer();
	}
}