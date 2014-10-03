<?php

/*

CREATE TABLE IF NOT EXISTS `symbic_mail_queue` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`contextJson` text,
	`mailData` longblob NOT NULL,
	`creationDate` datetime NOT NULL,
	`transportStartDate` datetime DEFAULT NULL,
	`transportFinishDate` datetime DEFAULT NULL,
	`failureCount` int(10) unsigned NOT NULL DEFAULT '0',
	`deleteWhenSent` tinyint(1) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/

class Symbic_Mail_Queue_Db extends Symbic_Db_Table_Abstract
{
	// CONFIG
	public $_name = 'symbic_mail_queue';
	public $_primary = 'id';
}