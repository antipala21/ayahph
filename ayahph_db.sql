-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 12, 2019 at 06:52 PM
-- Server version: 5.1.73-log
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ayahph_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(10) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `password`, `created`, `modified`) VALUES
(1, 'admin', '99fbb8b890506f686aa62915e90bba06a2bd07d0', '0000-00-00 00:00:00', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE IF NOT EXISTS `agencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `description` text,
  `short_description` text,
  `representative_name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `image_url` varchar(50) DEFAULT NULL,
  `business_permit_flg` tinyint(4) NOT NULL DEFAULT '0',
  `display_flg` tinyint(4) NOT NULL DEFAULT '0',
  `api_token` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `email`, `password`, `status`, `name`, `description`, `short_description`, `representative_name`, `address`, `phone_number`, `image_url`, `business_permit_flg`, `display_flg`, `api_token`, `created`, `modified`) VALUES
(1, 'antipala.fdc01@gmail.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Agency One Edit e', 'short edit eee is a test description Edtdfdf', 'Edited descritpon Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Juan', 'Cebu City phf', '09876423000eee', '1_profile.jpg', 1, 1, '', '2018-12-24 09:57:38', '2019-02-11 15:49:05'),
(4, 'antipala.fdc01+agency2@gmail.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Number 2 Agency', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Sample', 'Cebu city', '09876423', '', 0, 1, '', '2019-01-05 15:33:46', '2019-01-05 15:33:46');

-- --------------------------------------------------------

--
-- Table structure for table `agency_legal_documents`
--

CREATE TABLE IF NOT EXISTS `agency_legal_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hire_requests`
--

CREATE TABLE IF NOT EXISTS `hire_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `nurse_maid_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `user_phone_number` varchar(255) NOT NULL,
  `user_address` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `nurse_maids`
--

CREATE TABLE IF NOT EXISTS `nurse_maids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_lname` varchar(100) DEFAULT NULL,
  `self_introduction` text,
  `gender` tinyint(4) DEFAULT NULL,
  `marital_status` tinyint(4) DEFAULT '0',
  `years_experience` int(11) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `address` varchar(100) DEFAULT NULL,
  `phone_number` varchar(100) NOT NULL,
  `image_url` varchar(100) NOT NULL,
  `skills` varchar(100) DEFAULT NULL,
  `jobs_experience` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agency_id` (`agency_id`),
  KEY `gender` (`gender`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `nurse_maids`
--

INSERT INTO `nurse_maids` (`id`, `agency_id`, `first_name`, `middle_name`, `last_lname`, `self_introduction`, `gender`, `marital_status`, `years_experience`, `birthdate`, `status`, `address`, `phone_number`, `image_url`, `skills`, `jobs_experience`, `created`, `modified`) VALUES
(1, 1, 'Inday', 'day', 'Daday', 'Ako desu Ako desu Ako desu Ako desu Ako desu Ako desu  eeee', 0, 3, 3, '2013-02-01', 1, 'Cebu City', '11111', '', 'A,Caring,Loving', 'A,Jobs,Testjobs', '2019-02-11 16:37:47', '2019-02-12 17:33:45'),
(2, 1, 'adsf', 'adsf', 'adsf', 'adfdasfaf', 0, 1, 1, '2019-02-02', 1, 'adfadsf', '232323', '', 'A,Aaaa,Bbb', 'A,Ccc,Dddd', '2019-02-12 16:38:03', '2019-02-12 16:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `nurse_maid_ratings`
--

CREATE TABLE IF NOT EXISTS `nurse_maid_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nurse_maid_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL DEFAULT '5',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `comment` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(100) DEFAULT NULL,
  `agency_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `customer_name` varchar(100) DEFAULT NULL,
  `card_no` varchar(100) DEFAULT NULL,
  `card_type` varchar(10) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_id`, `agency_id`, `transaction_date`, `type`, `status`, `customer_name`, `card_no`, `card_type`, `amount`, `created`, `modified`) VALUES
(2, '94yfac9p', 4, '2019-02-11 15:07:04', 'sale', 1, 'Test name', '1210', 'Visa', 500, '2019-02-11 15:07:04', '2019-02-11 15:07:04'),
(3, 'ppx1mdfm', 1, '2019-02-11 15:45:25', 'sale', 1, 'TestPayment', '1210', 'Visa', 500, '2019-02-11 15:45:25', '2019-02-11 15:45:25'),
(4, 'ee1aknns', 1, '2019-02-11 15:49:05', 'sale', 1, 'Juan', '1210', 'Visa', 500, '2019-02-11 15:49:05', '2019-02-11 15:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `nurse_maid_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text,
  `user_phone_number` varchar(15) DEFAULT NULL,
  `user_address` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `transaction_start` datetime NOT NULL,
  `transaction_end` datetime NOT NULL,
  `status` int(4) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `agency_id`, `nurse_maid_id`, `user_id`, `comment`, `user_phone_number`, `user_address`, `transaction_start`, `transaction_end`, `status`, `type`, `created`, `modified`) VALUES
(2, 1, 1, 2, 'Test Hire', '1121212', 'Cebu City', '2019-02-12 14:01:00', '2019-02-12 14:01:00', 0, 0, '2019-02-12 15:03:27', '2019-02-12 15:03:27'),
(3, 1, 1, 2, 'test hire', '1212', 'Cebu City', '2019-02-12 14:11:00', '2019-02-12 14:11:00', 0, 0, '2019-02-12 15:11:44', '2019-02-12 15:11:44'),
(4, 1, 1, 2, 'Test hire', '12121', 'Cebu city', '2019-02-12 14:16:00', '2019-02-12 14:16:00', 1, 0, '2019-02-12 15:16:22', '2019-02-12 18:48:20'),
(14, 1, 1, 1, 'testeet', '1212', 'Cebu City', '2019-02-12 14:52:00', '2019-02-12 14:52:00', 1, 0, '2019-02-12 15:53:03', '2019-02-12 18:44:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `display_name` varchar(100) NOT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `gender` varchar(1) NOT NULL DEFAULT '1',
  `phone_number` varchar(100) NOT NULL,
  `image_url` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `api_token` varchar(100) DEFAULT NULL,
  `city` int(11) NOT NULL,
  `municipality` int(11) NOT NULL,
  `brangay` varchar(100) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `house_number` varchar(15) DEFAULT NULL,
  `valid_id_url` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`,`api_token`),
  KEY `api_token` (`api_token`),
  KEY `city` (`city`,`municipality`,`brangay`),
  KEY `brangay` (`brangay`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `status`, `display_name`, `fname`, `lname`, `gender`, `phone_number`, `image_url`, `address`, `birthdate`, `api_token`, `city`, `municipality`, `brangay`, `street`, `house_number`, `valid_id_url`, `created`, `modified`) VALUES
(1, 'antipala.fdc01+user1@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Nancy Binaygggg', NULL, 'Nancy gg', '0', '099999', NULL, 'test', NULL, 'testapitoken', 0, 0, '', NULL, NULL, NULL, '2018-12-24 10:17:02', '2019-02-10 12:27:52'),
(2, 'antipala.fdc01+user2@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'uiyuiui', NULL, NULL, '1', '099999', NULL, 'Cebu City', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:26:20', '2019-01-15 15:26:20');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
