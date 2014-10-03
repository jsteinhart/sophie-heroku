SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `sophie_experiment`;
CREATE TABLE IF NOT EXISTS `sophie_experiment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` int(11) unsigned NOT NULL,
  `state` enum('active','archived','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `ownerId` (`ownerId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session`;
CREATE TABLE IF NOT EXISTS `sophie_session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) unsigned NOT NULL,
  `treatmentId` int(11) unsigned NOT NULL,
  `sessiontypeId` int(11) unsigned DEFAULT NULL,
  `state` enum('created','running','paused','finished','archived','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'created',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastAdminProcess` datetime NOT NULL,
  `lastLock` datetime DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `debugConsole` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `treatmentId` (`sessiontypeId`),
  KEY `ownerId` (`ownerId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_group`;
CREATE TABLE IF NOT EXISTS `sophie_session_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(11) unsigned NOT NULL DEFAULT '0',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `groupStructure` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessionId` (`sessionId`,`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_log`;
CREATE TABLE IF NOT EXISTS `sophie_session_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(11) unsigned NOT NULL,
  `microtime` double NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'notice',
  PRIMARY KEY (`id`),
  KEY `sessionId` (`sessionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_parameter`;
CREATE TABLE IF NOT EXISTS `sophie_session_parameter` (
  `sessionId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sessionId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_participant`;
CREATE TABLE IF NOT EXISTS `sophie_session_participant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `number` int(11) NOT NULL,
  `typeLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLoop` int(11) unsigned DEFAULT NULL,
  `stepId` int(11) unsigned DEFAULT NULL,
  `code` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastContact` double DEFAULT NULL,
  `state` enum('new','started','finished','excluded') COLLATE utf8_unicode_ci NOT NULL,
  `httpSession` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`,`sessionId`),
  UNIQUE KEY `code` (`code`),
  KEY `stepId` (`stepId`),
  KEY `sessionId` (`sessionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_participant_group`;
CREATE TABLE IF NOT EXISTS `sophie_session_participant_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(11) unsigned NOT NULL,
  `stepgroupLabel` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stepgroupLoop` int(11) unsigned NOT NULL DEFAULT '0',
  `participantLabel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `groupLabel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessionId_2` (`sessionId`,`stepgroupLabel`,`stepgroupLoop`,`participantLabel`,`groupLabel`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_variable`;
CREATE TABLE IF NOT EXISTS `sophie_session_variable` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `participantLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLabel` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLoop` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_bool` tinyint(1) DEFAULT NULL,
  `value_int` bigint(20) DEFAULT NULL,
  `value_double` double DEFAULT NULL,
  `value_string` longtext COLLATE utf8_unicode_ci,
  `value_serialized` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessionId` (`sessionId`,`groupLabel`,`participantLabel`,`stepgroupLabel`,`stepgroupLoop`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_session_variable_log`;
CREATE TABLE IF NOT EXISTS `sophie_session_variable_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stepId` int(11) unsigned DEFAULT NULL,
  `sessionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `participantLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLabel` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLoop` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_bool` tinyint(1) DEFAULT NULL,
  `value_int` bigint(20) DEFAULT NULL,
  `value_double` double DEFAULT NULL,
  `value_string` longtext COLLATE utf8_unicode_ci,
  `value_serialized` longtext COLLATE utf8_unicode_ci,
  `logTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessionId` (`sessionId`,`groupLabel`,`participantLabel`,`stepgroupLabel`,`stepgroupLoop`,`name`),
  KEY `stepId` (`stepId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_steptype`;
CREATE TABLE IF NOT EXISTS `sophie_steptype` (
  `systemName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `authorEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dependencies` longtext COLLATE utf8_unicode_ci NOT NULL,
  `isAbstract` tinyint(1) NOT NULL DEFAULT '0',
  `isInstalled` tinyint(1) NOT NULL DEFAULT '0',
  `isActive` tinyint(1) unsigned NOT NULL,
  `isBroken` tinyint(1) unsigned NOT NULL,
  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`systemName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_steptype_category`;
CREATE TABLE IF NOT EXISTS `sophie_steptype_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_steptype_version`;
CREATE TABLE IF NOT EXISTS `sophie_steptype_version` (
  `steptypeSystemName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `config` longtext COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `isBroken` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`steptypeSystemName`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment`;
CREATE TABLE IF NOT EXISTS `sophie_treatment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `experimentId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `state` enum('template','draft','used','archiv','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'draft',
  `layout` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `css` text COLLATE utf8_unicode_ci NOT NULL,
  `payoffScript` text COLLATE utf8_unicode_ci NOT NULL,
  `payoffRetrivalMethod` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'payoffVarSum',
  `defaultLocale` varchar(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en_US',
  PRIMARY KEY (`id`),
  KEY `experimentId` (`experimentId`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_group_structure`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_group_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `treatmentId` int(11) unsigned NOT NULL,
  `label` enum('G') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `structureJson` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `treatmentId_2` (`treatmentId`,`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_log`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `treatmentId` int(11) unsigned NOT NULL,
  `microtime` double NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'notice',
  PRIMARY KEY (`id`),
  KEY `sessionId` (`treatmentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_parameter`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_parameter` (
  `treatmentId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`treatmentId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_screen`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_screen` (
  `treatmentId` int(11) unsigned NOT NULL,
  `createdHtml` longtext COLLATE utf8_unicode_ci NOT NULL,
  `finishedHtml` longtext COLLATE utf8_unicode_ci NOT NULL,
  `pausedHtml` longtext COLLATE utf8_unicode_ci NOT NULL,
  `archivedHtml` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`treatmentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_sessiontype`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_sessiontype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `treatmentId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `style` enum('static') COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) unsigned NOT NULL,
  `groupDefinitionJson` longtext COLLATE utf8_unicode_ci NOT NULL,
  `variableDefinition` longtext COLLATE utf8_unicode_ci NOT NULL,
  `state` enum('active','archived','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `treatmentId` (`treatmentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_sessiontype_parameter`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_sessiontype_parameter` (
  `sessiontypeId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sessiontypeId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_sessiontype_variable`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_sessiontype_variable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessiontypeId` int(11) unsigned NOT NULL,
  `groupLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `participantLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLabel` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLoop` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_bool` tinyint(1) DEFAULT NULL,
  `value_int` bigint(20) DEFAULT NULL,
  `value_double` double DEFAULT NULL,
  `value_string` longtext COLLATE utf8_unicode_ci,
  `value_serialized` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `treatmentId` (`sessiontypeId`,`groupLabel`,`participantLabel`,`stepgroupLabel`,`stepgroupLoop`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_step`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_step` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stepgroupId` int(11) unsigned NOT NULL,
  `position` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `steptypeSystemName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `position` (`stepgroupId`,`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_stepgroup`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_stepgroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `treatmentId` int(11) unsigned NOT NULL,
  `label` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) DEFAULT NULL,
  `loop` int(11) unsigned NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `treatmentId_2` (`treatmentId`,`label`),
  UNIQUE KEY `treatmentId` (`treatmentId`,`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_step_eav`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_step_eav` (
  `stepId` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`stepId`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_step_type`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_step_type` (
  `stepId` int(11) unsigned NOT NULL,
  `typeLabel` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`stepId`,`typeLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_type`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_type` (
  `treatmentId` int(11) unsigned NOT NULL,
  `label` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hue` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`treatmentId`,`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sophie_treatment_variable`;
CREATE TABLE IF NOT EXISTS `sophie_treatment_variable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `treatmentId` int(11) unsigned NOT NULL,
  `groupLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `participantLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLabel` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stepgroupLoop` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_bool` tinyint(1) DEFAULT NULL,
  `value_int` bigint(20) DEFAULT NULL,
  `value_double` double DEFAULT NULL,
  `value_string` longtext COLLATE utf8_unicode_ci,
  `value_serialized` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `treatmentId` (`treatmentId`,`groupLabel`,`participantLabel`,`stepgroupLabel`,`stepgroupLoop`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_acl`;
CREATE TABLE IF NOT EXISTS `system_acl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceClass` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `resourceId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `roleClass` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `roleId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rule` enum('allow','deny') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resourceClass` (`resourceClass`,`resourceId`,`roleClass`,`roleId`,`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_db_version`;
CREATE TABLE IF NOT EXISTS `system_db_version` (
  `version` int(11) NOT NULL,
  `lastChange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_log`;
CREATE TABLE IF NOT EXISTS `system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_session`;
CREATE TABLE IF NOT EXISTS `system_session` (
  `session_id` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `save_path` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `session_data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`session_id`,`save_path`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_user`;
CREATE TABLE IF NOT EXISTS `system_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','developer','user') COLLATE utf8_unicode_ci NOT NULL,
  `lastLogin` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_usergroup`;
CREATE TABLE IF NOT EXISTS `system_usergroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `system_user_usergroup`;
CREATE TABLE IF NOT EXISTS `system_user_usergroup` (
  `userId` int(11) unsigned NOT NULL,
  `usergroupId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`userId`,`usergroupId`),
  KEY `usergroupId` (`usergroupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `sophie_experiment`
  ADD CONSTRAINT `sophie_experiment_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `system_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session`
  ADD CONSTRAINT `sophie_session_ibfk_2` FOREIGN KEY (`ownerId`) REFERENCES `system_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sophie_session_ibfk_3` FOREIGN KEY (`sessiontypeId`) REFERENCES `sophie_treatment_sessiontype` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_group`
  ADD CONSTRAINT `sophie_session_group_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_log`
  ADD CONSTRAINT `sophie_session_log_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_parameter`
  ADD CONSTRAINT `sophie_session_parameter_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_participant`
  ADD CONSTRAINT `sophie_session_participant_ibfk_2` FOREIGN KEY (`stepId`) REFERENCES `sophie_treatment_step` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sophie_session_participant_ibfk_3` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_participant_group`
  ADD CONSTRAINT `sophie_session_participant_group_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_variable`
  ADD CONSTRAINT `sophie_session_variable_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_session_variable_log`
  ADD CONSTRAINT `sophie_session_variable_log_ibfk_1` FOREIGN KEY (`sessionId`) REFERENCES `sophie_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sophie_session_variable_log_ibfk_2` FOREIGN KEY (`stepId`) REFERENCES `sophie_treatment_step` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment`
  ADD CONSTRAINT `sophie_treatment_ibfk_1` FOREIGN KEY (`experimentId`) REFERENCES `sophie_experiment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_group_structure`
  ADD CONSTRAINT `sophie_treatment_group_structure_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`);

ALTER TABLE `sophie_treatment_parameter`
  ADD CONSTRAINT `sophie_treatment_parameter_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_screen`
  ADD CONSTRAINT `sophie_treatment_screen_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_sessiontype`
  ADD CONSTRAINT `sophie_treatment_sessiontype_ibfk_3` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`);

ALTER TABLE `sophie_treatment_sessiontype_parameter`
  ADD CONSTRAINT `sophie_treatment_sessiontype_parameter_ibfk_1` FOREIGN KEY (`sessiontypeId`) REFERENCES `sophie_treatment_sessiontype` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_sessiontype_variable`
  ADD CONSTRAINT `sophie_treatment_sessiontype_variable_ibfk_1` FOREIGN KEY (`sessiontypeId`) REFERENCES `sophie_treatment_sessiontype` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_step`
  ADD CONSTRAINT `sophie_treatment_step_ibfk_1` FOREIGN KEY (`stepgroupId`) REFERENCES `sophie_treatment_stepgroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_stepgroup`
  ADD CONSTRAINT `sophie_treatment_stepgroup_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_step_eav`
  ADD CONSTRAINT `sophie_treatment_step_eav_ibfk_1` FOREIGN KEY (`stepId`) REFERENCES `sophie_treatment_step` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_step_type`
  ADD CONSTRAINT `sophie_treatment_step_type_ibfk_1` FOREIGN KEY (`stepId`) REFERENCES `sophie_treatment_step` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sophie_treatment_variable`
  ADD CONSTRAINT `sophie_treatment_variable_ibfk_1` FOREIGN KEY (`treatmentId`) REFERENCES `sophie_treatment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `system_log`
  ADD CONSTRAINT `system_log_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `system_user_usergroup`
  ADD CONSTRAINT `system_user_usergroup_ibfk_2` FOREIGN KEY (`usergroupId`) REFERENCES `system_usergroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `system_user_usergroup_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `system_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
