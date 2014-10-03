<?php
class Symbic_View_Helper_TabNavigationPane extends Zend_View_Helper_Abstract
{
	public function tabNavigationPane($name, $title)
	{
	    return '<li><a href="#' . $name . '" data-toggle="tab">'. $title .'</a></li>';
	}
}