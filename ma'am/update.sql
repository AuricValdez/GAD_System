-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2025 at 12:17 PM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gad_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `narrative_entries`
--

DROP TABLE IF EXISTS `narrative_entries`;
CREATE TABLE IF NOT EXISTS `narrative_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `background` text,
  `participants` text,
  `topics` text,
  `results` text,
  `lessons` text,
  `what_worked` text,
  `issues` text,
  `recommendations` text,
  `ps_attribution` varchar(255) DEFAULT NULL,
  `evaluation` text,
  `activity_ratings` text,
  `timeliness_ratings` text,
  `photo_path` varchar(255) DEFAULT NULL,
  `photo_paths` text,
  `photo_caption` text,
  `gender_issue` text,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ppas_form_id` int DEFAULT NULL,
  `expected_results` text,
  `lessons_learned` text,
  `issues_concerns` text,
  `other_internal_personnel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ppas_form_id` (`ppas_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `narrative_personnel`
--

DROP TABLE IF EXISTS `narrative_personnel`;
CREATE TABLE IF NOT EXISTS `narrative_personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `personnel_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_activity_personnel` (`activity_id`,`personnel_id`),
  KEY `personnel_id` (`personnel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
