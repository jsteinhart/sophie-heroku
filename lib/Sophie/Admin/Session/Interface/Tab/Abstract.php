<?php
abstract class Sophie_Admin_Session_Interface_Tab_Abstract {

	private $view;

	private $id;
	private $params = array();
	private $attribs = array();
	private $priority = 1000000;
	protected $viewComponent = 'contentPane';

	public function __construct()
	{
		$this->init();
	}

	public function init()
	{
	}

	public function setView(Zend_View_Abstract $view)
	{
		$this->view = $view;
	}
	
	public function getView()
	{
		return $this->view;
	}

		public function setViewComponent($viewComponent)
	{
		$this->viewComponent = $viewComponent;
	}

	public function getViewComponent()
	{
		return $this->viewComponent;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	// PARAMS
	
	public function setParam($name, $value)
	{
		$this->params[$name] = $value;
	}

	public function getParam($name)
	{
		if (isset($this->params[$name]))
		{
			return $this->params[$name];
		}
		else
		{
			return null;
		}
	}
	
	public function setParams($params)
	{
		$this->params = array_merge($this->params, $params);
	}

	public function getParams()
	{
		return $this->params;
	}
	
	// ATTRIBS
	
	public function setAttrib($name, $value)
	{
		$this->attribs[$name] = $value;
	}

	public function getAttrib($name)
	{
		if (isset($this->attribs[$name]))
		{
			return $this->attribs[$name];
		}
		else
		{
			return null;
		}
	}
	
	public function setAttribs($attribs)
	{
		$this->attribs = array_merge($this->attribs, $attribs);
	}

	public function getAttribs()
	{
		return $this->attribs;
	}
	
	// PRIORITY
	
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	public function getPriority()
	{
		return $this->priority;
	}
	
	public function render()
	{
		$viewComponent = $this->getViewComponent();
		return $this->getView()->$viewComponent($this->getId(), $this->getContent(), $this->getParams(), $this->getAttribs());
	}
}