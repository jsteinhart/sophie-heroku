<?php
abstract class Symbic_Form_Decoratorset_AbstractDecoratorset
{
	protected $_formDecorators = null;
	protected $_subFormsFormDecorators = null;
	protected $_subFormDecorators = null;
	protected $_elementDefaultDecorators = null;
	protected $_elementInheritDecorators = array();
	protected $_elementDecorators = array();

	public function __construct()
	{
		$this->init();
	}

	protected function init()
	{
	}

	/*// add function to process actual setting of decorators from a decorator set
	public function setDecoratorsFromDecoratorSet($decoratorSet = null)
	{
		// default to $this->_decoratorSets
		if (is_null($decoratorSet))
		{
			$decoratorSet = $this->_decoratorSets;
		}

		// load decorators from decorator map array
		if (isset ($this->_decoratorSets) && is_array($this->_decoratorSets) && isset ($this->_decoratorSets[$this->_useDecoratorSet]) && is_array($this->_decoratorSets[$this->_useDecoratorSet]))
		{
			$this->setDecorators($this->_decoratorSets[$this->_useDecoratorSet]);
		}

		// let decorators be set by a decoratorset method
		$decoratorMethod = 'loadDefaultDecorators' . $this->_useDecoratorSet;
		if (method_exists($this, $decoratorMethod))
		{
			$this-> $decoratorMethod ();
		}
	}*/

	public function getFormDecorators($form)
	{
		if ($form->getIsSubform())
		{
			if (method_exists($this, '_getSubFormDecorators'))
			{
				return $this->_getSubFormDecorators($form);
			}

			if (!is_null($this->_subFormDecorators))
			{
				return $this->_subFormDecorators;
			}
		}

		if (sizeof($form->getSubForms()) > 0)
		{
			if (method_exists($this, '_getSubFormsFormDecorators'))
			{
				return $this->_getSubFormsFormDecorators($form);
			}

			if (isset($this->_subFormsFormDecorators))
			{
				return $this->_subFormsFormDecorators;
			}
		}

		if (method_exists($this, '_getFormDecorators'))
		{
			return $this->_getFormDecorators($form);
		}

		return $this->_formDecorators;
	}

	public function getElementDecorators(Zend_Form_Element $element)
	{
		$type = get_class($element);
		$type = substr($type, strrpos($type, '_') + 1);

		while (isset($this->_elementInheritDecorators[$type]))
		{
			$type = $this->_elementInheritDecorators[$type];
		}

		$elementMethod = '_get' . ucfirst($type) . 'ElementDecorators';
		if (method_exists($this, $elementMethod))
		{
			return $this->$elementMethod($type);
		}

		if (array_key_exists($type, $this->_elementDecorators))
		{
			return $this->_elementDecorators[$type];
		}

		$elementMethod = '_getDefaultElementDecorators';
		if (method_exists($this, $elementMethod))
		{
			return $this->$elementMethod();
		}

		return $this->_elementDefaultDecorators;
	}
}