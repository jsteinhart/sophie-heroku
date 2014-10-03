<?php
class Symbic_File_LineIterator extends \SplFileObject
{
	/*const READ_BLOCK_SIZE = 8192;*/

	public function __construct($file)
	{
		parent::__construct($file);
		$this->setFlags(SplFileObject::DROP_NEW_LINE);
	}

	public function countLines()
	{
		$oldPos = $this->ftell();
		$this->seek(PHP_INT_MAX);
		$lines = $this->key();
		$this->fseek($oldPos);
		return $lines;
	}

	public function seekFromEnd($line_pos)
	{
		$line_pos = $this->countLines() - 1 - $line_pos;
		if ($line_pos < 0)
		{
			$line_pos = 0;
		}
		$this->seek($line_pos);
	}

	/*public function seekFromEnd(int $line_pos)
	{
		$this->fseek(0, SEEK_END);
		$pos = $file->ftell();
		if ($pos < self::READ_BLOCK_SIZE)
		{
			$blockSize = $pos;
		}
		else
		{
			$blockSize = self::READ_BLOCK_SIZE;
		}
		
		$lines = 0;
		do
		{
			if ($file->fgetc() == "\n")
			{
				$pos++;
			}
			$this->fseek($pos, SEEK_END);
		} while ($pos < 0);
	}*/
}