-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 04, 2025 at 01:01 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposals`
--

INSERT INTO `gad_proposals` (`id`, `year`, `quarter`, `activity_title`, `start_date`, `end_date`, `venue`, `delivery_mode`, `ppas_id`, `project_leaders`, `leader_responsibilities`, `assistant_project_leaders`, `assistant_responsibilities`, `project_staff`, `staff_responsibilities`, `partner_offices`, `male_beneficiaries`, `female_beneficiaries`, `total_beneficiaries`, `rationale`, `specific_objectives`, `strategies`, `budget_source`, `total_budget`, `budget_breakdown`, `sustainability_plan`, `created_by`, `created_at`, `updated_at`, `project`, `program`) VALUES
(1, 2028, 'Q1', 'Sample GAD Activity', '2025-04-28', '2025-04-30', 'Campus Auditorium', 'face-to-face', NULL, 'John Doe, Jane Smith', 'Lead the project\nEnsure all objectives are met', 'Alice Johnson, Bob Williams', 'Assist the project leader\nCoordinate with the staff', 'Charlie Brown, Diana Prince, Edward Norton', 'Implement the project activities\nProvide support as needed\nDocument the project', 'Student Affairs Office, Academic Affairs Office', 50, 50, 100, 'This is a sample rationale for the GAD activity.\nIt explains why the activity is needed and how it will benefit the community.', 'Increase awareness of gender issues\nPromote gender equality\nDevelop action plans', 'Conduct workshops\nDistribute information materials\nEngage in community discussions', 'GAA', '50000.00', 'Materials: PHP 15,000\nFood: PHP 20,000\nSpeakers: PHP 10,000\nVenue: PHP 5,000', 'Regular follow-up activities\nIntegration with existing programs\nMonitoring and evaluation', 'Lipa', '2025-03-28 14:30:10', '2025-03-28 14:45:11', 'Gender Awareness Project', 'University Gender Program'),
(2, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'e', 'Elbert D. Nebres', 'ee', 'Test', 'e', 'd', 1, 1, 2, 'e', 'e', 'e', 'GAA', '222.00', '2', 'h', 'Lipa', '2025-03-28 14:37:45', NULL, 'Test Project', 'Test Program1'),
(3, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'e', 'Elbert D. Nebres', 'ee', 'Test', 'e', 'd', 1, 1, 2, 'e', 'e', 'e', 'GAA', '222.00', '2', 'h', 'Lipa', '2025-03-28 14:37:54', NULL, 'Test Project', 'Test Program1'),
(4, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'e', 'Elbert D. Nebres', 'ee', 'Test', 'e', 'd', 1, 1, 2, 'e', 'e', 'e', 'GAA', '222.00', '2', 'h', 'Lipa', '2025-03-28 14:39:05', NULL, 'Test Project', 'Test Program1'),
(5, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'hybrid', NULL, 'Fryan Auric L. Valdez', 'to ', 'Elbert D. Nebres', 'ot', 'Test', 'tw', '2f', 1, 1, 2, 'wdw', 'aaa', 'w', 'External', '99999999.99', 'w', 'llwlala\r\n', 'Lipa', '2025-03-28 14:43:00', NULL, 'Test Project', 'Test Program1'),
(6, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'ikaw', 'Elbert D. Nebres', 'ikaw', 'Test', 'ikaw', 'ikaw', 1, 1, 2, 'ikaw', 'ikaw', 'vikaw', 'Income', '2.00', 'ddd', 'ikaw', 'Lipa', '2025-03-28 14:58:31', NULL, 'Test Project', 'Test Program1'),
(7, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'hybrid', NULL, 'Fryan Auric L. Valdez', 'helloa', 'Elbert D. Nebres', 'helloa', 'Test', 'helloa', 'helloa', 1, 1, 2, 'helloa', 'helloa', 'helloa', 'GAA', '2.00', 'e', 'helloa', 'Lipa', '2025-03-28 15:18:57', '2025-03-28 15:25:07', 'Test Project', 'Test Program1'),
(8, 2028, 'Q1', 'Activity B', '2025-05-15', '2025-05-15', 'Location B', 'hybrid', NULL, '', 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', '', 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', 'ddddd', 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', 'Form Debugging Current Field Values: {   \"Selected Activity\": \"4\",   \"Activity Text\": \"Activity B\",   \"Activity Title\": \"Activity B\",   \"Project\": \"Project B\",   \"Program\": \"Program B\",   \"Start Date\": \"\",   \"End Date\": \"\",   \"Venue\": \"\" } Selected Option', 3, 2, 5, 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', 'Form Debugging\r\nCurrent Field Values:\r\n{\r\n  \"Selected Activity\": \"4\",\r\n  \"Activity Text\": \"Activity B\",\r\n  \"Activity Title\": \"Activity B\",\r\n  \"Project\": \"Project B\",\r\n  \"Program\": \"Program B\",\r\n  \"Start Date\": \"\",\r\n  \"End Date\": \"\",\r\n  \"Venue\": \"\"\r\n}\r\nSelected Option Data Attributes:\r\n{\r\n  \"Option dataset.project\": \"Project B\",\r\n  \"Option dataset.program\": \"Program B\",\r\n  \"Option dataset.startDate\": \"\",\r\n  \"Option dataset.endDate\": \"\",\r\n  \"Option dataset.location\": \"\"\r\n}', 'GAA', '200000.00', 'helloa', 'helloae', 'Lipa', '2025-03-28 15:26:49', '2025-04-02 16:21:22', 'Project B', 'Program B'),
(20, 2028, 'Q1', 'Activity B', '2025-05-15', '2025-05-15', 'Location B', 'online', NULL, '', 'eeeeewww', '', 'eeeewww', 'ddddd', 'eeeewww', '2eeeww', 3, 2, 5, 'wwwww', '2wweeeeewwww', '2eeeeww', 'Income', '222.00', '2weeeeewww', '2weeeeefffffffwwww', 'Lipa', '2025-04-02 13:10:03', '2025-04-02 15:34:20', 'Project B', 'Program B'),
(21, 2028, 'Q1', 'Activity B', '2025-05-15', '2025-05-15', 'Location B', 'online', NULL, '', 'e', '', 'e', 'ddddd', 'e', '2', 3, 2, 5, '2', '2', '2', 'Income', '222.00', '2', '2', 'Lipa', '2025-04-02 13:10:03', '2025-04-02 14:35:28', 'Project B', 'Program B'),
(22, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'face-to-face', NULL, 'Fryan Auric L. Valdez', 'fe', 'Elbert D. Nebres', 'fe', 'Test', 'dw', 'w', 1, 1, 2, 'ww', 'www', 'ww', 'Income', '2.00', 'w', 'w', 'Lipa', '2025-04-02 15:37:25', NULL, 'Test Project', 'Test Program1'),
(23, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'face-to-face', NULL, 'Fryan Auric L. Valdez', 'fe', 'Elbert D. Nebres', 'fe', 'Test', 'dw', 'w', 1, 1, 2, 'ww', 'www', 'ww', 'Income', '2.00', 'w', 'w', 'Lipa', '2025-04-02 15:37:25', NULL, 'Test Project', 'Test Program1'),
(24, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'face-to-face', NULL, 'Fryan Auric L. Valdez', 'fe', 'Elbert D. Nebres', 'fe', 'Test', 'dw', 'w', 1, 1, 2, 'ww', 'www', 'ww', 'Income', '2.00', 'w', 'w', 'Lipa', '2025-04-02 15:37:25', NULL, 'Test Project', 'Test Program1'),
(25, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'lol', 'Elbert D. Nebres', 'lol', 'Test', 'lol', 'lol', 1, 1, 2, 'lol', 'lol', 'lol', 'Income', '222.00', 'lol', 'lol', 'Lipa', '2025-04-02 15:46:42', NULL, 'Test Project', 'Test Program1'),
(26, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'lol', 'Elbert D. Nebres', 'lol', 'Test', 'lol', 'lol', 1, 1, 2, 'lol', 'lol', 'lol', 'Income', '222.00', 'lol', 'lol', 'Lipa', '2025-04-02 15:46:42', NULL, 'Test Project', 'Test Program1'),
(27, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'online', NULL, 'Fryan Auric L. Valdez', 'lol', 'Elbert D. Nebres', 'lol', 'Test', 'lol', 'lol', 1, 1, 2, 'lol', 'lol', 'lol', 'Income', '222.00', 'lol', 'lol', 'Lipa', '2025-04-02 15:46:42', NULL, 'Test Project', 'Test Program1'),
(28, 2028, 'Q1', 'Activity D', '2025-07-10', '2025-07-10', 'Location D', 'face-to-face', NULL, '', 'lol', '', 'lol', '', 'lol', 'lol', 4, 3, 7, 'lol', 'lol', 'lol', 'Income', '2222.00', 'lol', 'lol', 'Lipa', '2025-04-02 16:03:28', '2025-04-03 08:04:24', 'Project D', 'Program D'),
(29, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'face-to-face', NULL, 'Fryan Auric L. Valdez', 'lol', 'Elbert D. Nebres', 'lol', 'Test', 'lol', 'lol', 1, 1, 2, 'lol', 'lol', 'lol', 'Income', '2222.00', 'lol', 'lol', 'Lipa', '2025-04-02 16:03:28', NULL, 'Test Project', 'Test Program1'),
(30, 2028, 'Q1', 'Test Activity1', '2025-03-27', '2025-03-28', 'Test Location', 'face-to-face', NULL, 'Fryan Auric L. Valdez', 'lol', 'Elbert D. Nebres', 'lol', 'Test', 'lol', 'lol', 1, 1, 2, 'lol', 'lol', 'lol', 'Income', '2222.00', 'lol', 'lol', 'Lipa', '2025-04-02 16:03:28', NULL, 'Test Project', 'Test Program1');

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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_activities`
--

INSERT INTO `gad_proposal_activities` (`id`, `proposal_id`, `title`, `details`, `sequence`, `created_at`) VALUES
(1, 1, 'Opening Ceremony', 'Welcome remarks\\nIntroduction of participants\\nOverview of the program', 0, '2025-03-28 06:30:10'),
(2, 1, 'Workshop 1: Gender Awareness', 'Discussion of key concepts\\nGroup activities\\nSharing of experiences', 1, '2025-03-28 06:30:10'),
(3, 1, 'Workshop 2: Action Planning', 'Development of action plans\\nPresentations\\nFeedback sessions', 2, '2025-03-28 06:30:10'),
(4, 2, 'e', 'e', 0, '2025-03-28 06:37:45'),
(5, 2, 'e', 'e', 1, '2025-03-28 06:37:45'),
(6, 3, 'e', 'e', 0, '2025-03-28 06:37:54'),
(7, 3, 'e', 'e', 1, '2025-03-28 06:37:54'),
(8, 4, 'e', 'e', 0, '2025-03-28 06:39:05'),
(9, 4, 'e', 'e', 1, '2025-03-28 06:39:05'),
(10, 5, 'wff', 'f', 0, '2025-03-28 06:43:00'),
(11, 6, 'ikaw', 'ikaw', 0, '2025-03-28 06:58:31'),
(13, 7, 'helloa', 'vhelloa', 0, '2025-03-28 07:25:07'),
(56, 21, 'e', 'e', 0, '2025-04-02 06:35:28'),
(75, 29, 'Fallback Activity 1', 'Generated fallback activity due to missing inputs', 0, '2025-04-02 08:03:28'),
(76, 29, 'Work Plan: lol', 'Timeline: No specific days selected', 1, '2025-04-02 08:03:28'),
(77, 29, 'Work Plan: lol', 'Timeline: 2', 2, '2025-04-02 08:03:28'),
(78, 30, 'Fallback Activity 1', 'Generated fallback activity due to missing inputs', 0, '2025-04-02 08:03:28'),
(79, 30, 'Work Plan: lol', 'Timeline: No specific days selected', 1, '2025-04-02 08:03:28'),
(80, 30, 'Work Plan: lol', 'Timeline: 2', 2, '2025-04-02 08:03:28'),
(87, 8, 'helloa', 'helloa', 0, '2025-04-02 08:21:22'),
(88, 8, 'Work Plan: helloa', 'Timeline: 2', 1, '2025-04-02 08:21:22'),
(89, 8, 'Work Plan: helloa', 'Timeline: 1', 2, '2025-04-02 08:21:22'),
(92, 28, 'i hate this', 'i hate this', 0, '2025-04-03 00:04:24');

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
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_monitoring`
--

INSERT INTO `gad_proposal_monitoring` (`id`, `proposal_id`, `objectives`, `performance_indicators`, `baseline_data`, `performance_target`, `data_source`, `collection_method`, `frequency`, `responsible_office`, `sequence`) VALUES
(1, 1, 'Increase awareness', 'Number of participants who show improved understanding', '50% of participants have basic awareness', '90% of participants have improved awareness', 'Pre and post tests', 'Surveys', 'Before and after the activity', 'GAD Office', 0),
(2, 1, 'Develop action plans', 'Number of action plans developed', 'No existing action plans', 'At least 10 action plans', 'Submitted action plans', 'Document review', 'At the end of the activity', 'Planning Office', 1),
(3, 6, 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 0),
(4, 6, 'ikawikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 'ikaw', 1),
(7, 7, 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 0),
(8, 7, 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 1),
(89, 21, '2', '2', '2', '2', '2', '22', '2', '22', 0),
(90, 21, '2', '22', '2', '2', '2', '2', '2', '2', 1),
(143, 20, 'ewwww', 'ewww', 'ewwww', 'ewwwww', 'ewwww', 'eee', 'e', 'ee', 0),
(144, 20, 'eewww', 'eewwww', 'ee', 'eee', 'eeee', 'e', 'e', 'ee', 1),
(145, 22, 'd', 'ad', 'wwa', 'd', 'f', 'a', ' d', ' w', 0),
(146, 22, 'ad', 'wd', 'ww', 'dwd', 'w', 'dda', 'ww', 'wd', 1),
(147, 23, 'd', 'ad', 'wwa', 'd', 'f', 'a', ' d', ' w', 0),
(148, 23, 'ad', 'wd', 'ww', 'dwd', 'w', 'dda', 'ww', 'wd', 1),
(149, 24, 'd', 'ad', 'wwa', 'd', 'f', 'a', ' d', ' w', 0),
(150, 24, 'ad', 'wd', 'ww', 'dwd', 'w', 'dda', 'ww', 'wd', 1),
(151, 25, 'lol', 'lol', 'lol', 'lollollol', 'lol', 'lollollol', 'lollol', 'lol', 0),
(152, 25, 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 1),
(153, 26, 'lol', 'lol', 'lol', 'lollollol', 'lol', 'lollollol', 'lollol', 'lol', 0),
(154, 26, 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 1),
(155, 27, 'lol', 'lol', 'lol', 'lollollol', 'lol', 'lollollol', 'lollol', 'lol', 0),
(156, 27, 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 'lol', 1),
(159, 29, 'lol', 'lol', 'lollollol', 'lollol', 'lollol', 'lol', 'lollol', 'lol', 0),
(160, 29, 'lollol', 'lollol', 'lollol', 'lol', 'lollol', 'lollollollol', 'lol', 'lol', 1),
(161, 30, 'lol', 'lol', 'lollollol', 'lollol', 'lollol', 'lol', 'lollol', 'lol', 0),
(162, 30, 'lollol', 'lollol', 'lollol', 'lol', 'lollol', 'lollollollol', 'lol', 'lol', 1),
(167, 8, 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 0),
(168, 8, 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 'helloa', 1),
(173, 28, 'lol', 'lol', 'lollollol', 'lollol', 'lollol', 'lol', 'lollol', 'lol', 0),
(174, 28, 'lollol', 'lollol', 'lollol', 'lol', 'lollol', 'lollollollol', 'lol', 'lol', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_personnel`
--

INSERT INTO `gad_proposal_personnel` (`id`, `proposal_id`, `personnel_id`, `role`, `created_at`) VALUES
(1, 2, 124, 'project_leader', '2025-03-28 06:37:45'),
(2, 2, 117, 'assistant_project_leader', '2025-03-28 06:37:45'),
(3, 2, 132, 'project_staff', '2025-03-28 06:37:45'),
(4, 3, 124, 'project_leader', '2025-03-28 06:37:54'),
(5, 3, 117, 'assistant_project_leader', '2025-03-28 06:37:54'),
(6, 3, 132, 'project_staff', '2025-03-28 06:37:54'),
(7, 4, 124, 'project_leader', '2025-03-28 06:39:05'),
(8, 4, 117, 'assistant_project_leader', '2025-03-28 06:39:05'),
(9, 4, 132, 'project_staff', '2025-03-28 06:39:05'),
(10, 5, 124, 'project_leader', '2025-03-28 06:43:00'),
(11, 5, 117, 'assistant_project_leader', '2025-03-28 06:43:00'),
(12, 5, 132, 'project_staff', '2025-03-28 06:43:00'),
(13, 6, 124, 'project_leader', '2025-03-28 06:58:31'),
(14, 6, 117, 'assistant_project_leader', '2025-03-28 06:58:31'),
(15, 6, 132, 'project_staff', '2025-03-28 06:58:31'),
(19, 7, 124, 'project_leader', '2025-03-28 07:25:07'),
(20, 7, 117, 'assistant_project_leader', '2025-03-28 07:25:07'),
(21, 7, 132, 'project_staff', '2025-03-28 07:25:07'),
(54, 21, 136, 'project_staff', '2025-04-02 06:35:28'),
(66, 20, 136, 'project_staff', '2025-04-02 07:34:20'),
(67, 22, 124, 'project_leader', '2025-04-02 07:37:25'),
(68, 22, 117, 'assistant_project_leader', '2025-04-02 07:37:25'),
(69, 22, 132, 'project_staff', '2025-04-02 07:37:25'),
(70, 23, 124, 'project_leader', '2025-04-02 07:37:25'),
(71, 23, 117, 'assistant_project_leader', '2025-04-02 07:37:25'),
(72, 23, 132, 'project_staff', '2025-04-02 07:37:25'),
(73, 24, 124, 'project_leader', '2025-04-02 07:37:25'),
(74, 24, 117, 'assistant_project_leader', '2025-04-02 07:37:25'),
(75, 24, 132, 'project_staff', '2025-04-02 07:37:25'),
(76, 25, 124, 'project_leader', '2025-04-02 07:46:42'),
(77, 25, 117, 'assistant_project_leader', '2025-04-02 07:46:42'),
(78, 25, 132, 'project_staff', '2025-04-02 07:46:42'),
(79, 26, 124, 'project_leader', '2025-04-02 07:46:42'),
(80, 26, 117, 'assistant_project_leader', '2025-04-02 07:46:42'),
(81, 26, 132, 'project_staff', '2025-04-02 07:46:42'),
(82, 27, 124, 'project_leader', '2025-04-02 07:46:42'),
(83, 27, 117, 'assistant_project_leader', '2025-04-02 07:46:42'),
(84, 27, 132, 'project_staff', '2025-04-02 07:46:42'),
(88, 29, 124, 'project_leader', '2025-04-02 08:03:28'),
(89, 29, 117, 'assistant_project_leader', '2025-04-02 08:03:28'),
(90, 29, 132, 'project_staff', '2025-04-02 08:03:28'),
(91, 30, 124, 'project_leader', '2025-04-02 08:03:28'),
(92, 30, 117, 'assistant_project_leader', '2025-04-02 08:03:28'),
(93, 30, 132, 'project_staff', '2025-04-02 08:03:28'),
(96, 8, 136, 'project_staff', '2025-04-02 08:21:22');

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
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposal_workplan`
--

INSERT INTO `gad_proposal_workplan` (`id`, `proposal_id`, `activity`, `timeline_data`, `sequence`) VALUES
(1, 1, 'Preparation', 'Week 1, Week 2', 0),
(2, 1, 'Implementation', 'Week 3', 1),
(3, 1, 'Evaluation', 'Week 4', 2),
(4, 1, 'Reporting', 'Week 4', 3),
(5, 6, 'vikaw', '1', 0),
(6, 6, 'ikaw', '2', 1),
(9, 7, 'helloa', '1', 0),
(10, 7, 'eee', '2', 1),
(79, 21, '2', '', 0),
(80, 21, '2', '', 1),
(135, 20, 'eew', '', 0),
(136, 20, 'eeew', '1', 1),
(137, 20, 'w', '1', 2),
(138, 22, 'w', '', 0),
(139, 22, 'w', '2', 1),
(140, 23, 'w', '', 0),
(141, 23, 'w', '2', 1),
(142, 24, 'w', '', 0),
(143, 24, 'w', '2', 1),
(144, 25, 'lol', '', 0),
(145, 25, 'lol', '2', 1),
(146, 26, 'lol', '', 0),
(147, 26, 'lol', '2', 1),
(148, 27, 'lol', '', 0),
(149, 27, 'lol', '2', 1),
(152, 29, 'lol', '', 0),
(153, 29, 'lol', '2', 1),
(154, 30, 'lol', '', 0),
(155, 30, 'lol', '2', 1),
(160, 8, 'helloa', '2', 0),
(161, 8, 'helloa', '1', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `name`, `category`, `status`, `gender`, `academic_rank`, `campus`, `created_at`) VALUES
(117, 'Elbert D. Nebres', 'Non-teaching', 'Casual', 'male', 'Instructor III', 'Lipa', '2025-03-05 05:16:51'),
(116, 'Elbert D. Nebres', 'Teaching', 'Permanent', 'male', 'Instructor II', 'Alangilan', '2025-03-05 05:00:08'),
(132, 'Test', 'Non-teaching', 'Casual', 'female', 'Instructor III', 'Lipa', '2025-03-06 01:10:13'),
(124, 'Fryan Auric L. Valdez', 'Teaching', 'Guest Lecturer', 'male', 'Instructor I', 'Lipa', '2025-03-05 05:43:54'),
(136, 'ddddd', 'Non-teaching', 'Job Order', 'female', 'Admin Aide 1', 'Lipa', '2025-04-01 09:37:50');

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
(11, 'Lipa', 2028, 'Q1', 88, 'Project I', 'Program I', 'Activity I', 'Location I', '2026-01-15', '2026-01-15', '10:30:00', '14:30:00', '4.00', 'without', 5, 2, 3, 2, 8, 4, '0', 4, 4, 12, 6, 18, '6.00', 'GAA', '4000.00', '[\"SDG 9 - Industry, Innovation and Infrastructure\"]', '0000-00-00 00:00:00', '2026-01-15 05:30:00'),
(12, 'Lipa', 2028, 'Q2', 89, 'Project J', 'Program J', 'Activity J', 'Location J', '2026-02-20', '2026-02-20', '09:00:00', '13:00:00', '4.00', 'without', 3, 5, 3, 4, 6, 9, '0', 3, 2, 9, 11, 20, '6.50', 'GAA', '4100.10', '[\"SDG 10 - Reduced Inequality\"]', '0000-00-00 00:00:00', '2026-02-20 04:00:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `name1`, `gad_head_secretariat`, `name2`, `vice_chancellor_rde`, `name3`, `chancellor`, `name4`, `asst_director_gad`, `name5`, `head_extension_services`, `campus`, `created_at`, `updated_at`) VALUES
(3, 'f', 'GAD Head Secretariat', 'fe', 'Vice Chancellor For Research, Development and Extension', 'f', 'Chancellor', 'f', 'Assistant Director For GAD Advocacies', 'f', 'Head of Extension Services', 'Malvar', '2025-04-03 08:25:31', '2025-04-03 08:25:56'),
(4, 'e', 'GAD Head Secretariat', 'ee', 'Vice Chancellor For Research, Development and Extension', 'ef', 'Chancellor', 'e', 'Assistant Director For GAD Advocacies', 'e', 'Head of Extension Services', 'Lipa', '2025-04-03 08:35:45', '2025-04-04 00:38:56');

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
