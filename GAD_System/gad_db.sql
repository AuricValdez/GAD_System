-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 01, 2025 at 03:25 AM
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
  `project` varchar(255) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `year_quarter` (`year`,`quarter`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gpb_entries`
--

INSERT INTO `gpb_entries` (`id`, `category`, `gender_issue`, `cause_of_issue`, `gad_objective`, `relevant_agency`, `generic_activity`, `specific_activities`, `total_activities`, `male_participants`, `female_participants`, `total_participants`, `gad_budget`, `source_of_budget`, `responsible_unit`, `created_at`, `campus`, `year`) VALUES
(98, 'Client-Focused', 'Test', 'Test', 'Test', 'Higher Education Services', '[\"Test\"]', '[[\"test\"]]', 1, 1, 1, 2, 1.00, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-31 04:57:37', 'Lipa', 2025);

-- --------------------------------------------------------

--
-- Table structure for table `narrative_forms`
--

DROP TABLE IF EXISTS `narrative_forms`;
CREATE TABLE IF NOT EXISTS `narrative_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `ppas_activity_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `implementing_office` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `partner_agency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_agenda` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sdg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `beneficiaries` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `tasks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `general_objective` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `specific_objective` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activity_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activity_narrative` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `evaluation_result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `survey_result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `photos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `name`, `category`, `status`, `gender`, `academic_rank`, `campus`, `created_at`) VALUES
(136, 'Fryan Auric L. Valdez', 'Teaching', 'Guest Lecturer', 'male', 'Admin Aide 1', 'Lipa', '2025-03-31 04:57:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `campus`, `year`, `quarter`, `gender_issue_id`, `project`, `program`, `activity`, `location`, `start_date`, `end_date`, `start_time`, `end_time`, `total_duration_hours`, `lunch_break`, `students_male`, `students_female`, `faculty_male`, `faculty_female`, `total_internal_male`, `total_internal_female`, `external_type`, `external_male`, `external_female`, `total_male`, `total_female`, `total_beneficiaries`, `approved_budget`, `source_of_budget`, `ps_attribution`, `sdgs`, `created_at`, `updated_at`) VALUES
(14, 'Lipa', 2025, 'Q1', 98, 'Test', 'Test', 'Test', 'Test', '2025-03-31', '2025-03-31', '12:57:00', '17:57:00', 4.00, 'with', 1, 1, 4, 0, 5, 1, 'Test', 1, 1, 6, 2, 8, 1.00, 'MDS-GAD', 1818.24, '[\"SDG 1 - No Poverty\",\"SDG 2 - Zero Hunger\"]', '0000-00-00 00:00:00', '2025-03-31 04:58:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_personnel`
--

INSERT INTO `ppas_personnel` (`id`, `ppas_form_id`, `personnel_id`, `role`, `created_at`) VALUES
(23, 14, 136, 'Project Leader', '2025-03-31 04:58:14'),
(24, 14, 136, 'Assistant Project Leader', '2025-03-31 04:58:14'),
(25, 14, 136, 'Staff', '2025-03-31 04:58:14'),
(26, 14, 136, 'Other Internal Participants', '2025-03-31 04:58:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`id`, `year`, `campus`, `total_gaa`, `total_gad_fund`) VALUES
(163, '2025', 'Lipa', 100000.00, 5000.00);

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

--
-- Constraints for table `narrative_forms`
--
ALTER TABLE `narrative_forms`
  ADD CONSTRAINT `narrative_forms_ibfk_1` FOREIGN KEY (`ppas_id`) REFERENCES `ppas_forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
