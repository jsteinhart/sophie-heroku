<?php

/**
 * Returns a formated number string using the view locale
 * !!! UNSTABLE API !!!
 */
class Symbic_View_Helper_NumberFormat extends Zend_View_Helper_Abstract
{
	protected static $defaultZero		= '&ndash;';

	public static function setDefaultZero($zero)
	{
		self::$defaultZero = $defaultZero;
	}

	public static function getDefaultZero()
	{
		return self::$defaultZero;
	}
	
	/**
	 *
	 * @param int|real		$number
	 * @param int			$precision
	 * @param Zend_Locale	$locale
	 * @return string
	 */
	public function numberFormat($number, $precision = 0, $zero = null, $locale = null)
	{
		if ($zero === null && self::$defaultZero !== null)
		{
			$zero = self::$defaultZero;
		}

		if (is_null($locale))
		{
			$locale = $this->view->locale();
		}
		
		if (is_string($locale))
		{
			$locale = new Zend_Locale($locale);
		}
		
		if (!$locale instanceof \Zend_Locale)
		{
			throw new Exception('Invalid locale passed to ' . __CLASS__);
		}

		if (!is_numeric($number))
		{
			return false;
		}
		
		if ($number === 0 && is_string($zero))
		{
			return $zero;
		}
		
		return Zend_Locale_Format::toNumber($number, array('precision' => $precision, 'locale' => $locale));
	}

}
