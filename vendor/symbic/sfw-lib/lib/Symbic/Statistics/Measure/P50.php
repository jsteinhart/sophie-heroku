<?php
class Symbic_Statistics_Measure_P50
{
	public static $description = 'P50';

	/*
	function returns the minimum value from an array
	*/

	public static function get(array $data)
	{
		$n = Symbic_Statistics::measure('n', $data);
		if ($n == 0)
		{
			return null;
		}

		sort($data);
		
		if ($n % 2 == 0)
		{
			
			return ($data[($n / 2) - 1] + $data[($n / 2)]) / 2;
		}
		else
		{
			return $data[floor($n / 2)];
		}
	}
}