-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 27, 2025 at 03:36 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

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
(84, 'Instructor II', 9, 35000.00),
(85, 'Instructor III', 10, 43000.00),
(110, 'Instructor I', 8, 31000.00),
(111, 'College Lecturer', 2, 25000.00),
(112, 'Senior Lecturer', 2, 27500.00),
(113, 'Master Lecturer', 5, 30000.00),
(114, 'Assistant Professor II', 8, 32500.00),
(115, 'Associate Professor I', 6, 35000.00),
(116, 'Associate Professor II', 6, 37500.00),
(117, 'Professor I', 9, 40000.00),
(118, 'Professor II', 1, 42500.00),
(119, 'Professor III', 3, 45000.00),
(120, 'Professor IV', 4, 47500.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposals`
--

INSERT INTO `gad_proposals` (`id`, `year`, `quarter`, `activity_title`, `start_date`, `end_date`, `venue`, `delivery_mode`, `ppas_id`, `project_leaders`, `leader_responsibilities`, `assistant_project_leaders`, `assistant_responsibilities`, `project_staff`, `staff_responsibilities`, `partner_offices`, `male_beneficiaries`, `female_beneficiaries`, `total_beneficiaries`, `rationale`, `specific_objectives`, `strategies`, `budget_source`, `total_budget`, `budget_breakdown`, `sustainability_plan`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2023, 'Q1', 'w', '2025-03-19', '2025-03-20', 'w', 'face-to-face', NULL, 'Sophia Wilson', 'w', 'Jane Smith', 'w', 'Jane Smith', 'd\r\nw', 'w', 50, 50, 100, 'w', 'w', 'w', 'GAA', 222.00, 'w', 'd', 'Lipa', '2025-03-18 10:41:02', NULL),
(4, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'v', 50, 50, 100, 'v', 'v', 'v', 'GAA', 33.00, 'v', 's', NULL, '2025-03-19 09:13:08', NULL),
(6, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'online', 11, 'Jane Smith', 'e', 'Jane Smith', 'e', 'John Doe', 'e', 'e', 50, 50, 100, 'e', 'e', 'e', 'GAA', 333.00, 'e', 'f', NULL, '2025-03-19 11:23:35', NULL),
(7, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'face-to-face', 11, 'Jane Smith', 'd', 'Jane Smith', 'd', 'John Doe', 'd\r\nd\r\nd', 'k', 50, 50, 100, 'w', 'w', 'w', 'GAA', 222.00, 'w', 'w', NULL, '2025-03-19 13:11:50', NULL),
(8, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'face-to-face', 11, 'Jane Smith', 'd', 'Jane Smith', 'd', 'John Doe', 'd\r\nd\r\nd', 'k', 50, 50, 100, 'w', 'w', 'w', 'GAA', 222.00, 'w', 'w', NULL, '2025-03-19 13:13:02', NULL),
(9, 2022, 'Q1', 'df', '2025-03-19', '2025-03-22', 'f', 'online', 11, 'Jane Smith', 'r', 'Jane Smith', 'r', 'John Doe', 'r\r\nr', 'r', 50, 50, 100, 'r', 'r', 'r', 'GAA', 333.00, 'r', 'r', NULL, '2025-03-19 14:17:46', NULL),
(10, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'l', 50, 50, 100, 'l', 'l', 'l', 'GAA', 8.00, 'l', 'l', NULL, '2025-03-19 15:49:53', NULL),
(11, 2023, 'Q1', 'n', '2025-03-13', '2025-03-13', 'n', 'online', 2, 'Jane Smith, John Doe', '', 'Michael Johnson', '', 'Sophia Wilson', '', 'l', 50, 50, 100, 'l', 'l', 'l', 'GAA', 8.00, 'l', 'l', NULL, '2025-03-19 15:50:55', NULL),
(12, 2024, 'Q1', 'dwww', '2025-03-19', '2025-03-21', 'w', 'online', 12, 'Sophia Wilson', 'e', 'John Doe', 'e', 'John Doe', 'e\r\n2', '2', 50, 50, 100, 'f', 'f', 'f', 'Income', 333.00, '3', 'f', NULL, '2025-03-20 08:14:03', NULL),
(13, 2024, 'Q1', 'dwww', '2025-03-19', '2025-03-21', 'w', 'online', 12, 'Sophia Wilson', '', 'John Doe', '', 'John Doe', '', '223', 50, 50, 100, 'dfg', 'dfg', 'dgf', 'GAA', 3.00, '3', 'ert', NULL, '2025-03-24 14:33:14', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(27, 12, 3, 'project_staff', '2025-03-20 00:14:03'),
(28, 13, 8, 'project_leader', '2025-03-24 06:33:14'),
(29, 13, 1, 'assistant_project_leader', '2025-03-24 06:33:14'),
(30, 13, 3, 'project_staff', '2025-03-24 06:33:14');

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
  `total_activities` int NOT NULL,
  `male_participants` int NOT NULL,
  `female_participants` int NOT NULL,
  `total_participants` int NOT NULL,
  `gad_budget` decimal(15,2) NOT NULL,
  `source_of_budget` varchar(255) NOT NULL,
  `responsible_unit` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `campus` varchar(255) DEFAULT NULL,
  `year` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gpb_entries`
--

INSERT INTO `gpb_entries` (`id`, `category`, `gender_issue`, `cause_of_issue`, `gad_objective`, `relevant_agency`, `generic_activity`, `specific_activities`, `total_activities`, `male_participants`, `female_participants`, `total_participants`, `gad_budget`, `source_of_budget`, `responsible_unit`, `created_at`, `campus`, `year`) VALUES
(71, 'Attributable PAPs', 'Gender Equality: Journey', 'Gender Equality test cause', 'Gender Equality test GAD result', 'Technical Advisory Extension Services', '[\"Gender Equality Program #1\",\"Test #2\",\"Test #3\"]', '[[\"Jumping Jacks\",\"Test\"],[\"Test2\"],[\"Test3\",\"Test4\"]]', 5, 10, 20, 30, 6900.50, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-24 00:11:28', 'Lipa', 2025),
(82, 'Organization-Focused', 'Advocation of Women\'s Month: 2026', '1', '1', 'Technical Advisory Extension Services', '[\"1\"]', '[[\"1\",\"2\"]]', 2, 1, 1, 2, 1.00, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-24 22:17:00', 'Lipa', 2025),
(96, 'Client-Focused', 'Advocation of Women\'s Month: 2025', 'Elbert Nebres is so gay.', 'Elbert Nebres is super gay.', 'Higher Education Services', '[\"Rehabilitation of Elbert\",\"Vaping of Elbert\"]', '[[\"Gym\",\"Hotdog 3 sets\"],[\"Vaping Tricks 101\"]]', 3, 10, 20, 30, 690.00, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-26 06:42:31', 'Lipa', 2025),
(88, 'Client-Focused', 'test gender issue', 'test cause', 'test gad result', 'Higher Education Services', '[\"test program 1\",\"test program 2\"]', '[[\"test activity 1\",\"test activity 2\",\"1\"],[\"test activity 3\",\"1\"]]', 5, 5, 5, 10, 420.00, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-25 14:03:32', 'Lipa', 2028),
(89, 'Client-Focused', 'Advocation of Women\'s Month: 2026', '1', '1', 'Higher Education Services', '[\"1\"]', '[[\"1\",\"2\"]]', 2, 1, 1, 2, 1.00, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-25 15:19:57', 'Alangilan', 2025);

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
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `name`, `category`, `status`, `gender`, `academic_rank`, `campus`, `created_at`) VALUES
(117, 'Elbert D. Nebres', 'Non-teaching', 'Casual', 'male', 'Instructor III', 'Lipa', '2025-03-05 05:16:51'),
(116, 'Elbert D. Nebres', 'Teaching', 'Permanent', 'male', 'Instructor II', 'Alangilan', '2025-03-05 05:00:08'),
(132, 'Test', 'Non-teaching', 'Casual', 'female', 'Instructor III', 'Lipa', '2025-03-06 01:10:13'),
(124, 'Fryan Auric L. Valdez', 'Teaching', 'Guest Lecturer', 'male', 'Instructor I', 'Lipa', '2025-03-05 05:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_forms`
--

DROP TABLE IF EXISTS `ppas_forms`;
CREATE TABLE IF NOT EXISTS `ppas_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus` varchar(50) NOT NULL,
  `year` int NOT NULL,
  `quarter` varchar(10) NOT NULL,
  `gender_issue_id` int NOT NULL,
  `project` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_duration_hours` decimal(10,2) NOT NULL,
  `lunch_break` enum('with','without') NOT NULL,
  `students_male` int NOT NULL DEFAULT '0',
  `students_female` int NOT NULL DEFAULT '0',
  `faculty_male` int NOT NULL DEFAULT '0',
  `faculty_female` int NOT NULL DEFAULT '0',
  `total_internal_male` int NOT NULL DEFAULT '0',
  `total_internal_female` int NOT NULL DEFAULT '0',
  `external_type` varchar(255) NOT NULL,
  `external_male` int NOT NULL DEFAULT '0',
  `external_female` int NOT NULL DEFAULT '0',
  `total_male` int NOT NULL DEFAULT '0',
  `total_female` int NOT NULL DEFAULT '0',
  `total_beneficiaries` int NOT NULL DEFAULT '0',
  `approved_budget` decimal(15,2) NOT NULL DEFAULT '0.00',
  `source_of_budget` varchar(100) NOT NULL,
  `ps_attribution` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sdgs` text,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `campus`, `year`, `quarter`, `gender_issue_id`, `project`, `program`, `activity`, `location`, `start_date`, `end_date`, `start_time`, `end_time`, `total_duration_hours`, `lunch_break`, `students_male`, `students_female`, `faculty_male`, `faculty_female`, `total_internal_male`, `total_internal_female`, `external_type`, `external_male`, `external_female`, `total_male`, `total_female`, `total_beneficiaries`, `approved_budget`, `source_of_budget`, `ps_attribution`, `sdgs`, `created_at`, `updated_at`) VALUES
(1, 'Lipa', 2028, 'Q1', 88, 'Test Project', 'Test Program', 'Test Activity', 'Test Location', '2025-03-27', '2025-03-27', '11:33:00', '15:33:00', 4.00, 'without', 1, 1, 2, 2, 3, 3, '0', 1, 1, 4, 4, 8, 1.00, 'GAA', 3636.40, '[\"SDG 1 - No Poverty\",\"SDG 2 - Zero Hunger\",\"SDG 3 - Good Health and Well-being\"]', '0000-00-00 00:00:00', '2025-03-27 03:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_personnel`
--

DROP TABLE IF EXISTS `ppas_personnel`;
CREATE TABLE IF NOT EXISTS `ppas_personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_form_id` int NOT NULL,
  `personnel_id` int NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_personnel`
--

INSERT INTO `ppas_personnel` (`id`, `ppas_form_id`, `personnel_id`, `role`, `created_at`) VALUES
(1, 1, 124, 'Project Leader', '2025-03-27 03:33:39'),
(2, 1, 117, 'Assistant Project Leader', '2025-03-27 03:33:39'),
(3, 1, 132, 'Staff', '2025-03-27 03:33:39'),
(4, 1, 132, 'Other Internal Participants', '2025-03-27 03:33:39');

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
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`id`, `year`, `campus`, `total_gaa`, `total_gad_fund`) VALUES
(120, '2025', 'Pablo Borbon', 1.00, 0.05),
(146, '2025', 'Alangilan', 500.00, 25.00),
(149, '2026', 'Alangilan', 1.00, 0.05),
(152, '2025', 'Lipa', 690.00, 34.50),
(156, '2026', 'Pablo Borbon', 999.00, 49.95),
(157, '2027', 'Pablo Borbon', 3.00, 0.15),
(158, '2028', 'Lipa', 500.00, 25.00),
(161, '2029', 'Lipa', 69.00, 3.45);

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
