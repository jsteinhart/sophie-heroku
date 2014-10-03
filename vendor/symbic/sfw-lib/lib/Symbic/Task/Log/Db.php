<?php

/*

CREATE TABLE IF NOT EXISTS `symbic_task_log` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`jobId` int(11) unsigned DEFAULT NULL,
	`date` double unsigned NOT NULL COMMENT 'microtime',
	`content` text NOT NULL,
	`type` enum('message','notice','warning','error','exception') NOT NULL DEFAULT 'message',
	PRIMARY KEY (`id`),
	KEY `jobId` (`jobId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `symbic_task_log`
	ADD CONSTRAINT `symbic_task_log_ibfk_symbic_task_job_id` FOREIGN KEY (`jobId`) REFERENCES `symbic_task_job` (`id`) ON UPDATE CASCADE;

*/

class Symbic_Task_Log_Db extends Symbic_Db_Table_Existence
{
	// CONFIG
	public $_name = 'symbic_task_log';
	public $_primary = 'id';
}