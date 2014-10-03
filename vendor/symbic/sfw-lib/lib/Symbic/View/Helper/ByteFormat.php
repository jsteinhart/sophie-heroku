<?php

/**
 *
 */
class Symbic_View_Helper_ByteFormat extends Zend_View_Helper_Abstract
{
	// Look at: http://en.wikipedia.org/wiki/Binary_prefix
	protected $magnitudeSymbols1024 = array(
			'Bi',
			'KiB',
			'MiB',
			'GiB',
			'TiB',
			'PiB',
			'EiB',
			'ZiB',
			'YiB'
		);

	protected $magnitudeSymbols1000 = array(
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
			'PB',
			'EB',
			'ZB',
			'YB'
		);

	public function getMagnitude($bytes, $base = 1024)
	{
		return floor(log($bytes)/log($base));
	}

	/**
	 *
	 * @param int			$bytes
	 * @return string
	 */
	public function byteFormat($bytes, $places = 0, $minMagnitude = 1, $maxMagnitude = 6, $whitespaceBetween = ' ', $base = 1024)
	{
		if (is_string($bytes))
		{
			$bytes = (int)$bytes;
		}

		$magnitude = $this->getMagnitude($bytes, $base);
		
		if ($magnitude < $minMagnitude)
		{
			$magnitude = $minMagnitude;
		}
		
		if ($magnitude > $maxMagnitude)
		{
			$magnitude = $maxMagnitude;
		}		

		if ($base === 1024)
		{
			$magnitudeSymbols = $this->magnitudeSymbols1024;
		}
		else
		{
			$magnitudeSymbols = $this->magnitudeSymbols1000;
		}

		return $this->view->numberFormat($bytes / pow($base, $magnitude), $places) . $whitespaceBetween . $magnitudeSymbols[$magnitude - 1];
	}
}