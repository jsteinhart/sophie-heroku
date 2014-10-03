<?php

/**
 * Returns a formated date string using the view locale
 * DRAFT: UNSTABLE API
 */
class Symbic_View_Helper_DateFormat extends Zend_View_Helper_Abstract
{
	/**
	 *
	 * @param int|String|DateTime		$time
	 * @param String					$dateFormat (none|short|medium|long|full)
	 * @param String|Zend_Locale		$locale
	 * @param String					$timezone
	 * @return string
	 */
	public function dateFormat($time = null, $dateFormat = 'short', $locale = null, $timezone)
	{
		return $this->datetimeFormat($time, $dateFormat, 'none', $locale, $timezone);
	}
}