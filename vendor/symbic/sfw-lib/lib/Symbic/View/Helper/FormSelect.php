<?php
class Symbic_View_Helper_FormSelect extends Zend_View_Helper_FormSelect
{
	public function formSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
	{
		return parent::formSelect($name, $value, $attribs, $options, $listsep);
	}
}