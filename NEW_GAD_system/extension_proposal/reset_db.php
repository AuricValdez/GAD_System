<?php
// Database connection
require_once __DIR__ . '/../includes/db_connection.php';

// Check if running from command line (CLI)
$isCLI = (php_sapi_name() === 'cli');

// Set header for web requests
if (!$isCLI) {
    header('Content-Type: application/json');

    // Ensure this is only run in testing/development environment for web requests
    $allowedIPs = ['127.0.0.1', '::1', 'localhost'];
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        echo json_encode([
            'success' => false,
            'message' => 'This script can only be run in a development environment.'
        ]);
        exit;
    }

    // Check for confirmation parameter in web requests
    if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
        echo json_encode([
            'success' => false,
            'message' => 'Confirmation parameter missing. Use ?confirm=yes to execute.'
        ]);
        exit;
    }
} else {
    // For CLI, check for command-line arguments
    $options = getopt('', ['confirm:']);
    if (!isset($options['confirm']) || $options['confirm'] !== 'yes') {
        echo "Confirmation parameter missing. Use --confirm=yes to execute.\n";
        exit;
    }
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Tables to truncate/reset
    $tables = [
        'gad_proposal_activities',
        'gad_proposal_monitoring',
        'gad_proposal_workplan',
        'gad_proposal_personnel',
        'gad_proposals'
    ];
    
    // Truncate tables in reverse order to respect foreign key constraints
    foreach (array_reverse($tables) as $table) {
        $conn->exec("DELETE FROM $table");
        $conn->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
    }
    
    // Insert test data if needed
    $year = date('Y');
    $quarter = 'Q1';
    
    // Sample project leaders
    $projectLeaders = 'John Doe, Jane Smith';
    $leaderResponsibilities = "Lead the project\nEnsure all objectives are met";
    
    // Sample assistant project leaders
    $assistantProjectLeaders = 'Alice Johnson, Bob Williams';
    $assistantResponsibilities = "Assist the project leader\nCoordinate with the staff";
    
    // Sample project staff
    $projectStaff = 'Charlie Brown, Diana Prince, Edward Norton';
    $staffResponsibilities = "Implement the project activities\nProvide support as needed\nDocument the project";
    
    // Insert a sample GAD proposal
    $sql = "INSERT INTO gad_proposals (
                year, quarter, activity_title, start_date, end_date, venue, delivery_mode, 
                project, program, project_leaders, leader_responsibilities, 
                assistant_project_leaders, assistant_responsibilities, 
                project_staff, staff_responsibilities, partner_offices,
                male_beneficiaries, female_beneficiaries, total_beneficiaries,
                rationale, specific_objectives, strategies,
                budget_source, total_budget, budget_breakdown, sustainability_plan,
                created_by, created_at
            ) VALUES (
                :year, :quarter, :title, :start_date, :end_date, :venue, :delivery_mode,
                :project, :program, :project_leaders, :leader_responsibilities,
                :assistant_project_leaders, :assistant_responsibilities,
                :project_staff, :staff_responsibilities, :partner_offices,
                :male_beneficiaries, :female_beneficiaries, :total_beneficiaries,
                :rationale, :specific_objectives, :strategies,
                :budget_source, :total_budget, :budget_breakdown, :sustainability_plan,
                :created_by, NOW()
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':quarter' => $quarter,
        ':title' => 'Sample GAD Activity',
        ':start_date' => date('Y-m-d', strtotime('+1 month')),
        ':end_date' => date('Y-m-d', strtotime('+1 month +2 days')),
        ':venue' => 'Campus Auditorium',
        ':delivery_mode' => 'face-to-face',
        ':project' => 'Gender Awareness Project',
        ':program' => 'University Gender Program',
        ':project_leaders' => $projectLeaders,
        ':leader_responsibilities' => $leaderResponsibilities,
        ':assistant_project_leaders' => $assistantProjectLeaders,
        ':assistant_responsibilities' => $assistantResponsibilities,
        ':project_staff' => $projectStaff,
        ':staff_responsibilities' => $staffResponsibilities,
        ':partner_offices' => 'Student Affairs Office, Academic Affairs Office',
        ':male_beneficiaries' => 50,
        ':female_beneficiaries' => 50,
        ':total_beneficiaries' => 100,
        ':rationale' => "This is a sample rationale for the GAD activity.\nIt explains why the activity is needed and how it will benefit the community.",
        ':specific_objectives' => "Increase awareness of gender issues\nPromote gender equality\nDevelop action plans",
        ':strategies' => "Conduct workshops\nDistribute information materials\nEngage in community discussions",
        ':budget_source' => 'GAA',
        ':total_budget' => 50000.00,
        ':budget_breakdown' => "Materials: PHP 15,000\nFood: PHP 20,000\nSpeakers: PHP 10,000\nVenue: PHP 5,000",
        ':sustainability_plan' => "Regular follow-up activities\nIntegration with existing programs\nMonitoring and evaluation",
        ':created_by' => 'System'
    ]);
    
    $proposalId = $conn->lastInsertId();
    
    // Insert sample activities
    $activities = [
        ['Opening Ceremony', 'Welcome remarks\nIntroduction of participants\nOverview of the program'],
        ['Workshop 1: Gender Awareness', 'Discussion of key concepts\nGroup activities\nSharing of experiences'],
        ['Workshop 2: Action Planning', 'Development of action plans\nPresentations\nFeedback sessions']
    ];
    
    foreach ($activities as $index => $activity) {
        $sql = "INSERT INTO gad_proposal_activities (proposal_id, title, details, sequence)
                VALUES (:proposal_id, :title, :details, :sequence)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':proposal_id' => $proposalId,
            ':title' => $activity[0],
            ':details' => $activity[1],
            ':sequence' => $index
        ]);
    }
    
    // Insert sample monitoring plans
    $monitoringPlans = [
        [
            'objectives' => 'Increase awareness',
            'performance_indicators' => 'Number of participants who show improved understanding',
            'baseline_data' => '50% of participants have basic awareness',
            'performance_target' => '90% of participants have improved awareness',
            'data_source' => 'Pre and post tests',
            'collection_method' => 'Surveys',
            'frequency' => 'Before and after the activity',
            'responsible_office' => 'GAD Office'
        ],
        [
            'objectives' => 'Develop action plans',
            'performance_indicators' => 'Number of action plans developed',
            'baseline_data' => 'No existing action plans',
            'performance_target' => 'At least 10 action plans',
            'data_source' => 'Submitted action plans',
            'collection_method' => 'Document review',
            'frequency' => 'At the end of the activity',
            'responsible_office' => 'Planning Office'
        ]
    ];
    
    foreach ($monitoringPlans as $index => $plan) {
        $sql = "INSERT INTO gad_proposal_monitoring (
                    proposal_id, objectives, performance_indicators, baseline_data,
                    performance_target, data_source, collection_method, frequency,
                    responsible_office, sequence
                ) VALUES (
                    :proposal_id, :objectives, :performance_indicators, :baseline_data,
                    :performance_target, :data_source, :collection_method, :frequency,
                    :responsible_office, :sequence
                )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':proposal_id' => $proposalId,
            ':objectives' => $plan['objectives'],
            ':performance_indicators' => $plan['performance_indicators'],
            ':baseline_data' => $plan['baseline_data'],
            ':performance_target' => $plan['performance_target'],
            ':data_source' => $plan['data_source'],
            ':collection_method' => $plan['collection_method'],
            ':frequency' => $plan['frequency'],
            ':responsible_office' => $plan['responsible_office'],
            ':sequence' => $index
        ]);
    }
    
    // Insert sample work plan
    $workplans = [
        ['Preparation', 'Week 1, Week 2'],
        ['Implementation', 'Week 3'],
        ['Evaluation', 'Week 4'],
        ['Reporting', 'Week 4']
    ];
    
    foreach ($workplans as $index => $plan) {
        $sql = "INSERT INTO gad_proposal_workplan (proposal_id, activity, timeline_data, sequence)
                VALUES (:proposal_id, :activity, :timeline_data, :sequence)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':proposal_id' => $proposalId,
            ':activity' => $plan[0],
            ':timeline_data' => $plan[1],
            ':sequence' => $index
        ]);
    }
    
    // Commit all changes
    $conn->commit();
    
    $result = [
        'success' => true,
        'message' => 'Database reset successful. Sample data inserted.',
        'sampleProposalId' => $proposalId
    ];
    
    // Output result based on the runtime environment
    if ($isCLI) {
        echo "Success: Database reset completed successfully.\n";
        echo "Sample proposal ID: {$proposalId}\n";
    } else {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollBack();
    
    $error = 'Error resetting database: ' . $e->getMessage();
    
    // Output error based on the runtime environment
    if ($isCLI) {
        echo "Error: {$error}\n";
    } else {
        echo json_encode([
            'success' => false,
            'message' => $error
        ]);
    }
} 