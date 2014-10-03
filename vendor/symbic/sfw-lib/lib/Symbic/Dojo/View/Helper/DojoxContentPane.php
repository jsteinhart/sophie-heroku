<?php
class Symbic_Dojo_View_Helper_DojoxContentPane extends Zend_Dojo_View_Helper_DijitContainer
{
    protected $_dijit  = 'dojox.layout.ContentPane';
    protected $_module = 'dojox.layout.ContentPane';

    public function dojoxContentPane($id = null, $content = '', array $params = array(), array $attribs = array())
    {
        if (0 === func_num_args()) {
            return $this;
        }

        return $this->_createLayoutContainer($id, $content, $params, $attribs);
    }
}