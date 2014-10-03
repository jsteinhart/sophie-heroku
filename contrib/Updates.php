<?php
/**
* Class containing db updates
* Add Updates by creating arrays consisting of sql statements
* !!! Update the SoPHIE DB by calling update() from this class !!!
*/

class Application_Contrib_Updates extends \Symbic_Dbversion_Manager
{
	// Dummy....
	/**
	* Updates to Version X
	*/
	public function getUpdatesToVersionX()
	{
		$updates = array();
		$updates[] = '';
		return $updates;
	}
	// End of Dummy
 
	/**
	* Updates to Version 43
	*/
	public function getUpdatesToVersion43()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment` ADD `setupScript` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL';
		return $updates;
	}

	/**
	* Updates to Version 42
	*/
	public function getUpdatesToVersion42()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_screen` ADD `excludedHtml` LONGTEXT NOT NULL';
		return $updates;
	}
	
	/**
	* Updates to Version 41
	*/
	public function getUpdatesToVersion41()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session` ADD `cacheTreatment` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT \'1\'';
		return $updates;
	}

	/**
	* Updates to Version 40
	*/
	public function getUpdatesToVersion40()
	{
		$updates = array();
		/* DELETE all zombie entries from sophie_treatment_type */
		$updates[] = 'DELETE FROM `sophie_treatment_type` WHERE NOT `treatmentId` IN ( SELECT id FROM `sophie_treatment` )';
		/* add missing constaint to treatment type table */
		$updates[] = 'ALTER TABLE `sophie_treatment_type` ADD FOREIGN KEY ( `treatmentId` ) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;';
		return $updates;
	}
	
	/**
	* Updates to Version 39
	*/
	public function getUpdatesToVersion39()
	{
		$updates = array();
		$updates[] = 'CREATE TABLE `system_log_error_exception` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `referenceId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` text COLLATE utf8_unicode_ci NOT NULL,
  `line` int(11) NOT NULL,
  `stackTrace` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `previousType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousType2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousMessage` longtext COLLATE utf8_unicode_ci NOT NULL,
  `previousCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `previousFile` text COLLATE utf8_unicode_ci NOT NULL,
  `previousLine` int(11) NOT NULL,
  `previousStackTrace` longtext COLLATE utf8_unicode_ci NOT NULL,
  `requestModule` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestController` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestAction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestParameters` longtext COLLATE utf8_unicode_ci NOT NULL,
  `sessionId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `userLogin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referenceId` (`referenceId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
		return $updates;
	}
	
	/**
	* Updates to Version 38
	*/
	public function getUpdatesToVersion38()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_asset` CHANGE `contentType` `contentType` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `metadata` `metadata` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `comment` `comment` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL';
		return $updates;
	}

	/**
	* Updates to Version 37
	*/
	public function getUpdatesToVersion37()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_sessiontype` DROP `variableDefinition`';
		return $updates;
	}

	/**
	* Updates to Version 36
	*/
	public function getUpdatesToVersion36()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_stepgroup` ADD `runConditionScript` LONGTEXT NOT NULL, ADD `runConditionFalse` ENUM(\'skipStepgroup\', \'skipStepgroupLoop\') NOT NULL DEFAULT \'skipStepgroupLoop\'';
		return $updates;
	}

	/**
	* Updates to Version 35
	*/
	public function getUpdatesToVersion35()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_stepgroup` CHANGE `loop` `loop` INT( 11 ) NOT NULL DEFAULT \'1\'';
		return $updates;
	}

	/**
	* Updates to Version 34
	*/
	public function getUpdatesToVersion34()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_step` ADD `active` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT \'1\'';
		return $updates;
	}

	/**
	* Updates to Version 33
	*/
	public function getUpdatesToVersion33()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session_participant_group` DROP INDEX `sessionId_2` ,
ADD UNIQUE `participantMembership` ( `sessionId` , `stepgroupLabel` , `stepgroupLoop` , `participantLabel` )';
		return $updates;
	}

	/**
	* Updates to Version 32
	*/
	public function getUpdatesToVersion32()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session` ADD `participantMgmt` ENUM( \'static\', \'dynamic\' ) NOT NULL DEFAULT \'static\' AFTER `sessiontypeId`';
		$updates[] = 'ALTER TABLE `sophie_treatment_sessiontype` CHANGE `style` `style` ENUM( \'static\', \'dynamic\' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL';
		$updates[] = 'ALTER TABLE `sophie_treatment_sessiontype` CHANGE `style` `participantMgmt` ENUM( \'static\', \'dynamic\' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL';
		return $updates;
	}

	/**
	* Updates to Version 31
	*/
	public function getUpdatesToVersion31()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_asset` ADD `metadata` TEXT NOT NULL AFTER `contentType`';
		return $updates;
	}

	/**
	* Updates to Version 30
	*/
	public function getUpdatesToVersion30()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 29
	*/
	public function getUpdatesToVersion29()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 28
	*/
	public function getUpdatesToVersion28()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment` CHANGE `theme` `layoutTheme` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'sophie_1_0_0\', CHANGE `layout` `layoutDesign` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'default\'';
		$updates[] = 'UPDATE `sophie_treatment` SET layoutDesign = \'default\', layoutTheme = \'sophie_1_0_0\'';
		$updates[] = 'DELETE FROM `sophie_treatment_step_eav` WHERE name = \'layout\'';
		$updates[] = 'DELETE FROM `sophie_treatment_step_eav` WHERE name = \'theme\'';
		$updates[] = 'DELETE FROM `sophie_treatment_step_eav` WHERE name = \'layoutTheme\'';
		$updates[] = 'DELETE FROM `sophie_treatment_step_eav` WHERE name = \'layoutDesign\'';
		return $updates;
	}

	/**
	* Updates to Version 27
	*/
	public function getUpdatesToVersion27()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment` ADD `theme` VARCHAR( 255 ) NOT NULL DEFAULT \'sophie_1_0_0\' AFTER `state`';
		return $updates;
	}

	/**
	* Updates to Version 26
	*/
	public function getUpdatesToVersion26()
	{
		$updates = array();
		$updates[] = 'CREATE TABLE IF NOT EXISTS `sophie_treatment_report` (
						`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						  `treatmentId` int(11) unsigned NOT NULL,
						  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `definition` longtext COLLATE utf8_unicode_ci NOT NULL,
						  PRIMARY KEY (`id`),
						  KEY `treatmentId` (`treatmentId`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
		$updates[] = 'ALTER TABLE `sophie_treatment_report`
						  ADD CONSTRAINT `sophie_treatment_report_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;';
		return $updates;
	}

	/**
	* Updates to Version 25
	*/
	public function getUpdatesToVersion25()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session_log` ADD `groupLabel` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `sessionId` ,
						ADD `participantLabel` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `groupLabel` ,
						ADD `stepgroupLabel` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `participantLabel` ,
						ADD `stepgroupLoop` INT( 11 ) UNSIGNED NULL DEFAULT NULL AFTER `stepgroupLabel` ,
						ADD `stepLabel` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `stepgroupLoop`';
		$updates[] = 'ALTER TABLE `sophie_session_log` DROP INDEX `sessionId` ,
						ADD INDEX `sessionId` ( `sessionId` , `groupLabel` , `participantLabel` , `stepgroupLabel` , `stepgroupLoop` , `stepLabel` ) ';
		$updates[] = 'ALTER TABLE `sophie_session_log` ADD `contentId` VARCHAR( 64 ) NULL DEFAULT NULL COMMENT "machine readable content /event identifier" AFTER `content` ';
		$updates[] = 'ALTER TABLE `sophie_session_log` ADD `data` LONGTEXT NULL DEFAULT NULL AFTER `details` ';
		return $updates;
	}

	/**
	* Updates to Version 24
	*/
	public function getUpdatesToVersion24()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 23
	*/
	public function getUpdatesToVersion23()
	{
		$updates = array();
		$updates[] = 'CREATE TABLE IF NOT EXISTS `sophie_session_eav` (
						  `sessionId` int(11) unsigned NOT NULL,
						  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `value` text COLLATE utf8_unicode_ci,
						  PRIMARY KEY (`sessionId`,`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
		$updates[] = 'ALTER TABLE `sophie_session_eav` ADD FOREIGN KEY ( `sessionId` ) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
		return $updates;
	}

	/**
	* Updates to Version 22
	*/
	public function getUpdatesToVersion22()
	{
		$updates = array();
		$updates[] = 'CREATE TABLE IF NOT EXISTS `sophie_treatment_eav` (
						  `treatmentId` int(11) unsigned NOT NULL,
						  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `value` text COLLATE utf8_unicode_ci,
						  PRIMARY KEY (`treatmentId`,`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
		$updates[] = 'ALTER TABLE `sophie_treatment_eav` ADD FOREIGN KEY ( `treatmentId` ) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
		return $updates;
	}

	/**
	* Updates to Version 21
	*/
	public function getUpdatesToVersion21()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session` ADD INDEX ( `treatmentId` )';
		$updates[] = 'ALTER TABLE `sophie_session` ADD FOREIGN KEY ( `treatmentId` ) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;';
		$updates[] = 'ALTER TABLE `sophie_treatment_group_structure` DROP FOREIGN KEY `sophie_treatment_group_structure_ibfk_1` ;';
		$updates[] = 'ALTER TABLE `sophie_treatment_group_structure` ADD FOREIGN KEY ( `treatmentId` ) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;';
		$updates[] = 'ALTER TABLE `sophie_treatment_sessiontype` DROP FOREIGN KEY `sophie_treatment_sessiontype_ibfk_3` ;';
		$updates[] = 'ALTER TABLE `sophie_treatment_sessiontype` ADD FOREIGN KEY ( `treatmentId` ) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;';
		return $updates;
	}

	/**
	* Updates to Version 20
	*/
	public function getUpdatesToVersion20()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session_log` CHANGE `details` `details` LONGTEXT NULL DEFAULT NULL';
		return $updates;
	}

	/**
	* Updates to Version 19
	*/
	public function getUpdatesToVersion19()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session_log` ADD `details` TEXT NULL DEFAULT NULL ';
		return $updates;
	}

	/**
	* Updates to Version 18
	*/
	public function getUpdatesToVersion18()
	{
		$updates = array();
		$updates[] = 'UPDATE `sophie_treatment_step` SET `label` = CONCAT("step", CAST(`id` AS CHAR)) WHERE `label` IS NULL';
		$updates[] = 'ALTER TABLE `sophie_treatment_step` CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL';
		return $updates;
	}

	/**
	* Updates to Version 17
	*/
	public function getUpdatesToVersion17()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 16
	*/
	public function getUpdatesToVersion16()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_session_participant` ADD `externalData` TEXT NOT NULL ';
		return $updates;
	}

	/**
	* Updates to Version 15
	*/
	public function getUpdatesToVersion15()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_stepgroup` ADD `grouping` VARCHAR( 36 ) NOT NULL DEFAULT \'static\' AFTER `active`';
		return $updates;
	}


	/**
	* Updates to Version 14
	*/
	public function getUpdatesToVersion14()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `sophie_treatment_step` ADD `label` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `name`';
		return $updates;
	}

	/**
	* Updates to Version 13
	*/
	public function getUpdatesToVersion13()
	{
		$updates = array();
		$updates[] = 'ALTER TABLE `system_user` ADD `active` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT \'1\'';
		return $updates;
	}

	/**
	* Updates to Version 12
	*/
	public function getUpdatesToVersion12()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 11
	*/
	public function getUpdatesToVersion11()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 10
	*/
	public function getUpdatesToVersion10()
	{
		$updates = array();
		return $updates;
	}

	/**
	* Updates to Version 9
	*/
	public function getUpdatesToVersion9()
	{
		$updates = array();
		//Remove run conditions from sync steps:
		$sql = 'SELECT
					eav.stepId,
					eav.name
				FROM `sophie_treatment_step_eav` AS eav
				INNER JOIN `sophie_treatment_step` AS step
				ON step.id = eav.stepId
				WHERE
					step.steptypeSystemName LIKE "Sophie_Steptype_Sync%"
				AND step.steptypeSystemName NOT LIKE "Sophie_Steptype_Sync_Participant%"
				AND eav.name LIKE "runCondition%"
				';
		$eavEntries = $this->db->query($sql)->fetchAll();
		$stepId = null;
		foreach ($eavEntries as $eavEntry)
		{
			$updates[] = 'DELETE FROM `sophie_treatment_step_eav` WHERE stepId = ' . $this->db->quote($eavEntry['stepId']) . ' AND name = ' . $this->db->quote($eavEntry['name']);
			if ($stepId != $eavEntry['stepId'])
			{
				$updates[] = 'DELETE FROM `sophie_treatment_step_type` WHERE stepId = ' . $this->db->quote($eavEntry['stepId']);
				$stepId = $eavEntry['stepId'];
			}

		}
		return $updates;
	}

	public function getUpdatesToVersion8()
	{
		$updates = array();
		$updates[] = "ALTER TABLE `sophie_treatment`  ADD `secondaryPayoffScript` TEXT NOT NULL AFTER `payoffRetrivalMethod`,  ADD `secondaryPayoffRetrivalMethod` VARCHAR(255) NOT NULL DEFAULT 'inactive' AFTER `secondaryPayoffScript`;";
		return $updates;
	}

	public function getUpdatesToVersion7()
	{
		$updates = array();
		$updates[] = "CREATE TABLE IF NOT EXISTS `sophie_treatment_asset` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `treatmentId` int(11) unsigned NOT NULL,
			  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			  `data` mediumblob NOT NULL,
			  `contentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `treatmentId_2` (`treatmentId`,`label`),
			  KEY `treatmentId` (`treatmentId`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
		return $updates;
	}

	public function getUpdatesToVersion6()
	{
		$updates = array();
		$updates[] = "CREATE TABLE IF NOT EXISTS `sophie_validate_phpcode_function` (
					  `function` varchar(255) NOT NULL,
					  `allowed` tinyint(1) NOT NULL,
					  `comment` varchar(255) NOT NULL,
					  PRIMARY KEY (`function`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
					";
		$updates[] = "INSERT INTO `sophie_validate_phpcode_function` (`function`, `allowed`, `comment`) VALUES
						('abs', 1, ''),
						('acos', 1, ''),
						('acosh', 1, ''),
						('addcslashes', 1, ''),
						('addslashes', 1, ''),
						('apache_getenv', 0, ''),
						('apache_get_modules', 0, ''),
						('apache_get_version', 0, ''),
						('apache_lookup_uri', 0, ''),
						('apache_note', 0, ''),
						('apache_request_headers', 0, ''),
						('apache_response_headers', 0, ''),
						('apache_setenv', 0, ''),
						('array_change_key_case', 1, ''),
						('array_chunk', 1, ''),
						('array_combine', 1, ''),
						('array_count_values', 1, ''),
						('array_diff', 1, ''),
						('array_diff_assoc', 1, ''),
						('array_diff_key', 1, ''),
						('array_diff_uassoc', 1, ''),
						('array_diff_ukey', 1, ''),
						('array_fill', 1, ''),
						('array_fill_keys', 1, ''),
						('array_filter', 1, ''),
						('array_flip', 1, ''),
						('array_intersect', 1, ''),
						('array_intersect_assoc', 1, ''),
						('array_intersect_key', 1, ''),
						('array_intersect_uassoc', 1, ''),
						('array_intersect_ukey', 1, ''),
						('array_keys', 1, ''),
						('array_key_exists', 1, ''),
						('array_map', 1, ''),
						('array_merge', 1, ''),
						('array_merge_recursive', 1, ''),
						('array_multisort', 1, ''),
						('array_pad', 1, ''),
						('array_pop', 1, ''),
						('array_product', 1, ''),
						('array_push', 1, ''),
						('array_rand', 1, ''),
						('array_reduce', 1, ''),
						('array_replace', 1, ''),
						('array_replace_recursive', 1, ''),
						('array_reverse', 1, ''),
						('array_search', 1, ''),
						('array_shift', 1, ''),
						('array_slice', 1, ''),
						('array_splice', 1, ''),
						('array_sum', 1, ''),
						('array_udiff', 1, ''),
						('array_udiff_assoc', 1, ''),
						('array_udiff_uassoc', 1, ''),
						('array_uintersect', 1, ''),
						('array_uintersect_assoc', 1, ''),
						('array_uintersect_uassoc', 1, ''),
						('array_unique', 1, ''),
						('array_unshift', 1, ''),
						('array_values', 1, ''),
						('array_walk', 1, ''),
						('array_walk_recursive', 1, ''),
						('arsort', 1, ''),
						('asin', 1, ''),
						('asinh', 1, ''),
						('asort', 1, ''),
						('assert', 0, ''),
						('assert_options', 0, ''),
						('atan', 1, ''),
						('atan2', 1, ''),
						('atanh', 1, ''),
						('base64_decode', 1, ''),
						('base64_encode', 1, ''),
						('basename', 1, ''),
						('base_convert', 1, ''),
						('bcadd', 1, ''),
						('bccomp', 1, ''),
						('bcdiv', 1, ''),
						('bcmod', 1, ''),
						('bcmul', 1, ''),
						('bcpow', 1, ''),
						('bcpowmod', 1, ''),
						('bcscale', 1, ''),
						('bcsqrt', 1, ''),
						('bcsub', 1, ''),
						('bin2hex', 1, ''),
						('bindec', 1, ''),
						('bindtextdomain', 1, ''),
						('bind_textdomain_codeset', 1, ''),
						('bzclose', 0, ''),
						('bzcompress', 0, ''),
						('bzdecompress', 0, ''),
						('bzerrno', 0, ''),
						('bzerror', 0, ''),
						('bzerrstr', 0, ''),
						('bzflush', 0, ''),
						('bzopen', 0, ''),
						('bzread', 0, ''),
						('bzwrite', 0, ''),
						('call_user_func', 0, ''),
						('call_user_func_array', 0, ''),
						('call_user_method', 0, ''),
						('call_user_method_array', 0, ''),
						('cal_days_in_month', 1, ''),
						('cal_from_jd', 1, ''),
						('cal_info', 1, ''),
						('cal_to_jd', 1, ''),
						('ceil', 1, ''),
						('chdir', 0, ''),
						('checkdate', 1, ''),
						('checkdnsrr', 0, ''),
						('chgrp', 0, ''),
						('chmod', 0, ''),
						('chop', 1, ''),
						('chown', 0, ''),
						('chr', 1, ''),
						('chunk_split', 1, ''),
						('class_alias', 1, ''),
						('class_exists', 1, ''),
						('class_implements', 1, ''),
						('class_parents', 1, ''),
						('clearstatcache', 1, ''),
						('closedir', 0, ''),
						('closelog', 0, ''),
						('compact', 0, ''),
						('com_create_guid', 0, ''),
						('com_event_sink', 0, ''),
						('com_get_active_object', 0, ''),
						('com_load_typelib', 0, ''),
						('com_message_pump', 0, ''),
						('com_print_typeinfo', 0, ''),
						('connection_aborted', 0, ''),
						('connection_status', 0, ''),
						('constant', 1, ''),
						('convert_cyr_string', 1, ''),
						('convert_uudecode', 1, ''),
						('convert_uuencode', 1, ''),
						('copy', 0, ''),
						('cos', 1, ''),
						('cosh', 1, ''),
						('count', 1, ''),
						('count_chars', 1, ''),
						('crc32', 1, ''),
						('create_function', 0, ''),
						('crypt', 1, ''),
						('ctype_alnum', 1, ''),
						('ctype_alpha', 1, ''),
						('ctype_cntrl', 1, ''),
						('ctype_digit', 1, ''),
						('ctype_graph', 1, ''),
						('ctype_lower', 1, ''),
						('ctype_print', 1, ''),
						('ctype_punct', 1, ''),
						('ctype_space', 1, ''),
						('ctype_upper', 1, ''),
						('ctype_xdigit', 1, ''),
						('current', 1, ''),
						('date', 1, ''),
						('date_add', 1, ''),
						('date_create', 1, ''),
						('date_create_from_format', 1, ''),
						('date_date_set', 1, ''),
						('date_default_timezone_get', 1, ''),
						('date_default_timezone_set', 1, ''),
						('date_diff', 1, ''),
						('date_format', 1, ''),
						('date_get_last_errors', 1, ''),
						('date_interval_create_from_date_string', 1, ''),
						('date_interval_format', 1, ''),
						('date_isodate_set', 1, ''),
						('date_modify', 1, ''),
						('date_offset_get', 1, ''),
						('date_parse', 1, ''),
						('date_parse_from_format', 1, ''),
						('date_sub', 1, ''),
						('date_sunrise', 1, ''),
						('date_sunset', 1, ''),
						('date_sun_info', 1, ''),
						('date_timestamp_get', 1, ''),
						('date_timestamp_set', 1, ''),
						('date_timezone_get', 1, ''),
						('date_timezone_set', 1, ''),
						('date_time_set', 1, ''),
						('dcgettext', 0, ''),
						('dcngettext', 0, ''),
						('debug_backtrace', 0, ''),
						('debug_print_backtrace', 0, ''),
						('debug_zval_dump', 0, ''),
						('decbin', 1, ''),
						('dechex', 1, ''),
						('decoct', 1, ''),
						('define', 1, ''),
						('defined', 1, ''),
						('define_syslog_variables', 0, ''),
						('deg2rad', 1, ''),
						('dgettext', 0, ''),
						('die', 0, ''),
						('dir', 0, ''),
						('dirname', 0, ''),
						('diskfreespace', 0, ''),
						('disk_free_space', 0, ''),
						('disk_total_space', 0, ''),
						('dngettext', 0, ''),
						('dns_check_record', 0, ''),
						('dns_get_mx', 0, ''),
						('dns_get_record', 0, ''),
						('dom_import_simplexml', 0, ''),
						('doubleval', 1, ''),
						('each', 1, ''),
						('easter_date', 1, ''),
						('easter_days', 1, ''),
						('echo', 1, ''),
						('end', 1, ''),
						('ereg', 1, ''),
						('eregi', 1, ''),
						('eregi_replace', 1, ''),
						('ereg_replace', 1, ''),
						('error_get_last', 0, ''),
						('error_log', 0, ''),
						('error_reporting', 0, ''),
						('escapeshellarg', 0, ''),
						('escapeshellcmd', 0, ''),
						('eval', 0, ''),
						('exec', 0, ''),
						('exif_imagetype', 0, ''),
						('exif_read_data', 0, ''),
						('exif_tagname', 0, ''),
						('exif_thumbnail', 0, ''),
						('exit', 0, ''),
						('exp', 1, ''),
						('explode', 1, ''),
						('expm1', 1, ''),
						('extension_loaded', 0, ''),
						('extract', 1, ''),
						('ezmlm_hash', 0, ''),
						('fclose', 0, ''),
						('feof', 0, ''),
						('fflush', 0, ''),
						('fgetc', 0, ''),
						('fgetcsv', 0, ''),
						('fgets', 0, ''),
						('fgetss', 0, ''),
						('file', 0, ''),
						('fileatime', 0, ''),
						('filectime', 0, ''),
						('filegroup', 0, ''),
						('fileinode', 0, ''),
						('filemtime', 0, ''),
						('fileowner', 0, ''),
						('fileperms', 0, ''),
						('filesize', 0, ''),
						('filetype', 0, ''),
						('file_exists', 0, ''),
						('file_get_contents', 0, ''),
						('file_put_contents', 0, ''),
						('filter_has_var', 1, ''),
						('filter_id', 1, ''),
						('filter_input', 1, ''),
						('filter_input_array', 1, ''),
						('filter_list', 1, ''),
						('filter_var', 1, ''),
						('filter_var_array', 1, ''),
						('floatval', 1, ''),
						('flock', 0, ''),
						('floor', 1, ''),
						('flush', 1, ''),
						('fmod', 1, ''),
						('fnmatch', 0, ''),
						('fopen', 0, ''),
						('forward_static_call', 0, ''),
						('forward_static_call_array', 0, ''),
						('fpassthru', 0, ''),
						('fprintf', 0, ''),
						('fputcsv', 0, ''),
						('fputs', 0, ''),
						('fread', 0, ''),
						('frenchtojd', 0, ''),
						('fscanf', 0, ''),
						('fseek', 0, ''),
						('fsockopen', 0, ''),
						('fstat', 0, ''),
						('ftell', 0, ''),
						('ftp_alloc', 0, ''),
						('ftp_cdup', 0, ''),
						('ftp_chdir', 0, ''),
						('ftp_chmod', 0, ''),
						('ftp_close', 0, ''),
						('ftp_connect', 0, ''),
						('ftp_delete', 0, ''),
						('ftp_exec', 0, ''),
						('ftp_fget', 0, ''),
						('ftp_fput', 0, ''),
						('ftp_get', 0, ''),
						('ftp_get_option', 0, ''),
						('ftp_login', 0, ''),
						('ftp_mdtm', 0, ''),
						('ftp_mkdir', 0, ''),
						('ftp_nb_continue', 0, ''),
						('ftp_nb_fget', 0, ''),
						('ftp_nb_fput', 0, ''),
						('ftp_nb_get', 0, ''),
						('ftp_nb_put', 0, ''),
						('ftp_nlist', 0, ''),
						('ftp_pasv', 0, ''),
						('ftp_put', 0, ''),
						('ftp_pwd', 0, ''),
						('ftp_quit', 0, ''),
						('ftp_raw', 0, ''),
						('ftp_rawlist', 0, ''),
						('ftp_rename', 0, ''),
						('ftp_rmdir', 0, ''),
						('ftp_set_option', 0, ''),
						('ftp_site', 0, ''),
						('ftp_size', 0, ''),
						('ftp_systype', 0, ''),
						('ftruncate', 0, ''),
						('function', 0, 'comment'),
						('function_exists', 0, ''),
						('func_get_arg', 0, ''),
						('func_get_args', 0, ''),
						('func_num_args', 0, ''),
						('fwrite', 0, ''),
						('gc_collect_cycles', 0, ''),
						('gc_disable', 0, ''),
						('gc_enable', 0, ''),
						('gc_enabled', 0, ''),
						('gd_info', 0, ''),
						('getallheaders', 0, ''),
						('getcwd', 0, ''),
						('getdate', 1, ''),
						('getenv', 0, ''),
						('gethostbyaddr', 0, ''),
						('gethostbyname', 0, ''),
						('gethostbynamel', 0, ''),
						('gethostname', 0, ''),
						('getimagesize', 0, ''),
						('getlastmod', 0, ''),
						('getmxrr', 0, ''),
						('getmygid', 0, ''),
						('getmyinode', 0, ''),
						('getmypid', 0, ''),
						('getmyuid', 0, ''),
						('getopt', 0, ''),
						('getprotobyname', 0, ''),
						('getprotobynumber', 0, ''),
						('getrandmax', 1, ''),
						('getservbyname', 0, ''),
						('getservbyport', 0, ''),
						('gettext', 1, ''),
						('gettimeofday', 1, ''),
						('gettype', 1, ''),
						('get_browser', 0, ''),
						('get_called_class', 0, ''),
						('get_cfg_var', 0, ''),
						('get_class', 0, ''),
						('get_class_methods', 0, ''),
						('get_class_vars', 0, ''),
						('get_current_user', 0, ''),
						('get_declared_classes', 0, ''),
						('get_declared_interfaces', 0, ''),
						('get_defined_constants', 0, ''),
						('get_defined_functions', 0, ''),
						('get_defined_vars', 0, ''),
						('get_extension_funcs', 0, ''),
						('get_headers', 0, ''),
						('get_html_translation_table', 0, ''),
						('get_included_files', 0, ''),
						('get_include_path', 0, ''),
						('get_loaded_extensions', 0, ''),
						('get_magic_quotes_gpc', 0, ''),
						('get_magic_quotes_runtime', 0, ''),
						('get_meta_tags', 0, ''),
						('get_object_vars', 0, ''),
						('get_parent_class', 0, ''),
						('get_required_files', 0, ''),
						('get_resource_type', 0, ''),
						('glob', 0, ''),
						('gmdate', 1, ''),
						('gmmktime', 1, ''),
						('gmstrftime', 1, ''),
						('gregoriantojd', 1, ''),
						('gzclose', 0, ''),
						('gzcompress', 0, ''),
						('gzdeflate', 0, ''),
						('gzencode', 0, ''),
						('gzeof', 0, ''),
						('gzfile', 0, ''),
						('gzgetc', 0, ''),
						('gzgets', 0, ''),
						('gzgetss', 0, ''),
						('gzinflate', 0, ''),
						('gzopen', 0, ''),
						('gzpassthru', 0, ''),
						('gzputs', 0, ''),
						('gzread', 0, ''),
						('gzrewind', 0, ''),
						('gzseek', 0, ''),
						('gztell', 0, ''),
						('gzuncompress', 0, ''),
						('gzwrite', 0, ''),
						('hash', 0, ''),
						('hash_algos', 0, ''),
						('hash_copy', 0, ''),
						('hash_file', 0, ''),
						('hash_final', 0, ''),
						('hash_hmac', 0, ''),
						('hash_hmac_file', 0, ''),
						('hash_init', 0, ''),
						('hash_update', 0, ''),
						('hash_update_file', 0, ''),
						('hash_update_stream', 0, ''),
						('header', 0, ''),
						('headers_list', 0, ''),
						('headers_sent', 0, ''),
						('header_remove', 0, ''),
						('hebrev', 1, ''),
						('hebrevc', 1, ''),
						('hexdec', 1, ''),
						('highlight_file', 0, ''),
						('highlight_string', 1, ''),
						('htmlentities', 1, ''),
						('htmlspecialchars', 1, ''),
						('htmlspecialchars_decode', 1, ''),
						('html_entity_decode', 1, ''),
						('http_build_query', 1, ''),
						('hypot', 1, ''),
						('iconv', 1, ''),
						('iconv_get_encoding', 1, ''),
						('iconv_mime_decode', 1, ''),
						('iconv_mime_decode_headers', 1, ''),
						('iconv_mime_encode', 1, ''),
						('iconv_set_encoding', 1, ''),
						('iconv_strlen', 1, ''),
						('iconv_strpos', 1, ''),
						('iconv_strrpos', 1, ''),
						('iconv_substr', 1, ''),
						('idate', 1, ''),
						('ignore_user_abort', 0, ''),
						('image2wbmp', 0, ''),
						('imagealphablending', 0, ''),
						('imageantialias', 0, ''),
						('imagearc', 0, ''),
						('imagechar', 0, ''),
						('imagecharup', 0, ''),
						('imagecolorallocate', 0, ''),
						('imagecolorallocatealpha', 0, ''),
						('imagecolorat', 0, ''),
						('imagecolorclosest', 0, ''),
						('imagecolorclosestalpha', 0, ''),
						('imagecolorclosesthwb', 0, ''),
						('imagecolordeallocate', 0, ''),
						('imagecolorexact', 0, ''),
						('imagecolorexactalpha', 0, ''),
						('imagecolormatch', 0, ''),
						('imagecolorresolve', 0, ''),
						('imagecolorresolvealpha', 0, ''),
						('imagecolorset', 0, ''),
						('imagecolorsforindex', 0, ''),
						('imagecolorstotal', 0, ''),
						('imagecolortransparent', 0, ''),
						('imageconvolution', 0, ''),
						('imagecopy', 0, ''),
						('imagecopymerge', 0, ''),
						('imagecopymergegray', 0, ''),
						('imagecopyresampled', 0, ''),
						('imagecopyresized', 0, ''),
						('imagecreate', 0, ''),
						('imagecreatefromgd', 0, ''),
						('imagecreatefromgd2', 0, ''),
						('imagecreatefromgd2part', 0, ''),
						('imagecreatefromgif', 0, ''),
						('imagecreatefromjpeg', 0, ''),
						('imagecreatefrompng', 0, ''),
						('imagecreatefromstring', 0, ''),
						('imagecreatefromwbmp', 0, ''),
						('imagecreatefromxbm', 0, ''),
						('imagecreatetruecolor', 0, ''),
						('imagedashedline', 0, ''),
						('imagedestroy', 0, ''),
						('imageellipse', 0, ''),
						('imagefill', 0, ''),
						('imagefilledarc', 0, ''),
						('imagefilledellipse', 0, ''),
						('imagefilledpolygon', 0, ''),
						('imagefilledrectangle', 0, ''),
						('imagefilltoborder', 0, ''),
						('imagefilter', 0, ''),
						('imagefontheight', 0, ''),
						('imagefontwidth', 0, ''),
						('imageftbbox', 0, ''),
						('imagefttext', 0, ''),
						('imagegammacorrect', 0, ''),
						('imagegd', 0, ''),
						('imagegd2', 0, ''),
						('imagegif', 0, ''),
						('imagegrabscreen', 0, ''),
						('imagegrabwindow', 0, ''),
						('imageinterlace', 0, ''),
						('imageistruecolor', 0, ''),
						('imagejpeg', 0, ''),
						('imagelayereffect', 0, ''),
						('imageline', 0, ''),
						('imageloadfont', 0, ''),
						('imagepalettecopy', 0, ''),
						('imagepng', 0, ''),
						('imagepolygon', 0, ''),
						('imagerectangle', 0, ''),
						('imagerotate', 0, ''),
						('imagesavealpha', 0, ''),
						('imagesetbrush', 0, ''),
						('imagesetpixel', 0, ''),
						('imagesetstyle', 0, ''),
						('imagesetthickness', 0, ''),
						('imagesettile', 0, ''),
						('imagestring', 0, ''),
						('imagestringup', 0, ''),
						('imagesx', 0, ''),
						('imagesy', 0, ''),
						('imagetruecolortopalette', 0, ''),
						('imagettfbbox', 0, ''),
						('imagettftext', 0, ''),
						('imagetypes', 0, ''),
						('imagewbmp', 0, ''),
						('imagexbm', 0, ''),
						('image_type_to_extension', 0, ''),
						('image_type_to_mime_type', 0, ''),
						('imap_8bit', 0, ''),
						('imap_alerts', 0, ''),
						('imap_append', 0, ''),
						('imap_base64', 0, ''),
						('imap_binary', 0, ''),
						('imap_body', 0, ''),
						('imap_bodystruct', 0, ''),
						('imap_check', 0, ''),
						('imap_clearflag_full', 0, ''),
						('imap_close', 0, ''),
						('imap_create', 0, ''),
						('imap_createmailbox', 0, ''),
						('imap_delete', 0, ''),
						('imap_deletemailbox', 0, ''),
						('imap_errors', 0, ''),
						('imap_expunge', 0, ''),
						('imap_fetchbody', 0, ''),
						('imap_fetchheader', 0, ''),
						('imap_fetchstructure', 0, ''),
						('imap_fetchtext', 0, ''),
						('imap_fetch_overview', 0, ''),
						('imap_gc', 0, ''),
						('imap_getacl', 0, ''),
						('imap_getmailboxes', 0, ''),
						('imap_getsubscribed', 0, ''),
						('imap_get_quota', 0, ''),
						('imap_get_quotaroot', 0, ''),
						('imap_header', 0, ''),
						('imap_headerinfo', 0, ''),
						('imap_headers', 0, ''),
						('imap_last_error', 0, ''),
						('imap_list', 0, ''),
						('imap_listmailbox', 0, ''),
						('imap_listscan', 0, ''),
						('imap_listsubscribed', 0, ''),
						('imap_lsub', 0, ''),
						('imap_mail', 0, ''),
						('imap_mailboxmsginfo', 0, ''),
						('imap_mail_compose', 0, ''),
						('imap_mail_copy', 0, ''),
						('imap_mail_move', 0, ''),
						('imap_mime_header_decode', 0, ''),
						('imap_msgno', 0, ''),
						('imap_mutf7_to_utf8', 0, ''),
						('imap_num_msg', 0, ''),
						('imap_num_recent', 0, ''),
						('imap_open', 0, ''),
						('imap_ping', 0, ''),
						('imap_qprint', 0, ''),
						('imap_rename', 0, ''),
						('imap_renamemailbox', 0, ''),
						('imap_reopen', 0, ''),
						('imap_rfc822_parse_adrlist', 0, ''),
						('imap_rfc822_parse_headers', 0, ''),
						('imap_rfc822_write_address', 0, ''),
						('imap_savebody', 0, ''),
						('imap_scan', 0, ''),
						('imap_scanmailbox', 0, ''),
						('imap_search', 0, ''),
						('imap_setacl', 0, ''),
						('imap_setflag_full', 0, ''),
						('imap_set_quota', 0, ''),
						('imap_sort', 0, ''),
						('imap_status', 0, ''),
						('imap_subscribe', 0, ''),
						('imap_thread', 0, ''),
						('imap_timeout', 0, ''),
						('imap_uid', 0, ''),
						('imap_undelete', 0, ''),
						('imap_unsubscribe', 0, ''),
						('imap_utf7_decode', 0, ''),
						('imap_utf7_encode', 0, ''),
						('imap_utf8', 0, ''),
						('imap_utf8_to_mutf7', 0, ''),
						('implode', 1, ''),
						('import_request_variables', 0, ''),
						('include', 0, ''),
						('include_once', 0, ''),
						('inet_ntop', 0, ''),
						('inet_pton', 0, ''),
						('ini_alter', 0, ''),
						('ini_get', 0, ''),
						('ini_get_all', 0, ''),
						('ini_restore', 0, ''),
						('ini_set', 0, ''),
						('interface_exists', 0, ''),
						('intval', 1, ''),
						('in_array', 1, ''),
						('ip2long', 0, ''),
						('iptcembed', 0, ''),
						('iptcparse', 0, ''),
						('is_a', 1, ''),
						('is_array', 1, ''),
						('is_bool', 1, ''),
						('is_callable', 1, ''),
						('is_dir', 1, ''),
						('is_double', 1, ''),
						('is_executable', 1, ''),
						('is_file', 1, ''),
						('is_finite', 1, ''),
						('is_float', 1, ''),
						('is_infinite', 1, ''),
						('is_int', 1, ''),
						('is_integer', 1, ''),
						('is_link', 1, ''),
						('is_long', 1, ''),
						('is_nan', 1, ''),
						('is_null', 1, ''),
						('is_numeric', 1, ''),
						('is_object', 1, ''),
						('is_readable', 1, ''),
						('is_real', 1, ''),
						('is_resource', 1, ''),
						('is_scalar', 1, ''),
						('is_soap_fault', 1, ''),
						('is_string', 1, ''),
						('is_subclass_of', 1, ''),
						('is_uploaded_file', 1, ''),
						('is_writable', 1, ''),
						('is_writeable', 1, ''),
						('iterator_apply', 0, ''),
						('iterator_count', 0, ''),
						('iterator_to_array', 0, ''),
						('jddayofweek', 1, ''),
						('jdmonthname', 1, ''),
						('jdtofrench', 1, ''),
						('jdtogregorian', 1, ''),
						('jdtojewish', 1, ''),
						('jdtojulian', 1, ''),
						('jdtounix', 1, ''),
						('jewishtojd', 1, ''),
						('join', 1, ''),
						('jpeg2wbmp', 0, ''),
						('json_decode', 1, ''),
						('json_encode', 1, ''),
						('json_last_error', 1, ''),
						('juliantojd', 1, ''),
						('key', 1, ''),
						('key_exists', 1, ''),
						('krsort', 1, ''),
						('ksort', 1, ''),
						('lcfirst', 1, ''),
						('lcg_value', 1, ''),
						('levenshtein', 1, ''),
						('libxml_clear_errors', 0, ''),
						('libxml_disable_entity_loader', 0, ''),
						('libxml_get_errors', 0, ''),
						('libxml_get_last_error', 0, ''),
						('libxml_set_streams_context', 0, ''),
						('libxml_use_internal_errors', 0, ''),
						('link', 0, ''),
						('linkinfo', 0, ''),
						('localeconv', 1, ''),
						('localtime', 1, ''),
						('log', 1, ''),
						('log10', 1, ''),
						('log1p', 1, ''),
						('long2ip', 1, ''),
						('lstat', 0, ''),
						('ltrim', 1, ''),
						('magic_quotes_runtime', 0, ''),
						('mail', 0, ''),
						('max', 1, ''),
						('mb_check_encoding', 1, ''),
						('mb_convert_case', 1, ''),
						('mb_convert_encoding', 1, ''),
						('mb_convert_kana', 1, ''),
						('mb_convert_variables', 1, ''),
						('mb_decode_mimeheader', 1, ''),
						('mb_decode_numericentity', 1, ''),
						('mb_detect_encoding', 1, ''),
						('mb_detect_order', 1, ''),
						('mb_encode_mimeheader', 1, ''),
						('mb_encode_numericentity', 1, ''),
						('mb_encoding_aliases', 1, ''),
						('mb_ereg', 1, ''),
						('mb_eregi', 1, ''),
						('mb_eregi_replace', 1, ''),
						('mb_ereg_match', 1, ''),
						('mb_ereg_replace', 1, ''),
						('mb_ereg_search', 1, ''),
						('mb_ereg_search_getpos', 1, ''),
						('mb_ereg_search_getregs', 1, ''),
						('mb_ereg_search_init', 1, ''),
						('mb_ereg_search_pos', 1, ''),
						('mb_ereg_search_regs', 1, ''),
						('mb_ereg_search_setpos', 1, ''),
						('mb_get_info', 1, ''),
						('mb_http_input', 1, ''),
						('mb_http_output', 1, ''),
						('mb_internal_encoding', 1, ''),
						('mb_language', 1, ''),
						('mb_list_encodings', 1, ''),
						('mb_output_handler', 1, ''),
						('mb_parse_str', 1, ''),
						('mb_preferred_mime_name', 1, ''),
						('mb_regex_encoding', 1, ''),
						('mb_regex_set_options', 1, ''),
						('mb_send_mail', 1, ''),
						('mb_split', 1, ''),
						('mb_strcut', 1, ''),
						('mb_strimwidth', 1, ''),
						('mb_stripos', 1, ''),
						('mb_stristr', 1, ''),
						('mb_strlen', 1, ''),
						('mb_strpos', 1, ''),
						('mb_strrchr', 1, ''),
						('mb_strrichr', 1, ''),
						('mb_strripos', 1, ''),
						('mb_strrpos', 1, ''),
						('mb_strstr', 1, ''),
						('mb_strtolower', 1, ''),
						('mb_strtoupper', 1, ''),
						('mb_strwidth', 1, ''),
						('mb_substitute_character', 1, ''),
						('mb_substr', 1, ''),
						('mb_substr_count', 1, ''),
						('mcrypt_cbc', 0, ''),
						('mcrypt_cfb', 0, ''),
						('mcrypt_create_iv', 0, ''),
						('mcrypt_decrypt', 0, ''),
						('mcrypt_ecb', 0, ''),
						('mcrypt_encrypt', 0, ''),
						('mcrypt_enc_get_algorithms_name', 0, ''),
						('mcrypt_enc_get_block_size', 0, ''),
						('mcrypt_enc_get_iv_size', 0, ''),
						('mcrypt_enc_get_key_size', 0, ''),
						('mcrypt_enc_get_modes_name', 0, ''),
						('mcrypt_enc_get_supported_key_sizes', 0, ''),
						('mcrypt_enc_is_block_algorithm', 0, ''),
						('mcrypt_enc_is_block_algorithm_mode', 0, ''),
						('mcrypt_enc_is_block_mode', 0, ''),
						('mcrypt_enc_self_test', 0, ''),
						('mcrypt_generic', 0, ''),
						('mcrypt_generic_deinit', 0, ''),
						('mcrypt_generic_end', 0, ''),
						('mcrypt_generic_init', 0, ''),
						('mcrypt_get_block_size', 0, ''),
						('mcrypt_get_cipher_name', 0, ''),
						('mcrypt_get_iv_size', 0, ''),
						('mcrypt_get_key_size', 0, ''),
						('mcrypt_list_algorithms', 0, ''),
						('mcrypt_list_modes', 0, ''),
						('mcrypt_module_close', 0, ''),
						('mcrypt_module_get_algo_block_size', 0, ''),
						('mcrypt_module_get_algo_key_size', 0, ''),
						('mcrypt_module_get_supported_key_sizes', 0, ''),
						('mcrypt_module_is_block_algorithm', 0, ''),
						('mcrypt_module_is_block_algorithm_mode', 0, ''),
						('mcrypt_module_is_block_mode', 0, ''),
						('mcrypt_module_open', 0, ''),
						('mcrypt_module_self_test', 0, ''),
						('mcrypt_ofb', 0, ''),
						('md5', 1, ''),
						('md5_file', 0, ''),
						('mdecrypt_generic', 0, ''),
						('memory_get_peak_usage', 0, ''),
						('memory_get_usage', 0, ''),
						('metaphone', 0, ''),
						('method_exists', 0, ''),
						('mhash', 0, ''),
						('mhash_count', 0, ''),
						('mhash_get_block_size', 0, ''),
						('mhash_get_hash_name', 0, ''),
						('mhash_keygen_s2k', 0, ''),
						('microtime', 1, ''),
						('min', 1, ''),
						('ming_keypress', 0, ''),
						('ming_setcubicthreshold', 0, ''),
						('ming_setscale', 0, ''),
						('ming_setswfcompression', 0, ''),
						('ming_useconstants', 0, ''),
						('ming_useswfversion', 0, ''),
						('mkdir', 0, ''),
						('mktime', 1, ''),
						('move_uploaded_file', 0, ''),
						('mt_getrandmax', 1, ''),
						('mt_rand', 1, ''),
						('mt_srand', 1, ''),
						('mysql', 0, ''),
						('mysqli_affected_rows', 0, ''),
						('mysqli_autocommit', 0, ''),
						('mysqli_bind_param', 0, ''),
						('mysqli_bind_result', 0, ''),
						('mysqli_change_user', 0, ''),
						('mysqli_character_set_name', 0, ''),
						('mysqli_client_encoding', 0, ''),
						('mysqli_close', 0, ''),
						('mysqli_commit', 0, ''),
						('mysqli_connect', 0, ''),
						('mysqli_connect_errno', 0, ''),
						('mysqli_connect_error', 0, ''),
						('mysqli_data_seek', 0, ''),
						('mysqli_debug', 0, ''),
						('mysqli_dump_debug_info', 0, ''),
						('mysqli_errno', 0, ''),
						('mysqli_error', 0, ''),
						('mysqli_escape_string', 0, ''),
						('mysqli_execute', 0, ''),
						('mysqli_fetch', 0, ''),
						('mysqli_fetch_all', 0, ''),
						('mysqli_fetch_array', 0, ''),
						('mysqli_fetch_assoc', 0, ''),
						('mysqli_fetch_field', 0, ''),
						('mysqli_fetch_fields', 0, ''),
						('mysqli_fetch_field_direct', 0, ''),
						('mysqli_fetch_lengths', 0, ''),
						('mysqli_fetch_object', 0, ''),
						('mysqli_fetch_row', 0, ''),
						('mysqli_field_count', 0, ''),
						('mysqli_field_seek', 0, ''),
						('mysqli_field_tell', 0, ''),
						('mysqli_free_result', 0, ''),
						('mysqli_get_cache_stats', 0, ''),
						('mysqli_get_charset', 0, ''),
						('mysqli_get_client_info', 0, ''),
						('mysqli_get_client_stats', 0, ''),
						('mysqli_get_client_version', 0, ''),
						('mysqli_get_connection_stats', 0, ''),
						('mysqli_get_host_info', 0, ''),
						('mysqli_get_metadata', 0, ''),
						('mysqli_get_proto_info', 0, ''),
						('mysqli_get_server_info', 0, ''),
						('mysqli_get_server_version', 0, ''),
						('mysqli_get_warnings', 0, ''),
						('mysqli_info', 0, ''),
						('mysqli_init', 0, ''),
						('mysqli_insert_id', 0, ''),
						('mysqli_kill', 0, ''),
						('mysqli_more_results', 0, ''),
						('mysqli_multi_query', 0, ''),
						('mysqli_next_result', 0, ''),
						('mysqli_num_fields', 0, ''),
						('mysqli_num_rows', 0, ''),
						('mysqli_options', 0, ''),
						('mysqli_param_count', 0, ''),
						('mysqli_ping', 0, ''),
						('mysqli_poll', 0, ''),
						('mysqli_prepare', 0, ''),
						('mysqli_query', 0, ''),
						('mysqli_real_connect', 0, ''),
						('mysqli_real_escape_string', 0, ''),
						('mysqli_real_query', 0, ''),
						('mysqli_reap_async_query', 0, ''),
						('mysqli_refresh', 0, ''),
						('mysqli_report', 0, ''),
						('mysqli_rollback', 0, ''),
						('mysqli_select_db', 0, ''),
						('mysqli_send_long_data', 0, ''),
						('mysqli_set_charset', 0, ''),
						('mysqli_set_opt', 0, ''),
						('mysqli_sqlstate', 0, ''),
						('mysqli_ssl_set', 0, ''),
						('mysqli_stat', 0, ''),
						('mysqli_stmt_affected_rows', 0, ''),
						('mysqli_stmt_attr_get', 0, ''),
						('mysqli_stmt_attr_set', 0, ''),
						('mysqli_stmt_bind_param', 0, ''),
						('mysqli_stmt_bind_result', 0, ''),
						('mysqli_stmt_close', 0, ''),
						('mysqli_stmt_data_seek', 0, ''),
						('mysqli_stmt_errno', 0, ''),
						('mysqli_stmt_error', 0, ''),
						('mysqli_stmt_execute', 0, ''),
						('mysqli_stmt_fetch', 0, ''),
						('mysqli_stmt_field_count', 0, ''),
						('mysqli_stmt_free_result', 0, ''),
						('mysqli_stmt_get_result', 0, ''),
						('mysqli_stmt_get_warnings', 0, ''),
						('mysqli_stmt_init', 0, ''),
						('mysqli_stmt_insert_id', 0, ''),
						('mysqli_stmt_more_results', 0, ''),
						('mysqli_stmt_next_result', 0, ''),
						('mysqli_stmt_num_rows', 0, ''),
						('mysqli_stmt_param_count', 0, ''),
						('mysqli_stmt_prepare', 0, ''),
						('mysqli_stmt_reset', 0, ''),
						('mysqli_stmt_result_metadata', 0, ''),
						('mysqli_stmt_send_long_data', 0, ''),
						('mysqli_stmt_sqlstate', 0, ''),
						('mysqli_stmt_store_result', 0, ''),
						('mysqli_store_result', 0, ''),
						('mysqli_thread_id', 0, ''),
						('mysqli_thread_safe', 0, ''),
						('mysqli_use_result', 0, ''),
						('mysqli_warning_count', 0, ''),
						('mysql_affected_rows', 0, ''),
						('mysql_client_encoding', 0, ''),
						('mysql_close', 0, ''),
						('mysql_connect', 0, ''),
						('mysql_data_seek', 0, ''),
						('mysql_dbname', 0, ''),
						('mysql_db_name', 0, ''),
						('mysql_db_query', 0, ''),
						('mysql_errno', 0, ''),
						('mysql_error', 0, ''),
						('mysql_escape_string', 0, ''),
						('mysql_fetch_array', 0, ''),
						('mysql_fetch_assoc', 0, ''),
						('mysql_fetch_field', 0, ''),
						('mysql_fetch_lengths', 0, ''),
						('mysql_fetch_object', 0, ''),
						('mysql_fetch_row', 0, ''),
						('mysql_fieldflags', 0, ''),
						('mysql_fieldlen', 0, ''),
						('mysql_fieldname', 0, ''),
						('mysql_fieldtable', 0, ''),
						('mysql_fieldtype', 0, ''),
						('mysql_field_flags', 0, ''),
						('mysql_field_len', 0, ''),
						('mysql_field_name', 0, ''),
						('mysql_field_seek', 0, ''),
						('mysql_field_table', 0, ''),
						('mysql_field_type', 0, ''),
						('mysql_freeresult', 0, ''),
						('mysql_free_result', 0, ''),
						('mysql_get_client_info', 0, ''),
						('mysql_get_host_info', 0, ''),
						('mysql_get_proto_info', 0, ''),
						('mysql_get_server_info', 0, ''),
						('mysql_info', 0, ''),
						('mysql_insert_id', 0, ''),
						('mysql_listdbs', 0, ''),
						('mysql_listfields', 0, ''),
						('mysql_listtables', 0, ''),
						('mysql_list_dbs', 0, ''),
						('mysql_list_fields', 0, ''),
						('mysql_list_processes', 0, ''),
						('mysql_list_tables', 0, ''),
						('mysql_numfields', 0, ''),
						('mysql_numrows', 0, ''),
						('mysql_num_fields', 0, ''),
						('mysql_num_rows', 0, ''),
						('mysql_pconnect', 0, ''),
						('mysql_ping', 0, ''),
						('mysql_query', 0, ''),
						('mysql_real_escape_string', 0, ''),
						('mysql_result', 0, ''),
						('mysql_selectdb', 0, ''),
						('mysql_select_db', 0, ''),
						('mysql_set_charset', 0, ''),
						('mysql_stat', 0, ''),
						('mysql_tablename', 0, ''),
						('mysql_table_name', 0, ''),
						('mysql_thread_id', 0, ''),
						('mysql_unbuffered_query', 0, ''),
						('natcasesort', 1, ''),
						('natsort', 1, ''),
						('next', 1, ''),
						('ngettext', 1, ''),
						('nl2br', 1, ''),
						('number_format', 1, ''),
						('ob_clean', 0, ''),
						('ob_end_clean', 0, ''),
						('ob_end_flush', 0, ''),
						('ob_flush', 0, ''),
						('ob_get_clean', 0, ''),
						('ob_get_contents', 0, ''),
						('ob_get_flush', 0, ''),
						('ob_get_length', 0, ''),
						('ob_get_level', 0, ''),
						('ob_get_status', 0, ''),
						('ob_gzhandler', 0, ''),
						('ob_iconv_handler', 0, ''),
						('ob_implicit_flush', 0, ''),
						('ob_list_handlers', 0, ''),
						('ob_start', 0, ''),
						('octdec', 1, ''),
						('odbc_autocommit', 0, ''),
						('odbc_binmode', 0, ''),
						('odbc_close', 0, ''),
						('odbc_close_all', 0, ''),
						('odbc_columnprivileges', 0, ''),
						('odbc_columns', 0, ''),
						('odbc_commit', 0, ''),
						('odbc_connect', 0, ''),
						('odbc_cursor', 0, ''),
						('odbc_data_source', 0, ''),
						('odbc_do', 0, ''),
						('odbc_error', 0, ''),
						('odbc_errormsg', 0, ''),
						('odbc_exec', 0, ''),
						('odbc_execute', 0, ''),
						('odbc_fetch_array', 0, ''),
						('odbc_fetch_into', 0, ''),
						('odbc_fetch_object', 0, ''),
						('odbc_fetch_row', 0, ''),
						('odbc_field_len', 0, ''),
						('odbc_field_name', 0, ''),
						('odbc_field_num', 0, ''),
						('odbc_field_precision', 0, ''),
						('odbc_field_scale', 0, ''),
						('odbc_field_type', 0, ''),
						('odbc_foreignkeys', 0, ''),
						('odbc_free_result', 0, ''),
						('odbc_gettypeinfo', 0, ''),
						('odbc_longreadlen', 0, ''),
						('odbc_next_result', 0, ''),
						('odbc_num_fields', 0, ''),
						('odbc_num_rows', 0, ''),
						('odbc_pconnect', 0, ''),
						('odbc_prepare', 0, ''),
						('odbc_primarykeys', 0, ''),
						('odbc_procedurecolumns', 0, ''),
						('odbc_procedures', 0, ''),
						('odbc_result', 0, ''),
						('odbc_result_all', 0, ''),
						('odbc_rollback', 0, ''),
						('odbc_setoption', 0, ''),
						('odbc_specialcolumns', 0, ''),
						('odbc_statistics', 0, ''),
						('odbc_tableprivileges', 0, ''),
						('odbc_tables', 0, ''),
						('opendir', 0, ''),
						('openlog', 0, ''),
						('openssl_cipher_iv_length', 0, ''),
						('openssl_csr_export', 0, ''),
						('openssl_csr_export_to_file', 0, ''),
						('openssl_csr_get_public_key', 0, ''),
						('openssl_csr_get_subject', 0, ''),
						('openssl_csr_new', 0, ''),
						('openssl_csr_sign', 0, ''),
						('openssl_decrypt', 0, ''),
						('openssl_dh_compute_key', 0, ''),
						('openssl_digest', 0, ''),
						('openssl_encrypt', 0, ''),
						('openssl_error_string', 0, ''),
						('openssl_free_key', 0, ''),
						('openssl_get_cipher_methods', 0, ''),
						('openssl_get_md_methods', 0, ''),
						('openssl_get_privatekey', 0, ''),
						('openssl_get_publickey', 0, ''),
						('openssl_open', 0, ''),
						('openssl_pkcs12_export', 0, ''),
						('openssl_pkcs12_export_to_file', 0, ''),
						('openssl_pkcs12_read', 0, ''),
						('openssl_pkcs7_decrypt', 0, ''),
						('openssl_pkcs7_encrypt', 0, ''),
						('openssl_pkcs7_sign', 0, ''),
						('openssl_pkcs7_verify', 0, ''),
						('openssl_pkey_export', 0, ''),
						('openssl_pkey_export_to_file', 0, ''),
						('openssl_pkey_free', 0, ''),
						('openssl_pkey_get_details', 0, ''),
						('openssl_pkey_get_private', 0, ''),
						('openssl_pkey_get_public', 0, ''),
						('openssl_pkey_new', 0, ''),
						('openssl_private_decrypt', 0, ''),
						('openssl_private_encrypt', 0, ''),
						('openssl_public_decrypt', 0, ''),
						('openssl_public_encrypt', 0, ''),
						('openssl_random_pseudo_bytes', 0, ''),
						('openssl_seal', 0, ''),
						('openssl_sign', 0, ''),
						('openssl_verify', 0, ''),
						('openssl_x509_checkpurpose', 0, ''),
						('openssl_x509_check_private_key', 0, ''),
						('openssl_x509_export', 0, ''),
						('openssl_x509_export_to_file', 0, ''),
						('openssl_x509_free', 0, ''),
						('openssl_x509_parse', 0, ''),
						('openssl_x509_read', 0, ''),
						('ord', 1, ''),
						('output_add_rewrite_var', 0, ''),
						('output_reset_rewrite_vars', 0, ''),
						('pack', 1, ''),
						('parse_ini_file', 0, ''),
						('parse_ini_string', 1, ''),
						('parse_str', 1, ''),
						('parse_url', 1, ''),
						('passthru', 0, ''),
						('pathinfo', 0, ''),
						('pclose', 0, ''),
						('pdo_drivers', 0, ''),
						('pfsockopen', 0, ''),
						('phpcredits', 0, ''),
						('phpinfo', 0, ''),
						('phpversion', 0, ''),
						('php_egg_logo_guid', 0, ''),
						('php_ini_loaded_file', 0, ''),
						('php_ini_scanned_files', 0, ''),
						('php_logo_guid', 0, ''),
						('php_real_logo_guid', 0, ''),
						('php_sapi_name', 0, ''),
						('php_strip_whitespace', 0, ''),
						('php_uname', 0, ''),
						('pi', 1, ''),
						('png2wbmp', 0, ''),
						('popen', 0, ''),
						('pos', 1, ''),
						('pow', 1, ''),
						('preg_filter', 1, ''),
						('preg_grep', 1, ''),
						('preg_last_error', 1, ''),
						('preg_match', 1, ''),
						('preg_match_all', 1, ''),
						('preg_quote', 1, ''),
						('preg_replace', 1, ''),
						('preg_replace_callback', 1, ''),
						('preg_split', 1, ''),
						('prev', 1, ''),
						('printf', 1, ''),
						('print_r', 1, ''),
						('proc_close', 0, ''),
						('proc_get_status', 0, ''),
						('proc_open', 0, ''),
						('proc_terminate', 0, ''),
						('property_exists', 1, ''),
						('putenv', 0, ''),
						('quoted_printable_decode', 1, ''),
						('quoted_printable_encode', 1, ''),
						('quotemeta', 1, ''),
						('rad2deg', 1, ''),
						('rand', 1, ''),
						('range', 1, ''),
						('rawurldecode', 1, ''),
						('rawurlencode', 1, ''),
						('readdir', 0, ''),
						('readfile', 0, ''),
						('readgzfile', 0, ''),
						('readlink', 0, ''),
						('read_exif_data', 0, ''),
						('realpath', 0, ''),
						('realpath_cache_get', 0, ''),
						('realpath_cache_size', 0, ''),
						('register_shutdown_function', 0, ''),
						('register_tick_function', 0, ''),
						('rename', 0, ''),
						('require', 0, ''),
						('require_once', 0, ''),
						('reset', 1, ''),
						('restore_error_handler', 0, ''),
						('restore_exception_handler', 0, ''),
						('restore_include_path', 0, ''),
						('rewind', 0, ''),
						('rewinddir', 0, ''),
						('rmdir', 0, ''),
						('round', 1, ''),
						('rsort', 1, ''),
						('rtrim', 1, ''),
						('scandir', 0, ''),
						('serialize', 1, ''),
						('session_cache_expire', 0, ''),
						('session_cache_limiter', 0, ''),
						('session_commit', 0, ''),
						('session_decode', 0, ''),
						('session_destroy', 0, ''),
						('session_encode', 0, ''),
						('session_get_cookie_params', 0, ''),
						('session_id', 0, ''),
						('session_is_registered', 0, ''),
						('session_module_name', 0, ''),
						('session_name', 0, ''),
						('session_regenerate_id', 0, ''),
						('session_register', 0, ''),
						('session_save_path', 0, ''),
						('session_set_cookie_params', 0, ''),
						('session_set_save_handler', 0, ''),
						('session_start', 0, ''),
						('session_unregister', 0, ''),
						('session_unset', 0, ''),
						('session_write_close', 0, ''),
						('setcookie', 0, ''),
						('setlocale', 0, ''),
						('setrawcookie', 0, ''),
						('settype', 0, ''),
						('set_error_handler', 0, ''),
						('set_exception_handler', 0, ''),
						('set_file_buffer', 0, ''),
						('set_include_path', 0, ''),
						('set_magic_quotes_runtime', 0, ''),
						('set_socket_blocking', 0, ''),
						('set_time_limit', 0, ''),
						('sha1', 0, ''),
						('sha1_file', 0, ''),
						('shell_exec', 0, ''),
						('show_source', 0, ''),
						('shuffle', 1, ''),
						('similar_text', 1, ''),
						('simplexml_import_dom', 0, ''),
						('simplexml_load_file', 0, ''),
						('simplexml_load_string', 0, ''),
						('sin', 1, ''),
						('sinh', 1, ''),
						('sizeof', 1, ''),
						('sleep', 0, ''),
						('socket_accept', 0, ''),
						('socket_bind', 0, ''),
						('socket_clear_error', 0, ''),
						('socket_close', 0, ''),
						('socket_connect', 0, ''),
						('socket_create', 0, ''),
						('socket_create_listen', 0, ''),
						('socket_create_pair', 0, ''),
						('socket_getopt', 0, ''),
						('socket_getpeername', 0, ''),
						('socket_getsockname', 0, ''),
						('socket_get_option', 0, ''),
						('socket_get_status', 0, ''),
						('socket_last_error', 0, ''),
						('socket_listen', 0, ''),
						('socket_read', 0, ''),
						('socket_recv', 0, ''),
						('socket_recvfrom', 0, ''),
						('socket_select', 0, ''),
						('socket_send', 0, ''),
						('socket_sendto', 0, ''),
						('socket_setopt', 0, ''),
						('socket_set_block', 0, ''),
						('socket_set_blocking', 0, ''),
						('socket_set_nonblock', 0, ''),
						('socket_set_option', 0, ''),
						('socket_set_timeout', 0, ''),
						('socket_shutdown', 0, ''),
						('socket_strerror', 0, ''),
						('socket_write', 0, ''),
						('sort', 1, ''),
						('soundex', 1, ''),
						('split', 1, ''),
						('spliti', 1, ''),
						('spl_autoload', 0, ''),
						('spl_autoload_call', 0, ''),
						('spl_autoload_extensions', 0, ''),
						('spl_autoload_functions', 0, ''),
						('spl_autoload_register', 0, ''),
						('spl_autoload_unregister', 0, ''),
						('spl_classes', 0, ''),
						('spl_object_hash', 0, ''),
						('sprintf', 1, ''),
						('sqlite_array_query', 0, ''),
						('sqlite_busy_timeout', 0, ''),
						('sqlite_changes', 0, ''),
						('sqlite_close', 0, ''),
						('sqlite_column', 0, ''),
						('sqlite_create_aggregate', 0, ''),
						('sqlite_create_function', 0, ''),
						('sqlite_current', 0, ''),
						('sqlite_error_string', 0, ''),
						('sqlite_escape_string', 0, ''),
						('sqlite_exec', 0, ''),
						('sqlite_factory', 0, ''),
						('sqlite_fetch_all', 0, ''),
						('sqlite_fetch_array', 0, ''),
						('sqlite_fetch_column_types', 0, ''),
						('sqlite_fetch_object', 0, ''),
						('sqlite_fetch_single', 0, ''),
						('sqlite_fetch_string', 0, ''),
						('sqlite_field_name', 0, ''),
						('sqlite_has_more', 0, ''),
						('sqlite_has_prev', 0, ''),
						('sqlite_last_error', 0, ''),
						('sqlite_last_insert_rowid', 0, ''),
						('sqlite_libencoding', 0, ''),
						('sqlite_libversion', 0, ''),
						('sqlite_next', 0, ''),
						('sqlite_num_fields', 0, ''),
						('sqlite_num_rows', 0, ''),
						('sqlite_open', 0, ''),
						('sqlite_popen', 0, ''),
						('sqlite_prev', 0, ''),
						('sqlite_query', 0, ''),
						('sqlite_rewind', 0, ''),
						('sqlite_seek', 0, ''),
						('sqlite_single_query', 0, ''),
						('sqlite_udf_decode_binary', 0, ''),
						('sqlite_udf_encode_binary', 0, ''),
						('sqlite_unbuffered_query', 0, ''),
						('sqlite_valid', 0, ''),
						('sql_regcase', 0, ''),
						('sqrt', 1, ''),
						('srand', 1, ''),
						('sscanf', 0, ''),
						('stat', 0, ''),
						('strcasecmp', 1, ''),
						('strchr', 1, ''),
						('strcmp', 1, ''),
						('strcoll', 1, ''),
						('strcspn', 1, ''),
						('stream_bucket_append', 0, ''),
						('stream_bucket_make_writeable', 0, ''),
						('stream_bucket_new', 0, ''),
						('stream_bucket_prepend', 0, ''),
						('stream_context_create', 0, ''),
						('stream_context_get_default', 0, ''),
						('stream_context_get_options', 0, ''),
						('stream_context_get_params', 0, ''),
						('stream_context_set_default', 0, ''),
						('stream_context_set_option', 0, ''),
						('stream_context_set_params', 0, ''),
						('stream_copy_to_stream', 0, ''),
						('stream_filter_append', 0, ''),
						('stream_filter_prepend', 0, ''),
						('stream_filter_register', 0, ''),
						('stream_filter_remove', 0, ''),
						('stream_get_contents', 0, ''),
						('stream_get_filters', 0, ''),
						('stream_get_line', 0, ''),
						('stream_get_meta_data', 0, ''),
						('stream_get_transports', 0, ''),
						('stream_get_wrappers', 0, ''),
						('stream_is_local', 0, ''),
						('stream_register_wrapper', 0, ''),
						('stream_resolve_include_path', 0, ''),
						('stream_select', 0, ''),
						('stream_set_blocking', 0, ''),
						('stream_set_read_buffer', 0, ''),
						('stream_set_timeout', 0, ''),
						('stream_set_write_buffer', 0, ''),
						('stream_socket_accept', 0, ''),
						('stream_socket_client', 0, ''),
						('stream_socket_enable_crypto', 0, ''),
						('stream_socket_get_name', 0, ''),
						('stream_socket_pair', 0, ''),
						('stream_socket_recvfrom', 0, ''),
						('stream_socket_sendto', 0, ''),
						('stream_socket_server', 0, ''),
						('stream_socket_shutdown', 0, ''),
						('stream_supports_lock', 0, ''),
						('stream_wrapper_register', 0, ''),
						('stream_wrapper_restore', 0, ''),
						('stream_wrapper_unregister', 0, ''),
						('strftime', 1, ''),
						('stripcslashes', 1, ''),
						('stripos', 1, ''),
						('stripslashes', 1, ''),
						('strip_tags', 1, ''),
						('stristr', 1, ''),
						('strlen', 1, ''),
						('strnatcasecmp', 1, ''),
						('strnatcmp', 1, ''),
						('strncasecmp', 1, ''),
						('strncmp', 1, ''),
						('strpbrk', 1, ''),
						('strpos', 1, ''),
						('strrchr', 1, ''),
						('strrev', 1, ''),
						('strripos', 1, ''),
						('strrpos', 1, ''),
						('strspn', 1, ''),
						('strstr', 1, ''),
						('strtok', 1, ''),
						('strtolower', 1, ''),
						('strtotime', 1, ''),
						('strtoupper', 1, ''),
						('strtr', 1, ''),
						('strval', 1, ''),
						('str_getcsv', 1, ''),
						('str_ireplace', 1, ''),
						('str_pad', 1, ''),
						('str_repeat', 1, ''),
						('str_replace', 1, ''),
						('str_rot13', 1, ''),
						('str_shuffle', 1, ''),
						('str_split', 1, ''),
						('str_word_count', 1, ''),
						('substr', 1, ''),
						('substr_compare', 1, ''),
						('substr_count', 1, ''),
						('substr_replace', 1, ''),
						('symlink', 0, ''),
						('syslog', 0, ''),
						('system', 0, ''),
						('sys_get_temp_dir', 0, ''),
						('tan', 1, ''),
						('tanh', 1, ''),
						('tempnam', 0, ''),
						('textdomain', 0, ''),
						('time', 1, ''),
						('timezone_abbreviations_list', 1, ''),
						('timezone_identifiers_list', 1, ''),
						('timezone_location_get', 1, ''),
						('timezone_name_from_abbr', 1, ''),
						('timezone_name_get', 1, ''),
						('timezone_offset_get', 1, ''),
						('timezone_open', 1, ''),
						('timezone_transitions_get', 1, ''),
						('timezone_version_get', 1, ''),
						('time_nanosleep', 0, ''),
						('time_sleep_until', 0, ''),
						('tmpfile', 0, ''),
						('token_get_all', 0, ''),
						('token_name', 0, ''),
						('touch', 0, ''),
						('trigger_error', 1, 'triggered errors (warnings) are redirected to session admin''s log'),
						('trim', 1, ''),
						('uasort', 1, ''),
						('ucfirst', 1, ''),
						('ucwords', 1, ''),
						('uksort', 1, ''),
						('umask', 0, ''),
						('uniqid', 1, ''),
						('unixtojd', 1, ''),
						('unlink', 0, ''),
						('unpack', 0, ''),
						('unregister_tick_function', 0, ''),
						('unserialize', 1, ''),
						('urldecode', 1, ''),
						('urlencode', 1, ''),
						('user_error', 1, ''),
						('use_soap_error_handler', 0, ''),
						('usleep', 0, ''),
						('usort', 1, ''),
						('utf8_decode', 1, ''),
						('utf8_encode', 1, ''),
						('variant_abs', 1, ''),
						('variant_add', 1, ''),
						('variant_and', 1, ''),
						('variant_cast', 1, ''),
						('variant_cat', 1, ''),
						('variant_cmp', 1, ''),
						('variant_date_from_timestamp', 1, ''),
						('variant_date_to_timestamp', 1, ''),
						('variant_div', 1, ''),
						('variant_eqv', 1, ''),
						('variant_fix', 1, ''),
						('variant_get_type', 1, ''),
						('variant_idiv', 1, ''),
						('variant_imp', 1, ''),
						('variant_int', 1, ''),
						('variant_mod', 1, ''),
						('variant_mul', 1, ''),
						('variant_neg', 1, ''),
						('variant_not', 1, ''),
						('variant_or', 1, ''),
						('variant_pow', 1, ''),
						('variant_round', 1, ''),
						('variant_set', 1, ''),
						('variant_set_type', 1, ''),
						('variant_sub', 1, ''),
						('variant_xor', 1, ''),
						('var_dump', 0, ''),
						('var_export', 1, ''),
						('version_compare', 1, ''),
						('vfprintf', 0, ''),
						('virtual', 0, ''),
						('vprintf', 1, ''),
						('vsprintf', 1, ''),
						('wddx_add_vars', 0, ''),
						('wddx_deserialize', 0, ''),
						('wddx_packet_end', 0, ''),
						('wddx_packet_start', 0, ''),
						('wddx_serialize_value', 0, ''),
						('wddx_serialize_vars', 0, ''),
						('wordwrap', 1, ''),
						('xmlrpc_decode', 0, ''),
						('xmlrpc_decode_request', 0, ''),
						('xmlrpc_encode', 0, ''),
						('xmlrpc_encode_request', 0, ''),
						('xmlrpc_get_type', 0, ''),
						('xmlrpc_is_fault', 0, ''),
						('xmlrpc_parse_method_descriptions', 0, ''),
						('xmlrpc_server_add_introspection_data', 0, ''),
						('xmlrpc_server_call_method', 0, ''),
						('xmlrpc_server_create', 0, ''),
						('xmlrpc_server_destroy', 0, ''),
						('xmlrpc_server_register_introspection_callback', 0, ''),
						('xmlrpc_server_register_method', 0, ''),
						('xmlrpc_set_type', 0, ''),
						('xmlwriter_end_attribute', 0, ''),
						('xmlwriter_end_cdata', 0, ''),
						('xmlwriter_end_comment', 0, ''),
						('xmlwriter_end_document', 0, ''),
						('xmlwriter_end_dtd', 0, ''),
						('xmlwriter_end_dtd_attlist', 0, ''),
						('xmlwriter_end_dtd_element', 0, ''),
						('xmlwriter_end_dtd_entity', 0, ''),
						('xmlwriter_end_element', 0, ''),
						('xmlwriter_end_pi', 0, ''),
						('xmlwriter_flush', 0, ''),
						('xmlwriter_full_end_element', 0, ''),
						('xmlwriter_open_memory', 0, ''),
						('xmlwriter_open_uri', 0, ''),
						('xmlwriter_output_memory', 0, ''),
						('xmlwriter_set_indent', 0, ''),
						('xmlwriter_set_indent_string', 0, ''),
						('xmlwriter_start_attribute', 0, ''),
						('xmlwriter_start_attribute_ns', 0, ''),
						('xmlwriter_start_cdata', 0, ''),
						('xmlwriter_start_comment', 0, ''),
						('xmlwriter_start_document', 0, ''),
						('xmlwriter_start_dtd', 0, ''),
						('xmlwriter_start_dtd_attlist', 0, ''),
						('xmlwriter_start_dtd_element', 0, ''),
						('xmlwriter_start_dtd_entity', 0, ''),
						('xmlwriter_start_element', 0, ''),
						('xmlwriter_start_element_ns', 0, ''),
						('xmlwriter_start_pi', 0, ''),
						('xmlwriter_text', 0, ''),
						('xmlwriter_write_attribute', 0, ''),
						('xmlwriter_write_attribute_ns', 0, ''),
						('xmlwriter_write_cdata', 0, ''),
						('xmlwriter_write_comment', 0, ''),
						('xmlwriter_write_dtd', 0, ''),
						('xmlwriter_write_dtd_attlist', 0, ''),
						('xmlwriter_write_dtd_element', 0, ''),
						('xmlwriter_write_dtd_entity', 0, ''),
						('xmlwriter_write_element', 0, ''),
						('xmlwriter_write_element_ns', 0, ''),
						('xmlwriter_write_pi', 0, ''),
						('xmlwriter_write_raw', 0, ''),
						('xml_error_string', 0, ''),
						('xml_get_current_byte_index', 0, ''),
						('xml_get_current_column_number', 0, ''),
						('xml_get_current_line_number', 0, ''),
						('xml_get_error_code', 0, ''),
						('xml_parse', 0, ''),
						('xml_parser_create', 0, ''),
						('xml_parser_create_ns', 0, ''),
						('xml_parser_free', 0, ''),
						('xml_parser_get_option', 0, ''),
						('xml_parser_set_option', 0, ''),
						('xml_parse_into_struct', 0, ''),
						('xml_set_character_data_handler', 0, ''),
						('xml_set_default_handler', 0, ''),
						('xml_set_element_handler', 0, ''),
						('xml_set_end_namespace_decl_handler', 0, ''),
						('xml_set_external_entity_ref_handler', 0, ''),
						('xml_set_notation_decl_handler', 0, ''),
						('xml_set_object', 0, ''),
						('xml_set_processing_instruction_handler', 0, ''),
						('xml_set_start_namespace_decl_handler', 0, ''),
						('xml_set_unparsed_entity_decl_handler', 0, ''),
						('zend_logo_guid', 0, ''),
						('zend_version', 0, ''),
						('zip_close', 0, ''),
						('zip_entry_close', 0, ''),
						('zip_entry_compressedsize', 0, ''),
						('zip_entry_compressionmethod', 0, ''),
						('zip_entry_filesize', 0, ''),
						('zip_entry_name', 0, ''),
						('zip_entry_open', 0, ''),
						('zip_entry_read', 0, ''),
						('zip_open', 0, ''),
						('zip_read', 0, ''),
						('zlib_get_coding_type', 0, ''),
						('_', 0, '');";

		return $updates;
	}


	public function getUpdatesToVersion5()
	{
		$updates = array();
		$updates[] = "ALTER TABLE sophie_treatment MODIFY COLUMN loggingEnabled tinyint(1) default 1;";
		return $updates;
	}


	public function getUpdatesToVersion4()
	{
		$updates = array();
		$updates[] = "ALTER IGNORE TABLE sophie_treatment ADD COLUMN loggingEnabled bit(1) default 1";
		return $updates;
	}

	/**
	* SoPHIE Updates to Version 3
	*/
	public function getUpdatesToVersion3()
	{
		$updates   = array();

		$updates[] = "CREATE TABLE IF NOT EXISTS `system_db_version_log` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `microtime` double NOT NULL,
					  `version` int(11) NOT NULL,
					  `statement` longtext COLLATE utf8_unicode_ci NOT NULL,
					  `status` text COLLATE utf8_unicode_ci NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6";

		$updates[] = "ALTER TABLE system_db_version ADD PRIMARY KEY(version)";

		$updates[] = "CREATE TABLE IF NOT EXISTS `sophie_treatment_step_log` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `stepId` int(11) unsigned NOT NULL,
					  `microtime` double NOT NULL,
					  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
					  `type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'notice',
					  PRIMARY KEY (`id`),
					  KEY `sessionId` (`stepId`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";

		return $updates;
	}
}