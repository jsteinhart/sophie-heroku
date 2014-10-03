<?php
class Sophie_Admin_Session_Interface_Tab_Container extends Sophie_Admin_Session_Interface_Tab_Abstract {

	private $containerId = null;
	private $containerParams = array();
	private $containerAttribs = array();
	
	private $tabs = array();

	public function init()
	{
		$this->setContainerParams(
			array(
				'region' => 'center',
				'nested' => 'true',
			)
		);
		$this->setContainerAttribs(
			array(
				'style' => 'width: 100%; height: 100%; border: 1px;'
			)
		);
	}
	
	// CONTAINER ID
	
	public function setContainerId($id)
	{
		$this->containerId = $id;
	}

	public function getContainerId()
	{
		if (is_null($this->containerId))
		{
			return $this->getId() . 'Container';
		}
		return $this->containerId;
	}

	// CONTAINER PARAMS
	
	public function setContainerParam($name, $value)
	{
		$this->containerParams[$name] = $value;
	}

	public function getContainerParam($name)
	{
		if (isset($this->containerParams[$name]))
		{
			return $this->containerParams[$name];
		}
		else
		{
			return null;
		}
	}
	
	public function setContainerParams($params)
	{
		$this->containerParams = array_merge($this->containerParams, $params);
	}

	public function getContainerParams()
	{
		return $this->containerParams;
	}
	
	// ATTRIBS
	
	public function setContainerAttrib($name, $value)
	{
		$this->containerAttribs[$name] = $value;
	}

	public function getContainerAttrib($name)
	{
		if (isset($this->containerAttribs[$name]))
		{
			return $this->containerAttribs[$name];
		}
		else
		{
			return null;
		}
	}
	
	public function setContainerAttribs($attribs)
	{
		$this->containerAttribs = array_merge($this->containerAttribs, $attribs);
	}

	public function getContainerAttribs()
	{
		return $this->containerAttribs;
	}

	// TABS
	
	public function addTab(Sophie_Admin_Session_Interface_Tab_Abstract $tab)
	{
		$this->tabs[] = $tab;
	}
	
	public function sortTabs()
	{
		usort($this->tabs, function($a, $b)
		{
			return $a->getPriority() > $b->getPriority();
		});
	}
	
	public function getContent()
	{
		$eventManager = Zend_Registry::get('Zend_EventManager');
		$eventManager->trigger(
			'sophie_admin_session_interface_tabcontainer_VARIABLES_prerender',
			null,
			array(
			//	'sophie_admin_session_interface' => $this->getInterface(),
				'sophie_admin_session_interface_tabconainer' => $this
				)
		);
		
		$tabContents = '';
		
		$this->sortTabs();
		foreach ($this->tabs as $tab)
		{
			$tab->setView($this->getView());
			$tabContents .= $tab->render($this->getView());
		}
		
		$tabContents = $this->getView()->tabContainer($this->getContainerId(), $tabContents, $this->getContainerParams(), $this->getContainerAttribs());
		return $tabContents;
	}

}