<?php
// Include database connection
require_once '../includes/db_connection.php';

try {
    $conn = getConnection();
    
    // SQL to create the ppas_forms table
    $sql = "CREATE TABLE IF NOT EXISTS ppas_forms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        
        /* Gender Issue Section */
        campus VARCHAR(255) NOT NULL,
        year VARCHAR(10) NOT NULL,
        quarter VARCHAR(50) NOT NULL,
        gender_issue_id INT NOT NULL,
        program VARCHAR(255) NOT NULL,
        project VARCHAR(255) NOT NULL,
        activity VARCHAR(255) NOT NULL,
        
        /* Basic Info Section */
        location VARCHAR(255) NOT NULL,
        start_date VARCHAR(20) NOT NULL,
        end_date VARCHAR(20) NOT NULL,
        start_time VARCHAR(20) NOT NULL,
        end_time VARCHAR(20) NOT NULL,
        lunch_break TINYINT(1) DEFAULT 0,
        total_duration VARCHAR(100) NOT NULL,
        mode_of_delivery VARCHAR(255) NOT NULL,
        
        /* Agenda */
        agenda TEXT NOT NULL,
        
        /* SDGs */
        sdg JSON NOT NULL,
        
        /* Office and Program */
        office_college_organization JSON NOT NULL,
        program_list JSON NOT NULL,
        
        /* Project Team */
        project_leader JSON NOT NULL,
        project_leader_responsibilities JSON NOT NULL,
        assistant_project_leader JSON NOT NULL,
        assistant_project_leader_responsibilities JSON NOT NULL,
        project_staff_coordinator JSON NOT NULL,
        project_staff_coordinator_responsibilities JSON NOT NULL,
        
        /* Agency and Participants */
        internal_type VARCHAR(255) NOT NULL,
        internal_male INT NOT NULL,
        internal_female INT NOT NULL,
        internal_total INT NOT NULL,
        external_type VARCHAR(255) NOT NULL,
        external_male INT NOT NULL,
        external_female INT NOT NULL,
        external_total INT NOT NULL,
        grand_total_male INT NOT NULL,
        grand_total_female INT NOT NULL,
        grand_total INT NOT NULL,
        
        /* Program Description */
        rationale TEXT NOT NULL,
        general_objectives TEXT NOT NULL,
        specific_objectives JSON NOT NULL,
        description TEXT NOT NULL,
        strategy JSON NOT NULL,
        expected_output JSON NOT NULL,
        functional_requirements TEXT NOT NULL,
        sustainability_plan TEXT NOT NULL,
        specific_plan JSON NOT NULL,
        
        /* Workplan */
        workplan_activity JSON NOT NULL,
        workplan_date JSON NOT NULL,
        
        /* Financial Requirements */
        financial_plan TINYINT(1) DEFAULT 0,
        financial_plan_items JSON NOT NULL,
        financial_plan_quantity JSON NOT NULL,
        financial_plan_unit JSON NOT NULL,
        financial_plan_unit_cost JSON NOT NULL,
        financial_total_cost VARCHAR(50) NOT NULL,
        source_of_fund JSON NOT NULL,
        financial_note TEXT NOT NULL,
        ps_attribution VARCHAR(255) NOT NULL,
        
        /* Monitoring */
        monitoring_objectives JSON NOT NULL,
        monitoring_baseline_data JSON NOT NULL,
        monitoring_data_source JSON NOT NULL,
        monitoring_frequency_data_collection JSON NOT NULL,
        monitoring_performance_indicators JSON NOT NULL,
        monitoring_performance_target JSON NOT NULL,
        monitoring_collection_method JSON NOT NULL,
        monitoring_office_persons_involved JSON NOT NULL,
        
        /* Metadata */
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Execute query
    $conn->exec($sql);
    echo "Table ppas_forms created successfully";
    
} catch(PDOException $e) {
    error_log("Error creating ppas_forms table: " . $e->getMessage());
    echo "Error creating table: " . $e->getMessage();
}
?> 