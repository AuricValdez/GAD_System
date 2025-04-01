-- Create the ppas_forms table if it doesn't exist
CREATE TABLE IF NOT EXISTS `ppas_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `project_team` text DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add sample data if there are no records (ONLY run this part if you need sample data)
INSERT INTO `ppas_forms` (`title`, `location`, `duration`, `project_team`, `username`, `created_at`, `updated_at`) 
SELECT 
    'Sample PPAS Project 1', 
    'Batangas City', 
    'June 1, 2023 - December 31, 2023',
    '[{"role":"Project Leader","name":"John Doe"},{"role":"Assistant Project Leader","name":"Jane Smith"},{"role":"Project Staff","name":"Bob Johnson"}]',
    'Central',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
WHERE NOT EXISTS (SELECT 1 FROM `ppas_forms` LIMIT 1);

-- If you're using a specific user account (not Central), add records for that user as well
-- Replace 'YourUsername' with your actual username
INSERT INTO `ppas_forms` (`title`, `location`, `duration`, `project_team`, `username`, `created_at`, `updated_at`)
VALUES
    ('Community Training Program', 'Lipa City', 'January 15, 2023 - March 30, 2023', 
     '[{"role":"Project Leader","name":"Your Name"},{"role":"Assistant Project Leader","name":"Colleague Name"},{"role":"Project Staff","name":"Staff Member"}]',
     'YourUsername', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP); 