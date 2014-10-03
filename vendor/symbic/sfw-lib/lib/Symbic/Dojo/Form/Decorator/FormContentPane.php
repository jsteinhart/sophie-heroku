<?php
class Symbic_Dojo_Form_Decorator_FormContentPane extends Zend_Dojo_Form_Decorator_ContentPane
{
    protected $_helper = 'ContentPane';

    public function render($content)
    {
		$element = $this->getElement();

		if ($element->isErrors())
		{
	        $dijitParams = $this->getDijitParams();
			$dijitParams['iconClass'] = 'dijitIconError';
			$dijitParams['tooltip'] = 'This tab has an error';
			$this->_dijitParams = $dijitParams;
		}
     	return parent::render($content);

    }
}
