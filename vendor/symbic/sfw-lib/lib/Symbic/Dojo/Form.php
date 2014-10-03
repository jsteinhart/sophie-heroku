<?php
class Symbic_Dojo_Form extends Symbic_Form
{
	protected $_defaultDecoratorsetClass = 'Symbic_Dojo_Form_Decoratorset_Table';
	protected $_defaultSubFormClass = 'Symbic_Dojo_Form_SubForm';

	protected $_elementMap = array ();

	public function __construct($options = null)
	{
		// Extensions for dojo
		$this->addPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
			->addPrefixPath('Zend_Dojo_Form_Element', 'Zend/Dojo/Form/Element', 'element')
			->addElementPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
			->addDisplayGroupPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator')
			->setDefaultDisplayGroupClass('Zend_Dojo_Form_DisplayGroup');
		$this->addPrefixPath('Symbic_Dojo_Form_Decorator', 'Symbic/Dojo/Form/Decorator', 'decorator')
			->addPrefixPath('Symbic_Dojo_Form_Element', 'Symbic/Dojo/Form/Element', 'element')
			->addElementPrefixPath('Symbic_Dojo_Form_Decorator', 'Symbic/Dojo/Form/Decorator', 'decorator');

		$this->addElementMaps(array (
				'submit' => 'SubmitButton',
				'text' => 'ValidationTextBox',
				'textarea' => 'SimpleTextarea',
				'select' => 'FilteringSelect',
				'password' => 'PasswordTextBox'
		));

		parent::__construct($options);
	}

	/**
	 * Load the default decorators
	 *
	 * @return void
	 */
	public function loadDefaultDecorators()
	{
		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return;
		}

		$decorators = $this->getDecorators();

		if (empty($decorators)) {
			$this->addDecorator('FormElements')

			->addDecorator('HtmlTag', array('tag' => 'table', 'class' => 'symbic_form_table'))

			->addDecorator('DijitForm');
		}
		//return parent::loadDefaultDecorators();
	}

	public function setView(Zend_View_Interface $view = null)
	{
		if (null !== $view)
		{
			if (false === $view->getPluginLoader('helper')->getPaths('Zend_Dojo_View_Helper'))
			{
				$view->addHelperPath('Zend/Dojo/View/Helper', 'Zend_Dojo_View_Helper');
			}
			if (false === $view->getPluginLoader('helper')->getPaths('Symbic_Dojo_View_Helper'))
			{
				$view->addHelperPath('Symbic/Dojo/View/Helper', 'Symbic_Dojo_View_Helper');
			}
		}
		return parent :: setView($view);
	}
	
	// extend create element to allow element maps
	public function createElement($type, $name, $options = null)
	{
		if (is_null($options) || !is_array($options))
		{
			$options = array ();
		}

		$options['basicElementType'] = $type;
		if (!array_key_exists('elementType', $options))
		{
			$options['elementType'] = $type;
		}
		
		if (isset ($this->_elementMap) && is_array($this->_elementMap) && isset ($this->_elementMap[$type]))
		{
			$type = $this->_elementMap[$type];
		}

		$element = parent::createElement($type, $name, $options);
		return $element;
	}
	
	public function addElementMaps(array $mapping)
	{
		$this->_elementMap = array_merge($this->_elementMap, $mapping);
	}
}