-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 12, 2019 at 04:01 PM
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
  `address_key` varchar(100) DEFAULT NULL,
  `phone_number` varchar(50) NOT NULL,
  `image_url` varchar(50) DEFAULT NULL,
  `business_permit_flg` tinyint(4) NOT NULL DEFAULT '0',
  `display_flg` tinyint(4) NOT NULL DEFAULT '0',
  `api_token` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `email`, `password`, `status`, `name`, `description`, `short_description`, `representative_name`, `address`, `address_key`, `phone_number`, `image_url`, `business_permit_flg`, `display_flg`, `api_token`, `created`, `modified`) VALUES
(1, 'fdc.gon+agency1@gmail.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Agency One Edit e', 'short edit eee is a test description Edtdfdf', 'Edited descritpon Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Juan', 'Cebu City phf', NULL, '09876423000eee', '1_profile.jpg', 1, 1, '', '2018-12-24 09:57:38', '2019-02-11 15:49:05'),
(4, 'fdc.gon+agency2@gmail.com', '99fbb8b890506f686aa62915e90bba06a2bd07d0', 1, 'Number 2 Agency', 'This is a test description Edtdfdf', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation', 'Sample', 'Cebu city', NULL, '09876423', '', 0, 1, '', '2019-01-05 15:33:46', '2019-01-05 15:33:46');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cebu_municipalities`
--

CREATE TABLE IF NOT EXISTS `cebu_municipalities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `search_key` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cebu_municipalities`
--

INSERT INTO `cebu_municipalities` (`id`, `name`, `search_key`, `status`, `created`, `modified`) VALUES
(1, 'Cebu City', 'cebu_city', 0, '2019-02-12 14:17:14', '2019-02-12 14:17:14'),
(2, 'Lapu-lapu City', 'lapu_lapu_city', 0, '2019-02-12 14:19:01', '2019-02-12 14:19:01'),
(3, 'Mandaue City', 'mandaue_city', 0, '2019-02-12 14:20:36', '2019-02-12 14:20:36'),
(4, 'Talisay', 'talisay', 0, '2019-02-12 14:20:54', '2019-02-12 14:20:54'),
(5, 'Cebu Province', 'cebu_province', 0, '2019-02-12 14:21:34', '2019-02-12 14:21:34'),
(6, 'Consolacion', 'consolacion', 0, '2019-02-12 14:22:01', '2019-02-12 14:22:01'),
(7, 'Minglanilla', 'minglanilla', 0, '2019-02-12 14:22:53', '2019-02-12 14:22:53');

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
  `marital_status` tinyint(4) DEFAULT '0',
  `years_experience` int(11) DEFAULT '0',
  `birthdate` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `address` varchar(100) DEFAULT NULL,
  `address_key` varchar(100) DEFAULT NULL,
  `phone_number` varchar(100) NOT NULL,
  `image_url` varchar(100) NOT NULL,
  `skills` varchar(100) DEFAULT NULL,
  `jobs_experience` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nurse_maids`
--

INSERT INTO `nurse_maids` (`id`, `agency_id`, `first_name`, `middle_name`, `last_lname`, `self_introduction`, `gender`, `marital_status`, `years_experience`, `birthdate`, `status`, `address`, `address_key`, `phone_number`, `image_url`, `skills`, `jobs_experience`, `created`, `modified`) VALUES
(1, 1, 'Inday', 'day', 'Daday', 'Ako desu Ako desu Ako desu Ako desu Ako desu Ako desu  eeee', 0, 3, 3, '2013-02-01', 1, 'Cebu City', 'cebu_city', '11111', '', 'A,Caring,Loving', 'A,Jobs,Testjobs', '2019-02-11 16:37:47', '2019-02-12 14:40:41'),
(2, 4, 'adsf', 'adsf', 'adsf', 'adfdasfaf', 0, 1, 1, '1994-02-02', 1, 'Mandaue City', 'mandaue_city', '232323', '', 'A,Aaaa,Bbb', 'A,Ccc,Dddd', '2019-02-12 16:38:03', '2019-02-12 14:40:30'),
(3, 1, 'Dodong', 'Dong', 'Dongskie', 'Self Introduction Self Introduction', 1, 0, 2, '2009-02-06', 1, 'Lapu-lapu City', 'lapu_lapu_city', '2323232', '', 'Caring,Loving', '', '2019-02-12 14:44:11', '2019-02-12 14:44:11'),
(4, 1, 'Asdfd', 'Asdfd', 'Asdfd', 'df sdf dsfd', 1, 0, 2, '2009-02-05', 1, 'Cebu Province', 'cebu_province', '334334', '', 'Sdfd', 'Sdfdf', '2019-02-12 14:45:13', '2019-02-12 14:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `nurse_maid_ratings`
--

CREATE TABLE IF NOT EXISTS `nurse_maid_ratings` (
  `id` int(11) NOT NULL,
  `nurse_maid_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL DEFAULT '5',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `comment` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL,
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
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `agency_id`, `nurse_maid_id`, `user_id`, `comment`, `user_phone_number`, `user_address`, `transaction_start`, `transaction_end`, `status`, `type`, `created`, `modified`) VALUES
(2, 1, 1, 2, 'Test Hire', '1121212', 'Cebu City', '2019-02-12 14:01:00', '2019-02-12 14:01:00', 0, 0, '2019-02-12 15:03:27', '2019-02-12 15:03:27'),
(3, 1, 1, 2, 'test hire', '1212', 'Cebu City', '2019-02-12 14:11:00', '2019-02-12 14:11:00', 0, 0, '2019-02-12 15:11:44', '2019-02-12 15:11:44'),
(4, 1, 1, 2, 'Test hire', '12121', 'Cebu city', '2019-02-12 14:16:00', '2019-02-12 14:16:00', 1, 0, '2019-02-12 15:16:22', '2019-02-12 18:48:20'),
(14, 1, 1, 1, 'testeet', '1212', 'Cebu City', '2019-02-12 14:52:00', '2019-02-12 14:52:00', 1, 0, '2019-02-12 15:53:03', '2019-02-12 18:44:01'),
(15, 1, 2, 27, 'Test hire comment.', '09876544567', 'Laps2x', '2019-02-12 21:21:00', '2019-02-14 21:21:00', 1, 0, '2019-02-12 13:22:52', '2019-02-12 13:26:05'),
(16, 1, 2, 27, 'testete', '32323', 'dfdf', '2019-02-12 21:31:00', '2019-02-12 21:31:00', 0, 0, '2019-02-12 13:31:11', '2019-02-12 13:31:11');

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
  `address` varchar(100) DEFAULT NULL,
  `address_key` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `api_token` varchar(100) DEFAULT NULL,
  `city` int(11) NOT NULL,
  `municipality` int(11) NOT NULL,
  `brangay` varchar(100) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `house_number` varchar(15) DEFAULT NULL,
  `valid_id_url` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `status`, `display_name`, `fname`, `lname`, `gender`, `phone_number`, `image_url`, `address`, `address_key`, `birthdate`, `api_token`, `city`, `municipality`, `brangay`, `street`, `house_number`, `valid_id_url`, `created`, `modified`) VALUES
(1, 'fdc.gon+user2@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Nancy Binaygggg', NULL, 'Nancy gg', '0', '099999', NULL, 'Cebu City', 'cebu_city', '2010-02-04', 'testapitoken', 0, 0, '', NULL, NULL, NULL, '2018-12-24 10:17:02', '2019-02-10 12:27:52'),
(2, 'fdc.gon+user3@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'uiyuiui', NULL, NULL, '1', '099999', NULL, 'Cebu City', 'cebu_city', '2010-02-04', NULL, 0, 0, '', NULL, NULL, NULL, '2019-01-15 15:26:20', '2019-01-15 15:26:20'),
(27, 'fdc.gon+user1@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'Userone', 'Userfirst', 'Lastname', '0', '0987654321', '27_profile.jpg', 'Cebu City', 'cebu_city', '2010-02-04', NULL, 0, 0, '', NULL, NULL, '27_1549975822Userone.jpg', '2019-02-12 12:50:01', '2019-02-12 12:55:38'),
(28, 'fdc.gon+user4@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'test', 'teset', 'test', '0', '232442242', NULL, 'Cebu City', 'cebu_city', '1999-02-18', NULL, 0, 0, '', NULL, NULL, NULL, '2019-02-12 14:29:29', '2019-02-12 14:29:29'),
(29, 'fdc.gon+5@gmail.com', 'fcf401f9fe8f4b4db40acf88b387064afb127388', 0, 'test', 'teset', 'test', '1', '0987654321', NULL, 'Cebu Province', 'cebu_province', '2019-02-01', NULL, 0, 0, '', NULL, NULL, NULL, '2019-02-12 14:34:52', '2019-02-12 14:34:52');

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
-- Indexes for table `cebu_municipalities`
--
ALTER TABLE `cebu_municipalities`
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
-- Indexes for table `nurse_maid_ratings`
--
ALTER TABLE `nurse_maid_ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `agency_legal_documents`
--
ALTER TABLE `agency_legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cebu_municipalities`
--
ALTER TABLE `cebu_municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `hire_requests`
--
ALTER TABLE `hire_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `nurse_maids`
--
ALTER TABLE `nurse_maids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `nurse_maid_ratings`
--
ALTER TABLE `nurse_maid_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
