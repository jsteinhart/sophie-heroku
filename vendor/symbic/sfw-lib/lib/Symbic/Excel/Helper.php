<?php
class Symbic_Excel_Helper
{
	/*
	 * Transforms numeric cell coordinates (1, 1) to Excel cell coordinates (A1)
	 */
	static public function cellCoordinates($col, $row)
	{
		$colIndex2 = $col - 1;
		for ($colIndexAlpha = ""; $colIndex2 >= 0; $colIndex2 = intval($colIndex2 / 26) - 1)
		{
			$colIndexAlpha = chr($colIndex2 % 26 + 0x41) . $colIndexAlpha;
		}
		return $colIndexAlpha . $row;
	}
}