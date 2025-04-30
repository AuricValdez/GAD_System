-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 20, 2025 at 02:17 AM
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
-- Table structure for table `academic_rank`
--

DROP TABLE IF EXISTS `academic_rank`;
CREATE TABLE IF NOT EXISTS `academic_rank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_rank`
--

INSERT INTO `academic_rank` (`id`, `rank_name`) VALUES
(1, 'Professor'),
(2, 'Associate Professor'),
(3, 'Assistant Professor');

-- --------------------------------------------------------

--
-- Table structure for table `academic_ranks`
--

DROP TABLE IF EXISTS `academic_ranks`;
CREATE TABLE IF NOT EXISTS `academic_ranks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_rank` varchar(100) NOT NULL,
  `salary_grade` int NOT NULL,
  `monthly_salary` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) GENERATED ALWAYS AS ((`monthly_salary` / 176)) STORED,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_ranks`
--

INSERT INTO `academic_ranks` (`id`, `academic_rank`, `salary_grade`, `monthly_salary`) VALUES
(84, 'Instructor II', 9, '35000.00'),
(85, 'Instructor III', 10, '43000.00'),
(110, 'Instructor I', 8, '31000.00'),
(111, 'College Lecturer', 2, '25000.00'),
(112, 'Senior Lecturer', 2, '27500.00'),
(113, 'Master Lecturer', 5, '30000.00'),
(114, 'Assistant Professor II', 8, '32500.00'),
(115, 'Associate Professor I', 6, '35000.00'),
(116, 'Associate Professor II', 6, '37500.00'),
(117, 'Professor I', 9, '40000.00'),
(118, 'Professor II', 1, '42500.00'),
(119, 'Professor III', 3, '45000.00'),
(120, 'Professor IV', 4, '47500.00');

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

DROP TABLE IF EXISTS `credentials`;
CREATE TABLE IF NOT EXISTS `credentials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `credentials`
--

INSERT INTO `credentials` (`id`, `username`, `password`) VALUES
(1, 'Lipa', 'lipa'),
(2, 'Pablo Borbon', 'pablo borbon'),
(3, 'Alangilan', 'alangilan'),
(4, 'Nasugbu', 'nasugbu'),
(5, 'Malvar', 'malvar'),
(6, 'Rosario', 'rosario'),
(7, 'Balayan', 'balayan'),
(8, 'Lemery', 'lemery'),
(9, 'San Juan', 'san juan'),
(10, 'Lobo', 'lobo'),
(11, 'Central', 'central');

-- --------------------------------------------------------

--
-- Table structure for table `gad_proposals`
--

DROP TABLE IF EXISTS `gad_proposals`;
CREATE TABLE IF NOT EXISTS `gad_proposals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter` varchar(2) NOT NULL,
  `activity_title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `venue` varchar(255) NOT NULL,
  `delivery_mode` varchar(50) NOT NULL,
  `ppas_id` int DEFAULT NULL,
  `project_leaders` text,
  `leader_responsibilities` text,
  `assistant_project_leaders` text,
  `assistant_responsibilities` text,
  `project_staff` text,
  `staff_responsibilities` text,
  `partner_offices` varchar(255) DEFAULT NULL,
  `male_beneficiaries` int DEFAULT '0',
  `female_beneficiaries` int DEFAULT '0',
  `total_beneficiaries` int DEFAULT '0',
  `rationale` text,
  `specific_objectives` text,
  `strategies` text,
  `budget_source` varchar(50) DEFAULT NULL,
  `total_budget` decimal(10,2) DEFAULT '0.00',
  `budget_breakdown` text,
  `sustainability_plan` text,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `year_quarter` (`year`,`quarter`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposals`
--

INSERT INTO `gad_proposals` (`id`, `year`, `quarter`, `activity_title`, `start_date`, `end_date`, `venue`, `delivery_mode`, `ppas_id`, `project_leaders`, `leader_responsibilities`, `assistant_project_leaders`, `assistant_responsibilities`, `project_staff`, `staff_responsibilities`, `partner_offices`, `male_beneficiaries`, `female_beneficiaries`, `total_beneficiaries`, `rationale`, `specific_objectives`, `strategies`, `budget_source`, `total_budget`, `budget_breakdown`, `sustainability_plan`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2023, 'Q1', 'w', '2025-03-19', '2025-03-20', 'w', 'face-to-face', NULL, 'Sophia Wilson', 'w', 'Jane Smith', 'w', 'Jane Smith', 'd\r\nw', 'w', 50, 50, 100, 'w', 'w', 'w', 'GAA', '222.00', 'w', 'd', 'Lipa', '2025-03-18 10:41:02', NULL),
(4, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'v', 50, 50, 100, 'v', 'v', 'v', 'GAA', '33.00', 'v', 's', NULL, '2025-03-19 09:13:08', NULL),
(6, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'online', 11, 'Jane Smith', 'e', 'Jane Smith', 'e', 'John Doe', 'e', 'e', 50, 50, 100, 'e', 'e', 'e', 'GAA', '333.00', 'e', 'f', NULL, '2025-03-19 11:23:35', NULL),
(7, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'face-to-face', 11, 'Jane Smith', 'd', 'Jane Smith', 'd', 'John Doe', 'd\r\nd\r\nd', 'k', 50, 50, 100, 'w', 'w', 'w', 'GAA', '222.00', 'w', 'w', NULL, '2025-03-19 13:11:50', NULL),
(8, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'face-to-face', 11, 'Jane Smith', 'd', 'Jane Smith', 'd', 'John Doe', 'd\r\nd\r\nd', 'k', 50, 50, 100, 'w', 'w', 'w', 'GAA', '222.00', 'w', 'w', NULL, '2025-03-19 13:13:02', NULL),
(9, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'online', 11, 'Jane Smith', 'r', 'Jane Smith', 'r', 'John Doe', 'r\r\nr', 'r', 50, 50, 100, 'r', 'r', 'r', 'GAA', '333.00', 'r', 'r', NULL, '2025-03-19 14:17:46', NULL),
(10, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'l', 50, 50, 100, 'l', 'l', 'l', 'GAA', '8.00', 'l', 'l', NULL, '2025-03-19 15:49:53', NULL),
(11, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'l', 50, 50, 100, 'l', 'l', 'l', 'GAA', '8.00', 'l', 'l', NULL, '2025-03-19 15:50:55', NULL),
(12, 2024, 'Q1', 'dwww', '2025-03-19', '2025-03-21', 'w', 'online', 12, 'Sophia Wilson', 'e', 'John Doe', 'e', 'John Doe', 'e\r\n2', '2', 50, 50, 100, 'f', 'f', 'f', 'Income', '333.00', '3', 'f', NULL, '2025-03-20 08:14:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gad_proposal_activities`
--

DROP TABLE IF EXISTS `gad_proposal_activities`;
CREATE TABLE IF NOT EXISTS `gad_proposal_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text,
  `sequence` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_activities`
--

INSERT INTO `gad_proposal_activities` (`id`, `proposal_id`, `title`, `details`, `sequence`, `created_at`) VALUES
(1, 1, 'w', 'w', 1, '2025-03-19 03:20:36'),
(2, 6, 'e', 'e', 0, '2025-03-19 03:23:35'),
(3, 7, 'w', 'w', 0, '2025-03-19 05:11:50'),
(4, 8, 'w', 'w', 0, '2025-03-19 05:13:02'),
(5, 9, 'r', 'r', 0, '2025-03-19 06:17:46'),
(6, 9, 'r', 'r', 0, '2025-03-19 06:17:46'),
(7, 12, 'e', 'e', 0, '2025-03-20 00:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `gad_proposal_monitoring`
--

DROP TABLE IF EXISTS `gad_proposal_monitoring`;
CREATE TABLE IF NOT EXISTS `gad_proposal_monitoring` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `objectives` text,
  `performance_indicators` text,
  `baseline_data` text,
  `performance_target` text,
  `data_source` text,
  `collection_method` text,
  `frequency` text,
  `responsible_office` text,
  `sequence` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gad_proposal_personnel`
--

DROP TABLE IF EXISTS `gad_proposal_personnel`;
CREATE TABLE IF NOT EXISTS `gad_proposal_personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `personnel_id` int NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_personnel`
--

INSERT INTO `gad_proposal_personnel` (`id`, `proposal_id`, `personnel_id`, `role`, `created_at`) VALUES
(1, 4, 2, 'project_leader', '2025-03-19 01:13:08'),
(2, 4, 1, 'project_leader', '2025-03-19 01:13:08'),
(3, 4, 5, 'assistant_project_leader', '2025-03-19 01:13:08'),
(4, 4, 8, 'project_staff', '2025-03-19 01:13:08'),
(5, 6, 2, 'project_leader', '2025-03-19 03:23:35'),
(6, 6, 4, 'assistant_project_leader', '2025-03-19 03:23:35'),
(7, 6, 1, 'project_staff', '2025-03-19 03:23:35'),
(8, 7, 2, 'project_leader', '2025-03-19 05:11:50'),
(9, 7, 4, 'assistant_project_leader', '2025-03-19 05:11:50'),
(10, 7, 1, 'project_staff', '2025-03-19 05:11:50'),
(11, 8, 2, 'project_leader', '2025-03-19 05:13:02'),
(12, 8, 4, 'assistant_project_leader', '2025-03-19 05:13:02'),
(13, 8, 1, 'project_staff', '2025-03-19 05:13:02'),
(14, 9, 2, 'project_leader', '2025-03-19 06:17:46'),
(15, 9, 4, 'assistant_project_leader', '2025-03-19 06:17:46'),
(16, 9, 1, 'project_staff', '2025-03-19 06:17:46'),
(17, 10, 2, 'project_leader', '2025-03-19 07:49:53'),
(18, 10, 1, 'project_leader', '2025-03-19 07:49:53'),
(19, 10, 5, 'assistant_project_leader', '2025-03-19 07:49:53'),
(20, 10, 8, 'project_staff', '2025-03-19 07:49:53'),
(21, 11, 2, 'project_leader', '2025-03-19 07:50:55'),
(22, 11, 1, 'project_leader', '2025-03-19 07:50:55'),
(23, 11, 5, 'assistant_project_leader', '2025-03-19 07:50:55'),
(24, 11, 8, 'project_staff', '2025-03-19 07:50:55'),
(25, 12, 8, 'project_leader', '2025-03-20 00:14:03'),
(26, 12, 1, 'assistant_project_leader', '2025-03-20 00:14:03'),
(27, 12, 3, 'project_staff', '2025-03-20 00:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `gad_proposal_workplan`
--

DROP TABLE IF EXISTS `gad_proposal_workplan`;
CREATE TABLE IF NOT EXISTS `gad_proposal_workplan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `activity` varchar(255) NOT NULL,
  `timeline_data` text,
  `sequence` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gpb_entries`
--

DROP TABLE IF EXISTS `gpb_entries`;
CREATE TABLE IF NOT EXISTS `gpb_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `gender_issue` text NOT NULL,
  `cause_of_issue` text NOT NULL,
  `gad_objective` text NOT NULL,
  `relevant_agency` varchar(255) NOT NULL,
  `generic_activity` text NOT NULL,
  `specific_activities` text NOT NULL,
  `male_participants` int NOT NULL,
  `female_participants` int NOT NULL,
  `total_participants` int NOT NULL,
  `gad_budget` decimal(15,2) NOT NULL,
  `source_of_budget` varchar(255) NOT NULL,
  `responsible_unit` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `campus` varchar(255) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `total_gaa` decimal(15,2) DEFAULT NULL,
  `total_gad_fund` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gpb_entries`
--

INSERT INTO `gpb_entries` (`id`, `category`, `gender_issue`, `cause_of_issue`, `gad_objective`, `relevant_agency`, `generic_activity`, `specific_activities`, `male_participants`, `female_participants`, `total_participants`, `gad_budget`, `source_of_budget`, `responsible_unit`, `created_at`, `campus`, `year`, `total_gaa`, `total_gad_fund`) VALUES
(11, 'Client-Based', 'Test', '1', '1', 'Agency 1', '1', '1', 1, 1, 2, '1.00', 'Source 1', 'Unit 1', '2025-02-25 03:20:27', NULL, NULL, NULL, NULL),
(12, 'Gender Issue', 'e', 'e', 'e', 'Agency B', 'e; e', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit A', '2025-03-10 02:02:07', 'Lipa', 2027, '800.00', '40.00'),
(13, 'Gender Issue', 'e', 'e', 'e', 'Agency B', 'e; e', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit A', '2025-03-10 02:04:00', 'Lipa', 2027, '800.00', '40.00'),
(14, 'Gender Issue', 'e', 'e', 'e', 'Agency A', 'a; a', '[\"f\",\"f\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 02:04:30', 'Pablo Borbon', 2025, '1.00', '0.05'),
(15, 'Gender Issue', 'w', 'w', 'w', 'Agency B', 'w', '[\"w\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 02:06:03', 'Lipa', 2027, '800.00', '40.00'),
(16, 'Gender Issue', 'e', '2', 'e', 'Agency A', 'e', '[\"e\"]', 2, 2, 4, '2222.00', 'Source A', 'Unit B', '2025-03-10 02:08:38', 'Lipa', 2027, '800.00', '40.00'),
(17, 'Client-Focused', 'e', 'ee', 'e', 'Agency B', 'Default Activity', '[\"ee\",\"e\"]', 33, 3, 36, '553.00', 'Source B', 'Unit B', '2025-03-10 05:29:03', 'Lipa', 2027, '0.00', '0.00'),
(18, 'Client-Focused', 'j', 'j', 'n', 'Agency B', 'Default Activity', '[\"w\",\"w\"]', 2, 2, 4, '42.00', 'Source B', 'Unit B', '2025-03-10 05:30:40', 'Lipa', 2027, '0.00', '0.00'),
(19, 'Client-Focused', 'jj', 'j', 'd', 'Agency B', 'd', '[\"Default specific activity\"]', 2, 2, 4, '2222.00', 'Source B', 'Unit A', '2025-03-10 05:35:34', 'Lipa', 2027, '0.00', '0.00'),
(20, 'Client-Focused', 'ed', 'w', 'w', 'Agency A', 'General GAD Program', '[\"ww\",\"w\"]', 22, 22, 44, '2.00', 'Source B', 'Unit B', '2025-03-10 05:40:44', 'Lipa', 2027, '0.00', '0.00'),
(21, 'Organization-Focused', '2', '2', 'd', 'Agency B', 'ww', '[\"n\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 05:41:49', 'Default Campus', 2025, '0.00', '0.00'),
(22, 'Organization-Focused', 'n', 'f', 'f', 'Agency B', 'General GAD Program', '[\"f\",\"w\"]', 2, 22, 24, '2.00', 'Source B', 'Unit B', '2025-03-10 05:42:43', 'Pablo Borbon', 2025, '0.00', '0.00'),
(23, 'Client-Focused', 'Test Gender Issue', 'Test Cause', 'Test Objective', 'Agency A', 'Test Program', '[\"2\",\"d\"]', 10, 15, 25, '5000.00', 'Source A', 'Unit A', '2025-03-10 05:51:12', 'Lipa', 2027, '0.00', '0.00'),
(24, 'Client-Focused', 'nk', 'j', 'j', 'Agency B', 'b', '[\"j\",\"nb\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-10 06:06:33', 'Lipa', 2025, '0.00', '0.00'),
(25, 'Organization-Focused', 'dw', 'w', 'w', 'Agency B', 'w', '[\"2\"]', 4, 4, 8, '2222.00', 'Source B', 'Unit B', '2025-03-10 06:07:18', 'Lipa', 2026, '0.00', '0.00'),
(26, 'Client-Focused', 'nw', 'w', 'w', 'Agency B', 'www', '[\"eee\",\"eee\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 06:12:28', 'Lipa', 2026, '0.00', '0.00'),
(27, 'Client-Focused', 'nw', 'w', 'w', 'Agency B', 'wwwwww', '[\"eee\",\"eee\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 06:12:28', 'Lipa', 2026, '0.00', '0.00'),
(28, 'Organization-Focused', 'ww', 'dw', 'w', 'Agency B', '[\"www\",\"wwww\"]', '[\"ddwsa\",\"wdaw\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-10 06:18:30', 'Pablo Borbon', 2025, '0.00', '0.00'),
(29, 'Client-Focused', 'ejjj', 'e', '.f', 'Agency A', '[\"e\",\"e\"]', '[\"d\",\"d\"]', 2222, 22, 2244, '222.00', 'Source B', 'Unit B', '2025-03-10 06:58:20', 'Lipa', 2027, '0.00', '0.00'),
(30, 'Client-Focused', 'www', 'w', 'w', 'Agency A', '[\"www\",\"wwwwww\"]', '[\"wwwwww\",\"www\"]', 22, 22, 44, '2.00', 'Source A', 'Unit A', '2025-03-10 07:19:53', 'Lipa', 2025, '0.00', '0.00'),
(31, 'Organization-Focused', 'ek', 'c', 'w', 'Agency A', '[\"w\",\"w\"]', '[\"f\",\"wq\"]', 2, 0, 2, '222.00', 'Source A', 'Unit B', '2025-03-10 08:36:46', 'Pablo Borbon', 2025, '0.00', '0.00'),
(32, 'Organization-Focused', 'dwww', 'w', 'dddd', 'Agency A', '[\"w\",\"f\"]', '[\"f\",\",\"]', 7, 9, 16, '222.00', 'Source B', 'Unit B', '2025-03-10 08:37:59', 'Lipa', 2025, '0.00', '0.00'),
(33, 'Gender Issue', 'edw', 'e', 'e', 'Agency A', '[\"e\",\"f\"]', '[\"g\",\"g\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-11 01:34:00', 'Pablo Borbon', 2025, '1.00', '0.05'),
(34, 'Gender Issue', 'edw', 'e', 'e', 'Agency A', '[\"e\",\"f\"]', '[\"g\",\"g\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-11 01:34:00', 'Pablo Borbon', 2025, '1.00', '0.05'),
(35, 'Client-Focused', 'df', 'fe', 'f', 'Agency B', '[\"ww\",\"e\"]', '[\"w\",\"wwww\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-11 01:37:25', 'Lipa', 2026, '1000.00', '50.00'),
(36, 'Client-Focused', 'df', 'fe', 'f', 'Agency B', '[\"ww\",\"e\"]', '[\"w\",\"wwww\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-11 01:37:25', 'Lipa', 2026, '1000.00', '50.00'),
(37, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(38, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(39, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(40, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(41, 'Organization-Focused', 'dwwww', 'd', 'g', 'Agency B', '[\"d\",\"d\"]', '[\"cw\",\"wq\"]', 2, 2, 4, '22222.00', 'Source B', 'Unit B', '2025-03-11 01:46:47', 'Pablo Borbon', 2025, '1.00', '0.05'),
(42, 'Organization-Focused', 'dwwww', 'd', 'g', 'Agency B', '[\"d\",\"d\"]', '[\"cw\",\"wq\"]', 2, 2, 4, '22222.00', 'Source B', 'Unit B', '2025-03-11 01:46:47', 'Pablo Borbon', 2025, '1.00', '0.05'),
(43, 'Organization-Focused', 'eeee', 'e', 'e', 'Agency B', '[\"e\",\"f\"]', '[\"e\",\"e\"]', 3, 3, 6, '3.00', 'Source B', 'Unit B', '2025-03-12 00:21:19', 'Pablo Borbon', 2025, '1.00', '0.05'),
(44, 'Organization-Focused', 'eeee', 'e', 'e', 'Agency B', '[\"e\",\"f\"]', '[\"e\",\"e\"]', 3, 3, 6, '3.00', 'Source B', 'Unit B', '2025-03-12 00:21:19', 'Pablo Borbon', 2025, '1.00', '0.05'),
(45, 'Client-Focused', 'e', 'e', 'e', 'Agency A', '[\"e\",\"e\"]', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-17 04:54:10', 'Lipa', 2027, '800.00', '40.00'),
(46, 'Client-Focused', 'e', 'e', 'e', 'Agency A', '[\"e\",\"e\"]', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-17 04:54:10', 'Lipa', 2027, '800.00', '40.00'),
(47, 'Client-Focused', 'Test Gender Issue', 'w', 'w', 'Agency B', '[\"ww\",\"w\"]', '[\"w\",\"w\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-18 05:09:36', 'Pablo Borbon', 2026, '999.00', '49.95'),
(48, 'Client-Focused', 'Test Gender Issue', 'w', 'w', 'Agency B', '[\"ww\",\"w\"]', '[\"w\",\"w\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-18 05:09:36', 'Pablo Borbon', 2026, '999.00', '49.95');

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
CREATE TABLE IF NOT EXISTS `personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `academic_rank` varchar(100) NOT NULL,
  `campus` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_academic_rank` (`academic_rank`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `name`, `category`, `status`, `gender`, `academic_rank`, `campus`, `created_at`) VALUES
(117, 'Elbert D. Nebres', 'Teaching', 'Guest Lecturer', 'male', 'Instructor II', 'Lipa', '2025-03-05 05:16:51'),
(116, 'Elbert D. Nebres', 'Teaching', 'Permanent', 'male', 'Instructor II', 'Alangilan', '2025-03-05 05:00:08'),
(132, 'Test', 'Teaching', 'Temporary', 'female', 'Instructor III', 'Lipa', '2025-03-06 01:10:13'),
(124, 'Fryan Auric L. Valdez', 'Teaching', 'Guest Lecturer', 'Gay', 'Instructor I', 'Lipa', '2025-03-05 05:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_list`
--

DROP TABLE IF EXISTS `personnel_list`;
CREATE TABLE IF NOT EXISTS `personnel_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gender` enum('male','female','gay','lesbian') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `academic_rank_id` int DEFAULT NULL,
  `monthly_salary` decimal(10,2) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_rank_id` (`academic_rank_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_list`
--

INSERT INTO `personnel_list` (`id`, `name`, `gender`, `academic_rank_id`, `monthly_salary`, `hourly_rate`) VALUES
(1, 'John Doe', 'male', 1, '50000.00', '312.50'),
(2, 'Jane Smith', 'female', 2, '45000.00', '281.25'),
(3, 'John Doe', 'male', 1, '50000.00', '250.00'),
(4, 'Jane Smith', 'female', 2, '55000.00', '275.00'),
(5, 'Michael Johnson', 'gay', 3, '60000.00', '300.00'),
(6, 'Emily Davis', 'lesbian', 1, '52000.00', '260.00'),
(7, 'Daniel Brown', 'gay', 2, '58000.00', '290.00'),
(8, 'Sophia Wilson', 'lesbian', 3, '62000.00', '310.00'),
(9, 'Matthew Martinez', 'male', 1, '51000.00', '255.00'),
(10, 'Olivia Anderson', 'female', 2, '57000.00', '285.00'),
(11, 'Ethan Thomas', 'male', 3, '63000.00', '315.00'),
(12, 'Ava Taylor', 'female', 1, '53000.00', '265.00');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_beneficiaries`
--

DROP TABLE IF EXISTS `ppas_beneficiaries`;
CREATE TABLE IF NOT EXISTS `ppas_beneficiaries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `type` enum('internal_student','internal_faculty','external') NOT NULL,
  `male_count` int DEFAULT '0',
  `female_count` int DEFAULT '0',
  `external_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_beneficiaries`
--

INSERT INTO `ppas_beneficiaries` (`id`, `ppas_id`, `type`, `male_count`, `female_count`, `external_type`) VALUES
(1, 1, 'internal_student', 23, 3, NULL),
(2, 1, 'internal_faculty', 3, 2, NULL),
(3, 1, 'external', 2, 2, '2'),
(4, 2, 'internal_student', 9, 8, NULL),
(5, 2, 'internal_faculty', 3, 2, NULL),
(6, 2, 'external', 2, 2, 'j'),
(7, 3, 'internal_student', 12, 22, NULL),
(8, 3, 'internal_faculty', 1, 2, NULL),
(9, 3, 'external', 2, 2, '2'),
(10, 4, 'internal_student', 2, 2, NULL),
(11, 4, 'internal_faculty', 0, 3, NULL),
(12, 4, 'external', 2, 2, 'fw'),
(13, 5, 'internal_student', 2, 2, NULL),
(14, 5, 'internal_faculty', 1, 2, NULL),
(15, 5, 'external', 2, 2, '2'),
(16, 6, 'internal_student', 2, 2, NULL),
(17, 6, 'internal_faculty', 1, 3, NULL),
(18, 6, 'external', 2, 2, '2'),
(19, 7, 'internal_student', 0, 0, NULL),
(20, 7, 'internal_faculty', 1, 1, NULL),
(21, 7, 'external', 0, 0, ''),
(22, 8, 'internal_student', 3, 3, NULL),
(23, 8, 'internal_faculty', 0, 2, NULL),
(24, 8, 'external', 3, 3, '3'),
(25, 9, 'internal_student', 0, 0, NULL),
(26, 9, 'internal_faculty', 2, 2, NULL),
(27, 9, 'external', 0, 0, ''),
(28, 10, 'internal_student', 3, 3, NULL),
(29, 10, 'internal_faculty', 1, 2, NULL),
(30, 10, 'external', 3, 3, 'fw'),
(31, 11, 'internal_student', 2, 2, NULL),
(32, 11, 'internal_faculty', 1, 2, NULL),
(33, 11, 'external', 2, 2, '2'),
(34, 12, 'internal_student', 2, 2, NULL),
(35, 12, 'internal_faculty', 2, 1, NULL),
(36, 12, 'external', 2, 2, 'student'),
(37, 13, '', 2, 2, NULL),
(38, 13, 'internal_faculty', 2, 2, NULL),
(39, 13, '', 2, 2, NULL),
(40, 14, '', 2, 2, NULL),
(41, 14, 'internal_faculty', 2, 2, NULL),
(42, 14, '', 2, 2, NULL),
(43, 15, '', 2, 2, NULL),
(44, 15, 'internal_faculty', 2, 2, NULL),
(45, 15, '', 2, 2, NULL),
(46, 16, '', 2, 2, NULL),
(47, 16, 'internal_faculty', 2, 2, NULL),
(48, 16, '', 2, 2, NULL),
(49, 17, '', 2, 2, NULL),
(50, 17, 'internal_faculty', 1, 2, NULL),
(51, 17, '', 2, 2, NULL),
(52, 18, '', 2, 2, NULL),
(53, 18, 'internal_faculty', 1, 2, NULL),
(54, 18, '', 2, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ppas_forms`
--

DROP TABLE IF EXISTS `ppas_forms`;
CREATE TABLE IF NOT EXISTS `ppas_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `has_lunch_break` tinyint(1) DEFAULT '0',
  `has_am_break` tinyint(1) DEFAULT '0',
  `has_pm_break` tinyint(1) DEFAULT '0',
  `total_duration` decimal(10,2) NOT NULL,
  `duration_metadata` text,
  `approved_budget` decimal(10,2) NOT NULL,
  `source_of_budget` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `year`, `quarter`, `title`, `location`, `start_date`, `end_date`, `start_time`, `end_time`, `has_lunch_break`, `has_am_break`, `has_pm_break`, `total_duration`, `duration_metadata`, `approved_budget`, `source_of_budget`, `created_at`, `updated_at`, `created_by`, `status`) VALUES
(1, 2021, 'Q2', 'dwww', 'h', '2025-03-19', '2025-03-19', '05:24:00', '13:24:00', 1, 0, 0, '8.00', NULL, '2.00', 'Income', '2025-03-11 06:28:45', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(2, 2023, 'Q1', 'n', 'n', '2025-03-13', '2025-03-13', '03:12:00', '15:13:00', 0, 0, 0, '12.02', NULL, '22222.00', 'Grants', '2025-03-11 07:16:14', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(3, 2021, 'Q2', 'w', 'w', '2025-02-27', '2025-02-27', '09:35:00', '21:35:00', 1, 0, 0, '12.00', NULL, '2.00', 'Income', '2025-03-17 01:37:07', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(4, 2021, 'Q1', 'dwwww', 'w', '2025-03-17', '2025-03-17', '10:21:00', '22:21:00', 1, 0, 0, '12.00', NULL, '3.00', 'GAA', '2025-03-17 02:23:27', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(5, 2021, 'Q2', 'g', 'g', '2025-03-21', '2025-03-21', '10:47:00', '22:47:00', 0, 0, 0, '12.00', NULL, '2222222.00', 'Income', '2025-03-17 02:47:54', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(6, 2021, 'Q1', 'ed', 'e', '2025-03-27', '2025-03-27', '10:55:00', '22:55:00', 1, 0, 0, '12.00', NULL, '3.00', 'GAA', '2025-03-17 02:57:06', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(7, 2021, 'Q3', 'e', 'e', '2025-03-18', '2025-03-18', '01:48:00', '13:48:00', 1, 0, 0, '12.00', NULL, '2.00', 'GAA', '2025-03-18 05:49:38', '2025-03-19 02:43:27', 'Lipa', 'draft'),
(8, 2020, 'Q1', 'e', 'e', '2025-03-19', '2025-03-20', '10:41:00', '22:41:00', 1, 0, 0, '22.00', '22.00 (2 days × 11.00 hrs/day) | Lunch breaks: 2 hrs', '3.00', 'GAA', '2025-03-19 02:46:17', '2025-03-19 02:46:17', 'Lipa', 'draft'),
(9, 2020, 'Q3', 'ed', 'e', '2025-03-19', '2025-03-20', '10:51:00', '22:51:00', 1, 0, 0, '22.00', '22.00 (2 days × 11.00 hrs/day) | Lunch breaks: 2 hrs', '333.00', 'GAA', '2025-03-19 02:52:09', '2025-03-19 02:52:09', 'Lipa', 'draft'),
(10, 2021, 'Q4', 'j', 'j', '2025-03-19', '2025-03-22', '10:58:00', '22:58:00', 1, 0, 0, '44.00', '44.00 (4 days × 11.00 hrs/day) | Lunch breaks: 4 hrs', '3.00', 'Income', '2025-03-19 02:59:04', '2025-03-19 02:59:04', 'Lipa', 'draft'),
(11, 2022, 'Q1', 'df', 'f', '2025-03-19', '2025-03-22', '11:08:00', '23:08:00', 1, 0, 0, '44.00', '44.00 (4 days × 11.00 hrs/day) | Lunch breaks: 4 hrs', '2.00', 'GAA', '2025-03-19 03:09:27', '2025-03-19 03:09:27', 'Lipa', 'draft'),
(12, 2024, 'Q1', 'dwww', 'w', '2025-03-19', '2025-03-21', '08:11:00', '20:11:00', 1, 0, 0, '33.00', '33.00 (3 days × 11.00 hrs/day) | Lunch breaks: 3 hrs', '2.00', 'Income', '2025-03-20 00:12:32', '2025-03-20 00:12:32', 'Lipa', 'draft'),
(13, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-21', '09:57:00', '21:57:00', 1, 0, 0, '22.00', NULL, '2.00', 'GAA', '2025-03-20 02:00:36', '2025-03-20 02:00:36', 'Lipa', 'draft'),
(14, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-21', '09:57:00', '21:57:00', 1, 0, 0, '22.00', NULL, '2.00', 'GAA', '2025-03-20 02:00:36', '2025-03-20 02:00:36', 'Lipa', 'draft'),
(15, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-21', '09:57:00', '21:57:00', 1, 0, 0, '22.00', NULL, '2.00', 'GAA', '2025-03-20 02:00:38', '2025-03-20 02:00:38', 'Lipa', 'draft'),
(16, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-21', '09:57:00', '21:57:00', 1, 0, 0, '22.00', NULL, '2.00', 'GAA', '2025-03-20 02:00:38', '2025-03-20 02:00:38', 'Lipa', 'draft'),
(17, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-22', '10:08:00', '22:08:00', 1, 0, 0, '33.00', '33.00 (3 days × 11.00 hrs/day) | Lunch breaks: 3 hrs', '2.00', 'Income', '2025-03-20 02:09:19', '2025-03-20 02:09:19', 'Lipa', 'draft'),
(18, 2020, 'Q1', 'e', 'e', '2025-03-20', '2025-03-22', '10:08:00', '22:08:00', 1, 0, 0, '33.00', '33.00 (3 days × 11.00 hrs/day) | Lunch breaks: 3 hrs', '2.00', 'Income', '2025-03-20 02:09:19', '2025-03-20 02:09:19', 'Lipa', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_personnel`
--

DROP TABLE IF EXISTS `ppas_personnel`;
CREATE TABLE IF NOT EXISTS `ppas_personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `personnel_id` int NOT NULL,
  `personnel_name` varchar(255) NOT NULL,
  `role` enum('project_leader','asst_project_leader','project_staff','other_participant') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`),
  KEY `personnel_id` (`personnel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_personnel`
--

INSERT INTO `ppas_personnel` (`id`, `ppas_id`, `personnel_id`, `personnel_name`, `role`) VALUES
(1, 1, 2, '', 'project_leader'),
(2, 1, 1, '', 'asst_project_leader'),
(3, 1, 1, '', 'project_staff'),
(4, 1, 2, '', 'other_participant'),
(5, 1, 1, '', 'other_participant'),
(6, 2, 2, 'Jane Smith', 'project_leader'),
(7, 2, 1, 'John Doe', 'project_leader'),
(8, 2, 5, 'Michael Johnson', 'asst_project_leader'),
(9, 2, 8, 'Sophia Wilson', 'project_staff'),
(10, 2, 7, 'Daniel Brown', 'other_participant'),
(11, 3, 6, 'Emily Davis', 'project_leader'),
(12, 3, 7, 'Daniel Brown', 'asst_project_leader'),
(13, 3, 2, 'Jane Smith', 'project_staff'),
(14, 3, 1, 'John Doe', 'project_staff'),
(15, 3, 12, 'Ava Taylor', 'other_participant'),
(16, 3, 6, 'Emily Davis', 'other_participant'),
(17, 11, 2, 'Jane Smith', 'project_leader'),
(18, 11, 4, 'Jane Smith', 'asst_project_leader'),
(19, 11, 1, 'John Doe', 'project_staff'),
(20, 11, 5, 'Michael Johnson', 'other_participant'),
(21, 12, 8, 'Sophia Wilson', 'project_leader'),
(22, 12, 1, 'John Doe', 'asst_project_leader'),
(23, 12, 3, 'John Doe', 'project_staff'),
(24, 12, 2, 'Jane Smith', 'other_participant'),
(25, 13, 2, 'Jane Smith', 'project_leader'),
(26, 13, 4, 'Jane Smith', 'asst_project_leader'),
(27, 13, 1, 'John Doe', 'project_staff'),
(28, 13, 3, 'John Doe', 'other_participant'),
(29, 14, 2, 'Jane Smith', 'project_leader'),
(30, 14, 4, 'Jane Smith', 'asst_project_leader'),
(31, 14, 1, 'John Doe', 'project_staff'),
(32, 14, 3, 'John Doe', 'other_participant'),
(33, 15, 2, 'Jane Smith', 'project_leader'),
(34, 15, 4, 'Jane Smith', 'asst_project_leader'),
(35, 15, 1, 'John Doe', 'project_staff'),
(36, 15, 3, 'John Doe', 'other_participant'),
(37, 16, 2, 'Jane Smith', 'project_leader'),
(38, 16, 4, 'Jane Smith', 'asst_project_leader'),
(39, 16, 1, 'John Doe', 'project_staff'),
(40, 16, 3, 'John Doe', 'other_participant'),
(41, 17, 2, 'Jane Smith', 'project_leader'),
(42, 17, 4, 'Jane Smith', 'asst_project_leader'),
(43, 17, 5, 'Michael Johnson', 'project_staff'),
(44, 17, 1, 'John Doe', 'other_participant'),
(45, 18, 2, 'Jane Smith', 'project_leader'),
(46, 18, 4, 'Jane Smith', 'asst_project_leader'),
(47, 18, 5, 'Michael Johnson', 'project_staff'),
(48, 18, 1, 'John Doe', 'other_participant');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_sdgs`
--

DROP TABLE IF EXISTS `ppas_sdgs`;
CREATE TABLE IF NOT EXISTS `ppas_sdgs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `sdg_number` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_sdgs`
--

INSERT INTO `ppas_sdgs` (`id`, `ppas_id`, `sdg_number`) VALUES
(1, 1, 4),
(2, 2, 4),
(3, 3, 2),
(4, 4, 2),
(5, 5, 3),
(6, 6, 2),
(7, 7, 2),
(8, 8, 3),
(9, 9, 2),
(10, 10, 1),
(11, 10, 3),
(12, 11, 3),
(13, 12, 3),
(14, 13, 4),
(15, 14, 4),
(16, 15, 4),
(17, 16, 4),
(18, 17, 3),
(19, 18, 3);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_name` (`program_name`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `program_name`, `created_at`) VALUES
(1, 'eff', '2025-03-17 01:06:21'),
(2, 'fwa', '2025-03-17 01:09:58'),
(3, 'g', '2025-03-17 01:11:35'),
(4, 'wwww', '2025-03-17 01:14:00'),
(5, 'w', '2025-03-17 01:19:12'),
(6, 'f', '2025-03-17 01:21:45'),
(7, 'wff', '2025-03-17 02:00:06'),
(8, 'd', '2025-03-18 05:07:54'),
(9, 'fffff', '2025-03-18 05:48:40'),
(10, 'e', '2025-03-19 02:51:12'),
(11, 'lol', '2025-03-20 00:11:15');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `created_at`) VALUES
(1, 'e', '2025-03-17 01:06:35'),
(2, 'fawa', '2025-03-17 01:06:43'),
(3, 'fese', '2025-03-17 01:11:48'),
(4, 'fwa', '2025-03-17 01:17:04'),
(5, 'www', '2025-03-17 01:19:23'),
(6, 'd', '2025-03-17 02:00:12'),
(7, 'y', '2025-03-18 05:07:59'),
(8, 'eeeee', '2025-03-18 05:48:48'),
(9, 'lol', '2025-03-20 00:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `target`
--

DROP TABLE IF EXISTS `target`;
CREATE TABLE IF NOT EXISTS `target` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` year NOT NULL,
  `campus` enum('Lipa','Pablo Borbon','Alangilan','Nasugbu','Malvar''Rosario','Balayan','Lemery','San Juan','Lobo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_gaa` decimal(15,2) NOT NULL,
  `total_gad_fund` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_year_campus` (`year`,`campus`),
  KEY `idx_year` (`year`),
  KEY `idx_campus` (`campus`)
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`id`, `year`, `campus`, `total_gaa`, `total_gad_fund`) VALUES
(120, 2025, 'Pablo Borbon', '1.00', '0.05'),
(146, 2025, 'Alangilan', '500.00', '25.00'),
(149, 2026, 'Alangilan', '1.00', '0.05'),
(152, 2025, 'Lipa', '420.00', '21.00'),
(154, 2026, 'Lipa', '1000.00', '50.00'),
(155, 2027, 'Lipa', '800.00', '40.00'),
(156, 2026, 'Pablo Borbon', '999.00', '49.95');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gad_proposal_activities`
--
ALTER TABLE `gad_proposal_activities`
  ADD CONSTRAINT `fk_gad_activities_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gad_proposal_monitoring`
--
ALTER TABLE `gad_proposal_monitoring`
  ADD CONSTRAINT `fk_gad_monitoring_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gad_proposal_personnel`
--
ALTER TABLE `gad_proposal_personnel`
  ADD CONSTRAINT `gad_proposal_personnel_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gad_proposal_workplan`
--
ALTER TABLE `gad_proposal_workplan`
  ADD CONSTRAINT `fk_gad_workplan_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
