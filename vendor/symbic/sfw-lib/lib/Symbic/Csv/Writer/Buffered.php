<?php
class Symbic_Csv_Writer_Buffered extends Symbic_Csv_Writer
{
	protected $useAutoHeader = false;
	protected $writeBuffer = array();
	protected $bufferLineOffset = 0;
	protected $bufferWritten = false;
	protected $headerWritten = false;
	protected $bufferAutoWrite = true;

	public function __destruct()
	{
		if ($this->bufferAutoWrite)
		{
			$this->writeBuffer();
		}
		parent::__destruct();
	}

	public function initAutoHeader()
	{
		if ($this->bufferLineOffset > 0)
		{
			throw new Exception(__CLASS__ . ': Auto CSV headers can not been initialized after writing data to the buffer');
		}
		$this->useHeader = true;
		$this->useAutoHeader = true;
	}

	public function initHeader(array $headerFields)
	{
		if ($this->bufferLineOffset > 0)
		{
			throw new Exception(__CLASS__ . ': CSV headers can not been initialized after writing data to the buffer');
		}
		$this->useHeader = true;
		$this->headerFields = $headerFields;
	}

	public function write(array $fields)
	{
		if ($this->useAutoHeader)
		{
			$fieldNames = array_keys($fields);
			foreach ($fieldNames as $fieldName)
			{
				if (!in_array($fieldName, $this->headerFields))
				{
					if ($this->bufferWritten)
					{
						throw new Exception(__CLASS__ . ': Trying to add header fields after writing to file');
					}
					$this->headerFields[] = $fieldName;
				}
			}
		}

		$this->bufferLineOffset++;
		$this->writeBuffer[] = $fields;
	}

	public function writeBuffer()
	{
		$this->bufferWritten = true;

		if (!$this->headerWritten)
		{
			$this->_write($this->headerFields);
			$this->headerWritten = true;
		}

		while ($fields = array_shift($this->writeBuffer))
		{
            parent::write($fields);
		}
	}
}