-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 29, 2019 at 03:40 PM
-- Server version: 5.6.37
-- PHP Version: 7.1.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ayahph_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

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
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `description` text,
  `short_description` text,
  `representative_name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `image_url` varchar(50) NOT NULL,
  `business_permit_flg` tinyint(4) NOT NULL DEFAULT '0',
  `display_flg` tinyint(4) NOT NULL DEFAULT '0',
  `api_token` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `email`, `password`, `status`, `name`, `description`, `short_description`, `representative_name`, `address`, `phone_number`, `image_url`, `business_permit_flg`, `display_flg`, `api_token`, `created`, `modified`) VALUES
(1, 'agency1@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Agency One Edit e', 'short edit eee is a test description Edtdfdf', 'Edited descritpon Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Juan', 'Cebu City phf', '09876423000eee', '', 1, 1, '', '2018-12-24 09:57:38', '2019-01-23 14:48:04'),
(4, 'agency2@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Number 2 Agency', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Sample', 'Cebu city', '09876423', '', 0, 1, '', '2019-01-05 15:33:46', '2019-01-05 15:33:46'),
(5, 'agency3@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'agency number twe', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Test', 'Cebu city', '09876423', '', 0, 1, '', '2019-01-05 15:45:38', '2019-01-05 15:45:38'),
(6, 'agency4@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 0, 'testtest', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'tset se t', 'testtest ', '009090909090', '', 0, 0, '', '2019-01-05 15:48:44', '2019-01-17 15:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `agency_legal_documents`
--

CREATE TABLE IF NOT EXISTS `agency_legal_documents` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hire_requests`
--

CREATE TABLE IF NOT EXISTS `hire_requests` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `nurse_maid_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `user_phone_number` varchar(255) NOT NULL,
  `user_address` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nurse_maids`
--

CREATE TABLE IF NOT EXISTS `nurse_maids` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_lname` varchar(100) DEFAULT NULL,
  `self_introduction` text,
  `gender` tinyint(4) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `address` varchar(100) DEFAULT NULL,
  `phone_number` varchar(100) NOT NULL,
  `image_url` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nurse_maids`
--

INSERT INTO `nurse_maids` (`id`, `agency_id`, `first_name`, `middle_name`, `last_lname`, `self_introduction`, `gender`, `birthdate`, `status`, `address`, `phone_number`, `image_url`, `created`, `modified`) VALUES
(1, 1, 'asdf', NULL, 'asdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2010-01-05', 1, 'Cebu City', '', '', '2019-01-05 17:43:36', '2019-01-05 17:43:36'),
(6, 1, 'nursemaid one', NULL, 'sdfjs', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '1994-01-05', 1, 'Mandaue City', '', '', '2019-01-05 18:04:00', '2019-01-05 18:04:00'),
(7, 1, 'asdff', NULL, 'sfdsf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 0, '1990-01-06', 1, 'Talisay City', '', '', '2019-01-06 06:57:29', '2019-01-06 06:57:29'),
(8, 7, 'test nurse', NULL, 'etst', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2000-01-06', 1, 'Lapu-lapu City', '', '', '2019-01-06 07:03:31', '2019-01-06 07:03:31'),
(9, 1, 'Nurse one', 'midle', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2010-01-07', 1, 'Cebu City', '', '', '2019-01-07 15:34:16', '2019-01-07 15:34:16'),
(10, 1, 'Nurse two', 'middel two', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 0, '2010-12-10', 1, 'Cebu City', '', '', '2019-01-07 15:35:04', '2019-01-07 15:35:04'),
(16, 14, 'dfd', 'dfdf', 'sdfdsf', 'sdfdsf', 1, '2009-01-01', 1, 'asdfbb b', '', '', '2019-01-27 14:41:46', '2019-01-27 14:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL,
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
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `display_name` varchar(100) NOT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `gender` varchar(1) NOT NULL DEFAULT '1',
  `phone_number` varchar(100) NOT NULL,
  `image_url` varchar(100) DEFAULT NULL,
  `api_token` varchar(100) DEFAULT NULL,
  `city` int(11) NOT NULL,
  `municipality` int(11) NOT NULL,
  `brangay` varchar(100) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `house_number` varchar(15) DEFAULT NULL,
  `valid_id_url` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `status`, `display_name`, `fname`, `lname`, `gender`, `phone_number`, `image_url`, `api_token`, `city`, `municipality`, `brangay`, `street`, `house_number`, `valid_id_url`, `created`, `modified`) VALUES
(1, 'user1@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Nancy Binaygggg', NULL, 'Nancy gg', '0', '099999', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2018-12-24 10:17:02', '2019-01-17 15:26:53'),
(2, 'user2@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'uiyuiui', NULL, NULL, '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:26:20', '2019-01-15 15:26:20'),
(14, 'user10@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Tin2x', NULL, NULL, '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-29 14:40:29', '2019-01-29 15:04:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agency_legal_documents`
--
ALTER TABLE `agency_legal_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hire_requests`
--
ALTER TABLE `hire_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nurse_maids`
--
ALTER TABLE `nurse_maids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `gender` (`gender`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`,`api_token`),
  ADD KEY `api_token` (`api_token`),
  ADD KEY `city` (`city`,`municipality`,`brangay`),
  ADD KEY `brangay` (`brangay`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `agency_legal_documents`
--
ALTER TABLE `agency_legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `hire_requests`
--
ALTER TABLE `hire_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `nurse_maids`
--
ALTER TABLE `nurse_maids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
