<?php
class Symbic_View_Helper_Link extends Zend_View_Helper_HtmlElement
{
	public function link($content, $href = '#', $attribs = array())
	{
		return '<a href="' . $href . '"' . $this->_htmlAttribs($attribs) . '>' . $content . '</a>';
	}
}