<?php
// Start the session
session_start();

// Enable debugging - log errors but don't display them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Always set JSON content type header at the start
header('Content-Type: application/json');

// Create a debug log file
$debugFile = fopen(__DIR__ . '/debug.log', 'a');
fwrite($debugFile, "\n\n--- " . date('Y-m-d H:i:s') . " ---\n");
fwrite($debugFile, "REQUEST METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n");
fwrite($debugFile, "SESSION: " . print_r($_SESSION, true) . "\n");

// Custom error handler to log errors
set_error_handler('customErrorHandler');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Log session info
fwrite($debugFile, "SESSION data: " . print_r($_SESSION, true) . "\n");

// Database connection
try {
    require_once '../includes/db_connection.php';
    // Get a PDO connection
    $conn = getConnection();
    fwrite($debugFile, "Database connection successful\n");
} catch (Exception $e) {
    fwrite($debugFile, "Database connection failed: " . $e->getMessage() . "\n");
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Debug received data
$debug = [];
$debug['post_data'] = $_POST;

try {
    // Extract form data
    $year = isset($_POST['year']) ? intval($_POST['year']) : null;
    $quarter = isset($_POST['quarter']) ? $_POST['quarter'] : null;
    $activityTitle = isset($_POST['activityTitle']) ? $_POST['activityTitle'] : null;
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $venue = isset($_POST['venue']) ? $_POST['venue'] : null;
    $deliveryMode = isset($_POST['deliveryMode']) ? $_POST['deliveryMode'] : null;
    $ppasId = isset($_POST['ppasId']) ? intval($_POST['ppasId']) : null;
    $project = isset($_POST['project']) ? $_POST['project'] : null;
    $program = isset($_POST['program']) ? $_POST['program'] : null;
    
    $projectLeaders = isset($_POST['projectLeaders']) ? $_POST['projectLeaders'] : null;
    $leaderResponsibilities = isset($_POST['leaderResponsibilities']) ? $_POST['leaderResponsibilities'] : null;
    $assistantProjectLeaders = isset($_POST['assistantProjectLeaders']) ? $_POST['assistantProjectLeaders'] : null;
    $assistantResponsibilities = isset($_POST['assistantResponsibilities']) ? $_POST['assistantResponsibilities'] : null;
    $projectStaff = isset($_POST['projectStaff']) ? $_POST['projectStaff'] : null;
    $staffResponsibilities = isset($_POST['staffResponsibilities']) ? $_POST['staffResponsibilities'] : null;
    
    $partnerOffices = isset($_POST['partnerOffices']) ? $_POST['partnerOffices'] : null;
    $maleBeneficiaries = isset($_POST['maleBeneficiaries']) ? intval($_POST['maleBeneficiaries']) : 0;
    $femaleBeneficiaries = isset($_POST['femaleBeneficiaries']) ? intval($_POST['femaleBeneficiaries']) : 0;
    $totalBeneficiaries = isset($_POST['totalBeneficiaries']) ? intval($_POST['totalBeneficiaries']) : 0;
    
    $rationale = isset($_POST['rationale']) ? $_POST['rationale'] : null;
    $specificObjectives = isset($_POST['specificObjectives']) ? $_POST['specificObjectives'] : null;
    $strategies = isset($_POST['strategies']) ? $_POST['strategies'] : null;
    
    $budgetSource = isset($_POST['budgetSource']) ? $_POST['budgetSource'] : null;
    $totalBudget = isset($_POST['totalBudget']) ? floatval($_POST['totalBudget']) : 0.00;
    $budgetBreakdown = isset($_POST['budgetBreakdown']) ? $_POST['budgetBreakdown'] : null;
    
    $sustainabilityPlan = isset($_POST['sustainabilityPlan']) ? $_POST['sustainabilityPlan'] : null;
    $currentProposalId = isset($_POST['currentProposalId']) && !empty($_POST['currentProposalId']) ? intval($_POST['currentProposalId']) : null;
    
    // Get campus from session if available, but make it optional
    $campus = $_SESSION['campus'] ?? null;
    // If campus is needed but not in session, use a default value
    if (!$campus && isset($_SESSION['campus_id'])) {
        // We could look up campus name from campus_id, but the campus table doesn't exist
        // So we'll use a default value
        $campus = 'Main';
    }
    
    // Get personnel IDs from hidden fields
    $projectLeadersIds = isset($_POST['projectLeadersHidden']) ? $_POST['projectLeadersHidden'] : '';
    $assistantProjectLeadersIds = isset($_POST['assistantProjectLeadersHidden']) ? $_POST['assistantProjectLeadersHidden'] : '';
    $projectStaffIds = isset($_POST['projectStaffHidden']) ? $_POST['projectStaffHidden'] : '';
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Check if we're updating an existing record or creating a new one
    if ($currentProposalId) {
        // Update existing proposal
        $stmt = $conn->prepare("
            UPDATE gad_proposals SET 
                year = :year,
                quarter = :quarter,
                activity_title = :activity_title, 
                start_date = :start_date, 
                end_date = :end_date, 
                venue = :venue,
                delivery_mode = :delivery_mode, 
                ppas_id = :ppas_id,
                project = :project,
                program = :program,
                project_leaders = :project_leaders, 
                leader_responsibilities = :leader_responsibilities, 
                assistant_project_leaders = :assistant_project_leaders, 
                assistant_responsibilities = :assistant_responsibilities, 
                project_staff = :project_staff, 
                staff_responsibilities = :staff_responsibilities,
                partner_offices = :partner_offices,
                male_beneficiaries = :male_beneficiaries,
                female_beneficiaries = :female_beneficiaries,
                total_beneficiaries = :total_beneficiaries,
                rationale = :rationale,
                specific_objectives = :specific_objectives,
                strategies = :strategies,
                budget_source = :budget_source,
                total_budget = :total_budget,
                budget_breakdown = :budget_breakdown,
                sustainability_plan = :sustainability_plan,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':year' => $year, 
            ':quarter' => $quarter, 
            ':activity_title' => $activityTitle, 
            ':start_date' => $startDate, 
            ':end_date' => $endDate, 
            ':venue' => $venue, 
            ':delivery_mode' => $deliveryMode, 
            ':ppas_id' => $ppasId,
            ':project' => $project,
            ':program' => $program,
            ':project_leaders' => $projectLeaders, 
            ':leader_responsibilities' => $leaderResponsibilities, 
            ':assistant_project_leaders' => $assistantProjectLeaders, 
            ':assistant_responsibilities' => $assistantResponsibilities, 
            ':project_staff' => $projectStaff, 
            ':staff_responsibilities' => $staffResponsibilities,
            ':partner_offices' => $partnerOffices,
            ':male_beneficiaries' => $maleBeneficiaries,
            ':female_beneficiaries' => $femaleBeneficiaries,
            ':total_beneficiaries' => $totalBeneficiaries,
            ':rationale' => $rationale,
            ':specific_objectives' => $specificObjectives,
            ':strategies' => $strategies,
            ':budget_source' => $budgetSource,
            ':total_budget' => $totalBudget,
            ':budget_breakdown' => $budgetBreakdown,
            ':sustainability_plan' => $sustainabilityPlan,
            ':id' => $currentProposalId
        ]);
        
        $proposalId = $currentProposalId;
        
    } else {
        // Create new proposal
        $createdBy = $_SESSION['username'] ?? null;
        
        $stmt = $conn->prepare("
            INSERT INTO gad_proposals (
                year, 
                quarter, 
                activity_title, 
                start_date, 
                end_date, 
                venue, 
                delivery_mode, 
                ppas_id,
                project,
                program,
                project_leaders, 
                leader_responsibilities, 
                assistant_project_leaders, 
                assistant_responsibilities, 
                project_staff, 
                staff_responsibilities,
                partner_offices,
                male_beneficiaries,
                female_beneficiaries,
                total_beneficiaries,
                rationale,
                specific_objectives,
                strategies,
                budget_source,
                total_budget,
                budget_breakdown,
                sustainability_plan,
                created_by
            ) VALUES (
                :year, :quarter, :activity_title, :start_date, :end_date, :venue, 
                :delivery_mode, :ppas_id, :project, :program, :project_leaders, 
                :leader_responsibilities, :assistant_project_leaders, :assistant_responsibilities, 
                :project_staff, :staff_responsibilities, :partner_offices, :male_beneficiaries, 
                :female_beneficiaries, :total_beneficiaries, :rationale, :specific_objectives, 
                :strategies, :budget_source, :total_budget, :budget_breakdown, 
                :sustainability_plan, :created_by
            )
        ");
        
        $stmt->execute([
            ':year' => $year, 
            ':quarter' => $quarter, 
            ':activity_title' => $activityTitle, 
            ':start_date' => $startDate, 
            ':end_date' => $endDate, 
            ':venue' => $venue, 
            ':delivery_mode' => $deliveryMode, 
            ':ppas_id' => $ppasId,
            ':project' => $project,
            ':program' => $program,
            ':project_leaders' => $projectLeaders, 
            ':leader_responsibilities' => $leaderResponsibilities, 
            ':assistant_project_leaders' => $assistantProjectLeaders, 
            ':assistant_responsibilities' => $assistantResponsibilities, 
            ':project_staff' => $projectStaff, 
            ':staff_responsibilities' => $staffResponsibilities,
            ':partner_offices' => $partnerOffices,
            ':male_beneficiaries' => $maleBeneficiaries,
            ':female_beneficiaries' => $femaleBeneficiaries,
            ':total_beneficiaries' => $totalBeneficiaries,
            ':rationale' => $rationale,
            ':specific_objectives' => $specificObjectives,
            ':strategies' => $strategies,
            ':budget_source' => $budgetSource,
            ':total_budget' => $totalBudget,
            ':budget_breakdown' => $budgetBreakdown,
            ':sustainability_plan' => $sustainabilityPlan,
            ':created_by' => $createdBy
        ]);
        
        $proposalId = $conn->lastInsertId();
    }
    
    // Handle activities
    // First, delete any existing activities for this proposal
    if ($currentProposalId) {
        $deleteStmt = $conn->prepare("DELETE FROM gad_proposal_activities WHERE proposal_id = :proposal_id");
        $deleteStmt->execute([':proposal_id' => $currentProposalId]);
    }
    
    // Insert new activities
    $sequence = 0;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'activity_title_') === 0 && !empty($value)) {
            $activityNumber = substr($key, strlen('activity_title_'));
            $activityDetails = isset($_POST["activity_details_$activityNumber"]) ? $_POST["activity_details_$activityNumber"] : '';
            
            // Skip if this is a fallback activity inserted by the frontend
            if (strpos($value, 'Fallback Activity') !== false && 
                strpos($activityDetails, 'Generated fallback') !== false) {
                fwrite($debugFile, "Skipping fallback activity: $value\n");
                continue;
            }
            
            // Skip meaningless test entries
            $meaninglessValues = ['lol', 'test', 'abc', 'xyz', '123'];
            if (in_array(strtolower(trim($value)), $meaninglessValues)) {
                fwrite($debugFile, "Skipping meaningless activity entry: $value\n");
                continue;
            }
            
            $activityStmt = $conn->prepare("
                INSERT INTO gad_proposal_activities (proposal_id, title, details, sequence, created_at)
                VALUES (:proposal_id, :title, :details, :sequence, CURRENT_TIMESTAMP)
            ");
            
            $activityStmt->execute([
                ':proposal_id' => $proposalId, 
                ':title' => $value, 
                ':details' => $activityDetails, 
                ':sequence' => $sequence
            ]);
            
            $sequence++;
        }
    }
    
    // Also insert work plan data into activities table for better tracking
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'workplan_activity_') === 0 && !empty($value)) {
            $workplanNumber = substr($key, strlen('workplan_activity_'));
            $timelineData = isset($_POST["workplan_timeline_$workplanNumber"]) ? $_POST["workplan_timeline_$workplanNumber"] : '';
            
            // Skip meaningless test entries
            $meaninglessValues = ['lol', 'test', 'abc', 'xyz', '123'];
            if (in_array(strtolower(trim($value)), $meaninglessValues)) {
                fwrite($debugFile, "Skipping meaningless work plan activity entry: $value\n");
                continue;
            }
            
            // Get days selected for formatted details
            $timelineDetails = "Timeline: " . ($timelineData ?: "No specific days selected");
            
            // Save workplan data as an activity too
            $activityStmt = $conn->prepare("
                INSERT INTO gad_proposal_activities (proposal_id, title, details, sequence, created_at)
                VALUES (:proposal_id, :title, :details, :sequence, CURRENT_TIMESTAMP)
            ");
            
            $activityStmt->execute([
                ':proposal_id' => $proposalId, 
                ':title' => "Work Plan: " . $value, 
                ':details' => $timelineDetails, 
                ':sequence' => $sequence
            ]);
            
            $sequence++;
        }
    }
    
    // Handle monitoring plans
    // First, delete any existing monitoring plans for this proposal
    if ($currentProposalId) {
        $deleteStmt = $conn->prepare("DELETE FROM gad_proposal_monitoring WHERE proposal_id = :proposal_id");
        $deleteStmt->execute([':proposal_id' => $currentProposalId]);
    }
    
    // Insert new monitoring plans if they exist in the POST data
    $monitoringSequence = 0;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'monitoring_objectives_') === 0 && !empty($value)) {
            $monitoringNumber = substr($key, strlen('monitoring_objectives_'));
            
            $objectives = $value;
            $indicators = isset($_POST["monitoring_indicators_$monitoringNumber"]) ? $_POST["monitoring_indicators_$monitoringNumber"] : '';
            $baseline = isset($_POST["monitoring_baseline_$monitoringNumber"]) ? $_POST["monitoring_baseline_$monitoringNumber"] : '';
            $target = isset($_POST["monitoring_target_$monitoringNumber"]) ? $_POST["monitoring_target_$monitoringNumber"] : '';
            $source = isset($_POST["monitoring_source_$monitoringNumber"]) ? $_POST["monitoring_source_$monitoringNumber"] : '';
            $method = isset($_POST["monitoring_method_$monitoringNumber"]) ? $_POST["monitoring_method_$monitoringNumber"] : '';
            $frequency = isset($_POST["monitoring_frequency_$monitoringNumber"]) ? $_POST["monitoring_frequency_$monitoringNumber"] : '';
            $responsible = isset($_POST["monitoring_responsible_$monitoringNumber"]) ? $_POST["monitoring_responsible_$monitoringNumber"] : '';
            
            $monitoringStmt = $conn->prepare("
                INSERT INTO gad_proposal_monitoring (
                    proposal_id, 
                    objectives, 
                    performance_indicators, 
                    baseline_data, 
                    performance_target, 
                    data_source, 
                    collection_method, 
                    frequency, 
                    responsible_office, 
                    sequence
                ) VALUES (
                    :proposal_id, :objectives, :indicators, :baseline, :target, 
                    :source, :method, :frequency, :responsible, :sequence
                )
            ");
            
            $monitoringStmt->execute([
                ':proposal_id' => $proposalId, 
                ':objectives' => $objectives, 
                ':indicators' => $indicators, 
                ':baseline' => $baseline, 
                ':target' => $target, 
                ':source' => $source, 
                ':method' => $method, 
                ':frequency' => $frequency, 
                ':responsible' => $responsible, 
                ':sequence' => $monitoringSequence
            ]);
            
            $monitoringSequence++;
        }
    }
    
    // Handle work plan
    // First, delete any existing work plan for this proposal
    if ($currentProposalId) {
        $deleteStmt = $conn->prepare("DELETE FROM gad_proposal_workplan WHERE proposal_id = :proposal_id");
        $deleteStmt->execute([':proposal_id' => $currentProposalId]);
    }
    
    // Insert new work plan items if they exist in the POST data
    $workplanSequence = 0;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'workplan_activity_') === 0 && !empty($value)) {
            $workplanNumber = substr($key, strlen('workplan_activity_'));
            $timelineData = isset($_POST["workplan_timeline_$workplanNumber"]) ? $_POST["workplan_timeline_$workplanNumber"] : '';
            
            // Skip meaningless test entries
            $meaninglessValues = ['lol', 'test', 'abc', 'xyz', '123'];
            if (in_array(strtolower(trim($value)), $meaninglessValues)) {
                fwrite($debugFile, "Skipping meaningless work plan entry in workplan table: $value\n");
                continue;
            }
            
            $workplanStmt = $conn->prepare("
                INSERT INTO gad_proposal_workplan (proposal_id, activity, timeline_data, sequence)
                VALUES (:proposal_id, :activity, :timeline_data, :sequence)
            ");
            
            $workplanStmt->execute([
                ':proposal_id' => $proposalId,
                ':activity' => $value,
                ':timeline_data' => $timelineData,
                ':sequence' => $workplanSequence
            ]);
            
            $workplanSequence++;
        }
    }
    
    // Handle personnel
    // First, delete any existing personnel for this proposal
    if ($currentProposalId) {
        $deleteStmt = $conn->prepare("DELETE FROM gad_proposal_personnel WHERE proposal_id = :proposal_id");
        $deleteStmt->execute([':proposal_id' => $currentProposalId]);
    }
    
    // Process project leaders
    if (!empty($projectLeadersIds)) {
        $leaderIds = explode(',', $projectLeadersIds);
        foreach ($leaderIds as $leaderId) {
            if (!empty($leaderId)) {
                $personnelStmt = $conn->prepare("
                    INSERT INTO gad_proposal_personnel (proposal_id, personnel_id, role)
                    VALUES (:proposal_id, :personnel_id, 'project_leader')
                ");
                
                $personnelStmt->execute([
                    ':proposal_id' => $proposalId,
                    ':personnel_id' => $leaderId
                ]);
            }
        }
    }
    
    // Process assistant project leaders
    if (!empty($assistantProjectLeadersIds)) {
        $assistantIds = explode(',', $assistantProjectLeadersIds);
        foreach ($assistantIds as $assistantId) {
            if (!empty($assistantId)) {
                $personnelStmt = $conn->prepare("
                    INSERT INTO gad_proposal_personnel (proposal_id, personnel_id, role)
                    VALUES (:proposal_id, :personnel_id, 'assistant_project_leader')
                ");
                
                $personnelStmt->execute([
                    ':proposal_id' => $proposalId,
                    ':personnel_id' => $assistantId
                ]);
            }
        }
    }
    
    // Process project staff
    if (!empty($projectStaffIds)) {
        $staffIds = explode(',', $projectStaffIds);
        foreach ($staffIds as $staffId) {
            if (!empty($staffId)) {
                $personnelStmt = $conn->prepare("
                    INSERT INTO gad_proposal_personnel (proposal_id, personnel_id, role)
                    VALUES (:proposal_id, :personnel_id, 'project_staff')
                ");
                
                        $personnelStmt->execute([
                    ':proposal_id' => $proposalId,
                    ':personnel_id' => $staffId
                ]);
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => $currentProposalId ? 'Proposal updated successfully' : 'Proposal created successfully',
        'proposal_id' => $currentProposalId ?: $conn->lastInsertId(),
        'debug' => $debug // For debugging only
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log error
    fwrite($debugFile, "ERROR: " . $e->getMessage() . "\n");
    fwrite($debugFile, "STACK TRACE: " . $e->getTraceAsString() . "\n");
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// Close debug file
fclose($debugFile);

// No need for output buffering 
exit();

// Custom error handler function definition
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $debugFile;
    $error_message = "ERROR [$errno] $errstr in $errfile on line $errline\n";
    fwrite($debugFile, $error_message);
    return true; // Don't execute PHP internal error handler
}
?> 