<?php
class Symbic_Form_Decorator_ElementAttributes extends Zend_Form_Decorator_Abstract
{
	public function render($content)
	{
		$this->getElement()->setAttribs($this->getOptions());

		return $content;
	}
}