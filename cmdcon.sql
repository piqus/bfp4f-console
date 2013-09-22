-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 18, 2013 at 11:02 AM
-- Server version: 5.1.70-community
-- PHP Version: 5.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cmdcon`
--

-- --------------------------------------------------------

--
-- Table structure for table `cc_admins`
--

CREATE TABLE IF NOT EXISTS `cc_admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `soldier_name` varchar(50) NOT NULL COMMENT 'Must be exact with game soldier_name',
  `profile_id` bigint(20) NOT NULL,
  `soldier_id` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `level` smallint(6) NOT NULL DEFAULT '50',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cc_admins`
--

INSERT INTO `cc_admins` (`admin_id`, `soldier_name`, `profile_id`, `soldier_id`, `active`, `level`) VALUES
(1, 'piqus.pl', 2627733530, 609452444, 1, 50);

-- --------------------------------------------------------

--
-- Table structure for table `cc_chat_log`
--

CREATE TABLE IF NOT EXISTS `cc_chat_log` (
  `chat_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `origin` varchar(50) NOT NULL COMMENT 'SoldierName (max: 16 chars)',
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`chat_log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cc_chat_log`
--

INSERT INTO `cc_chat_log` (`chat_log_id`, `origin`, `type`, `message`, `datetime`) VALUES
(1, 'piqus.pl', 'Global', '', '2013-09-07 23:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `cc_commands`
--

CREATE TABLE IF NOT EXISTS `cc_commands` (
  `command_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `class` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `count_params` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`command_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `cc_commands`
--

INSERT INTO `cc_commands` (`command_id`, `name`, `class`, `method`, `count_params`, `active`) VALUES
(1, 'Send Global Message', 'CommonCommands', 'sendMsg', 1, 1),
(2, 'Check Ping', 'CommonCommands', 'showPing', 0, 1),
(3, 'Report Issue/Player', 'CommonCommands', 'report', 1, 1),
(8, 'Kick Player', 'CommonCommands', 'kickPlayer', 1, 1),
(9, 'Send Private Message', 'CommonCommands', 'sendPM', 1, 1),
(10, 'Switch Autobalance', 'CommonCommands', 'switchAutobalance', 1, 1),
(11, 'Restart Round', 'CommonCommands', 'restart', 0, 1),
(12, 'Warn Player', 'CommonCommands', 'warnPlayer', 1, 1),
(13, 'Ban Player (custom time, panel ban)', 'CommonCommands', 'timeKickPlayer', 2, 1),
(14, 'Change Map', 'CommonCommands', 'changeMap', 2, 1),
(15, 'Pause (unranked only)', 'CommonCommands', 'pause', 0, 1),
(16, 'Unpause (unranked only)', 'CommonCommands', 'unpause', 0, 1),
(17, 'Toggle Pause (unranked only)', 'CommonCommands', 'togglePause', 0, 1),
(18, 'Ban player (modmanager bans)', 'CommonCommands', 'banPlayer', 2, 1),
(19, 'List Admins (online, all)', 'CommonCommands', 'listAdmins', 0, 1),
(20, 'Search for Admin', 'CommonCommands', 'searchAdmin', 0, 1),
(21, 'List Available Commands', 'CommonCommands', 'listAvailableCommands', 0, 1),
(22, 'Next Map (Skip)', 'CommonCommands', 'nextMap', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cc_errors`
--

CREATE TABLE IF NOT EXISTS `cc_errors` (
  `error_id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `context` text NOT NULL,
  PRIMARY KEY (`error_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cc_kicked_players`
--

CREATE TABLE IF NOT EXISTS `cc_kicked_players` (
  `kick_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `soldier_name` varchar(50) NOT NULL,
  `profile_id` bigint(20) NOT NULL,
  `soldier_id` bigint(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(100) NOT NULL,
  `expiration_date` datetime NOT NULL DEFAULT '9999-12-31 23:59:59',
  PRIMARY KEY (`kick_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cc_kicked_players`
--

INSERT INTO `cc_kicked_players` (`kick_id`, `soldier_name`, `profile_id`, `soldier_id`, `date_created`, `reason`, `expiration_date`) VALUES
(1, 'piqus.pl', 2627733530, 609452444, '2013-09-18 17:04:56', 'brzydal', '2013-10-08 17:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `cc_reports`
--

CREATE TABLE IF NOT EXISTS `cc_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter` varchar(50) NOT NULL,
  `suspect` varchar(50) DEFAULT NULL,
  `issue` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `r_profile_id` bigint(20) NOT NULL COMMENT 'reporting player profile_id',
  `r_soldier_id` bigint(20) NOT NULL COMMENT 'reporting player soldier id',
  `s_profile_id` bigint(20) DEFAULT NULL COMMENT 'suspect player profile id',
  `s_soldier_id` bigint(20) DEFAULT NULL COMMENT 'suspect player soldier id',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `cc_reports`
--

INSERT INTO `cc_reports` (`report_id`, `reporter`, `suspect`, `issue`, `date_created`, `r_profile_id`, `r_soldier_id`, `s_profile_id`, `s_soldier_id`, `active`) VALUES
(10, 'piqus.pl', NULL, '', '2013-09-08 19:17:46', 2627733530, 609452444, NULL, NULL, 1),
(11, 'piqus.pl', NULL, 'nooooooooob ', '2013-09-08 19:20:58', 2627733530, 609452444, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cc_tick_events`
--

CREATE TABLE IF NOT EXISTS `cc_tick_events` (
  `tick_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticks_elapsed` smallint(6) NOT NULL DEFAULT '900',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `class` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `params` text COMMENT 'encoded using JSON',
  PRIMARY KEY (`tick_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cc_user_commands`
--

CREATE TABLE IF NOT EXISTS `cc_user_commands` (
  `user_command_id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL,
  `command_id` int(11) NOT NULL,
  `args` varchar(500) NOT NULL COMMENT 'encoded in json',
  `required_level` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_command_id`),
  KEY `command_id` (`command_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cc_user_commands`
--

INSERT INTO `cc_user_commands` (`user_command_id`, `alias`, `command_id`, `args`, `required_level`, `date_created`, `enabled`) VALUES
(1, 'test', 22, '[]', 0, '2013-09-07 21:26:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cc_vote`
--

CREATE TABLE IF NOT EXISTS `cc_vote` (
  `votekick_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `suspect` varchar(50) NOT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `accuser` varchar(50) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `votes` int(11) NOT NULL,
  PRIMARY KEY (`votekick_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cc_user_commands`
--
ALTER TABLE `cc_user_commands`
  ADD CONSTRAINT `cc_user_commands_ibfk_1` FOREIGN KEY (`command_id`) REFERENCES `cc_commands` (`command_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
