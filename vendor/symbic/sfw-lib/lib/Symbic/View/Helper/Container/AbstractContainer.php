<?php
class Symbic_View_Helper_Container_AbstractContainer extends \Symbic_Base_AbstractSingleton
{
	protected $elements = array();
	
	public function append($element)
	{
		if (isset($element['id']) && (!isset($element['allowDuplicate']) || $element['allowDuplicate'] !== true))
		{
			foreach ($this->elements as &$existingElement)
			{
				// do not append an already existing element unless allowDuplicate is true
				if (isset($existingElement['id']) && $element['id'] === $existingElement['id'])
				{
					return $this;
				}
			}
		}
		$this->elements[] = $element;
		return $this;
	}
	
	public function prepend($element)
	{
		if (isset($element['id']) && (!isset($element['allowDuplicate']) || $element['allowDuplicate'] !== true))
		{
			foreach ($this->elements as $existingElementKey => $existingElement)
			{
				// remove existing element from elements list and prepend
				if (isset($existingElement['id']) && $element['id'] === $existingElement['id'])
				{					
					// unset existing element and prepend new one
					unset($this->elements[$existingElementKey]);
					break;
				}
			}
		}
		array_unshift($this->elements, $element);
		return $this;
	}

	public function clear()
	{
		$this->elements = array();
		return $this;
	}

	public function get()
	{
		return $this->elements;
	}
}