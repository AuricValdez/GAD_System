-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 14, 2025 at 04:22 PM
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
(139, 'Admin Asst 3', 5, '31000.00');

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
  `request_type` enum('client','department') DEFAULT 'client',
  `type` enum('program','project','activity') DEFAULT 'activity',
  PRIMARY KEY (`proposal_id`),
  KEY `idx_ppas_form_id` (`ppas_form_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gad_proposals`
--

INSERT INTO `gad_proposals` (`proposal_id`, `ppas_form_id`, `campus`, `mode_of_delivery`, `partner_office`, `rationale`, `general_objectives`, `description`, `budget_breakdown`, `sustainability_plan`, `project_leader_responsibilities`, `assistant_leader_responsibilities`, `staff_responsibilities`, `specific_objectives`, `strategies`, `methods`, `materials`, `workplan`, `monitoring_items`, `specific_plans`, `created_at`, `updated_at`, `request_type`, `type`) VALUES
(30, 1, 'Lipa', 'FacetoFace', 'Partners', 'Rationale', 'General Objective', 'The Daniel and Marilyn\'s General Merchandise Store Web System is a robust digital platform designed to streamline inventory management and enhance the customer shopping experience. This system integrates a user-friendly point-of-sale interface with cloud-based e-commerce functionalities, allowing real-time synchronization of stock data and transactions. By incorporating GCash as a payment option, it caters to the growing demand for cashless transactions, making purchases more convenient for customers. The design emphasizes role-based access control, ensuring that only authorized personnel can manage sensitive data and system operations. With compliance to ISO 21500 standards, the system maintains a high level of project management excellence and operational efficiency.\n\nAdditionally, the platform supports a dynamic admin dashboard that provides insightful analytics and sales reports, empowering business owners to make data-driven decisions. Customer profiles, purchase history, and product recommendations are seamlessly integrated, creating a personalized shopping experience. Built with scalability in mind, the system can accommodate future business expansions and additional features without disrupting current operations. Security protocols are strictly enforced, protecting both customer and store data from breaches and unauthorized access. Ultimately, the web system aims to elevate the overall functionality, reliability, and competitiveness of the store in a rapidly evolving digital marketplace.', 'budget_breakdown', 'To ensure the long-term success and viability of the Daniel and Marilyn’s General Merchandise Store Web System, a comprehensive sustainability plan has been established. This plan focuses on maintaining system performance, reducing operational costs, and ensuring adaptability to future technological advancements. Regular system updates and maintenance schedules will be implemented to keep the platform secure, responsive, and compliant with the latest security protocols. Energy-efficient hosting solutions and optimized backend processes will be adopted to minimize the environmental impact of continuous system operation. Additionally, comprehensive training and documentation will be provided to staff, ensuring smooth day-to-day operations and reducing reliance on external technical support.\n\nThe sustainability strategy also includes continuous monitoring and evaluation mechanisms to assess the system’s efficiency, reliability, and user satisfaction over time. Feedback loops from customers and staff will help guide future updates and feature enhancements. The integration of scalable cloud infrastructure ensures the system can grow alongside the business, supporting increased traffic, transactions, and data without service disruption. Partnerships with local tech providers and open-source communities will also be explored to promote innovation and cost-effective development. Through proactive planning, resource optimization, and stakeholder involvement, the web system is positioned to support the store’s operations and goals well into the future.', '[\"Project Leader Responsibilities 1\", \"Project Leader Responsibilities 2\", \"Project Leader Responsibilities 3\", \"Project Leader Responsibilities 4\", \"Project Leader Responsibilities 5\"]', '[\"Assistant Project Leader Responsibilities 1\", \"Assistant Project Leader Responsibilities 2\", \"Assistant Project Leader Responsibilities 3\", \"Assistant Project Leader Responsibilities 4\", \"Assistant Project Leader Responsibilities 5\"]', '[\"Project Staff Responsibilities 1\", \"Project Staff Responsibilities 2\", \"Project Staff Responsibilities3\", \"Project Staff Responsibilities 4\", \"Project Staff Responsibilities 5\"]', '[\"Specific Objectives 1\", \"Specific Objectives 2\", \"Specific Objectives 3\"]', '[\"Strategies 1\", \"Strategies 2\", \"Strategies 3\"]', '[[\"Activity Name 1\", [\"Activity Details 1\", \"Activity Details 2\"]], [\"Activity Name 2\", [\"Activity Details 1\"]]]', '[\"Material 1\", \"Material 2\", \"Material 3\"]', '[[\"Work plan 11\", [\"2025-04-03\"]], [\"Work plan 2\", [\"2025-04-04\", \"2025-04-05\", \"2025-04-06\"]]]', '[[\"Objectives 1\", \"Performance Indicators 1\", \"Baseline Data 1\", \"Performance Target 1\", \"Data Source 1\", \"Collection Method 1\", \"Frequency of Data Collection 1\", \"Office/Persons Responsible 1\"], [\"Objectives 2\", \"Performance Indicators 2\", \"Baseline Data 2\", \"Performance Target 2\", \"Data Source 2\", \"Collection Method 2\", \"Frequency of Data Collection 2\", \"Office/Persons Responsible 2\"]]', '[\"Specific Plans 1\", \"Specific Plans 2\", \"Specific Plans 3\"]', '2025-04-07 05:38:32', '2025-05-08 03:51:25', 'client', 'project'),
(31, 2, 'Central', 'FacetoFace', 'Partners', 'Rationale', 'General Objective', 'The Daniel and Marilyn\'s General Merchandise Store Web System is a robust digital platform designed to streamline inventory management and enhance the customer shopping experience. This system integrates a user-friendly point-of-sale interface with cloud-based e-commerce functionalities, allowing real-time synchronization of stock data and transactions. By incorporating GCash as a payment option, it caters to the growing demand for cashless transactions, making purchases more convenient for customers. The design emphasizes role-based access control, ensuring that only authorized personnel can manage sensitive data and system operations. With compliance to ISO 21500 standards, the system maintains a high level of project management excellence and operational efficiency.\n\nAdditionally, the platform supports a dynamic admin dashboard that provides insightful analytics and sales reports, empowering business owners to make data-driven decisions. Customer profiles, purchase history, and product recommendations are seamlessly integrated, creating a personalized shopping experience. Built with scalability in mind, the system can accommodate future business expansions and additional features without disrupting current operations. Security protocols are strictly enforced, protecting both customer and store data from breaches and unauthorized access. Ultimately, the web system aims to elevate the overall functionality, reliability, and competitiveness of the store in a rapidly evolving digital marketplace.', 'budget_breakdown', 'To ensure the long-term success and viability of the Daniel and Marilyn’s General Merchandise Store Web System, a comprehensive sustainability plan has been established. This plan focuses on maintaining system performance, reducing operational costs, and ensuring adaptability to future technological advancements. Regular system updates and maintenance schedules will be implemented to keep the platform secure, responsive, and compliant with the latest security protocols. Energy-efficient hosting solutions and optimized backend processes will be adopted to minimize the environmental impact of continuous system operation. Additionally, comprehensive training and documentation will be provided to staff, ensuring smooth day-to-day operations and reducing reliance on external technical support.\n\nThe sustainability strategy also includes continuous monitoring and evaluation mechanisms to assess the system’s efficiency, reliability, and user satisfaction over time. Feedback loops from customers and staff will help guide future updates and feature enhancements. The integration of scalable cloud infrastructure ensures the system can grow alongside the business, supporting increased traffic, transactions, and data without service disruption. Partnerships with local tech providers and open-source communities will also be explored to promote innovation and cost-effective development. Through proactive planning, resource optimization, and stakeholder involvement, the web system is positioned to support the store’s operations and goals well into the future.', '[\"Project Leader Responsibilities 1\", \"Project Leader Responsibilities 2\", \"Project Leader Responsibilities 3\", \"Project Leader Responsibilities 4\", \"Project Leader Responsibilities 5\"]', '[\"Assistant Project Leader Responsibilities 1\", \"Assistant Project Leader Responsibilities 2\", \"Assistant Project Leader Responsibilities 3\", \"Assistant Project Leader Responsibilities 4\", \"Assistant Project Leader Responsibilities 5\"]', '[\"Project Staff Responsibilities 1\", \"Project Staff Responsibilities 2\", \"Project Staff Responsibilities3\", \"Project Staff Responsibilities 4\", \"Project Staff Responsibilities 5\"]', '[\"Specific Objectives 1\", \"Specific Objectives 2\", \"Specific Objectives 3\"]', '[\"Strategies 1\", \"Strategies 2\", \"Strategies 3\"]', '[[\"Activity Name 1\", [\"Activity Details 1\", \"Activity Details 2\"]], [\"Activity Name 2\", [\"Activity Details 1\"]]]', '[\"Material 1\", \"Material 2\", \"Material 3\"]', '[[\"Work plan 11\", [\"2025-04-03\"]], [\"Work plan 2\", [\"2025-04-04\", \"2025-04-05\", \"2025-04-06\"]]]', '[[\"Objectives 1\", \"Performance Indicators 1\", \"Baseline Data 1\", \"Performance Target 1\", \"Data Source 1\", \"Collection Method 1\", \"Frequency of Data Collection 1\", \"Office/Persons Responsible 1\"], [\"Objectives 2\", \"Performance Indicators 2\", \"Baseline Data 2\", \"Performance Target 2\", \"Data Source 2\", \"Collection Method 2\", \"Frequency of Data Collection 2\", \"Office/Persons Responsible 2\"]]', '[\"Specific Plans 1\", \"Specific Plans 2\", \"Specific Plans 3\"]', '2025-04-06 21:38:32', '2025-05-05 08:06:21', 'client', 'activity');

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
  `actual_male_participants` int DEFAULT NULL,
  `female_participants` int NOT NULL,
  `actual_female_participants` int DEFAULT NULL,
  `total_participants` int NOT NULL,
  `gad_budget` decimal(15,2) NOT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `source_of_budget` varchar(255) NOT NULL,
  `responsible_unit` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `campus` varchar(255) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `feedback` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gpb_entries`
--

INSERT INTO `gpb_entries` (`id`, `category`, `gender_issue`, `cause_of_issue`, `gad_objective`, `relevant_agency`, `generic_activity`, `specific_activities`, `total_activities`, `male_participants`, `actual_male_participants`, `female_participants`, `actual_female_participants`, `total_participants`, `gad_budget`, `actual_cost`, `source_of_budget`, `responsible_unit`, `created_at`, `campus`, `year`, `status`, `feedback`) VALUES
(88, 'Client-Focused', 'Test 1', 'Test', 'Test', 'Higher Education Services', '[\"Test\",\"Test2\"]', '[[\"Test\",\"Test 2\"],[\"Test\"]]', 1, 1, NULL, 1, NULL, 2, '100000.00', NULL, 'GAA', 'OVCAA', '2025-04-25 00:34:25', 'Lipa', 2025, 'Approved', '[\"Test 1\",\"Test 2\",\"Test 3\",\"Test 4\",\"Test 5\",\"Test 6\",\"Test 7\",\"Test 8\",\"Test 9\"]'),
(100, 'Client-Focused', 'Test 2', 'Test', 'Test', 'Higher Education Services', '[\"Test\",\"Test2\"]', '[[\"Test\",\"Test 2\"],[\"Test\"]]', 3, 1, NULL, 1, NULL, 2, '100000.00', NULL, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-04-27 16:23:48', 'Lipa', 2026, 'Approved', '[]'),
(98, 'Client-Focused', 'Test', 'Test', 'Test', 'Higher Education Services', '[\"Test\"]', '[[\"test\"]]', 1, 5, NULL, 5, NULL, 10, '1.00', NULL, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-04-27 16:23:40', 'Central', 2025, 'Approved', '[\"Test 1\"]'),
(101, 'Organization-Focused', 'Test 3', 'Test', 'Test', 'Higher Education Services', '[\"Test\"]', '[[\"Test\"]]', 1, 1, NULL, 1, NULL, 2, '1.00', NULL, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-04-25 00:35:02', 'Lipa', 2025, 'Approved', '[\"Test3\"]'),
(103, 'Client-Focused', 'Gender Awareness 2025', 'Test', 'Test', 'Higher Education Services', '[\"Test Program #1\",\"Test Program #2\",\"Test Program #3\",\"Test Program #4\"]', '[[\"Test Activity #1\",\"Test Activity #2\"],[\"Test Activity #1\"],[\"Test Activity #1\"],[\"Test Activity #1\",\"Test Activity #2\"]]', 6, 5, NULL, 5, NULL, 10, '50000.00', NULL, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-04-27 17:04:45', 'Alangilan', 2025, 'Approved', '[]'),
(102, 'Attributable PAPs', 'Test 4', 'Test', 'Test', 'Higher Education Services', '[\"Test\"]', '[[\"Test\"]]', 9, 222, NULL, 22, NULL, 222, '1.00', NULL, 'GAA', 'Extension Services - GAD Office of Student Affairs and Services', '2025-04-25 00:49:33', 'Lipa', 2025, 'Approved', '[\"Test\"]');

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
  `background_rationale` text,
  `description_participants` text,
  `narrative_topics` text,
  `expected_results` text,
  `lessons_learned` text,
  `what_worked` text,
  `issues_concerns` text,
  `recommendations` text,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_form_id` (`ppas_form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `narrative`
--

INSERT INTO `narrative` (`id`, `ppas_form_id`, `campus`, `implementing_office`, `partner_agency`, `extension_service_agenda`, `type_beneficiaries`, `beneficiary_distribution`, `leader_tasks`, `assistant_tasks`, `staff_tasks`, `activity_narrative`, `activity_ratings`, `timeliness_ratings`, `activity_images`, `created_at`, `background_rationale`, `description_participants`, `narrative_topics`, `expected_results`, `lessons_learned`, `what_worked`, `issues_concerns`, `recommendations`, `updated_at`) VALUES
(37, 1, 'Lipa', '[\"College of Informatics and Computing Sciences\",\"College of Nursing and Allied Health Sciences\"]', 'Partner Agency', '[1,0,1,0,0,0,1,0,0,0,1,1]', 'Beneficiaries', '{\"maleOthers\": \"2\", \"femaleOthers\": \"2\", \"maleBatStateU\": \"1\", \"femaleBatStateU\": \"1\"}', '[\"Project Leader1\", \"Project Leader2\", \"Project Leader3\"]', '[\"Assistant Project Leader1\", \"Assistant Project Leader2\", \"Assistant Project Leader3\"]', '[\"Project Staff1\", \"Project Staff2\", \"Project Staff3\"]', 'In the heart of a rapidly transforming world, where technology intertwines with every facet of daily life, the human experience continues to evolve in ways that would have once been unimaginable. From the rise of artificial intelligence and renewable energy sources to the profound shifts in global communication, the twenty-first century has ushered in an era defined by innovation and complexity. Yet, amid these swift changes, the fundamental desires of human beings — connection, purpose, understanding — remain as steadfast as ever.\n\nThroughout history, every significant advancement has been accompanied by a fundamental question: How does this serve the greater good? The invention of the printing press democratized information, much as the internet does today. Each new tool, each new discovery, challenges society to reconsider its values, structures, and aspirations. It is not enough to simply create; we must also reflect. We must ask ourselves whether the futures we are building are inclusive, sustainable, and just.\n\nTake, for instance, the global movement toward sustainability. Faced with the undeniable consequences of climate change, communities around the world are reimagining agriculture, energy consumption, and even urban living. Vertical farms rise within cities, feeding millions without the heavy carbon footprint of traditional agriculture. Solar and wind energy replace coal and oil, creating cleaner skies and healthier populations. Even industries historically rooted in waste — fashion, manufacturing, and construction — are innovating with circular economy principles, proving that prosperity and responsibility need not be mutually exclusive.\n\nMeanwhile, technology accelerates human potential in unprecedented ways. Artificial intelligence assists doctors in diagnosing rare diseases, helps scientists map distant galaxies, and aids humanitarian efforts by predicting natural disasters. At the same time, it raises profound ethical concerns: biases coded into algorithms, threats to privacy, the displacement of traditional jobs. The balance between harnessing technology’s power and safeguarding human dignity is delicate, requiring constant vigilance, robust dialogue, and compassionate leadership.\n\nEducation, too, is undergoing a revolution. No longer confined to physical classrooms, knowledge travels across borders in mere seconds. A child in a remote village can access the same lectures, materials, and mentorship as a student in a sprawling metropolitan center. Lifelong learning has become a necessity rather than a luxury, with new skills and literacies emerging as fast as industries themselves. In this environment, adaptability, creativity, and critical thinking have become the cornerstones of success.\n\nAmid these sweeping transformations, the human spirit proves resilient. Art continues to flourish, giving voice to the inexpressible and providing solace in uncertain times. Literature, music, and visual media bridge cultures, build empathy, and remind us of our shared humanity. Even as borders harden and political landscapes shift, the arts persist as a testament to the enduring need for connection and meaning.', '{\"Fair\": {\"Others\": 5, \"BatStateU\": 1}, \"Poor\": {\"Others\": 2, \"BatStateU\": 1}, \"Excellent\": {\"Others\": 9, \"BatStateU\": 1}, \"Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}, \"Very Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}}', '{\"Fair\": {\"Others\": 2, \"BatStateU\": 1}, \"Poor\": {\"Others\": 2, \"BatStateU\": 1}, \"Excellent\": {\"Others\": 2, \"BatStateU\": 1}, \"Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}, \"Very Satisfactory\": {\"Others\": 2, \"BatStateU\": 1}}', '[\"narrative_18_1745218599_0.jpeg\", \"narrative_18_1745218599_1.jpg\"]', '2025-04-21 06:56:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `narrative_entries`
--

INSERT INTO `narrative_entries` (`id`, `campus`, `year`, `title`, `background`, `participants`, `topics`, `results`, `lessons`, `what_worked`, `issues`, `recommendations`, `ps_attribution`, `evaluation`, `activity_ratings`, `timeliness_ratings`, `photo_path`, `photo_paths`, `photo_caption`, `gender_issue`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(9, 'Lipa', '2026', 'Test Activity', 'fae', 'faf', 'wafw', 'ddwad', 'dwadaw', 'dwadwa', 'dwada', 'dawda2', '6006.40', '{\"activity\":{\"Excellent\":{\"BatStateU\":5,\"Others\":2},\"Very Satisfactory\":{\"BatStateU\":123,\"Others\":777},\"Satisfactory\":{\"BatStateU\":7,\"Others\":277},\"Fair\":{\"BatStateU\":23,\"Others\":2},\"Poor\":{\"BatStateU\":7,\"Others\":7}},\"timeliness\":{\"Excellent\":{\"BatStateU\":7,\"Others\":7},\"Very Satisfactory\":{\"BatStateU\":7,\"Others\":77},\"Satisfactory\":{\"BatStateU\":77,\"Others\":77},\"Fair\":{\"BatStateU\":7,\"Others\":8},\"Poor\":{\"BatStateU\":81,\"Others\":8}}}', '{\"Excellent\":{\"BatStateU\":5,\"Others\":2},\"Very Satisfactory\":{\"BatStateU\":123,\"Others\":777},\"Satisfactory\":{\"BatStateU\":7,\"Others\":277},\"Fair\":{\"BatStateU\":23,\"Others\":2},\"Poor\":{\"BatStateU\":7,\"Others\":7}}', '{\"Excellent\":{\"BatStateU\":7,\"Others\":7},\"Very Satisfactory\":{\"BatStateU\":7,\"Others\":77},\"Satisfactory\":{\"BatStateU\":77,\"Others\":77},\"Fair\":{\"BatStateU\":7,\"Others\":8},\"Poor\":{\"BatStateU\":81,\"Others\":8}}', 'photos/narrative_1746955903_0.jpg', '[\"photos\\/narrative_1746955903_0.jpg\"]', '55', 'Test 4', 'Lipa', '2025-05-11 09:30:55', 'Lipa', '2025-05-11 09:36:28'),
(12, 'Lipa', '2028', 'Test Activity', '3', '33', '3', '3', '3', '3', '3', '32', '3200.80', '{\"activity\":{\"Excellent\":{\"BatStateU\":2,\"Others\":2},\"Very Satisfactory\":{\"BatStateU\":222,\"Others\":2},\"Satisfactory\":{\"BatStateU\":2,\"Others\":2},\"Fair\":{\"BatStateU\":22,\"Others\":2},\"Poor\":{\"BatStateU\":2,\"Others\":2}},\"timeliness\":{\"Excellent\":{\"BatStateU\":2,\"Others\":22},\"Very Satisfactory\":{\"BatStateU\":2,\"Others\":2},\"Satisfactory\":{\"BatStateU\":2,\"Others\":4},\"Fair\":{\"BatStateU\":4,\"Others\":4},\"Poor\":{\"BatStateU\":4,\"Others\":4}}}', '{\"Excellent\":{\"BatStateU\":2,\"Others\":2},\"Very Satisfactory\":{\"BatStateU\":222,\"Others\":2},\"Satisfactory\":{\"BatStateU\":2,\"Others\":2},\"Fair\":{\"BatStateU\":22,\"Others\":2},\"Poor\":{\"BatStateU\":2,\"Others\":2}}', '{\"Excellent\":{\"BatStateU\":2,\"Others\":22},\"Very Satisfactory\":{\"BatStateU\":2,\"Others\":2},\"Satisfactory\":{\"BatStateU\":2,\"Others\":4},\"Fair\":{\"BatStateU\":4,\"Others\":4},\"Poor\":{\"BatStateU\":4,\"Others\":4}}', 'photos/narrative_1746956580_0.jpeg', '[\"photos\\/narrative_1746956580_0.jpeg\",\"photos\\/narrative_1746956583_0.jpg\",\"photos\\/narrative_1746956608_0.jpeg\"]', '2', '96', 'Lipa', '2025-05-11 09:43:07', 'Lipa', '2025-05-11 09:43:30'),
(13, 'Lipa', '2028', 'Test Activity', 'e', 'ee', 'e', 'e', 'ee', 'e', 'e', 'e', '3700.20', '{\"activity\":{\"Excellent\":{\"BatStateU\":3,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":3333,\"Others\":3},\"Satisfactory\":{\"BatStateU\":3333,\"Others\":3},\"Fair\":{\"BatStateU\":33,\"Others\":3},\"Poor\":{\"BatStateU\":3,\"Others\":3}},\"timeliness\":{\"Excellent\":{\"BatStateU\":33,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":3,\"Others\":33},\"Satisfactory\":{\"BatStateU\":333,\"Others\":33},\"Fair\":{\"BatStateU\":333,\"Others\":3},\"Poor\":{\"BatStateU\":32,\"Others\":34}}}', '{\"Excellent\":{\"BatStateU\":3,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":3333,\"Others\":3},\"Satisfactory\":{\"BatStateU\":3333,\"Others\":3},\"Fair\":{\"BatStateU\":33,\"Others\":3},\"Poor\":{\"BatStateU\":3,\"Others\":3}}', '{\"Excellent\":{\"BatStateU\":33,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":3,\"Others\":33},\"Satisfactory\":{\"BatStateU\":333,\"Others\":33},\"Fair\":{\"BatStateU\":333,\"Others\":3},\"Poor\":{\"BatStateU\":32,\"Others\":34}}', 'photos/narrative_1746957234_0.jpeg', '[\"photos\\/narrative_1746957234_0.jpeg\",\"photos\\/narrative_1746957239_0.jpg\",\"photos\\/narrative_1746957242_0.jpeg\",\"photos\\/narrative_1746957721_0.jpg\"]', '2222', 'Test 1', 'Lipa', '2025-05-11 09:54:03', 'Central', '2025-05-11 10:02:03'),
(14, 'Lipa', '2028', 'Test Activity', '33', '3333', '3', '3', '33', '33', '3', '33', '3900.30', '{\"activity\":{\"Excellent\":{\"BatStateU\":333,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":33,\"Others\":366},\"Satisfactory\":{\"BatStateU\":3,\"Others\":3333},\"Fair\":{\"BatStateU\":44,\"Others\":666},\"Poor\":{\"BatStateU\":3,\"Others\":66}},\"timeliness\":{\"Excellent\":{\"BatStateU\":6,\"Others\":6},\"Very Satisfactory\":{\"BatStateU\":6,\"Others\":666},\"Satisfactory\":{\"BatStateU\":6622,\"Others\":6},\"Fair\":{\"BatStateU\":6,\"Others\":6},\"Poor\":{\"BatStateU\":6,\"Others\":66}}}', '{\"Excellent\":{\"BatStateU\":333,\"Others\":3},\"Very Satisfactory\":{\"BatStateU\":33,\"Others\":366},\"Satisfactory\":{\"BatStateU\":3,\"Others\":3333},\"Fair\":{\"BatStateU\":44,\"Others\":666},\"Poor\":{\"BatStateU\":3,\"Others\":66}}', '{\"Excellent\":{\"BatStateU\":6,\"Others\":6},\"Very Satisfactory\":{\"BatStateU\":6,\"Others\":666},\"Satisfactory\":{\"BatStateU\":6622,\"Others\":6},\"Fair\":{\"BatStateU\":6,\"Others\":6},\"Poor\":{\"BatStateU\":6,\"Others\":66}}', '', '[]', '222', '96', 'Lipa', '2025-05-11 10:14:10', 'Lipa', '2025-05-12 02:11:28'),
(15, 'Lipa', '2026', 'Test Activity', 'e222', 'ee222', 'e', 'e', 'ee', 'eee', '33', '39', '6006.40', '{\"activity\":{\"Excellent\":{\"BatStateU\":9991,\"Others\":9},\"Very Satisfactory\":{\"BatStateU\":9999,\"Others\":9},\"Satisfactory\":{\"BatStateU\":9922222,\"Others\":9},\"Fair\":{\"BatStateU\":99,\"Others\":99},\"Poor\":{\"BatStateU\":99,\"Others\":99}},\"timeliness\":{\"Excellent\":{\"BatStateU\":99522221,\"Others\":9},\"Very Satisfactory\":{\"BatStateU\":922,\"Others\":9},\"Satisfactory\":{\"BatStateU\":91,\"Others\":99},\"Fair\":{\"BatStateU\":92,\"Others\":99},\"Poor\":{\"BatStateU\":91,\"Others\":9}}}', '{\"Excellent\":{\"BatStateU\":9991,\"Others\":9},\"Very Satisfactory\":{\"BatStateU\":9999,\"Others\":9},\"Satisfactory\":{\"BatStateU\":9922222,\"Others\":9},\"Fair\":{\"BatStateU\":99,\"Others\":99},\"Poor\":{\"BatStateU\":99,\"Others\":99}}', '{\"Excellent\":{\"BatStateU\":99522221,\"Others\":9},\"Very Satisfactory\":{\"BatStateU\":922,\"Others\":9},\"Satisfactory\":{\"BatStateU\":91,\"Others\":99},\"Fair\":{\"BatStateU\":92,\"Others\":99},\"Poor\":{\"BatStateU\":91,\"Others\":9}}', 'photos/narrative_1746959218_0.jpg', '[\"photos\\/narrative_1746959218_0.jpg\",\"photos\\/narrative_1746959221_0.jpeg\",\"photos\\/narrative_1746959229_0.jpeg\",\"photos\\/narrative_1747010642_0.png\",\"photos\\/narrative_1747014583_0.jpg\",\"photos\\/narrative_1747016931_0.jpg\"]', '55', 'Test 4', 'Lipa', '2025-05-11 10:27:11', 'Lipa', '2025-05-12 02:28:53'),
(17, 'Lipa', '2028', 'Activity B', '22', '22', '222', '22', '222', '2222', '222', '222', '3500.75', '{\"activity\":{\"Excellent\":{\"BatStateU\":54,\"Others\":545},\"Very Satisfactory\":{\"BatStateU\":44,\"Others\":3444},\"Satisfactory\":{\"BatStateU\":5,\"Others\":44},\"Fair\":{\"BatStateU\":454,\"Others\":44},\"Poor\":{\"BatStateU\":444,\"Others\":44}},\"timeliness\":{\"Excellent\":{\"BatStateU\":7,\"Others\":7},\"Very Satisfactory\":{\"BatStateU\":7666,\"Others\":6277},\"Satisfactory\":{\"BatStateU\":565,\"Others\":6},\"Fair\":{\"BatStateU\":56,\"Others\":66},\"Poor\":{\"BatStateU\":6,\"Others\":66}}}', '{\"Excellent\":{\"BatStateU\":54,\"Others\":545},\"Very Satisfactory\":{\"BatStateU\":44,\"Others\":3444},\"Satisfactory\":{\"BatStateU\":5,\"Others\":44},\"Fair\":{\"BatStateU\":454,\"Others\":44},\"Poor\":{\"BatStateU\":444,\"Others\":44}}', '{\"Excellent\":{\"BatStateU\":7,\"Others\":7},\"Very Satisfactory\":{\"BatStateU\":7666,\"Others\":6277},\"Satisfactory\":{\"BatStateU\":565,\"Others\":6},\"Fair\":{\"BatStateU\":56,\"Others\":66},\"Poor\":{\"BatStateU\":6,\"Others\":66}}', 'photos/narrative_1747016971_0.jpg', '[\"photos\\/narrative_1747016971_0.jpg\",\"photos\\/narrative_1747016975_0.jpeg\"]', '51', 'Test 1', 'Lipa', '2025-05-12 02:29:41', 'Central', '2025-05-12 02:30:35'),
(18, 'Lipa', '2028', 'Activity D', '2222', '22', '2', '22', '22', '2', '22', '2454', '3100.60', '{\"activity\":{\"Excellent\":{\"BatStateU\":91,\"Others\":788},\"Very Satisfactory\":{\"BatStateU\":89,\"Others\":88},\"Satisfactory\":{\"BatStateU\":9,\"Others\":8},\"Fair\":{\"BatStateU\":7,\"Others\":7},\"Poor\":{\"BatStateU\":7,\"Others\":7}},\"timeliness\":{\"Excellent\":{\"BatStateU\":5,\"Others\":55},\"Very Satisfactory\":{\"BatStateU\":155,\"Others\":55},\"Satisfactory\":{\"BatStateU\":5555,\"Others\":5},\"Fair\":{\"BatStateU\":5,\"Others\":55},\"Poor\":{\"BatStateU\":55,\"Others\":5}}}', '{\"Excellent\":{\"BatStateU\":91,\"Others\":788},\"Very Satisfactory\":{\"BatStateU\":89,\"Others\":88},\"Satisfactory\":{\"BatStateU\":9,\"Others\":8},\"Fair\":{\"BatStateU\":7,\"Others\":7},\"Poor\":{\"BatStateU\":7,\"Others\":7}}', '{\"Excellent\":{\"BatStateU\":5,\"Others\":55},\"Very Satisfactory\":{\"BatStateU\":155,\"Others\":55},\"Satisfactory\":{\"BatStateU\":5555,\"Others\":5},\"Fair\":{\"BatStateU\":5,\"Others\":55},\"Poor\":{\"BatStateU\":55,\"Others\":5}}', 'photos/narrative_1747035916_0.jpeg', '[\"photos\\/narrative_1747035916_0.jpeg\"]', '342d', 'Test 1', 'Lipa', '2025-05-12 07:45:21', 'Central', '2025-05-12 08:19:25'),
(19, 'Lipa', '2028', 'Activity G', 'w', 'ww', 'w', 'w', 'w', 'w', 'w', 'w1', '3800.90', '{\"activity\":{\"Excellent\":{\"BatStateU\":11,\"Others\":11},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":11},\"Fair\":{\"BatStateU\":1,\"Others\":11},\"Poor\":{\"BatStateU\":1,\"Others\":144}},\"timeliness\":{\"Excellent\":{\"BatStateU\":42,\"Others\":4},\"Very Satisfactory\":{\"BatStateU\":44,\"Others\":4444},\"Satisfactory\":{\"BatStateU\":44,\"Others\":4},\"Fair\":{\"BatStateU\":4,\"Others\":4},\"Poor\":{\"BatStateU\":44,\"Others\":4}}}', '{\"Excellent\":{\"BatStateU\":11,\"Others\":11},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":11},\"Fair\":{\"BatStateU\":1,\"Others\":11},\"Poor\":{\"BatStateU\":1,\"Others\":144}}', '{\"Excellent\":{\"BatStateU\":42,\"Others\":4},\"Very Satisfactory\":{\"BatStateU\":44,\"Others\":4444},\"Satisfactory\":{\"BatStateU\":44,\"Others\":4},\"Fair\":{\"BatStateU\":4,\"Others\":4},\"Poor\":{\"BatStateU\":44,\"Others\":4}}', 'photos/narrative_1747040659_0.jpeg', '[\"photos\\/narrative_1747040659_0.jpeg\",\"photos\\/narrative_1747040676_0.png\"]', '111', '82', 'Central', '2025-05-12 09:04:21', 'Central', '2025-05-12 09:04:37'),
(20, 'Lipa', '2026', 'Activity A', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', '4000.50', '{\"activity\":{\"Excellent\":{\"BatStateU\":1,\"Others\":1},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Fair\":{\"BatStateU\":1,\"Others\":1},\"Poor\":{\"BatStateU\":0,\"Others\":0}},\"timeliness\":{\"Excellent\":{\"BatStateU\":1,\"Others\":1},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Fair\":{\"BatStateU\":1,\"Others\":1},\"Poor\":{\"BatStateU\":0,\"Others\":0}}}', '{\"Excellent\":{\"BatStateU\":1,\"Others\":1},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Fair\":{\"BatStateU\":1,\"Others\":1},\"Poor\":{\"BatStateU\":0,\"Others\":0}}', '{\"Excellent\":{\"BatStateU\":1,\"Others\":1},\"Very Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Satisfactory\":{\"BatStateU\":1,\"Others\":1},\"Fair\":{\"BatStateU\":1,\"Others\":1},\"Poor\":{\"BatStateU\":0,\"Others\":0}}', 'photos/narrative_1747203206_0.jpeg', '[\"photos\\/narrative_1747203206_0.jpeg\"]', '1', 'Test 2', 'Lipa', '2025-05-14 06:13:27', NULL, NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `campus`, `year`, `quarter`, `gender_issue_id`, `program`, `project`, `activity`, `location`, `start_date`, `end_date`, `start_time`, `end_time`, `lunch_break`, `total_duration`, `mode_of_delivery`, `agenda`, `sdg`, `office_college_organization`, `program_list`, `project_leader`, `project_leader_responsibilities`, `assistant_project_leader`, `assistant_project_leader_responsibilities`, `project_staff_coordinator`, `project_staff_coordinator_responsibilities`, `internal_type`, `internal_male`, `internal_female`, `internal_total`, `external_type`, `external_male`, `external_female`, `external_total`, `grand_total_male`, `grand_total_female`, `grand_total`, `rationale`, `general_objectives`, `specific_objectives`, `description`, `strategy`, `expected_output`, `functional_requirements`, `sustainability_plan`, `specific_plan`, `workplan_activity`, `workplan_date`, `financial_plan`, `financial_plan_items`, `financial_plan_quantity`, `financial_plan_unit`, `financial_plan_unit_cost`, `financial_total_cost`, `source_of_fund`, `financial_note`, `approved_budget`, `ps_attribution`, `monitoring_objectives`, `monitoring_baseline_data`, `monitoring_data_source`, `monitoring_frequency_data_collection`, `monitoring_performance_indicators`, `monitoring_performance_target`, `monitoring_collection_method`, `monitoring_office_persons_involved`, `created_at`, `updated_at`) VALUES
(29, 'Lipa', '2025', 'Q1', 120, 'Test Program', 'Test Project', 'Test Activity', 'Location', '12/16/2030', '12/18/2030', '17:51', '21:51', 1, '12.00', 'Face-to-Face', 'BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program', '[\"SDG 1\", \"SDG 2\", \"SDG 3\"]', '[\"Office 1\", \"Office 2\"]', '[\"Program 1\", \"Program 2\"]', '[\"Test\"]', '[\"Responsibilities 1,Responsibilities 2\"]', '[\"Test 2\"]', '[\"Responsibilities 1,Responsibilities 2\"]', '[\"Fryan Auric L. Valdez\"]', '[\"Responsibilities 1,Responsibilities 2\"]', 'Internal Participants', 1, 2, 3, 'External Participants', 1, 2, 3, 2, 4, 6, 'Rationale', 'General Objectives', '[\"Specific Objectives 1\", \"Specific Objectives 2\"]', 'Description', '[\"Strategies 1\", \"Strategies 2\"]', '[\"1\"]', 'Functional Requirements', 'Sustainability', '[\"Specific Plans 1\", \"Specific Plans 2\"]', '[\" Activity 1\", \" Activity 2\"]', '[\"December 16\", \"December 17,December 18\"]', 0, '[\"none\"]', '[\"none\"]', '[\"none\"]', '[\"none\"]', '0', '[\"GAA\", \"MDS\", \"STF\"]', 'Financial Note', 1234, '4909.20', '[\"Objectives\", \"Objectives\"]', '[\"Baseline Data\", \"Baseline Data\"]', '[\"Data Source\", \"Data Source\"]', '[\"Frequency of Data Collection\", \"Frequency of Data Collection\"]', '[\"Performance Indicators\", \"Performance Indicators\"]', '[\"Performance Target\", \"Performance Target\"]', '[\"Collection Method\", \"Collection Method\"]', '[\"Office/Persons Involved\", \"Office/Persons Involved\"]', '2025-05-14 05:02:54', '2025-05-14 05:39:39'),
(28, 'Lipa', '2025', 'Q1', 120, 'Test Program', 'Test Project', 'Test Activity', 'Location', '12/16/2030', '12/18/2030', '17:51', '21:51', 1, '12.00', 'Face-to-Face', 'BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program', '[\"SDG 1\", \"SDG 2\", \"SDG 3\"]', '[\"Office 1\", \"Office 2\"]', '[\"Program 1\", \"Program 2\"]', '[\"Test\"]', '[\"Responsibilities 1,Responsibilities 2\"]', '[\"Test 2\"]', '[\"Responsibilities 1,Responsibilities 2\"]', '[\"Fryan Auric L. Valdez\"]', '[\"Responsibilities 1,Responsibilities 2\"]', 'Internal Participants', 1, 2, 3, 'External Participants', 1, 2, 3, 2, 4, 6, 'Rationale', 'General Objectives', '[\"Specific Objectives 1\", \"Specific Objectives 2\"]', 'Description', '[\"Strategies 1\", \"Strategies 2\"]', '[\"1\"]', 'Functional Requirements', 'Sustainability', '[\"Specific Plans 1\", \"Specific Plans 2\"]', '[\" Activity 1\", \" Activity 2\"]', '[\"December 16\", \"December 17,December 18\"]', 1, '[\" Item Description 1\", \" Item Description 2\"]', '[\"5\", \"5\"]', '[\"Unit 1\", \"Unit 2\"]', '[\"1\", \"2\"]', '15.00', '[\"GAA\", \"MDS\", \"STF\"]', 'Financial Note', 1234, '4909.20', '[\"Objectives\", \"Objectives\"]', '[\"Baseline Data\", \"Baseline Data\"]', '[\"Data Source\", \"Data Source\"]', '[\"Frequency of Data Collection\", \"Frequency of Data Collection\"]', '[\"Performance Indicators\", \"Performance Indicators\"]', '[\"Performance Target\", \"Performance Target\"]', '[\"Collection Method\", \"Collection Method\"]', '[\"Office/Persons Involved\", \"Office/Persons Involved\"]', '2025-05-14 05:02:38', '2025-05-14 05:39:34');

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
  `name6` varchar(255) DEFAULT NULL,
  `vice_chancellor_admin_finance` varchar(255) DEFAULT NULL,
  `campus` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name7` varchar(255) DEFAULT NULL,
  `dean` varchar(255) DEFAULT 'Dean',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `name1`, `gad_head_secretariat`, `name2`, `vice_chancellor_rde`, `name3`, `chancellor`, `name4`, `asst_director_gad`, `name5`, `head_extension_services`, `name6`, `vice_chancellor_admin_finance`, `campus`, `created_at`, `updated_at`, `name7`, `dean`) VALUES
(2, 'Henry Silva', 'GAD Head Secretariat', 'Ivy Ramirez', 'Vice Chancellor For Research, Development and Extension', 'Jake Navarro', 'Chancellor', 'Kyla Villanueva', 'Assistant Director For GAD Advocacies', 'Luis Ramos', 'Head of Extension Services', 'Maria Dizon', 'Vice Chancellor for Administration and Finance', 'Alangilan', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Noel Aquino', 'Dean'),
(1, 'Anna Cruz', 'GAD Head Secretariat', 'Benjamin Reyes', 'Vice Chancellor For Research, Development and Extension', 'Carla Gomez', 'Chancellor', 'Daniel Lopez', 'Assistant Director For GAD Advocacies', 'Elaine Mendoza', 'Head of Extension Services', 'Francis Tan', 'Vice Chancellor for Administration and Finance', 'Central', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Grace Santos', 'Dean'),
(3, 'Olivia Lim', 'GAD Head Secretariat', 'Patrick Cruz', 'Vice Chancellor For Research, Development and Extension', 'Queenie Torres', 'Chancellor', 'Ryan Garcia', 'Assistant Director For GAD Advocacies', 'Samantha Uy', 'Head of Extension Services', 'Thomas Vega', 'Vice Chancellor for Administration and Finance', 'Pablo Borbon', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Uma Santos', 'Dean'),
(4, 'Victor Chua', 'GAD Head Secretariat', 'Wendy Roxas', 'Vice Chancellor For Research, Development and Extension', 'Xavier Reyes', 'Chancellor', 'Yvonne Delgado', 'Assistant Director For GAD Advocacies', 'Zach Sy', 'Head of Extension Services', 'Abby Ong', 'Vice Chancellor for Administration and Finance', 'ARASOF-Nasugbu', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Brianne Javier', 'Dean'),
(5, 'Carmen Diaz', 'GAD Head Secretariat', 'Diego Bautista', 'Vice Chancellor For Research, Development and Extension', 'Elena Ponce', 'Chancellor', 'Felix Ramos', 'Assistant Director For GAD Advocacies', 'Gloria Rivera', 'Head of Extension Services', 'Harold Tan', 'Vice Chancellor for Administration and Finance', 'Balayan', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Isabel Flores', 'Dean'),
(6, 'Jasper Manalo', 'GAD Head Secretariat', 'Karen Sison', 'Vice Chancellor For Research, Development and Extension', 'Leo Gutierrez', 'Chancellor', 'Monica Santos', 'Assistant Director For GAD Advocacies', 'Nathan Cruz', 'Head of Extension Services', 'Opal Medina', 'Vice Chancellor for Administration and Finance', 'Lemery', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Paulo Enriquez', 'Dean'),
(7, 'Quincy Tan', 'GAD Head Secretariat', 'Rachel Ong', 'Vice Chancellor For Research, Development and Extension', 'Samuel Chavez', 'Chancellor', 'Tina Yulo', 'Assistant Director For GAD Advocacies', 'Ulysses Ramosn', 'Head of Extension Services', 'Vanessa Lim', 'Vice Chancellor for Administration and Finance', 'Lipa', '2025-04-24 00:29:32', '2025-05-07 08:36:41', 'Warren Domingo', 'Dean'),
(8, 'Alex Bautista', 'GAD Head Secretariat', 'Bea Santiago', 'Vice Chancellor For Research, Development and Extension', 'Caleb Morales', 'Chancellor', 'Diana Cruz', 'Assistant Director For GAD Advocacies', 'Eugene Delos Reyes', 'Head of Extension Services', 'Faye Tan', 'Vice Chancellor for Administration and Finance', 'Lobo', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'George Castillo', 'Dean'),
(9, 'Hannah Uy', 'GAD Head Secretariat', 'Ian Garcia', 'Vice Chancellor For Research, Development and Extension', 'Jolina Diaz', 'Chancellor', 'Karl Soriano', 'Assistant Director For GAD Advocacies', 'Lara Buenaventura', 'Head of Extension Services', 'Miguel Tan', 'Vice Chancellor for Administration and Finance', 'Mabini', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Nina Trinidad', 'Dean'),
(10, 'Oscar Dela Cruz', 'GAD Head Secretariat', 'Pamela Reyes', 'Vice Chancellor For Research, Development and Extension', 'Quintin Sarmiento', 'Chancellor', 'Rhea David', 'Assistant Director For GAD Advocacies', 'Sergio Yap', 'Head of Extension Services', 'Tess Mercado', 'Vice Chancellor for Administration and Finance', 'Malvar', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Uriel Santos', 'Dean'),
(11, 'Vera Go', 'GAD Head Secretariat', 'Wilmer Chavez', 'Vice Chancellor For Research, Development and Extension', 'Xandra Ramos', 'Chancellor', 'Yuri Navarro', 'Assistant Director For GAD Advocacies', 'Zenaida Castro', 'Head of Extension Services', 'Ariel Torres', 'Vice Chancellor for Administration and Finance', 'Rosario', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Beatrice Luna', 'Dean'),
(12, 'Clyde Santos', 'GAD Head Secretariat', 'Denise Aquino', 'Vice Chancellor For Research, Development and Extension', 'Enzo Mateo', 'Chancellor', 'Fiona Sy', 'Assistant Director For GAD Advocacies', 'Gavin Reyes', 'Head of Extension Services', 'Hazel Ong', 'Vice Chancellor for Administration and Finance', 'San Juan', '2025-04-24 00:29:32', '2025-05-04 18:49:00', 'Irene Valdez', 'Dean');

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
(120, 2025, 'Pablo Borbon', '1.00', '0.05'),
(146, 2025, 'Alangilan', '500.00', '25.00'),
(149, 2026, 'Alangilan', '1.00', '0.05'),
(156, 2026, 'Pablo Borbon', '999.00', '49.95'),
(157, 2027, 'Pablo Borbon', '3.00', '0.15'),
(158, 2028, 'Lipa', '500.00', '25.00'),
(161, 2029, 'Lipa', '69.00', '3.45'),
(162, 2026, 'Lipa', '2222.00', '111.10'),
(163, 2025, 'Lipa', '3333.00', '166.65');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
