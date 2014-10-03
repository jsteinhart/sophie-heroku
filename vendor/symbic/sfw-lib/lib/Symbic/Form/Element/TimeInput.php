<?php
class Symbic_Form_Element_TimeInput extends Symbic_Form_Element_TextInput
{
	public $type	= 'time';
	
	public static function valueToNumber($value)
	{
		if ($value === '')
		{
			return 0;
		}
		
		$valueExplode = explode(':', $value);
		
		if (sizeof($valueExplode) > 3)
		{
			return 23 * 60 + 59 * 60 + 59;
		}

		if (sizeof($valueExplode) == 3)
		{
			$hours = (int)$valueExplode[0];
			$minutes = (int)$valueExplode[1];
			$seconds = (int)$valueExplode[2];
		}
		elseif (sizeof($valueExplode) == 2)
		{
			// For now we assume hours = 0 for this case
			$hours = 0;
			$minutes = (int)$valueExplode[0];
			$seconds = (int)$valueExplode[1];
		}
		elseif (sizeof($valueExplode) == 1)
		{
			// For now we assume hours = 0 and minutes = 0 for this case
			$hours = 0;
			$minutes = 0;
			$seconds = (int)$valueExplode[0];
		}
		
		// ignore microseconds float part
		$seconds2 = explode('.', $seconds);
		
		return ((($hours * 60) + $minutes) * 60) + $seconds2[0];	
	}

	public function getValueAsNumber()
	{
		return self::valueToNumber($this->getValue());
	}

	public function setValueAsNumber($seconds)
	{
		if (!is_numeric($seconds))
		{
			$seconds = 0;
		}
		
		$hours = floor($seconds / (60 * 60));
		$seconds = $seconds - ($hours * 60 * 60);

		if ($hours > 23)
		{
			$hours = 23;
		}

		$minutes = floor($seconds / 60);
		$seconds = $seconds - ($minutes * 60);

		// ignore microseconds float part		
		$seconds = round($seconds, 0);

		$this->setValue(str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT));
	}
}