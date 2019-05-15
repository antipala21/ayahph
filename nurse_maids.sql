-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 15, 2019 at 04:07 PM
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
  `education` text,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nurse_maids`
--

INSERT INTO `nurse_maids` (`id`, `agency_id`, `first_name`, `middle_name`, `last_lname`, `self_introduction`, `gender`, `marital_status`, `years_experience`, `birthdate`, `status`, `address`, `address_key`, `phone_number`, `image_url`, `skills`, `jobs_experience`, `education`, `created`, `modified`) VALUES
(1, 1, 'Indaw', 'Day', 'Badaday', 'Test sfsd sf d ', 1, 0, 2, '1992-02-01', 1, 'Cebu City', 'cebu_city', '34234234', '1_nursemaid_profile.jpg', 'Dfs', 'Dfdf', '2', '2019-02-16 09:00:46', '2019-02-16 09:01:30'),
(2, 1, 'Dodong', 'done', 'Dodong', 'sdfsafadfdaf', 1, 2, 3, '2009-02-06', 0, 'Cebu City', 'cebu_city', '324324', '', 'Sdf,Dfdf', 'Dfs,Dsf', NULL, '2019-02-16 09:07:27', '2019-05-15 14:48:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nurse_maids`
--
ALTER TABLE `nurse_maids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `gender` (`gender`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nurse_maids`
--
ALTER TABLE `nurse_maids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
