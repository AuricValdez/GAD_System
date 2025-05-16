-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 16, 2025 at 04:44 AM
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
) ENGINE=MyISAM AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_ranks`
--

INSERT INTO `academic_ranks` (`id`, `academic_rank`, `salary_grade`, `monthly_salary`) VALUES
(110, 'Instructor I', 8, 31000.00),
(111, 'Instructor II', 9, 35000.00),
(112, 'Instructor III', 10, 43000.00),
(113, 'College Lecturer', 2, 25000.00),
(114, 'Senior Lecturer', 2, 27500.00),
(115, 'Master Lecturer', 5, 30000.00),
(116, 'Assistant Professor I', 7, 34000.00),
(117, 'Assistant Professor II', 8, 32500.00),
(118, 'Assistant Professor III', 9, 38000.00),
(119, 'Assistant Professor IV', 10, 40000.00),
(120, 'Associate Professor I', 6, 35000.00),
(121, 'Associate Professor II', 6, 37500.00),
(122, 'Associate Professor III', 7, 39000.00),
(123, 'Associate Professor IV', 8, 41000.00),
(124, 'Associate Professor V', 9, 43000.00),
(125, 'Professor I', 9, 40000.00),
(126, 'Professor II', 1, 42500.00),
(127, 'Professor III', 3, 45000.00),
(128, 'Professor IV', 4, 47500.00),
(129, 'Professor V', 5, 50000.00),
(130, 'Professor VI', 6, 52500.00),
(131, 'Admin Aide 1', 1, 20000.00),
(132, 'Admin Aide 2', 1, 21000.00),
(133, 'Admin Aide 3', 2, 22000.00),
(134, 'Admin Aide 4', 2, 23000.00),
(135, 'Admin Aide 5', 3, 24000.00),
(136, 'Admin Aide 6', 3, 25000.00),
(137, 'Admin Asst 1', 4, 27000.00),
(138, 'Admin Asst 2', 4, 29000.00),
(139, 'Admin Asst 3', 5, 31000.00);

-- --------------------------------------------------------

--
-- Table structure for table `central_notifications`
--

DROP TABLE IF EXISTS `central_notifications`;
CREATE TABLE IF NOT EXISTS `central_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gbp_id` int NOT NULL,
  `campus` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `campus` (`campus`(250)),
  KEY `gbp_id` (`gbp_id`),
  KEY `is_read` (`is_read`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Table structure for table `gbp_notifications`
--

DROP TABLE IF EXISTS `gbp_notifications`;
CREATE TABLE IF NOT EXISTS `gbp_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gbp_id` int NOT NULL,
  `campus` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gbp_id` (`gbp_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `status` varchar(100) NOT NULL,
  `feedback` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `reply` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppas_forms`
--

DROP TABLE IF EXISTS `ppas_forms`;
CREATE TABLE IF NOT EXISTS `ppas_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `quarter` varchar(50) NOT NULL,
  `gender_issue_id` int NOT NULL,
  `program` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL,
  `activity` varchar(500) NOT NULL,
  `location` varchar(255) NOT NULL,
  `start_date` varchar(20) NOT NULL,
  `end_date` varchar(20) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `lunch_break` tinyint(1) DEFAULT '0',
  `total_duration` varchar(100) NOT NULL,
  `mode_of_delivery` varchar(255) NOT NULL,
  `agenda` text NOT NULL,
  `sdg` json NOT NULL,
  `office_college_organization` json NOT NULL,
  `program_list` json NOT NULL,
  `project_leader` json NOT NULL,
  `project_leader_responsibilities` json NOT NULL,
  `assistant_project_leader` json NOT NULL,
  `assistant_project_leader_responsibilities` json NOT NULL,
  `project_staff_coordinator` json NOT NULL,
  `project_staff_coordinator_responsibilities` json NOT NULL,
  `internal_type` varchar(255) NOT NULL,
  `internal_male` int NOT NULL,
  `internal_female` int NOT NULL,
  `internal_total` int NOT NULL,
  `external_type` varchar(255) NOT NULL,
  `external_male` int NOT NULL,
  `external_female` int NOT NULL,
  `external_total` int NOT NULL,
  `grand_total_male` int NOT NULL,
  `grand_total_female` int NOT NULL,
  `grand_total` int NOT NULL,
  `rationale` text NOT NULL,
  `general_objectives` text NOT NULL,
  `specific_objectives` json NOT NULL,
  `description` text NOT NULL,
  `strategy` json NOT NULL,
  `expected_output` json NOT NULL,
  `functional_requirements` text NOT NULL,
  `sustainability_plan` text NOT NULL,
  `specific_plan` json NOT NULL,
  `workplan_activity` json NOT NULL,
  `workplan_date` json NOT NULL,
  `financial_plan` tinyint(1) DEFAULT '0',
  `financial_plan_items` json NOT NULL,
  `financial_plan_quantity` json NOT NULL,
  `financial_plan_unit` json NOT NULL,
  `financial_plan_unit_cost` json NOT NULL,
  `financial_total_cost` varchar(50) NOT NULL,
  `source_of_fund` json NOT NULL,
  `financial_note` text NOT NULL,
  `approved_budget` double NOT NULL,
  `ps_attribution` varchar(255) NOT NULL,
  `monitoring_objectives` json NOT NULL,
  `monitoring_baseline_data` json NOT NULL,
  `monitoring_data_source` json NOT NULL,
  `monitoring_frequency_data_collection` json NOT NULL,
  `monitoring_performance_indicators` json NOT NULL,
  `monitoring_performance_target` json NOT NULL,
  `monitoring_collection_method` json NOT NULL,
  `monitoring_office_persons_involved` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signatories`
--

DROP TABLE IF EXISTS `signatories`;
CREATE TABLE IF NOT EXISTS `signatories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name1` varchar(255) NOT NULL,
  `gad_head_secretariat` varchar(255) NOT NULL,
  `name2` varchar(255) NOT NULL,
  `vice_chancellor_rde` varchar(255) NOT NULL,
  `name3` varchar(255) NOT NULL,
  `chancellor` varchar(255) NOT NULL,
  `name4` varchar(255) NOT NULL,
  `asst_director_gad` varchar(255) NOT NULL,
  `name5` varchar(255) NOT NULL,
  `head_extension_services` varchar(255) NOT NULL,
  `name6` varchar(255) DEFAULT NULL,
  `vice_chancellor_admin_finance` varchar(255) DEFAULT NULL,
  `campus` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name7` varchar(255) DEFAULT NULL,
  `dean` varchar(255) DEFAULT 'Dean',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `target`
--

DROP TABLE IF EXISTS `target`;
CREATE TABLE IF NOT EXISTS `target` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` year NOT NULL,
  `campus` enum('Lipa','Pablo Borbon','Alangilan','Nasugbu','Malvar','Rosario','Balayan','Lemery','San Juan','Lobo','Central') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_gaa` decimal(15,2) NOT NULL,
  `total_gad_fund` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_year_campus` (`year`,`campus`),
  KEY `idx_year` (`year`),
  KEY `idx_campus` (`campus`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
