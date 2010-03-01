-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: vitae
-- Generation Time: Mar 01, 2010 at 08:39 AM
-- Server version: 5.1.31
-- PHP Version: 5.2.6

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;

--
-- Database: `hermes`
--

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `run_id` int(10) unsigned NOT NULL,
  `uniq` text COLLATE utf8_unicode_ci NOT NULL,
  `headers` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `sent` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `run_id` (`run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mail`
--


-- --------------------------------------------------------

--
-- Table structure for table `run`
--

DROP TABLE IF EXISTS `run`;
CREATE TABLE IF NOT EXISTS `run` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `run_key` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `run`
--


-- --------------------------------------------------------

--
-- Table structure for table `run_tag`
--

DROP TABLE IF EXISTS `run_tag`;
CREATE TABLE IF NOT EXISTS `run_tag` (
  `run_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  KEY `run_id` (`run_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `run_tag`
--


-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `tag_key` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `ident` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`id`, `user_id`, `tag_key`, `ident`) VALUES
(1, 1, '1b763f80-17b4-11df-9313-c1e90bfb96df', 'matchmail');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `apikey` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `naam` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `apikey`, `naam`, `created`, `updated`) VALUES
(1, '1b75df50-17b4-11df-b5c1-61856f3a2e36', 'DMM Websolutions BV', '2010-03-01 08:34:21', '2010-03-01 08:34:21');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mail`
--
ALTER TABLE `mail`
  ADD CONSTRAINT `mail_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `run` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `run`
--
ALTER TABLE `run`
  ADD CONSTRAINT `run_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `run_tag`
--
ALTER TABLE `run_tag`
  ADD CONSTRAINT `run_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `run_tag_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `run` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tag`
--
ALTER TABLE `tag`
  ADD CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
