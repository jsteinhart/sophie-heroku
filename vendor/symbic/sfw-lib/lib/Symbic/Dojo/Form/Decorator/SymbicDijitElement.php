<?php
class Symbic_Dojo_Form_Decorator_SymbicDijitElement extends Zend_Dojo_Form_Decorator_DijitElement
{
    public function render($content)
    {
        $element = $this->getElement();
		if (!$element instanceof Zend_Dojo_Form_Element_Dijit)
		{
			//die('non dijit caught');
			$viewHelper = new Zend_Form_Decorator_ViewHelper($this->getOptions());
			$viewHelper->setElement($element);
			return $viewHelper->render($content);
		}

		return parent::render($content);
    }
}
