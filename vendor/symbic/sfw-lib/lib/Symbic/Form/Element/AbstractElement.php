<?php
abstract class Symbic_Form_Element_AbstractElement extends Zend_Form_Element
{
    protected $_disableLoadDefaultDecorators = true;
	protected $_disableLoadDefaultValidators = false;

	protected $_extra = array();

	public function __construct($spec, $options = null)
    {
		parent::__construct($spec, $options);
        $this->loadDefaultValidators();
    }

	public function setClass($class)
	{
		if (is_string($class))
		{
			$this->class = array($class);
			return;
		}

		$this->class = (array)$class;
		return $this;
	}

	public function addClass($class, $placement = 'append')
	{
		if (!isset($this->class))
		{
			$this->class = array();
		}
		elseif (is_string($this->class))
		{
			$this->class = explode(' ', $this->class);
		}
		
		if (is_string($class))
		{
			$class = explode(' ', $class);
		}

		foreach ($class as $oneClass)
		{
			if (!in_array($class, $this->class))
			{
				if ($placement == 'append')
				{
					$this->class[] = $oneClass;
				}
				else
				{
					array_unshift($this->class, $oneClass);
				}
			}
		}
		return $this;
	}

	public function prependClass($class, $placement = 'prepend')
	{
		$this->addClass($class, 'prepend');
		return $this;
	}

	public function appendClass($class, $placement = 'append')
	{
		$this->addClass($class, 'append');
		return $this;
	}

	public function hasClass($class)
	{
		if (!isset($this->class))
		{
			return;
		}
		
		if (is_string($this->class))
		{
			$this->class = explode(' ', $this->class);
		}
	
		return in_array($class, $this->class);
	}
	
	public function unsetClass($class)
	{
		if (!isset($this->class))
		{
			return;
		}

		if (is_string($this->class))
		{
			$this->class = explode(' ', $this->class);
		}
	
		if(($key = array_search($class, $this->class)) !== false)
		{
			unset($this->class[$key]);
		}
		return $this;
	}

	public function replaceClass($unsetClass, $addClass)
	{
		$this->unsetClass($unsetClass);
		$this->addClass($addClass);
		return $this;
	}
	
	public function setExtra($key, $data = null)
	{
		if (is_array($key))
		{
			$this->_extra = array_replace_recursive($this->_extra, $data);
		}
		else
		{
			$this->_extra = array_replace_recursive($this->_extra, array($key =>$data));
		}
		return $this;
	}

	public function getExtra($key = null)
	{
		if ($key === null)
		{
			return $this->_extra;
		}

		if (isset($this->_extra[$key]))
		{
			return $this->_extra[$key];
		}
		return null;
	}
	
	public function getDefaultValidators()
	{
		return array();
	}
	
	protected function _loadDefaultValidators()
	{
		$validators = $this->getDefaultValidators();
		$this->setOptions(array('validators' => $validators));
	}

	public function loadDefaultValidators()
	{
		if (!isset($this->_disableLoadDefaultValidators) || $this->_disableLoadDefaultValidators === true)
		{
			return;
		}

		$validators = $this->getValidators();
        if (empty($validators))
		{
			$this->_loadDefaultValidators();
		}
		return $this;
	}
}