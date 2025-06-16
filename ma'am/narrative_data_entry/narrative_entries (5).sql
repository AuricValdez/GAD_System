-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 04, 2025 at 04:49 AM
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
  `other_internal_personnel` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ppas_form_id` (`ppas_form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `narrative_entries`
--

INSERT INTO `narrative_entries` (`id`, `campus`, `year`, `title`, `background`, `participants`, `topics`, `results`, `lessons`, `what_worked`, `issues`, `recommendations`, `ps_attribution`, `evaluation`, `activity_ratings`, `timeliness_ratings`, `photo_path`, `photo_paths`, `photo_caption`, `gender_issue`, `created_by`, `created_at`, `updated_by`, `updated_at`, `ppas_form_id`, `expected_results`, `lessons_learned`, `issues_concerns`, `other_internal_personnel`) VALUES
(31, 'Lipa', '2028', '18', '1', '1', '1', '1', '1', '1', '1', '1', '14238.65', '{&quot;activity&quot;:{&quot;Excellent&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:2},&quot;Very Satisfactory&quot;:{&quot;BatStateU&quot;:2222,&quot;Others&quot;:22},&quot;Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:222},&quot;Fair&quot;:{&quot;BatStateU&quot;:2222,&quot;Others&quot;:2},&quot;Poor&quot;:{&quot;BatStateU&quot;:0,&quot;Others&quot;:0}},&quot;timeliness&quot;:{&quot;Excellent&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:222},&quot;Very Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:22},&quot;Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:2},&quot;Fair&quot;:{&quot;BatStateU&quot;:22,&quot;Others&quot;:2},&quot;Poor&quot;:{&quot;BatStateU&quot;:0,&quot;Others&quot;:0}}}', '{&quot;Excellent&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:2},&quot;Very Satisfactory&quot;:{&quot;BatStateU&quot;:2222,&quot;Others&quot;:22},&quot;Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:222},&quot;Fair&quot;:{&quot;BatStateU&quot;:2222,&quot;Others&quot;:2},&quot;Poor&quot;:{&quot;BatStateU&quot;:0,&quot;Others&quot;:0}}', '{&quot;Excellent&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:222},&quot;Very Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:22},&quot;Satisfactory&quot;:{&quot;BatStateU&quot;:2,&quot;Others&quot;:2},&quot;Fair&quot;:{&quot;BatStateU&quot;:22,&quot;Others&quot;:2},&quot;Poor&quot;:{&quot;BatStateU&quot;:0,&quot;Others&quot;:0}}', '', '[\"\"]', '', '', 'Lipa', '2025-06-04 04:47:53', NULL, NULL, 18, NULL, NULL, NULL, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
