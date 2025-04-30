-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 22, 2025 at 07:30 AM
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
) ENGINE=MyISAM AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_ranks`
--

INSERT INTO `academic_ranks` (`id`, `academic_rank`, `salary_grade`, `monthly_salary`) VALUES
(110, 'Instructor I', 8, '31000.00'),
(111, 'Instructor II', 9, '35000.00'),
(112, 'Instructor III', 10, '43000.00'),
(113, 'College Lecturer', 2, '25000.00'),
(114, 'Senior Lecturer', 2, '27500.00'),
(115, 'Master Lecturer', 5, '30000.00'),
(116, 'Assistant Professor I', 7, '34000.00'),
(117, 'Assistant Professor II', 8, '32500.00'),
(118, 'Assistant Professor III', 9, '38000.00'),
(119, 'Assistant Professor IV', 10, '40000.00'),
(120, 'Associate Professor I', 6, '35000.00'),
(121, 'Associate Professor II', 6, '37500.00'),
(122, 'Associate Professor III', 7, '39000.00'),
(123, 'Associate Professor IV', 8, '41000.00'),
(124, 'Associate Professor V', 9, '43000.00'),
(125, 'Professor I', 9, '40000.00'),
(126, 'Professor II', 1, '42500.00'),
(127, 'Professor III', 3, '45000.00'),
(128, 'Professor IV', 4, '47500.00'),
(129, 'Professor V', 5, '50000.00'),
(130, 'Professor VI', 6, '52500.00'),
(131, 'Admin Aide 1', 1, '21.00'),
(132, 'Admin Aide 2', 1, '21000.00'),
(133, 'Admin Aide 3', 2, '22000.00'),
(134, 'Admin Aide 4', 2, '23000.00'),
(135, 'Admin Aide 5', 3, '24000.00'),
(136, 'Admin Aide 6', 3, '25000.00'),
(137, 'Admin Asst 1', 4, '27000.00'),
(138, 'Admin Asst 2', 4, '29000.00'),
(139, 'Admin Asst 3', 5, '31000.00'),
(140, 'w', 2, '222.00');

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
  `proposal_id` int NOT NULL AUTO_INCREMENT,
  `ppas_form_id` int NOT NULL,
  `campus` varchar(100) NOT NULL,
  `mode_of_delivery` varchar(50) NOT NULL,
  `partner_office` varchar(255) NOT NULL,
  `rationale` text NOT NULL,
  `general_objectives` text NOT NULL,
  `description` text NOT NULL,
  `budget_breakdown` text NOT NULL,
  `sustainability_plan` text NOT NULL,
  `project_leader_responsibilities` json NOT NULL,
  `assistant_leader_responsibilities` json NOT NULL,
  `staff_responsibilities` json NOT NULL,
  `specific_objectives` json NOT NULL,
  `strategies` json NOT NULL,
  `methods` json NOT NULL,
  `materials` json NOT NULL,
  `workplan` json NOT NULL,
  `monitoring_items` json NOT NULL,
  `specific_plans` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`proposal_id`),
  KEY `idx_ppas_form_id` (`ppas_form_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposals`
--

INSERT INTO `gad_proposals` (`proposal_id`, `ppas_form_id`, `campus`, `mode_of_delivery`, `partner_office`, `rationale`, `general_objectives`, `description`, `budget_breakdown`, `sustainability_plan`, `project_leader_responsibilities`, `assistant_leader_responsibilities`, `staff_responsibilities`, `specific_objectives`, `strategies`, `methods`, `materials`, `workplan`, `monitoring_items`, `specific_plans`, `created_at`, `updated_at`) VALUES
(30, 1, 'Lipa', 'FacetoFace', 'Partners', 'Rationale', 'General Objective', 'Description', 'Budget Breakdown', 'Sustainability Plan', '[\"Project Leader Responsibilities 1\", \"Project Leader Responsibilities 2\", \"Project Leader Responsibilities 3\"]', '[\"Assistant Project Leader Responsibilities 1\", \"Assistant Project Leader Responsibilities 2\", \"Assistant Project Leader Responsibilities 3\"]', '[\"Project Staff Responsibilities 1\", \"Project Staff Responsibilities 2\", \"Project Staff Responsibilities3\"]', '[\"Specific Objectives 1\", \"Specific Objectives 2\"]', '[\"Strategies 1\", \"Strategies 2\", \"Strategies 3\"]', '[[\"Activity Name 1\", [\"Activity Details 1\", \"Activity Details 2\"]], [\"Activity Name 2\", [\"Activity Details 1\"]]]', '[\"Material 1\", \"Material 2\", \"Material 3\"]', '[[\"Work plan 1\", [\"2025-04-03\"]], [\"Work plan 2\", [\"2025-04-04\", \"2025-04-05\", \"2025-04-06\"]]]', '[[\"Objectives 1\", \"Performance Indicators 1\", \"Baseline Data 1\", \"Performance Target 1\", \"Data Source 1\", \"Collection Method 1\", \"Frequency of Data Collection 1\", \"Office/Persons Responsible 1\"], [\"Objectives 2\", \"Performance Indicators 2\", \"Baseline Data 2\", \"Performance Target 2\", \"Data Source 2\", \"Collection Method 2\", \"Frequency of Data Collection 2\", \"Office/Persons Responsible 2\"]]', '[\"Specific Plans 1\", \"Specific Plans 2\", \"Specific Plans 3\"]', '2025-04-07 05:38:32', '2025-04-22 01:55:56');

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
(71, 'Attributable PAPs', 'Gender Equality: Journey', 'Gender Equality test cause', 'Gender Equality test GAD result', 'Technical Advisory Extension Services', '[\"Gender Equality Program #1\",\"Test #2\",\"Test #3\"]', '[[\"Jumping Jacks\",\"Test\"],[\"Test2\"],[\"Test3\",\"Test4\"]]', 5, 10, 20, 30, '6900.50', 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-24 00:11:28', 'Lipa', 2025),
(82, 'Organization-Focused', 'Advocation of Women\'s Month: 2026', '1', '1', 'Technical Advisory Extension Services', '[\"1\"]', '[[\"1\",\"2\"]]', 2, 1, 1, 2, '1.00', 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-24 22:17:00', 'Lipa', 2025),
(96, 'Client-Focused', 'Advocation of Women\'s Month: 2025', 'Elbert Nebres is so gay.', 'Elbert Nebres is super gay.', 'Higher Education Services', '[\"Rehabilitation of Elbert\",\"Vaping of Elbert\"]', '[[\"Gym\",\"Hotdog 3 sets\"],[\"Vaping Tricks 101\"]]', 3, 10, 20, 30, '690.00', 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-26 06:42:31', 'Lipa', 2025),
(88, 'Client-Focused', 'test gender issue', 'test cause', 'test gad result', 'Higher Education Services', '[\"test program 1\",\"test program 2\"]', '[[\"test activity 1\",\"test activity 2\",\"1\"],[\"test activity 3\",\"1\"]]', 5, 5, 5, 10, '420.00', 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-25 14:03:32', 'Lipa', 2028),
(89, 'Client-Focused', 'Advocation of Women\'s Month: 2026', '1', '1', 'Higher Education Services', '[\"1\"]', '[[\"1\",\"2\"]]', 2, 1, 1, 2, '1.00', 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-03-25 15:19:57', 'Alangilan', 2025);

-- --------------------------------------------------------

--
-- Table structure for table `narrative`
--

DROP TABLE IF EXISTS `narrative`;
CREATE TABLE IF NOT EXISTS `narrative` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_form_id` int NOT NULL,
  `campus` varchar(100) NOT NULL,
  `implementing_office` text NOT NULL,
  `partner_agency` varchar(255) DEFAULT NULL,
  `extension_service_agenda` text NOT NULL,
  `type_beneficiaries` varchar(255) NOT NULL,
  `beneficiary_distribution` json NOT NULL,
  `leader_tasks` json NOT NULL,
  `assistant_tasks` json NOT NULL,
  `staff_tasks` json NOT NULL,
  `activity_narrative` text NOT NULL,
  `activity_ratings` json NOT NULL,
  `timeliness_ratings` json NOT NULL,
  `activity_images` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ppas_form_id` (`ppas_form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `narrative`
--

INSERT INTO `narrative` (`id`, `ppas_form_id`, `campus`, `implementing_office`, `partner_agency`, `extension_service_agenda`, `type_beneficiaries`, `beneficiary_distribution`, `leader_tasks`, `assistant_tasks`, `staff_tasks`, `activity_narrative`, `activity_ratings`, `timeliness_ratings`, `activity_images`, `created_at`) VALUES
(37, 18, 'Lipa', '[\"College of Informatics and Computing Sciences\",\"College of Nursing and Allied Health Sciences\"]', 'Partner Agency', '[1,1,1,1,0,0,0,0,0,0,0,0]', 'Beneficiaries', '{\"maleOthers\": \"2\", \"femaleOthers\": \"2\", \"maleBatStateU\": \"1\", \"femaleBatStateU\": \"1\"}', '[\"Project Leader\", \"Project Leader\"]', '[\"Assistant Project Leader\", \"Assistant Project Leader\"]', '[\"Project Staff\", \"Project Staff\"]', 'Narrative of the Activity', '{\"Fair\": {\"Others\": 2, \"BatStateU\": 1}, \"Poor\": {\"Others\": 2, \"BatStateU\": 1}, \"Excellent\": {\"Others\": 2, \"BatStateU\": 1}, \"Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}, \"Very Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}}', '{\"Fair\": {\"Others\": 2, \"BatStateU\": 1}, \"Poor\": {\"Others\": 2, \"BatStateU\": 1}, \"Excellent\": {\"Others\": 2, \"BatStateU\": 1}, \"Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}, \"Very Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}}', '[\"narrative_18_1745218599_0.jpg\", \"narrative_18_1745218599_1.jpg\"]', '2025-04-21 06:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `narrative_forms`
--

DROP TABLE IF EXISTS `narrative_forms`;
CREATE TABLE IF NOT EXISTS `narrative_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `ppas_activity_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `project_leader` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `assistant_leader` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coordinators` text COLLATE utf8mb4_general_ci,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `implementing_office` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `partner_agency` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_agenda` text COLLATE utf8mb4_general_ci,
  `sdg` text COLLATE utf8mb4_general_ci,
  `beneficiaries` text COLLATE utf8mb4_general_ci,
  `tasks` text COLLATE utf8mb4_general_ci,
  `general_objective` text COLLATE utf8mb4_general_ci NOT NULL,
  `specific_objective` text COLLATE utf8mb4_general_ci NOT NULL,
  `activity_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `activity_narrative` text COLLATE utf8mb4_general_ci NOT NULL,
  `evaluation_result` text COLLATE utf8mb4_general_ci NOT NULL,
  `evaluation_data` json DEFAULT NULL,
  `timeliness_data` json DEFAULT NULL,
  `survey_result` text COLLATE utf8mb4_general_ci NOT NULL,
  `photos` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`),
  KEY `username` (`username`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_username_created` (`username`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `campus`, `year`, `quarter`, `gender_issue_id`, `project`, `program`, `activity`, `location`, `start_date`, `end_date`, `start_time`, `end_time`, `total_duration_hours`, `lunch_break`, `students_male`, `students_female`, `faculty_male`, `faculty_female`, `total_internal_male`, `total_internal_female`, `external_type`, `external_male`, `external_female`, `total_male`, `total_female`, `total_beneficiaries`, `approved_budget`, `source_of_budget`, `ps_attribution`, `sdgs`, `created_at`, `updated_at`) VALUES
(1, 'Lipa', 2028, 'Q1', 88, 'Test Project', 'Test Program', 'Test Activity', 'Test Location', '2025-03-27', '2025-03-27', '11:33:00', '15:33:00', '4.00', 'without', 1, 1, 2, 2, 3, 3, '0', 1, 1, 4, 4, 8, '1.00', 'GAA', '3636.40', '[\"SDG 1 - No Poverty\",\"SDG 2 - Zero Hunger\",\"SDG 3 - Good Health and Well-being\"]', '0000-00-00 00:00:00', '2025-03-27 03:33:39'),
(2, 'Lipa', 2028, 'Q1', 88, 'Test Project', 'Test Program1', 'Test Activity1', 'Test Location', '2025-03-27', '2025-03-28', '11:33:00', '15:33:00', '8.00', 'without', 1, 1, 2, 2, 3, 3, '0', 1, 1, 4, 4, 8, '1.00', 'GAA', '3636.40', '[\"SDG 1 - No Poverty\",\"SDG 2 - Zero Hunger\",\"SDG 3 - Good Health and Well-being\"]', '0000-00-00 00:00:00', '2025-03-28 01:29:19'),
(3, 'Lipa', 2028, 'Q1', 71, 'Project A', 'Program A', 'Activity A', 'Location A', '2025-04-01', '2025-04-01', '10:00:00', '14:00:00', '4.00', 'without', 2, 3, 2, 3, 4, 6, '0', 2, 2, 6, 8, 14, '2.00', 'GAA', '4000.50', '[\"SDG 1 - No Poverty\"]', '0000-00-00 00:00:00', '2025-04-01 04:00:00'),
(4, 'Lipa', 2028, 'Q1', 88, 'Project B', 'Program B', 'Activity B', 'Location B', '2025-05-15', '2025-05-15', '09:30:00', '13:30:00', '4.00', 'without', 3, 2, 1, 4, 4, 6, '0', 3, 2, 7, 8, 15, '2.50', 'GAA', '3500.75', '[\"SDG 2 - Zero Hunger\"]', '0000-00-00 00:00:00', '2025-03-28 01:29:23'),
(5, 'Lipa', 2028, 'Q3', 96, 'Project C', 'Program C', 'Activity C', 'Location C', '2025-06-20', '2025-06-20', '11:00:00', '15:00:00', '4.00', 'without', 4, 4, 2, 2, 6, 6, '0', 3, 3, 9, 9, 18, '3.00', 'GAA', '3200.80', '[\"SDG 3 - Good Health and Well-being\"]', '0000-00-00 00:00:00', '2025-06-20 06:00:00'),
(6, 'Lipa', 2028, 'Q1', 88, 'Project D', 'Program D', 'Activity D', 'Location D', '2025-07-10', '2025-07-10', '12:00:00', '16:00:00', '4.00', 'without', 2, 2, 3, 3, 5, 5, '0', 4, 3, 9, 8, 17, '3.50', 'GAA', '3100.60', '[\"SDG 4 - Quality Education\"]', '0000-00-00 00:00:00', '2025-03-28 01:29:28'),
(7, 'Lipa', 2028, 'Q1', 89, 'Project E', 'Program E', 'Activity E', 'Location E', '2025-08-05', '2025-08-05', '13:00:00', '17:00:00', '4.00', 'without', 5, 5, 2, 2, 7, 7, '0', 2, 4, 9, 11, 20, '4.00', 'GAA', '3600.40', '[\"SDG 5 - Gender Equality\"]', '0000-00-00 00:00:00', '2025-08-05 08:00:00'),
(8, 'Lipa', 2028, 'Q1', 88, 'Project F', 'Program F', 'Activity F', 'Location F', '2025-09-12', '2025-09-12', '08:30:00', '12:30:00', '4.00', 'without', 3, 3, 1, 5, 4, 8, '0', 4, 2, 8, 10, 18, '4.50', 'GAA', '3700.20', '[\"SDG 6 - Clean Water and Sanitation\"]', '0000-00-00 00:00:00', '2025-03-28 01:29:33'),
(9, 'Lipa', 2028, 'Q3', 82, 'Project G', 'Program G', 'Activity G', 'Location G', '2025-10-22', '2025-10-22', '14:00:00', '18:00:00', '4.00', 'without', 4, 3, 3, 2, 7, 5, '0', 2, 3, 9, 8, 17, '5.00', 'GAA', '3800.90', '[\"SDG 7 - Affordable and Clean Energy\"]', '0000-00-00 00:00:00', '2025-10-22 09:00:00'),
(10, 'Lipa', 2028, 'Q4', 96, 'Project H', 'Program H', 'Activity H', 'Location H', '2025-11-30', '2025-11-30', '15:00:00', '19:00:00', '4.00', 'without', 2, 4, 2, 3, 4, 7, '0', 3, 3, 7, 10, 17, '5.50', 'GAA', '3900.30', '[\"SDG 8 - Decent Work and Economic Growth\"]', '0000-00-00 00:00:00', '2025-11-30 10:00:00'),
(11, 'Lobo', 2028, 'Q1', 88, 'Project I', 'Program I', 'Activity I', 'Location I', '2026-01-15', '2026-01-15', '10:30:00', '14:30:00', '4.00', 'without', 5, 2, 3, 2, 8, 4, '0', 4, 4, 12, 6, 18, '6.00', 'GAA', '4000.00', '[\"SDG 9 - Industry, Innovation and Infrastructure\"]', '0000-00-00 00:00:00', '2025-04-15 07:03:26'),
(12, 'Alangilan', 2028, 'Q2', 89, 'Project J', 'Program J', 'Activity J', 'Location J', '2026-02-20', '2026-02-20', '09:00:00', '13:00:00', '4.00', 'without', 3, 5, 3, 4, 6, 9, '0', 3, 2, 9, 11, 20, '6.50', 'GAA', '4100.10', '[\"SDG 10 - Reduced Inequality\"]', '0000-00-00 00:00:00', '2025-04-15 06:59:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_personnel`
--

INSERT INTO `ppas_personnel` (`id`, `ppas_form_id`, `personnel_id`, `role`, `created_at`) VALUES
(1, 1, 124, 'Project Leader', '2025-03-27 03:33:39'),
(2, 1, 117, 'Assistant Project Leader', '2025-03-27 03:33:39'),
(3, 1, 132, 'Staff', '2025-03-27 03:33:39'),
(4, 1, 132, 'Other Internal Participants', '2025-03-27 03:33:39'),
(5, 2, 124, 'Project Leader', '2025-03-26 19:33:39'),
(6, 2, 117, 'Assistant Project Leader', '2025-03-26 19:33:39'),
(7, 2, 132, 'Staff', '2025-03-26 19:33:39'),
(8, 2, 132, 'Other Internal Participants', '2025-03-26 19:33:39'),
(9, 3, 125, 'Project Leader', '2025-04-01 04:00:00'),
(10, 3, 118, 'Assistant Project Leader', '2025-04-01 04:00:00'),
(11, 3, 133, 'Staff', '2025-04-01 04:00:00'),
(12, 3, 134, 'Other Internal Participants', '2025-04-01 04:00:00'),
(13, 4, 126, 'Project Leader', '2025-05-15 03:30:00'),
(14, 4, 119, 'Assistant Project Leader', '2025-05-15 03:30:00'),
(15, 4, 135, 'Staff', '2025-05-15 03:30:00'),
(16, 4, 136, 'Other Internal Participants', '2025-05-15 03:30:00'),
(17, 5, 127, 'Project Leader', '2025-06-20 06:00:00'),
(18, 5, 120, 'Assistant Project Leader', '2025-06-20 06:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_name` (`program_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `campus` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `name1`, `gad_head_secretariat`, `name2`, `vice_chancellor_rde`, `name3`, `chancellor`, `name4`, `asst_director_gad`, `name5`, `head_extension_services`, `campus`, `created_at`, `updated_at`) VALUES
(3, 'Micah Reynolds\n\n', 'GAD Head Secretariat', 'Julian Hayes', 'Vice Chancellor For Research, Development and Extension', 'Aria Sullivan\n\n', 'Chancellor', 'Ezra Mitchell\n\n', 'Assistant Director For GAD Advocacies', 'Lila Gardner\n\n', 'Head of Extension Services', 'Malvar', '2025-04-03 08:25:31', '2025-04-07 00:27:02'),
(4, 'Liam Carterseeew', 'GAD Head Secretariat', 'Ava Thompsonhwwwee', 'Vice Chancellor For Research, Development and Extension', 'Noah Reyesew', 'Chancellor', 'Maya Collinsnww', 'Assistant Director For GAD Advocacies', 'Elijah Brooks3wwww', 'Head of Extension Services', 'Lipa', '2025-04-03 08:35:45', '2025-04-22 07:10:06'),
(5, 'Sofia Bennett', 'GAD Head Secretariat', 'Caleb Navarro', 'Vice Chancellor For Research, Development and Extension', 'Chloe Ramsey', 'Chancellor', 'Lucas Avery', 'Assistant Director For GAD Advocacies', 'Isla Monroe', 'Head of Extension Services', 'Alangilan', '2025-04-07 00:10:02', '2025-04-07 00:10:02'),
(6, 'Elena Harper', 'GAD Head Secretariat', 'Elena Harper', 'Vice Chancellor For Research, Development and Extension', 'Nina Caldwell', 'Chancellor', 'Owen Blake', 'Assistant Director For GAD Advocacies', 'Zoe Chambers', 'Head of Extension Services', 'Rosario', '2025-04-07 03:01:41', '2025-04-07 03:01:41');

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
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`id`, `year`, `campus`, `total_gaa`, `total_gad_fund`) VALUES
(120, 2025, 'Pablo Borbon', '1.00', '0.05'),
(146, 2025, 'Alangilan', '500.00', '25.00'),
(149, 2026, 'Alangilan', '1.00', '0.05'),
(156, 2026, 'Pablo Borbon', '999.00', '49.95'),
(157, 2027, 'Pablo Borbon', '3.00', '0.15'),
(158, 2028, 'Lipa', '500.00', '25.00'),
(161, 2029, 'Lipa', '69.00', '3.45'),
(162, 2026, 'Lipa', '2222.00', '111.10');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `narrative_forms`
--
ALTER TABLE `narrative_forms`
  ADD CONSTRAINT `narrative_forms_ibfk_1` FOREIGN KEY (`ppas_id`) REFERENCES `ppas_forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
