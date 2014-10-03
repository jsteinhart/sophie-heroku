<?php
class Symbic_Statistics_Measure_Max
{
	public static $description = 'maximum';

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
		return max($data);
	}
}