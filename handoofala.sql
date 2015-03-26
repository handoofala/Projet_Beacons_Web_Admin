-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2015 at 11:31 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `handoofala`
--

-- --------------------------------------------------------

--
-- Table structure for table `beacons`
--

CREATE TABLE IF NOT EXISTS `beacons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `UUID` varchar(64) NOT NULL,
  `id_room` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;

--
-- Dumping data for table `beacons`
--

INSERT INTO `beacons` (`id`, `UUID`, `id_room`) VALUES
(19, 'b1', 6),
(20, 'b2', 6),
(21, 'b3', 6),
(22, 'b4', 6),
(23, 'b5', 6),
(24, 'b6', 6),
(25, 'b7', 6),
(26, 'georges', 5),
(27, 'b12', 8),
(28, 'b13', 8),
(29, 'b14', 8),
(30, 'b15', 8),
(31, 'b12', 9),
(32, 'b13', 9),
(33, 'b14', 9),
(34, 'b15', 9),
(35, 'b12', 10),
(36, 'b13', 10),
(37, 'b14', 10),
(38, 'b15', 10),
(39, 'd16', 11),
(40, 'b17', 11),
(41, 'b18', 11),
(42, 'b19', 11),
(43, 'd16', 12),
(44, 'b17', 12),
(45, 'b18', 12),
(46, 'b19', 12);

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE IF NOT EXISTS `devices` (
  `id_device` varchar(512) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_device`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecoles`
--

CREATE TABLE IF NOT EXISTS `ecoles` (
  `id` int(11) NOT NULL,
  `nomEcole` varchar(16) NOT NULL,
  `ville` varchar(16) NOT NULL,
  PRIMARY KEY (`nomEcole`,`ville`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ecoles`
--

INSERT INTO `ecoles` (`id`, `nomEcole`, `ville`) VALUES
(0, 'Esiea', 'Ivry sur Seine');

-- --------------------------------------------------------

--
-- Table structure for table `lien_rooms_ecoles`
--

CREATE TABLE IF NOT EXISTS `lien_rooms_ecoles` (
  `id_ecole` int(11) NOT NULL,
  `id_room` int(11) NOT NULL,
  PRIMARY KEY (`id_ecole`,`id_room`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lien_rooms_ecoles`
--

INSERT INTO `lien_rooms_ecoles` (`id_ecole`, `id_room`) VALUES
(0, 6),
(0, 7),
(0, 10),
(0, 12);

-- --------------------------------------------------------

--
-- Table structure for table `lien_rooms_users`
--

CREATE TABLE IF NOT EXISTS `lien_rooms_users` (
  `id_user` int(11) NOT NULL,
  `id_room` int(11) NOT NULL,
  `is_kicked` tinyint(4) NOT NULL,
  `duration` datetime NOT NULL,
  PRIMARY KEY (`id_user`,`id_room`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lien_users_ecoles`
--

CREATE TABLE IF NOT EXISTS `lien_users_ecoles` (
  `id_ecole` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_ecole`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lien_users_ecoles`
--

INSERT INTO `lien_users_ecoles` (`id_ecole`, `id_user`) VALUES
(0, 1),
(0, 10),
(0, 11);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_room` int(11) NOT NULL,
  `message` text NOT NULL,
  `dateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `password_room` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `password_room`) VALUES
(6, 'test', '098f6bcd4621d373cade4e832627b4f6'),
(7, 'test2', 'ad0234829205b9033196ba818f7a872b'),
(10, 'test2', 'f71dbe52628a3f83a77ab494817525c6'),
(12, 'test3', 'f71dbe52628a3f83a77ab494817525c6');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(32) NOT NULL,
  `pswd` varchar(256) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `email` varchar(32) NOT NULL,
  `dateCrea` datetime NOT NULL,
  `token` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pseudo` (`pseudo`,`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `pswd`, `isAdmin`, `email`, `dateCrea`, `token`) VALUES
(1, 'Handoofala', '6922b5ee0d3f30e1e52e9aa08c31058316363d542ea369c7a76012f06f39cef7', 2, 'handoofala@gmail.com', '2015-03-09 14:34:29', 'fa3f93cbb1fefc0184d029d79766ce8f'),
(2, 'test', '18ea285983df355f3024e412fb46ad6cbd98a7ffe6872e26612e35f38aa39c41', 1, 'test@test.fr', '2015-03-23 14:16:54', '3547a7f8cc16a250f144fdab7c8dd949'),
(10, 'test2', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 0, 'test2@gmail.com', '2015-03-23 22:50:28', 'fa3f93cbb1fefc0184d029d79766ce8f'),
(11, 'testProf', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 1, 'prof@ecole.fr', '2015-03-23 22:51:02', 'fa3f93cbb1fefc0184d029d79766ce8f');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
