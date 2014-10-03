<?php
class Symbic_Statistics_Measure_Var
{
	public static $description = 'variance';

	/*
	function returns the variance from an array
	*/

	public static function get(array $data)
	{
		$n = Symbic_Statistics::measure('n', $data);
		if ($n == 0)
		{
			return null;
		}
		
		$mean = Symbic_Statistics::measure('mean', $data);
		$var = 0;
		foreach ($data as &$entry)
		{
			$var += pow($mean - $entry, 2);
		}
		return ($var / $n);
	}
}