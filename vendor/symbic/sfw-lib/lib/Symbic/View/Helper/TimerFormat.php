<?php

/**
 *
 */
class Symbic_View_Helper_TimerFormat extends Zend_View_Helper_Abstract
{

	/**
	 *
	 * @var type
	 */
	public static $units = array(
		/* 'millenniums',
		  'centuries',
		  'decades',
		  'years',
		  'month', */
		'days'		 => array(
			'multiplier'	 => 86400,
			'labelSingular'	 => 'Day',
			'labelPlural'	 => 'Days'
		),
		'hours'		 => array(
			'multiplier'	 => 3600,
			'labelSingular'	 => 'Hour',
			'labelPlural'	 => 'Hours'
		),
		'minutes'	 => array(
			'multiplier'	 => 60,
			'labelSingular'	 => 'Minute',
			'labelPlural'	 => 'Minutes'
		),
		'seconds'	 => array(
			'multiplier'	 => 1,
			'labelSingular'	 => 'Second',
			'labelPlural'	 => 'Seconds'
		),
		/* 'milliseconds'
		  'microseconds',
		  'nanoseconds', */
	);

	/**
	 *
	 * @param type $duration
	 * @param type $options
	 * @return string
	 * @throws type
	 */
	public function timerFormat($duration, $options = array())
	{
		if (!is_array($options))
		{
			throw Exception('Options parameters need to be an array');
		}

		if (!isset($options['durationUnit']))
		{
			$options['durationUnit'] = 'seconds';
		}
		elseif (!array_key_exists($options['durationUnit'], self::$units))
		{
			throw Exception('Unknown unit for durationUnit');
		}

		if (!isset($options['smallestUnit']))
		{
			$options['smallestUnit'] = 'seconds';
		}
		elseif (!array_key_exists($options['smallestUnit'], self::$units))
		{
			throw Exception('Unknown unit for smallestUnit');
		}

		if (!isset($options['largestUnit']))
		{
			$options['largestUnit'] = 'days';
		}
		elseif (!array_key_exists($options['largestUnit'], self::$units))
		{
			throw Exception('Unknown unit for largestUnit');
		}

		if (!isset($options['smallestUnitShowZero']))
		{
			$options['smallestUnitShowZero'] = null;
		}
		elseif (!array_key_exists($options['smallestUnitShowZero'], self::$units))
		{
			throw Exception('Unknown unit for smallestUnitShowZero');
		}

		if (!isset($options['largestUnitShowZero']))
		{
			$options['largestUnitShowZero'] = null;
		}
		elseif (!array_key_exists($options['largestUnitShowZero'], self::$units))
		{
			throw Exception('Unknown unit for largestUnitShowZero');
		}

		if (!isset($options['showZero']))
		{
			$options['showZero'] = false;
		}
		else
		{
			$options['showZero'] = (Boolean) $options['showZero'];
		}

		if (!isset($options['emptyValue']))
		{
			$options['emptyValue'] = '-';
		}

		$seconds = $duration * self::$units[$options['durationUnit']]['multiplier'];

		$largestUnit = false;
		if (!is_null($options['largestUnitShowZero']))
		{
			$showZero = false;
		}
		else
		{
			$showZero = $options['showZero'];
		}
		$smallestUnitShowZero = false;

		$formatedTimer = '';

		foreach (self::$units as $unitName => $unitData)
		{
			if ($unitName == $options['largestUnitShowZero'] && $options['showZero'])
			{
				$showZero = true;
			}

			if ($unitName == $options['smallestUnitShowZero'])
			{
				$showZero = false;
			}

			if (!$largestUnit)
			{
				if ($unitName != $options['largestUnit'])
				{
					continue;
				}
				else
				{
					$largestUnit = true;
				}
			}

			if ($unitName == $options['smallestUnit'])
			{
				$unitNumber = round($seconds / $unitData['multiplier'], 0);
			}
			else
			{
				$unitNumber = floor($seconds / $unitData['multiplier']);
			}

			$seconds = $seconds - ($unitNumber * $unitData['multiplier']);

			if ($unitNumber != 0 || $showZero)
			{
				if ($formatedTimer != '')
				{
					$formatedTimer .= ' ';
				}

				$formatedTimer .= $unitNumber . ' ';
				if ($unitNumber == 1)
				{
					$formatedTimer .= $unitData['labelSingular'];
				}
				else
				{
					$formatedTimer .= $unitData['labelPlural'];
				}
			}

			if ($unitName == $options['smallestUnit'])
			{
				break;
			}
		}

		if ($formatedTimer == '')
		{
			return $options['emptyValue'];
		}
		return $formatedTimer;
	}
}