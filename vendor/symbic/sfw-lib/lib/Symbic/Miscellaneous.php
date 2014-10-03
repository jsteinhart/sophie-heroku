<?php
class Symbic_Miscellaneous
{
	static private function getDecimals($number, $autoDecimals)
	{
		if (is_int($autoDecimals))
		{
			$decimals = $autoDecimals;
		}
		else
		{
			$decimals = 2;
			if ($autoDecimals && preg_match('/^([-]{0,1})(\d+)\.(\d*)([1-9])0*$/', $number, $matches))
			{
				$decimals = max($decimals, strlen($matches[3]) + 1);
			}
		}
		return $decimals;
	}
	
	static private function replaceLastDecimals($formatted, $decimals, $replace = '--')
	{
		return (preg_match('/0{' . (int)$decimals . '}$/', $formatted))
			? (substr($formatted, 0, -1 * (int)$decimals) . $replace) 
			: $formatted;
	}
	
	static public function germanCurrency($number, &$decimals = false)
	{
		$decimals = self :: getDecimals($number, $decimals);
		$result = number_format($number, $decimals, ',', '.');
		return self :: replaceLastDecimals($result, $decimals, '-');
	}

	static public function germanCurrencyHTML($number, &$decimals = false)
	{
		$decimals = self :: getDecimals($number, $decimals);
		$result = number_format($number, $decimals, ',', '.');
		return self :: replaceLastDecimals($result, $decimals, '&ndash;');
	}
	
	static public function getIpAddress()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if (isset($_SERVER['REMOTE_ADDR']))
		{
			return $_SERVER['REMOTE_ADDR'];
		}
		return null;
	}
	
	static public function getISODate($ddate, $ddateh = '', $ddatemin = '')
	{
		$date = explode('.', $ddate);
		if (count($date) == 1)
			$date = explode('-', $ddate);
		if (count($date) == 1)
			$date = explode(',', $ddate);
		if (count($date) != 3)
			return false;
		
		$day = $date[0];
		if ($day < 1 || $day > 31)
			$day = false;
		$month = $date[1];
		if ($month < 1 || $month > 12)
			$month = false;
		$year = $date[2];
		if (empty($year))
			$year = date('Y');
		if (!is_numeric($year))
			$year = false;
		if ($year >= 0 && $year <= 30)
			$year += 2000;
		else if ($year > 30 && $year < 100)
			$year += 1900;

		$dataTime = str_pad((int)$year, 4, '0', STR_PAD_LEFT) . '-' . str_pad((int)$month, 2, '0', STR_PAD_LEFT) . '-' . str_pad((int)$day, 2, '0', STR_PAD_LEFT);
		if (is_numeric($ddateh) && is_numeric($ddatemin))
			$dataTime .= ' ' . str_pad((int)$ddateh, 2, '0', STR_PAD_LEFT) . ':' . str_pad((int)$ddatemin, 2, '0', STR_PAD_LEFT);
		if ($day && $month && $year)
			return $dataTime;
		else
			return false;
	} 
}