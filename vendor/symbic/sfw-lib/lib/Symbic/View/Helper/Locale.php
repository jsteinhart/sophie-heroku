<?php

/**
 *
 */
class Symbic_View_Helper_Locale extends Zend_View_Helper_Abstract
{
	protected static $locale	= null;

	public static function setLocale($locale)
	{
		if (is_string($locale))
		{
			self::$locale = new Zend_Locale($locale);
			return;
		}
		elseif ($locale instanceof \Zend_Locale)
		{
			self::$locale = $locale;
			return;
		}
		throw new Exception('Invalid locale passed to ' . __CLASS__);		
	}

	public static function getLocale()
	{
		if (is_null(self::$locale))
		{
			if (Zend_Registry::isRegistered('locale'))
			{
				self::setLocale(Zend_Registry::get('locale'));
			}
			elseif (Zend_Registry::isRegistered('Zend_Locale'))
			{
				self::setLocale(Zend_Registry::get('Zend_Locale'));
			}
			else
			{
				self::setLocale('en_US');
			}
		}
		return self::$locale;
	}

	/**
	 *
	 * @param String|Zend_Locale	$locale
	 * @return string
	 */
	public function locale($locale = null)
	{
		if (is_string($locale) || $locale instanceof \Zend_Locale)
		{
			self::setLocale($locale);
			$locale === null;
		}

		elseif ($locale !== null)
		{
			throw new Exception('Invalid locale passed to ' . __CLASS__);	
		}

		return self::getLocale();
	}

}