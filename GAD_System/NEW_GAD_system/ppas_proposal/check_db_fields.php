<?php
// Database connection
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to check if a table exists
function tableExists($conn, $tableName) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to check if a column exists in a table
function columnExists($conn, $tableName, $columnName) {
    try {
        $result = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to add a column to a table if it doesn't exist
function addColumnIfNotExists($conn, $tableName, $columnName, $columnDefinition) {
    try {
        if (!columnExists($conn, $tableName, $columnName)) {
            $sql = "ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDefinition";
            $conn->exec($sql);
            return "Added column '$columnName' to table '$tableName'";
        }
        return "Column '$columnName' already exists in table '$tableName'";
    } catch (PDOException $e) {
        return "Error adding column '$columnName' to table '$tableName': " . $e->getMessage();
    }
}

// Function to create a table if it doesn't exist
function createTableIfNotExists($conn, $tableName, $tableDefinition) {
    try {
        if (!tableExists($conn, $tableName)) {
            $conn->exec($tableDefinition);
            return "Created table '$tableName'";
        }
        return "Table '$tableName' already exists";
    } catch (PDOException $e) {
        return "Error creating table '$tableName': " . $e->getMessage();
    }
}

$results = [];

// Check and create necessary tables if they don't exist
$tables = [
    'gad_proposals' => "CREATE TABLE `gad_proposals` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `year` int(11) NOT NULL,
        `quarter` varchar(2) NOT NULL,
        `activity_title` varchar(255) NOT NULL,
        `start_date` date NOT NULL,
        `end_date` date NOT NULL,
        `venue` varchar(255) NOT NULL,
        `delivery_mode` varchar(50) NOT NULL,
        `ppas_id` int(11) DEFAULT NULL,
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
        KEY `year_quarter` (`year`,`quarter`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'gad_proposal_activities' => "CREATE TABLE `gad_proposal_activities` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `proposal_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `details` text DEFAULT NULL,
        `sequence` int(11) NOT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `proposal_id` (`proposal_id`),
        CONSTRAINT `fk_gad_activities_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'gad_proposal_monitoring' => "CREATE TABLE `gad_proposal_monitoring` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'gad_proposal_workplan' => "CREATE TABLE `gad_proposal_workplan` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `proposal_id` int(11) NOT NULL,
        `activity` varchar(255) NOT NULL,
        `timeline_data` text DEFAULT NULL,
        `sequence` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `proposal_id` (`proposal_id`),
        CONSTRAINT `fk_gad_workplan_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    'gad_proposal_personnel' => "CREATE TABLE `gad_proposal_personnel` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `proposal_id` int(11) NOT NULL,
        `personnel_id` int(11) NOT NULL,
        `role` varchar(50) NOT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `proposal_id` (`proposal_id`),
        CONSTRAINT `gad_proposal_personnel_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `gad_proposals` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

// Check each table
foreach ($tables as $tableName => $tableDefinition) {
    $results['tables'][$tableName] = createTableIfNotExists($conn, $tableName, $tableDefinition);
}

// Check additional columns for existing tables
$columns = [
    'ppas_forms' => [
        'external_type' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'external_male' => "INT(11) NOT NULL DEFAULT 0",
        'external_female' => "INT(11) NOT NULL DEFAULT 0",
        'gender_issue_id' => "INT(11) DEFAULT NULL",
        'approved_budget' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
        'source_of_budget' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'sdgs' => "TEXT DEFAULT NULL",
        'project' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'program' => "VARCHAR(255) NOT NULL DEFAULT ''"
    ],
    'personnel' => [
        'gender' => "VARCHAR(10) NOT NULL DEFAULT 'male'"
    ],
    'gad_proposals' => [
        'project' => "VARCHAR(255) DEFAULT NULL",
        'program' => "VARCHAR(255) DEFAULT NULL"
    ]
];

// Check each column
foreach ($columns as $tableName => $columnDefs) {
    if (tableExists($conn, $tableName)) {
        foreach ($columnDefs as $columnName => $columnDefinition) {
            $results['columns'][$tableName][$columnName] = addColumnIfNotExists($conn, $tableName, $columnName, $columnDefinition);
        }
    } else {
        $results['columns'][$tableName] = "Table '$tableName' does not exist, cannot add columns";
    }
}

echo json_encode($results, JSON_PRETTY_PRINT); 