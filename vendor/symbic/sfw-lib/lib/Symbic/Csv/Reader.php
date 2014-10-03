<?php
class Symbic_Csv_Reader
{
	private $fileHandler;
	private $delim = ',';
	private $enclosure = '"';
	private $escape = '"';
	private $useHeader = false;
	private $headerFields = array();
	private $lineOffset = 0;

	public function __construct($filename = null)
	{
		if (!is_null($filename))
		{
			$this->open($filename);
		}
	}

	public function open($filename)
	{
		$this->fileHandler = fopen($filename, 'r');
		if (!$this->fileHandler)
		{
			throw new Exception(__CLASS__ . ': Could not open file ' . $filename);
		}
	}

	public function close()
	{
		fclose($this->fileHandler);
	}

	public function setOptions($options = array())
	{
		if (!is_array($options))
			throw new Exception('Symbic_Csv_Reader can not set options from a non array input');

		if (isset($options['delim']))
			$this->delim = $options['delim'];

		if (isset($options['enclosure']))
			$this->enclosure = $options['enclosure'];

		if (isset($options['escape']))
			$this->escape = $options['escape'];

	}

	public function initHeader()
	{
		if (!$this->fileHandler)
		{
			throw new Exception('Symbic_Csv_Reader has no open fileHandler');
		}
		if ($this->lineOffset > 0)
		{
			throw new Exception('Symbic_Csv_Reader cannot initialize headers after having read data lines');
		}

		$this->headerFields = $this->fgetcsv_ex($this->fileHandler, $this->delim, $this->enclosure, $this->escape);
		$this->useHeader = true;
	}

	public function readLine()
	{
		if ($this->useHeader)
		{
			return $this->readAssoc();
		}
		else
		{
			return $this->readNum();
		}
	}

	protected function readNum()
	{
		if (!$this->fileHandler)
			throw new Exception('Symbic_Csv_Reader has no open fileHandler');

		return $this->fgetcsv_ex($this->fileHandler, $this->delim, $this->enclosure, $this->escape);
	}

	protected function readAssoc()
	{
		if (!$this->useHeader)
		{
			throw new Exception('Symbic_Csv_Reader CSV Header has not been initialized');
		}

		$line = $this->readNum();
		if (!$line)
		{
			return $line;
		}

		$headerFields = $this->headerFields;
		if (sizeof($line) > sizeof($headerFields))
		{
			throw new Exception('Field count mismatch: more data than header fields');
		}
		elseif (sizeof($line) < sizeof($headerFields))
		{
			throw new Exception('Field count mismatch: more header than data fields');
		}
		$assocLine = array_combine($headerFields, $line);
		return $assocLine;
	}

	/* The following function comes from the PHP documentation comments
	 * http://php.net/manual/de/function.fgetcsv.php
	 * Post from: marcos at yuniti com (09-Sep-2008 02:35)
	 */
	protected function fgetcsv_ex($file_handle, $delim = ',', $enclosure = '"', $escape = '"')
	{
		$fields = null;
		$fldCount = 0;
		$inQuotes = false;

		$complete = false;
		$search_chars_list = array (
			'\r\n',
			'\n',
			'\r'
		);
		if ($delim && ($delim != ''))
			$search_chars_list[] = $delim;
		if ($enclosure && ($enclosure != ''))
		{
			$search_chars_list[] = $enclosure;
			$enclosure_len = strlen($enclosure);
		}
		else
			$enclosure_len = 0;

		if ($escape && ($escape != ''))
		{
			$search_chars_list[] = $escape;
			$escape_len = strlen($escape);
		}
		else
			$escape_len = 0;
		$search_regex = '/' . implode('|', $search_chars_list) . '/';

		$cur_pos = 0;
		$line = '';
		$cur_value = '';
		$in_value = false;
		$last_value = 0;
		while (!$complete)
		{
			$read_result = fread($file_handle, 4096);
			if ($read_result)
				$line .= $read_result;
			else
				if (strlen($line) == 0)
					return null;
				else
					$line .= "\n";

			$line_len = strlen($line);

			while (true)
			{
				if (!preg_match($search_regex, $line, $matches, PREG_OFFSET_CAPTURE, $cur_pos))
				{
					if ($read_result) //need more chars
						break;
					else
						return null; //Incomplete file
				}
				else
				{
					$non_escape = false;
					$cur_char = $matches[0][0];
					$cur_len = strlen($cur_char);
					$new_pos = $matches[0][1];
					if (($enclosure == $escape) && $in_value && ($cur_char == $escape)) //Escape char = enclosure char special handling
					{
						if (($new_pos + $cur_len + $enclosure_len) >= $line_len) //We need the next char
							break;

						$next_char = substr($line, $new_pos + $cur_len, $enclosure_len);
						if ((!$enclosure) || ($next_char != $enclosure))
							$non_escape = true;
					}

					$cur_pos = $new_pos;
					if ($in_value && (!$non_escape))
					{
						$cur_value .= mb_substr($line, $last_value, $cur_pos - $last_value);
						if ($cur_char == $escape) //Skip escape char
							$cur_pos += $escape_len;
						$last_value = $cur_pos;
					}
					else
						if (($cur_char == "\n") || ($cur_char == "\r") || ($cur_char == "\r\n"))
						{
							$blank_start_lines = ($cur_pos == 0);
							++ $cur_pos;
							$cur_pos = $cur_pos +strspn($line, "\n\r", $cur_pos);
							if (!$blank_start_lines)
							{
								$fields[] = $cur_value;
								$complete = true;
								break;
							}
							else
							{
								$last_value = $cur_pos;
								continue;
							}
						}
						else
							if ($cur_char == $delim)
							{
								if (is_null($fields))
									$fields = array ();
								$fields[] = $cur_value . trim(mb_substr($line, $last_value, $cur_pos - $last_value));
								$last_value = $cur_pos + $cur_len;
								$cur_value = '';
							}
							else
								if ($cur_char == $enclosure)
								{
									if ($in_value)
										$cur_value .= mb_substr($line, $last_value, $cur_pos - $last_value);
									$last_value = $cur_pos + $cur_len;
									$in_value = !$in_value;
								}
					$cur_pos += $cur_len;
				}
			}
		}

		fseek($file_handle, $cur_pos -strlen($line), SEEK_CUR);
		$this->lineOffset++;
		return $fields;
	}
}