<?php
class Symbic_View_Helper_FormTextareaAutosize extends Symbic_View_Helper_FormTextarea
{
    public function formTextareaAutosize($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

/*
JS Requires:
'/components/jquery/1.10.2/jquery-1.10.2.min.js',
'/components/jquery-autosize/1.18.1/jquery.autosize.min.js',
*/

		$this->view->jsOnLoad()->appendScript('
var element = jQuery(\'#' . $id . '\');
var lineHeight = element.css(\'line-height\');
if (isNaN(lineHeight)) { lineHeight = 12; }
element.css(\'\line-height\', lineHeight + \'px\');
element.css(\'height\', lineHeight + \'px\');
element.css(\'max-height\', \'500px\');
element.autosize({append: "\n"});
');
		return $this->renderInput($name, $value, $attribs);
    }
}