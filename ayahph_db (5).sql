-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 24, 2019 at 02:22 PM
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
  `business_permit_url` varchar(100) DEFAULT NULL,
  `display_flg` tinyint(4) NOT NULL DEFAULT '0',
  `api_token` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `email`, `password`, `status`, `name`, `description`, `short_description`, `representative_name`, `address`, `phone_number`, `image_url`, `business_permit_url`, `display_flg`, `api_token`, `created`, `modified`) VALUES
(1, 'agency1@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Agency One Edit e', 'short edit eee is a test description Edtdfdf', 'Edited descritpon Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Juan', 'Cebu City phf', '09876423000eee', '', '1_business_permit_.jpg', 1, '', '2018-12-24 09:57:38', '2019-01-23 14:48:04'),
(2, '', '', 1, 'Agency One', 'This is a test description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', '', 'Cebu City', '09876423', '', NULL, 1, '', '2019-01-03 14:58:16', '2019-01-17 15:36:00'),
(3, '', '', 1, 'Agency One Edit', 'This is a test description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', '', 'Cebu City', '09876423', '', NULL, 1, '', '2019-01-03 14:58:44', '2019-01-17 15:28:59'),
(4, 'agency2@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Number 2 Agency', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Sample', 'Cebu city', '09876423', '', NULL, 1, '', '2019-01-05 15:33:46', '2019-01-05 15:33:46'),
(5, 'agency3@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'agency number twe', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Test', 'Cebu city', '09876423', '', NULL, 1, '', '2019-01-05 15:45:38', '2019-01-05 15:45:38'),
(6, 'agency4@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 0, 'testtest', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'tset se t', 'testtest ', '009090909090', '', NULL, 0, '', '2019-01-05 15:48:44', '2019-01-17 15:35:03'),
(7, 'aaa@aaa.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'aaa', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'aaa', 'aaa', '', '', NULL, 1, '', '2019-01-06 06:59:57', '2019-01-06 06:59:57'),
(8, 'agency@test.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'adsf', 'asdfdf', NULL, 'adsfda', 'adf', 'adsfdfaa', '', NULL, 1, '', '2019-01-17 14:39:40', '2019-01-17 15:28:51'),
(9, 'agency1@test.com', '937f930d6576b9d92b3c8c38fef95553520afdc8', 0, 'asdf', NULL, NULL, 'asdf', 'asdf', '', '', NULL, 0, '', '2019-01-23 13:02:34', '2019-01-23 13:02:34'),
(10, 'agency1@test.com', '937f930d6576b9d92b3c8c38fef95553520afdc8', 0, 'asdfasdf', NULL, NULL, 'asdf', 'asdf', '', '', NULL, 0, '', '2019-01-23 13:03:54', '2019-01-23 13:03:54');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `agency_id`, `content`, `status`, `created`, `modified`) VALUES
(1, 1, 'Test Announcement', 0, '2019-01-06 04:57:19', '2019-01-06 04:57:19'),
(2, 8, 'adsfd', 0, '2019-01-17 14:40:53', '2019-01-17 14:40:53'),
(3, 1, 'adsfd', 0, '2019-01-17 15:18:42', '2019-01-17 15:18:42'),
(4, 1, 'brbrbrbrb', 0, '2019-01-17 15:25:17', '2019-01-17 15:25:17'),
(5, 1, 'asdfdfsdfsd sdf', 0, '2019-01-22 13:09:06', '2019-01-22 13:09:06');

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
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nurse_maids`
--

INSERT INTO `nurse_maids` (`id`, `agency_id`, `first_name`, `middle_name`, `last_lname`, `self_introduction`, `gender`, `birthdate`, `status`, `address`, `created`, `modified`) VALUES
(1, 1, 'asdf', NULL, 'asdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2010-01-05', 1, 'Cebu City', '2019-01-05 17:43:36', '2019-01-05 17:43:36'),
(6, 1, 'nursemaid one', NULL, 'sdfjs', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '1994-01-05', 1, 'Mandaue City', '2019-01-05 18:04:00', '2019-01-05 18:04:00'),
(7, 1, 'asdff', NULL, 'sfdsf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 0, '1990-01-06', 1, 'Talisay City', '2019-01-06 06:57:29', '2019-01-06 06:57:29'),
(8, 7, 'test nurse', NULL, 'etst', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2000-01-06', 1, 'Lapu-lapu City', '2019-01-06 07:03:31', '2019-01-06 07:03:31'),
(9, 1, 'Nurse one', 'midle', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 1, '2010-01-07', 1, 'Cebu City', '2019-01-07 15:34:16', '2019-01-07 15:34:16'),
(10, 1, 'Nurse two', 'middel two', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 0, '2010-12-10', 1, 'Cebu City', '2019-01-07 15:35:04', '2019-01-07 15:35:04'),
(11, 8, 'adsf', 'adsf', NULL, NULL, 1, '0000-00-00', 1, 'adfd', '2019-01-17 14:41:07', '2019-01-17 14:41:07'),
(12, 1, 'bbb', 'bbb', NULL, NULL, 1, '0000-00-00', 1, 'asdfbb b', '2019-01-17 15:22:15', '2019-01-17 15:22:15'),
(13, 1, 'ccc', 'ccc', NULL, NULL, 1, '0000-00-00', 1, 'cc', '2019-01-17 15:25:03', '2019-01-17 15:25:03');

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
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `testtete` varchar(100) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `agency_id`, `nurse_maid_id`, `user_id`, `comment`, `user_phone_number`, `user_address`, `status`, `type`, `created`, `modified`, `testtete`) VALUES
(1, 1, 1, 1, 'asdfadsf', '121', '', 0, 0, '2019-01-24 12:31:40', '2019-01-24 12:31:40', ''),
(2, 1, 1, 1, 'asdfad', '233', '', 0, 0, '2019-01-24 12:32:44', '2019-01-24 12:32:44', ''),
(3, 1, 1, 1, 'adsfdas', 'asdfdsa', NULL, 0, 0, '2019-01-24 12:33:35', '2019-01-24 12:33:35', ''),
(4, 1, 1, 1, 'asdfasdf', 'asdfdsf', NULL, 0, 0, '2019-01-24 12:35:55', '2019-01-24 12:35:55', ''),
(5, 1, 1, 1, 'asdf', '332323', NULL, 0, 0, '2019-01-24 12:41:32', '2019-01-24 12:41:32', ''),
(6, 1, 1, 1, 'asdf', 'asdfdsa', NULL, 0, 0, '2019-01-24 12:42:11', '2019-01-24 12:42:11', ''),
(7, 1, 1, 1, 'aa', 'aaaa', NULL, 0, 0, '2019-01-24 12:46:45', '2019-01-24 12:46:45', ''),
(8, 1, 1, 1, 'adf', 'asdf', NULL, 0, 0, '2019-01-24 12:55:54', '2019-01-24 12:55:54', ''),
(9, 1, 1, 1, 'asdf', 'fdsa', NULL, 0, 0, '2019-01-24 12:56:25', '2019-01-24 12:56:25', ''),
(10, 1, 0, 1, 'gg', 'gg', NULL, 0, 0, '2019-01-24 13:00:47', '2019-01-24 13:00:47', ''),
(11, 1, 7, 1, 'tete', 'tete', NULL, 0, 0, '2019-01-24 13:01:33', '2019-01-24 13:01:33', ''),
(12, 1, 1, 1, 'asdfsdf', 'sdfdsf', NULL, 0, 0, '2019-01-24 13:49:59', '2019-01-24 13:49:59', ''),
(13, 1, 1, 1, 'asdf', 'asdfdsa', NULL, 0, 0, '2019-01-24 13:52:55', '2019-01-24 13:52:55', ''),
(14, 1, 1, 1, 'asdf', 'asdfdsa', NULL, 0, 0, '2019-01-24 13:56:10', '2019-01-24 13:56:10', ''),
(15, 1, 1, 1, 'asdf', 'asdfdsa', NULL, 0, 0, '2019-01-24 13:57:19', '2019-01-24 13:57:19', '');

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
  `business_permit_url` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `status`, `display_name`, `fname`, `lname`, `gender`, `phone_number`, `image_url`, `api_token`, `city`, `municipality`, `brangay`, `street`, `house_number`, `business_permit_url`, `created`, `modified`) VALUES
(1, 'user1@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Nancy Binaygggg', NULL, 'Nancy gg', '0', '099999', NULL, NULL, 0, 0, '', NULL, NULL, '1_business_permit_Nancy_Binaygg.jpg', '2018-12-24 10:17:02', '2019-01-17 15:26:53'),
(2, 'user2@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'uiyuiui', NULL, NULL, '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:26:20', '2019-01-15 15:26:20'),
(3, 'agency11@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'bmbmn', NULL, NULL, '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:27:16', '2019-01-15 15:27:16'),
(4, 'agency11@test.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'qwer', NULL, NULL, '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:28:15', '2019-01-15 15:28:15'),
(5, 'sasdfdfasdf', '', 0, 'asdf', 'asdfdsf', 'adsfdsf', '1', 'sdfd', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'sdfsdf', 'asdfdf', 0, 'asdfdsf', 'asdfdsa', 'asdfdsf', '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'sdfsdf', 'asdfdf', 0, 'asdfdsf', 'asdfdsa', 'asdfdsf', '1', '', NULL, NULL, 0, 0, '', NULL, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `agency_legal_documents`
--
ALTER TABLE `agency_legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;