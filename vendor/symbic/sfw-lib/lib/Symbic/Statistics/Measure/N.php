<?php
class Symbic_Statistics_Measure_N
{
	public static $description = 'number of observations';
	
	/*
	function returns the number of observations from an array of numbers	
	*/

	public static function get(array $data)
	{
		return sizeof($data);
	}
}