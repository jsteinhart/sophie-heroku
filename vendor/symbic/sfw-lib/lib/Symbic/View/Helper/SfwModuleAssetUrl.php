<?php
class Symbic_View_Helper_SfwModuleAssetUrl extends Zend_View_Helper_Abstract
{
	public function sfwModuleAssetUrl($file, $module = null)
	{
		return $this->view->url(array('module' => $module, 'file' => $file), 'sfwmoduleasset', true);
	}
}