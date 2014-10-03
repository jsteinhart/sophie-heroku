<?php

class Symbic_Filesystem
{
	public static function getFiles($directory, $includeSubdirectories = true)
	{
		try
		{
			$dir = new DirectoryIterator($directory);
		}
		catch (Exception $e)
		{
			return array();
		}
		
		$result = array();
		foreach($dir as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}
			if ($fileInfo->isDir())
			{
				if ($includeSubdirectories)
				{
					$result = array_merge($result, self :: getFiles($fileInfo->getPathname(), true));
				}
				continue;
			}
			$result[] = realpath($fileInfo->getPathname());
		}
		return $result;
	}
}
