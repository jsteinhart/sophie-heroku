<?php
class Symbic_Statistics_Measure_Sd
{
	public static $description = 'standard deviation';

	/*
	function returns the variance from an array
	*/

	public static function get(array $data)
	{
		$var = Symbic_Statistics::measure('var', $data);
		if (is_null($var))
		{
			return null;
		}
		
		return sqrt($var);
	}
}