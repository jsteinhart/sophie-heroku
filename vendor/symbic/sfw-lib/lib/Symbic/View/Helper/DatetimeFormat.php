<?php

/**
 * Returns a formated datetime string using the view locale
 * DRAFT: UNSTABLE API
 */
class Symbic_View_Helper_DatetimeFormat extends Zend_View_Helper_Abstract
{
	/**
	 *
	 * @param int|String|DateTime		$time
	 * @param String					$dateFormat (none|short|medium|long|full)
	 * @param String					$timeFormat (none|short|medium|long|full)
	 * @param String|Zend_Locale		$locale
	 * @param String					$timezone
	 * @param String					$pattern	Individual format pattern
	 * @return string
	 */
	public function datetimeFormat($datetime = null, $dateFormat = 'short', $timeFormat = 'short', $locale = null, $timezone = null, $pattern = null)
	{
		if (is_null($locale))
		{
			$locale = $this->view->locale();
		}
		
		if (!$locale instanceof \Zend_Locale)
		{
			throw new Exception('Invalid locale supplied to ' . __CLASS__);
		}

		$dateFormat = strtoupper($dateFormat);
		$timeFormat = strtoupper($timeFormat);

		if (!defined('\IntlDateFormatter::' . $dateFormat))
		{
			throw new Exception('Invalid dateFormat supplied to ' . __CLASS__);
		}

		if (!defined('\IntlDateFormatter::' . $timeFormat))
		{
			throw new Exception('Invalid timeFormat supplied to ' . __CLASS__);
		}
				
		$dateFormat = constant('\IntlDateFormatter::' . $dateFormat);
		$timeFormat = constant('\IntlDateFormatter::' . $timeFormat);

		if (is_null($datetime))
		{
			$datetime = time();
		}
		elseif (is_string($datetime))
		{
			$datetime = strtotime($datetime);
			if ($datetime === false)
			{
				throw new Exception('Invalid string date supplied to ' . __CLASS__);
			}
		}
		elseif ($datetime instanceof \DateTime)
		{
			$datetime = $datetime->getTimestamp();
		}
		else
		{
			throw new Exception('Invalid datetime passed to ' . __CLASS__);
		}

		return datefmt_format(datefmt_create($locale, $dateFormat, $timeFormat, $timezone, NULL, $pattern), $datetime);
	}
}