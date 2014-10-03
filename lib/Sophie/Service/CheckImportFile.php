<?php
	/**
	 * Checks if the Treatment import file is in the correct format
	 * and has not been corrupted
	 */
	class Sophie_Service_CheckImportFile
	{
		static function check_header_fields($treatment, $testChecksum = true)
		{
			//header fields checks
			if(!is_array($treatment['header']))
			{
				$error = "Import file does not contain header.";
				throw new Exception($error);
			}
			else
			{
				if($treatment['header']['format'] != 'treatmentJSON')
				{
					$error = "Import file is not in format 'treatmentJSON'.";
					throw new Exception($error);
				}
			}

			//Check content field
			if(!is_array($treatment['content']))
			{
				$error = "Import file does not contain valid content part.";
				throw new Exception($error);
			}
			else
			{
				//checksum
				if($testChecksum && version_compare($treatment['header']['formatVersion'],'1.0.2') >= 0)
				{
					if($treatment['header']['md5'] != md5(print_r($treatment['content'],true)))
					{
						$error = "Checksum test failed. It seems that the file has been corrupted.";
						throw new Exception($error);
					}
				}
			}
			return true;
		}
	}