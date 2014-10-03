<?php
abstract class Symbic_Form_AbstractForm extends \Zend_Form
{
    const FILTER							= 'FILTER';
    const VALIDATE							= 'VALIDATE';

	protected $_defaultSubFormClass			= NULL;
	protected $_defaultDisplayGroupClass	= NULL;
	
	protected $_defaultElementLoader		= NULL;
	protected $_defaultDecoratorLoader		= NULL;
	protected $_defaultFilterLoader			= NULL;
	protected $_defaultValidateLoader		= NULL;
	
	protected $_defaultDecoratorsetClass	= NULL;
	protected $_defaultDecoratorsetParams	= NULL;

	protected $_decoratorset				= NULL;
	protected $_isSubForm					= FALSE;

	// extend to add symbic specific prefixes
	public function __construct($options = NULL)
	{
		if (is_array($options))
		{
			$this->setOptions($options);
		}
		elseif ($options instanceof Zend_Config)
		{
			$this->setConfig($options);
		}

		// Extentions to set defaults
		$this->setMethod('POST');
		$this->setName(get_class($this));

		// !!! EXPLICITLY DO NOT LOAD DEFAULT DECORATORS HERE !!!

		$this->init();
	}

	// use classmap loader for elementtypes and decorators		
    public function getPluginLoader($type = NULL)
    {
        $type = strtoupper($type);
		if ($type === self::ELEMENT)
		{
			$loaderClass = $this->_defaultElementLoader;
		}
		elseif ($type === self::DECORATOR)
		{
			$loaderClass = $this->_defaultDecoratorLoader;
		}
		elseif ($type === self::FILTER)
		{
			$loaderClass = $this->_defaultFilterLoader;
		}
		elseif ($type === self::VALIDATE)
		{
			$loaderClass = $this->_defaultValidateLoader;
		}
		else
		{
			throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
		}
		
		$this->_loaders[$type] = $loaderClass::getInstance();
        return $this->_loaders[$type];		
    }

    public function getFallbackPluginLoader($type = null)
    {
		return parent::getPluginLoader($type);
	}

	public function getIsSubForm()
	{
		return $this->_isSubForm;
	}
	
	public function setDecoratorset($decoratorset, $decoratorsetParams = NULL, $applyToSubForms = TRUE)
	{
		if (is_string($decoratorset))
		{
			$this->_decoratorset = new $decoratorset($decoratorsetParams);
		}
		else
		{
			$this->_decoratorset = $decoratorset;
		}

		if (!$this->_decoratorset instanceof Symbic_Form_Decoratorset_AbstractDecoratorset)
		{
			throw new Exception('Decoratorset must be an instance of Symbic_Form_Decoratorset_AbstractDecoratorset');
		}

		if ($applyToSubForms)
		{
			$subForms = $this->getSubForms();
			foreach ($subForms as $subForm)
			{
				$subForm->setDecoratorset($decoratorset, null, $applyToSubForms);
			}
		}
	}

	public function getDecoratorset()
	{
		if (empty($this->_decoratorset))
		{
			$this->setDecoratorset($this->_defaultDecoratorsetClass, $this->_defaultDecoratorsetParams);
		}
		return $this->_decoratorset;
	}

	public function createSubForm($options = null)
	{
		return new $this->_defaultSubFormClass($options);
	}

    public function createElement($type, $name, $options = null)
    {
		if (!is_string($type))
		{
			throw new Zend_Form_Exception('Element type must be a string indicating type');
		}

		if (!is_string($name))
		{
			throw new Zend_Form_Exception('Element name must be a string');
		}

		if ($options instanceof Zend_Config)
		{
			$options = $options->toArray();
		}

		if ($options === null || !is_array($options))
		{
			$options = array();
        }

		$options['disableLoadDefaultDecorators'] = true;

		$type = ucfirst($type);
		$class = $this->getPluginLoader(self::ELEMENT)->load($type);

		if (gettype($class) !== 'string')
		{
			throw new Exception('Error creating form element ' . $type);
		}
		
		return new $class($name, $options);
    }

	public function addElement($element, $name = null, $options = null)
	{
		if (is_string($element))
		{
			$element = $this->createElement($element, $name, $options);
		}

		if (!$element instanceof Zend_Form_Element)
		{
			throw new Zend_Form_Exception('Element must be specified by string or Zend_Form_Element instance');
		}

		$name = $element->getName();

		if (isset($this->_elements[$name]))
		{
			throw new Exception('Form element with this name already exists');
		}

		$element->setPluginLoader($this->getPluginLoader(self::DECORATOR), self::DECORATOR);
		$element->setPluginLoader($this->getPluginLoader(self::FILTER), self::FILTER);
		$element->setPluginLoader($this->getPluginLoader(self::VALIDATE), self::VALIDATE);
		
		$this->_elements[$name] = $element;
		
		$this->_order[$name] = $element->getOrder();
		$this->_orderUpdated = true;
		$this->_setElementsBelongTo($name);

		return $this;
	}
	
	public function loadDefaultDecorators()
	{
		return $this;
	}

	public function loadDefaultElementDecorators()
	{
		return $this;
	}

	public function hasElementErrors()
	{
		$errors = false;
		foreach ($this->getElements() as $key => $element)
		{
			if ($element->hasErrors())
			{
				$errors = true;
				break;
			}
		}
		return $errors;
	}
	
	public function render(Zend_View_Interface $view = null)
	{
		// Load element decorators
		$elements = $this->getElements();
		foreach ($elements as $element)
		{
			$decorators = $element->getDecorators();
			if (sizeof($decorators) > 0)
			{
				continue;
			}
			
			$decoratorset = $element->getAttrib('decoratorset');
			if (is_null($decoratorset))
			{
				$decoratorset = $this->getDecoratorset();
			}
			else
			{
				if (is_string($decoratorset))
				{
					$decoratorsetParams = (array)$element->getAttrib('decoratorsetParams');
				}
				elseif (is_array($decoratorset))
				{
					if (sizeof($decoratorset) == 1)
					{
						$decoratorset = $decoratorset[0];
						$decoratorsetParams = array();
					}
					elseif  (sizeof($decoratorset) == 2)
					{
						$decoratorsetParams = (array)$decoratorset[1];
						$decoratorset = $decoratorset[0];
					}
					else
					{
						throw new Exception('Decoratorset specified as array must have one or two fields');
					}
				}
				else
				{
					throw new Exception('Decoratorset must be specified as either string or array');
				}

				$decoratorset = new $decoratorset($decoratorsetParams);
			}

			if (!$decoratorset instanceof Symbic_Form_Decoratorset_AbstractDecoratorset)
			{
				throw new Exception('Decoratorset must be an instance of Symbic_Form_Decoratorset_AbstractDecoratorset');
			}
			
			$elementDecorators = $decoratorset->getElementDecorators($element);

			if (is_null($elementDecorators))
			{
				throw new Exception('No decorators defined for element type ' . get_class($element));
			}
			
			$element->setDecorators($elementDecorators);
		}
		
		// Load form decorators and render form		
		$decorators = $this->getDecorators();
		if (sizeof($decorators) == 0)
		{
			$this->setDecorators($this->getDecoratorset()->getFormDecorators($this));
		}

        return parent::render($view);
	}
}