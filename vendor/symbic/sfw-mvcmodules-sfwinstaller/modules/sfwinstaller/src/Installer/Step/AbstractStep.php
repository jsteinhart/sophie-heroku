<?php
namespace Sfwinstaller\Installer\Step;

abstract class AbstractStep
{
	protected $request;
	protected $view;
	protected $session;
	protected $namespace;

	public function __construct($request, $view, $session)
	{
		$this->request = $request;
		$this->view = $view;
		$this->session = $session;
		
		if (empty($this->namespace))
		{
			$class = get_class($this);
			$classParts = explode('\\', $class);
			$lastClassPart = array_pop($classParts);
			$this->namespace = strtolower($lastClassPart);
		}
	}
	
	protected function getRequest()
	{
		return $this->request;
	}

	protected function getForm()
	{
		$form = new \Symbic_Form_Standard();
		return $form;
	}

	protected function clearValues()
	{
		$this->session->unsetAll();
	}

	protected function setValues($values, $namespace = null)
	{
		if (is_null($namespace))
		{
			$namespace = $this->namespace;
		}
		$this->session->$namespace = $values;
	}

	protected function getAllValues()
	{
		$values = array();
		foreach ($this->session as $index => $value)
		{
			$values[$index] = $value;
		}
		return $values;
	}

	protected function getValues($namespace = null)
	{
		if (is_null($namespace))
		{
			$namespace = $this->namespace;
		}
		return (array)$this->session->$namespace;
	}
	
	protected function processValid($form)
	{
		$this->setValues($form->getValues());
		return true;
	}
	
	protected function processForm($form)
	{
		if ($form->isValid($_POST))
		{
			if ($this->processValid($form) === true)
			{
				return true;
			}
		}
		return false;
	}
	
	protected function render($form)
	{
		echo $form->render();
	}

	public function process()
	{
		$form = $this->getForm();
		$form->setDefaults($this->getValues());
	
		if ($this->getRequest()->isPost())
		{
			if ($this->processForm($form) === true)
			{
				return true;
			}
		}

		$this->render($form);

		return false;
	}
}