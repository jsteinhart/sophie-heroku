<?php
// TODO: eliminate this class in favor of Symbic_Form_Standard
class Symbic_Form extends Zend_Form
{

	protected $_isSubForm = false;

	// overwrite to use symbic specific default sub classes
	protected $_defaultDisplayGroupClass = 'Symbic_Form_DisplayGroup';
	protected $_defaultSubFormClass = 'Symbic_Form_SubForm';

	// add decorator sets and default decorator set
	protected $_defaultDecoratorsetClass = 'Symbic_Form_Decoratorset_Table';
	protected $_decoratorsetEnabled = true;
	protected $_decoratorset = null;

	// extend to add symbic specific prefixes
	public function __construct($options = null)
	{
		if (is_array($options))
		{
			$this->setOptions($options);
		}
		elseif ($options instanceof Zend_Config)
		{
			$this->setConfig($options);
		}

		// Extensions for plugin loader
		$this->addPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator', 'decorator');
		$this->addPrefixPath('Symbic_Form_Element', 'Symbic/Form/Element', 'element');
		$this->addElementPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator', 'decorator');
		$this->addDisplayGroupPrefixPath('Symbic_Form_Decorator', 'Symbic/Form/Decorator');

		// Extentions to set defaults
		$this->setMethod('POST');
		$this->setName(get_class($this));

		// !!! EXPLICITLY DO NOT LOAD DEFAULT DECORATORS HERE !!!

		$this->init();
	}

	// extend to add symbic specific prefixes
	public function setView(Zend_View_Interface $view = null)
	{
		if (null !== $view)
		{
			if (false === $view->getPluginLoader('helper')->getPaths('Symbic_View_Helper'))
			{
				$view->addHelperPath('Symbic/View/Helper', 'Symbic_View_Helper');
			}
		}
		return parent :: setView($view);
	}

	public function getIsSubForm()
	{
		return $this->_isSubForm;
	}

	// added function to set the use of a specific default decoratorset
	public function setDecoratorset($decoratorset, $applyToSubForms = true)
	{
		if (is_string($decoratorset))
		{
			$decoratorset = new $decoratorset();
		}

		if (!$decoratorset instanceof Symbic_Form_Decoratorset_AbstractDecoratorset)
		{
			throw new Exception('Decoratorset must be an instance of Symbic_Form_Decoratorset_AbstractDecoratorset');
		}

		$this->_decoratorset = $decoratorset;

		if ($applyToSubForms)
		{
			$subForms = $this->getSubForms();
			foreach ($subForms as $subForm)
			{
				$subForm->setDecoratorset($decoratorset, $applyToSubForms);
			}
		}
	}

	public function getDecoratorset()
	{
		if (is_null($this->_decoratorset) || $this->_decoratorset == '')
		{
			$this->setDecoratorset($this->_defaultDecoratorsetClass);
		}
		return $this->_decoratorset;
	}

	public function getDecoratorsetEnabled()
	{
		return $this->_decoratorsetEnabled;
	}

	public function enableDecoratorset($applyToSubForms = true)
	{
		$this->_decoratorsetEnabled = true;
		if ($applyToSubForms)
		{
			$subForms = $form->getSubForms();
			foreach ($subForms as $subForm)
			{
				$subForm->enableDecoratorset($applyToSubForms);
			}
		}
	}

	public function disableDecoratorset($applyToSubForms = true)
	{
		$this->_decoratorsetEnabled = false;
		if ($applyToSubForms)
		{
			$subForms = $form->getSubForms();
			foreach ($subForms as $subForm)
			{
				$subForm->enableDecoratorset($applyToSubForms);
			}
		}
	}

	// extend to use decoratorset instead of static default decorators
	public function loadDefaultDecorators()
	{
		if ($this->loadDefaultDecoratorsIsDisabled())
		{
			return $this;
		}

		if ($this->getDecoratorsetEnabled())
		{
			$this->clearDecorators();
			$formDecorators = $this->getDecoratorset()->getFormDecorators($this);

			if (!is_null($formDecorators))
			{
				$this->setDecorators($formDecorators);
				return $this;
			}
		}

		return parent :: loadDefaultDecorators();
	}

	// added subform factory function to utilize the defaultSubFormClass variable
	public function createSubForm($options = null)
	{
		$subFormClass = $this->_defaultSubFormClass;
		$subForm = new $subFormClass ($options);
		//$subForm->setDecoratorset($this->_decoratorset);
		return $subForm;
	}

	// extend create element to set element decorators according to decorator set
	public function createElement($type, $name, $options = null)
	{
		if (is_null($options) || !is_array($options))
		{
			$options = array ();
		}
		$options['disableLoadDefaultDecorators'] = true;

		if (!array_key_exists('elementType', $options))
		{
			$options['elementType'] = $type;
		}

		$element = parent :: createElement($type, $name, $options);

		return $element;
	}

	public function loadDefaultElementDecorators()
	{
		$elements = $this->getElements();
		foreach ($elements as $element)
		{
			$decorators = $element->getDecorators();

			if (sizeof($decorators) == 0)
			{
				$decoratorset = $element->getAttrib('decoratorset');
				if (!is_null($decoratorset))
				{
					if (is_string($decoratorset))
					{
						$decoratorsetParams = $element->getAttrib('decoratorsetParams');
						if (is_null($decoratorsetParams))
						{
							$decoratorsetParams = array ();
						}

						$decoratorset = new $decoratorset($decoratorsetParams);
					}

					if (!$decoratorset instanceof Symbic_Form_Decoratorset_AbstractDecoratorset)
					{
						throw new Exception('Decoratorset must be an instance of Symbic_Form_Decoratorset_AbstractDecoratorset');
					}

					$elementDecorators = $decoratorset->getElementDecorators($element);
				}
				else
				{
					if ($this->getDecoratorsetEnabled())
					{
						$decoratorset = $this->getDecoratorset();
						$elementDecorators = $decoratorset->getElementDecorators($element);
					}
				}

				if (!is_null($elementDecorators))
				{
					$element->setDecorators($elementDecorators);
				}
			}

		}
	}

	public function render(Zend_View_Interface $view = null)
	{
		$this->loadDefaultDecorators();
		$this->loadDefaultElementDecorators();
		return parent :: render($view);
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
}