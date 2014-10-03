<?php
class Symbic_Form_Decorator_ElementTypeConditional extends Zend_Form_Decorator_Abstract
{

	protected $_subDecorator;
	
	public function setSubDecorator($subDecorator)
	{
		$this->_subDecorator = $subDecorator;
	}

	public function getSubDecorator()
	{
		if (!isset($this->_subDecorator))
		{
			$options = $this->getOptions();
			if (!isset($options['decorator']))
			{
				throw new Exception('Coditional Decorator requires a "decoratorName" option to be set');
			}
			
			$decoratorClass = $this->getPluginLoader(Symbic_Form::DECORATOR)->load($options['decoratorName']);
			if (!isset($options['decoratorOptions']))
			{
				$this->_subDecorator = new $decoratorClass();
			} else {
				$this->_subDecorator = new $decoratorClass($options['decoratorOptions']);
			}
			
			$this->_subDecorator->setElement($this->getElement());
		}
		return $this->_subDecorator;
	}
	
	public function render($content)
	{
		$elementClass = get_class($this->getElement());
		$type = substr($elementClass, strrpos($elementClass, '_') + 1);

		$options = $this->getOptions();
		if (isset($options['include']))
		{
			$decorate = false;
			if (is_array($options['include']) && in_array($type, $options['include']))
			{
				$decorate = true;
			}
			elseif ((string)$options['include'] == $type)
			{
				$decorate = true;
			}
		}
		elseif (isset($options['exclude']))
		{
			$decorate = true;
			if (is_array($options['exclude']) && in_array($type, $options['exclude']))
			{
				$decorate = false;
			}
			elseif ((string)$options['exclude'] == $type)
			{
				$decorate = false;
			}
		}
		else
		{
			throw Exception('Either include or exclude must be set for Conitional Decorator');
		}
		
		if (!$decorate)
		{
			return $content;
		}
		
		$decorator = $this->getSubDecorator();
		return $decorator->render($content);
	}
}