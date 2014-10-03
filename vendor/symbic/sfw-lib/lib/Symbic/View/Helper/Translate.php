<?php

/**
 *
 */
class Symbic_View_Helper_Translate extends Zend_View_Helper_Abstract
{

	/**
	 *
	 * @param type $str
	 * @return type
	 */
	public function translate($str)
	{
		if (Zend_Registry::isRegistered('Zend_Translate'))
		{
			$str = Zend_Registry::get('Zend_Translate')->getAdapter()->translate($str);
		}
		if (func_num_args() == 1)
		{
			return $str;
		}
		else
		{
			$args = func_get_args();
			unset($args[0]);
			return vsprintf($str, $args);
		}
	}

	public function T($str)
	{
		return call_user_func_array(array($this, 'translate'), func_get_args());
	}

}
