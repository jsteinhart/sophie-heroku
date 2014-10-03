<?php
class Symbic_Form_Decorator_FormContentPane extends Symbic_Form_Decorator_ContentPane
{
    protected $_helper = 'ContentPane';

    public function render($content)
    {
		$element = $this->getElement();

		$dijitParams = $this->getDijitParams();
		if ($element->isErrors())
		{
	        
			$dijitParams['iconClass'] = 'dijitIconError';
			$dijitParams['tooltip'] = 'This tab has an error';
			
		}
		$dijitParams['doLayout'] = 'false';
		$this->_dijitParams = $dijitParams;
     	return parent::render($content);

    }
}
