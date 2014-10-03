<?php

/**
 * Returns a formated time string using the view locale
 * DRAFT: UNSTABLE API
 */
class Symbic_View_Helper_TimeFormat extends Zend_View_Helper_Abstract
{
	/**
	 *
	 * @param int|String|DateTime		$time
	 * @param String					$timeFormat (none|short|medium|long|full)
	 * @param String|Zend_Locale		$locale
	 * @param String					$timezone
	 * @return string
	 */
	public function timeFormat($time = null, $timeFormat = 'short', $locale = null, $timezone = null)
	{
		return $this->datetimeFormat($time, 'none', $timeFormat, $locale, $timezone);
	}
}