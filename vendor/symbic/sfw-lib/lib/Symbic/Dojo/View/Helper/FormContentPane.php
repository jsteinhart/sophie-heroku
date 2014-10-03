<?php
class Symbic_Dojo_View_Helper_FormContentPane extends Zend_Dojo_View_Helper_ContentPane
{
    protected $_dijit  = 'dijit.layout.ContentPane';
    protected $_module = 'dijit.layout.ContentPane';

    public function formContentPane($id = null, $content = '', array $params = array(), array $attribs = array())
    {
        if (0 === func_num_args()) {
            return $this;
        }

		$element = $this->getElement();
		if (sizeof($element->getErrors()) > 0)
		{
			$params['title'] = '*' . $params['title'];
			$params['iconClass'] = 'plusIcon';
			$params['tooltip'] = 'This tab has an error';
		}

        return $this->_createLayoutContainer($id, $content, $params, $attribs);
    }
}