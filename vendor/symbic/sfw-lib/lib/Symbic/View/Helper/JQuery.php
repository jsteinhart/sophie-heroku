<?php
class Symbic_View_Helper_JQuery extends Zend_View_Helper_Abstract
{
	public function jQuery($file = 'jquery-1.10.2.min.js')
	{

		$file = '/_scripts/jquery/js/' . $file;
		if (file_exists(BASE_PATH . '/www' . $file))
		{
			$this->view->headScript()->appendFile($this->view->baseUrl( true ) . $file);
		}
		else
		{
			$this->view->headScript()->appendScript('alert("' . $file . ' not found.");');
		}
	}

}