<?php

/**
 *
 */
class Symbic_Statistics
{

	/**
	 * Function returns a single statistical measures calculated from an array
	 *
	 * @return type
	 * @throws Exception
	 */
	public static function measure()
	{
		$args	 = func_get_args();
		$measure = array_shift($args);

		// validate $type
		if (!is_string($measure) && !preg_match('/^[a-zA-Z0-9]+$/', $measure))
		{
			throw new Exception('Statistical measure "' . $measure . '" not implemented');
		}

		// load measure class
		$measureClass = 'Symbic_Statistics_Measure_' . ucfirst($measure);
		/*
		  if (!class_exists($measureClass))
		  {
		  try {
		  Zend_Loader::loadClass($measureClass);
		  }
		  catch (Exception $e)
		  {
		  throw new Exception('Statistical measure "' . $measure . '" not implemented');
		  }
		  } */

		// calculate measure
		try
		{
			$measure = forward_static_call_array(array(
				$measureClass,
				'get'), $args);
		}
		catch (Exception $e)
		{
			throw new Exception('Calculation of statistical measure "' . $measure . '" failed: ' . $e->getMessage());
		}

		return $measure;
	}

	/**
	 *
	 * @param array $data
	 * @param string $measures
	 * @return type
	 * @throws Exception
	 */
	public static function summarize(array $data, $measures = null)
	{
		$statistics = array();

		if (is_null($measures))
		{
			$measures = array(
				'n',
				'mean',
				'min',
				'max',
				'p50',
				'var',
				'sum',
				'sd');
		}

		if (!is_array($measures))
		{
			throw new Exception('Summarize expects measures parameter to be an array');
		}

		foreach ($measures as $measure)
		{
			$statistics[$measure] = self::measure($measure, $data);
		}
		return $statistics;
	}

}
