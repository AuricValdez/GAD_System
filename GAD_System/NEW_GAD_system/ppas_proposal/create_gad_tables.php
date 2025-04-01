<?php
// Include database connection
require_once '../includes/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Creating GAD Proposal Tables</h1>";

try {
    // Create gad_proposals table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `gad_proposals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `year` int(4) NOT NULL,
            `quarter` varchar(2) NOT NULL,
            `activity_title` varchar(255) NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `venue` varchar(255) NOT NULL,
            `delivery_mode` varchar(50) NOT NULL,
            `project_leaders` text DEFAULT NULL,
            `leader_responsibilities` text DEFAULT NULL,
            `assistant_project_leaders` text DEFAULT NULL,
            `assistant_responsibilities` text DEFAULT NULL,
            `project_staff` text DEFAULT NULL,
            `staff_responsibilities` text DEFAULT NULL,
            `partner_offices` varchar(255) DEFAULT NULL,
            `male_beneficiaries` int(11) DEFAULT 0,
            `female_beneficiaries` int(11) DEFAULT 0,
            `total_beneficiaries` int(11) DEFAULT 0,
            `rationale` text DEFAULT NULL,
            `specific_objectives` text DEFAULT NULL,
            `strategies` text DEFAULT NULL,
            `budget_source` varchar(50) DEFAULT NULL,
            `total_budget` decimal(10,2) DEFAULT 0.00,
            `budget_breakdown` text DEFAULT NULL,
            `sustainability_plan` text DEFAULT NULL,
            `created_by` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `year_quarter` (`year`, `quarter`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p>✅ GAD Proposals table created or already exists.</p>";

    // Create gad_proposal_activities table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `gad_proposal_activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `proposal_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `details` text DEFAULT NULL,
            `sequence` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `proposal_id` (`proposal_id`),
            CONSTRAINT `fk_gad_activities_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p>✅ GAD Proposal Activities table created or already exists.</p>";

    // Create gad_proposal_workplan table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `gad_proposal_workplan` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `proposal_id` int(11) NOT NULL,
            `activity` varchar(255) NOT NULL,
            `timeline_data` text DEFAULT NULL,
            `sequence` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `proposal_id` (`proposal_id`),
            CONSTRAINT `fk_gad_workplan_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p>✅ GAD Proposal Work Plan table created or already exists.</p>";

    // Create gad_proposal_monitoring table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `gad_proposal_monitoring` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `proposal_id` int(11) NOT NULL,
            `objectives` text DEFAULT NULL,
            `performance_indicators` text DEFAULT NULL,
            `baseline_data` text DEFAULT NULL,
            `performance_target` text DEFAULT NULL,
            `data_source` text DEFAULT NULL,
            `collection_method` text DEFAULT NULL,
            `frequency` text DEFAULT NULL,
            `responsible_office` text DEFAULT NULL,
            `sequence` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `proposal_id` (`proposal_id`),
            CONSTRAINT `fk_gad_monitoring_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p>✅ GAD Proposal Monitoring table created or already exists.</p>";

    echo "<h2>All tables created successfully!</h2>";
    echo "<p>You can now use the GAD Proposal system.</p>";
    echo "<p><a href='gad_proposal.php' class='btn btn-primary'>Go to GAD Proposal Form</a></p>";

} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "<h2>Error creating tables:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    
    // Additional debugging information
    echo "<h3>Debug Information:</h3>";
    echo "<pre>";
    print_r($e);
    echo "</pre>";
}
?> 