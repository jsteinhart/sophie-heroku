<?php
class Symbic_Statistics_Measure_Sum
{
	public static $description = 'sum of variables';

	/*
	function returns the sum of all value from an array
	*/

	public static function get(array $data)
	{
		$n = Symbic_Statistics::measure('n', $data);
		if ($n == 0)
		{
			return null;
		}

		$sum = 0;
		foreach ($data as &$entry)
		{
			$sum += $entry;
		}
		return $sum;
	}
}