<?php
class Symbic_File_Tail
{
	public function getLines($file, $number = 10)
	{
		if (!is_string($file))
		{
			throw new Exception('Invalid file reference passed to getLines function in ' . __CLASS__);
		}
		
		if (!file_exists($file) || filesize($file) === 0)
		{
			return false;
		}

		if (!$file = fopen($file, 'r'))
		{
			return false;
		}

		$pos = -2;
		$char = '';
		$beginning_of_file = false;

		$lines = array();

		for ($i=1; $i <= $number; $i++)
		{
			if ($beginning_of_file == true)
			{
				continue;
			}

			while ($char != "\n")
			{

				if(fseek($file, $pos, SEEK_END) < 0)
				{
					$beginning_of_file = true;
					rewind($file);
					break;
				}

				$pos--;

				fseek($file, $pos, SEEK_END);

				$char = fgetc($file);
			}

			array_push($lines, fgets($file));
			$char = '';
		}

		fclose($file);

		return array_reverse($lines);
	}
}