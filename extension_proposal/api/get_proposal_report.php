<?php
session_start();
error_reporting(0); // Disable error reporting to prevent HTML errors from being output
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Default to HTML format for browser display
header('Content-Type: text/html');

// Function to safely get array value with null default
function safe_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("Session not found. Current session data: " . print_r($_SESSION, true));
    echo "<div class='alert alert-danger'>User not logged in. Please refresh the page and try again.</div>";
    exit;
}

// Get parameters
$campus = $_GET['campus'] ?? null;
$year = $_GET['year'] ?? null;
$proposal_id = $_GET['proposal_id'] ?? null;

error_log("Request parameters: campus=$campus, year=$year, proposal_id=$proposal_id");

if (!$campus || !$year || !$proposal_id) {
    error_log("Missing required parameters: campus=$campus, year=$year, proposal_id=$proposal_id");
    echo "<div class='alert alert-danger'>Missing required parameters. Please ensure all fields are filled.</div>";
    exit;
}

try {
    // Use config file for database connection
    require_once '../../includes/config.php';
    
    // Enable detailed error logging 
    error_log("Using database: host=$servername, dbname=$dbname, user=$username");
    error_log("Parameters: proposal_id=$proposal_id, campus=$campus, year=$year");
    
    // Create database connection using config variables
    $db = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    error_log("Database connection successful");
    
    // Get proposal details
    $query = "
        SELECT 
            gp.*,
            pf.year,
            pf.campus,
            pf.location as venue,
            pf.activity,
            pf.students_male,
            pf.students_female,
            pf.faculty_male,
            pf.faculty_female,
            pf.total_internal_male,
            pf.total_internal_female,
            pf.external_type,
            pf.external_male,
            pf.external_female,
            pf.total_male,
            pf.total_female,
            pf.total_beneficiaries,
            CONCAT(
                DATE_FORMAT(pf.start_date, '%M %d, %Y'),
                ' to ',
                DATE_FORMAT(pf.end_date, '%M %d, %Y')
            ) as duration
        FROM gad_proposals gp
        JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
        WHERE gp.proposal_id = :proposal_id
        AND pf.campus = :campus
        AND pf.year = :year
    ";
    
    error_log("Executing query: " . $query);
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([
            'proposal_id' => $proposal_id,
            'campus' => $campus,
            'year' => $year
        ]);
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        exit;
    }
    
    $proposal = $stmt->fetch();
    
    if (!$proposal) {
        error_log("No proposal found for ID: $proposal_id, Campus: $campus, Year: $year");
        echo "<div class='alert alert-danger'>Proposal not found. Please check your selection and try again.</div>";
        exit;
    }
    
    error_log("Found proposal: " . json_encode($proposal));
    
    // Get personnel from ppas_personnel table
    $personnel_query = "
        SELECT 
            pp.personnel_id,
            pp.role,
            p.name,
            p.gender,
            p.academic_rank
        FROM ppas_personnel pp 
        JOIN personnel p ON pp.personnel_id = p.id
        WHERE pp.ppas_form_id = :ppas_form_id
        ORDER BY pp.role, p.name
    ";
    
    try {
        $stmt = $db->prepare($personnel_query);
        $stmt->execute(['ppas_form_id' => $proposal['ppas_form_id']]);
        $personnel = $stmt->fetchAll();
        error_log("Found personnel: " . json_encode($personnel));
    } catch (PDOException $e) {
        error_log("Personnel query error: " . $e->getMessage());
        $personnel = [];
    }
    
    // Group personnel by role
    $personnel_by_role = [
        'project_leaders' => [],
        'assistant_project_leaders' => [],
        'project_staff' => []
    ];
    
    foreach ($personnel as $person) {
        if ($person['role'] == 'Project Leader') {
            $personnel_by_role['project_leaders'][] = $person;
        } elseif ($person['role'] == 'Assistant Project Leader') {
            $personnel_by_role['assistant_project_leaders'][] = $person;
        } elseif ($person['role'] == 'Staff') {
            $personnel_by_role['project_staff'][] = $person;
        }
    }
    
    // Get signatories
    $signatories_query = "
        SELECT * FROM signatories
        WHERE campus = :campus
    ";
    
    try {
        $stmt = $db->prepare($signatories_query);
        $stmt->execute(['campus' => $campus]);
        $signatories = $stmt->fetch();
        error_log("Found signatories: " . json_encode($signatories));
    } catch (PDOException $e) {
        error_log("Signatories query error: " . $e->getMessage());
        $signatories = null;
    }
    
    // Extract and decode JSON fields
    $sections = [];
    
    // Process activities (stored as JSON)
    try {
        $sections['activities'] = json_decode($proposal['activities'] ?? '[]', true);
        $sections['objectives'] = [
            'general' => $proposal['general_objective'] ?? '',
            'specific' => json_decode($proposal['specific_objectives'] ?? '[]', true)
        ];
        $sections['workplan'] = json_decode($proposal['workplan'] ?? '[]', true);
        $sections['participants'] = [
            'students_male' => $proposal['students_male'] ?? 0,
            'students_female' => $proposal['students_female'] ?? 0,
            'faculty_male' => $proposal['faculty_male'] ?? 0,
            'faculty_female' => $proposal['faculty_female'] ?? 0,
            'total_internal_male' => $proposal['total_internal_male'] ?? 0,
            'total_internal_female' => $proposal['total_internal_female'] ?? 0,
            'external_male' => $proposal['external_male'] ?? 0,
            'external_female' => $proposal['external_female'] ?? 0,
            'total_male' => $proposal['total_male'] ?? 0,
            'total_female' => $proposal['total_female'] ?? 0,
            'total' => $proposal['total_beneficiaries'] ?? 0
        ];
        $sections['rationale'] = $proposal['rationale'] ?? '';
        $sections['strategies'] = $proposal['strategies'] ?? '';
        $sections['methods'] = json_decode($proposal['methods'] ?? '[]', true);
        $sections['monitoring_evaluation'] = json_decode($proposal['monitoring_evaluation'] ?? '[]', true);
        $sections['sustainability'] = json_decode($proposal['sustainability_plan'] ?? '[]', true);
        $sections['financial'] = [
            'source' => $proposal['budget_source'] ?? '',
            'total' => $proposal['budget_amount'] ?? 0,
            'breakdown' => $proposal['budget_breakdown'] ?? ''
        ];
    } catch (Exception $e) {
        error_log("Error decoding JSON fields: " . $e->getMessage());
    }
    
    // Build the HTML report
    $html = '<div class="proposal-container">';
    
    // Header section
    $html .= '<div class="header-section">';
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    $html .= '<tr>';
    $html .= '<td class="logo-cell" style="width: 15%; text-align: center; padding: 10px; border: 1px solid black;"><img src="../../images/Batangas_State_Logo.png" alt="Logo" style="max-width: 70px; height: auto;"></td>';
    $html .= '<td class="reference-cell" style="width: 85%; text-align: center; border: 1px solid black; padding: 10px;">';
    $html .= '<strong style="font-size: 16pt;">BATANGAS STATE UNIVERSITY</strong><br>';
    $html .= '<span style="font-size: 12pt;">The National Engineering University</span><br>';
    $html .= '<span style="font-size: 12pt;">' . htmlspecialchars($campus) . ' CAMPUS</span>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    
    // Title row
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 0;">';
    $html .= '<tr class="title-row">';
    $html .= '<td style="text-align: center; font-weight: bold; padding: 10px; border: 1px solid black; background-color: #f8f9fa;">GAD PROPOSAL FORM</td>';
    $html .= '</tr>';
    $html .= '</table>';
    
    // Checkbox options
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 0;" class="checkbox-table">';
    $html .= '<tr>';
    $html .= '<td style="text-align: center; padding: 10px; border: 1px solid black; border-top: none;">';
    $html .= '<div class="checkbox-container" style="text-align: center;">';
    $html .= '<span class="checkbox-option">☐ Program</span>';
    $html .= '<span class="checkbox-option">☐ Project</span>';
    $html .= '<span class="checkbox-option">☒ Activity</span>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</div>'; // End header section
    
    // Main content table
    $html .= '<div class="main-section">';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 0;">';
    
    // Activity title
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; width: 30%;">I. Activity Title:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px; width: 70%;"><strong>' . htmlspecialchars($proposal['activity_title'] ?? 'Not specified') . '</strong></td>';
    $html .= '</tr>';
    
    // Duration
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">II. Duration of Activity:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">' . htmlspecialchars($proposal['duration'] ?? 'Not specified') . '</td>';
    $html .= '</tr>';
    
    // Venue
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">III. Venue:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">' . htmlspecialchars($proposal['venue'] ?? 'Not specified') . '</td>';
    $html .= '</tr>';
    
    // Project team
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">IV. Project Team:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse;" class="project-team-table">';
    $html .= '<tr>';
    $html .= '<td style="border: none; padding: 5px; width: 40%; font-weight: bold;">Project Leader:</td>';
    $html .= '<td style="border: none; padding: 5px; width: 60%;">';
    
    if (!empty($personnel_by_role['project_leaders'])) {
        $html .= implode('<br>', array_map(function($person) {
            return htmlspecialchars($person['name']);
        }, $personnel_by_role['project_leaders']));
    } else {
        $html .= 'Not specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Assistant Project Leader
    $html .= '<tr>';
    $html .= '<td style="border: none; padding: 5px; font-weight: bold;">Assistant Project Leader:</td>';
    $html .= '<td style="border: none; padding: 5px;">';
    
    if (!empty($personnel_by_role['assistant_project_leaders'])) {
        $html .= implode('<br>', array_map(function($person) {
            return htmlspecialchars($person['name']);
        }, $personnel_by_role['assistant_project_leaders']));
    } else {
        $html .= 'None';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Project Staff
    $html .= '<tr>';
    $html .= '<td style="border: none; padding: 5px; font-weight: bold;">Project Staff:</td>';
    $html .= '<td style="border: none; padding: 5px;">';
    
    if (!empty($personnel_by_role['project_staff'])) {
        $html .= implode('<br>', array_map(function($person) {
            return htmlspecialchars($person['name']);
        }, $personnel_by_role['project_staff']));
    } else {
        $html .= 'None';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td>';
    $html .= '</tr>';
    
    // Proponent
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">V. Proponent:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">' . htmlspecialchars($proposal['proponent'] ?? 'Not specified') . '</td>';
    $html .= '</tr>';
    
    // Participants
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">VI. Participants:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    $html .= '<tr style="background-color: #f8f9fa;">';
    $html .= '<th style="border: 1px solid black; padding: 5px; text-align: left;">Category</th>';
    $html .= '<th style="border: 1px solid black; padding: 5px; text-align: center;">Count</th>';
    $html .= '</tr>';
    
    // Internal participants
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Students (Male)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['students_male'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Students (Female)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['students_female'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Faculty (Male)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['faculty_male'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Faculty (Female)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['faculty_female'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    // External participants
    if (($sections['participants']['external_male'] ?? 0) > 0 || ($sections['participants']['external_female'] ?? 0) > 0) {
        $html .= '<tr>';
        $html .= '<td style="border: 1px solid black; padding: 5px;">External (Male)</td>';
        $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['external_male'] ?? 0) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="border: 1px solid black; padding: 5px;">External (Female)</td>';
        $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['external_female'] ?? 0) . '</td>';
        $html .= '</tr>';
    }
    
    // Totals
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Total (Male)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['total_male'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;">Total (Female)</td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;">' . ($sections['participants']['total_female'] ?? 0) . '</td>';
    $html .= '</tr>';
    
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 5px;"><strong>Total Participants</strong></td>';
    $html .= '<td style="border: 1px solid black; padding: 5px; text-align: center;"><strong>' . ($sections['participants']['total'] ?? 0) . '</strong></td>';
    $html .= '</tr>';
    
    $html .= '</table>';
    $html .= '</td>';
    $html .= '</tr>';
    
    // Add remaining sections
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">VII. Rationale/Background:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">' . nl2br(htmlspecialchars($sections['rationale'] ?? 'Not specified')) . '</td>';
    $html .= '</tr>';
    
    // Objectives
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">VIII. Objectives:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    $html .= '<strong>General Objective:</strong><br>';
    $html .= nl2br(htmlspecialchars($sections['objectives']['general'] ?? 'Not specified')) . '<br><br>';
    
    $html .= '<strong>Specific Objectives:</strong><br>';
    if (!empty($sections['objectives']['specific'])) {
        $html .= '<ul>';
        foreach ($sections['objectives']['specific'] as $objective) {
            $html .= '<li>' . htmlspecialchars($objective) . '</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= 'Not specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Strategies and Methods
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">IX. Description, Strategies, and Methods:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    
    $html .= '<strong>Strategies:</strong><br>';
    $html .= nl2br(htmlspecialchars($sections['strategies'] ?? 'Not specified')) . '<br><br>';
    
    $html .= '<strong>Methods:</strong><br>';
    if (!empty($sections['methods'])) {
        $html .= '<ul>';
        foreach ($sections['methods'] as $method) {
            if (is_array($method)) {
                $html .= '<li>' . htmlspecialchars(is_array($method) ? implode(': ', $method) : $method) . '</li>';
            } else {
                $html .= '<li>' . htmlspecialchars($method) . '</li>';
            }
        }
        $html .= '</ul>';
    } else {
        $html .= 'Not specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Workplan
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">X. Work Plan:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    
    if (!empty($sections['workplan'])) {
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr style="background-color: #f8f9fa;">';
        $html .= '<th style="border: 1px solid black; padding: 5px;">Activity</th>';
        $html .= '<th style="border: 1px solid black; padding: 5px;">Timeline</th>';
        $html .= '</tr>';
        
        foreach ($sections['workplan'] as $activity) {
            if (is_array($activity) && count($activity) >= 2) {
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($activity[0] ?? '') . '</td>';
                
                // Handle timeline which might be an array or string
                $timeline = $activity[1] ?? '';
                if (is_array($timeline)) {
                    $html .= '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars(implode(', ', $timeline)) . '</td>';
                } else {
                    $html .= '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($timeline) . '</td>';
                }
                
                $html .= '</tr>';
            }
        }
        
        $html .= '</table>';
    } else {
        $html .= 'No work plan specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Financial Requirements
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">XI. Financial Requirements:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    $html .= '<strong>Source of Funds:</strong> ' . htmlspecialchars($sections['financial']['source'] ?? 'Not specified') . '<br>';
    $html .= '<strong>Total Budget:</strong> ₱' . number_format(floatval($sections['financial']['total'] ?? 0), 2);
    $html .= '</td>';
    $html .= '</tr>';
    
    // Monitoring and Evaluation
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">XII. Monitoring and Evaluation:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    
    if (!empty($sections['monitoring_evaluation'])) {
        $html .= '<table style="width: 100%; border-collapse: collapse;" class="monitoring-table">';
        $html .= '<tr style="background-color: #f8f9fa;">';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Objectives</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Indicators</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Baseline</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Target</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Source</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Method</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Frequency</th>';
        $html .= '<th style="border: 1px solid black; padding: 4px; text-align: center;">Responsible</th>';
        $html .= '</tr>';
        
        foreach ($sections['monitoring_evaluation'] as $item) {
            if (is_array($item) && count($item) >= 8) {
                $html .= '<tr>';
                for ($i = 0; $i < 8; $i++) {
                    $html .= '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($item[$i] ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '</table>';
    } else {
        $html .= 'No monitoring and evaluation plan specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    // Sustainability Plan
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid black; padding: 10px; vertical-align: top;">XIII. Sustainability Plan:</td>';
    $html .= '<td style="border: 1px solid black; padding: 10px;">';
    
    if (!empty($sections['sustainability']) && is_array($sections['sustainability'])) {
        $html .= '<ul>';
        foreach ($sections['sustainability'] as $plan) {
            $html .= '<li>' . htmlspecialchars($plan) . '</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= 'No sustainability plan specified';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    
    $html .= '</table>';
    $html .= '</div>'; // End main section
    
    // Signatures section
    $html .= '<div class="signatures-section" style="margin-top: 30px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse;" class="signatures-table">';
    $html .= '<tr>';
    
    // Prepared by (Project Leader)
    $html .= '<td style="width: 33%; text-align: center; vertical-align: bottom; padding: 10px;">';
    $html .= '<div class="signature-line" style="border-bottom: 1px solid black; width: 80%; margin: 30px auto 5px;"></div>';
    
    if (!empty($personnel_by_role['project_leaders'][0]['name'])) {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">' . htmlspecialchars($personnel_by_role['project_leaders'][0]['name']) . '</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">Project Leader</p>';
    } else {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">________________________</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">Project Leader</p>';
    }
    
    $html .= '</td>';
    
    // Reviewed by
    $html .= '<td style="width: 33%; text-align: center; vertical-align: bottom; padding: 10px;">';
    $html .= '<div class="signature-line" style="border-bottom: 1px solid black; width: 80%; margin: 30px auto 5px;"></div>';
    
    if (!empty($signatories['gad_coordinator_name'])) {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">' . htmlspecialchars($signatories['gad_coordinator_name']) . '</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">GAD Coordinator</p>';
    } else {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">________________________</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">GAD Coordinator</p>';
    }
    
    $html .= '</td>';
    
    // Approved by
    $html .= '<td style="width: 33%; text-align: center; vertical-align: bottom; padding: 10px;">';
    $html .= '<div class="signature-line" style="border-bottom: 1px solid black; width: 80%; margin: 30px auto 5px;"></div>';
    
    if (!empty($signatories['campus_director_name'])) {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">' . htmlspecialchars($signatories['campus_director_name']) . '</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">Campus Director</p>';
    } else {
        $html .= '<p class="signature-name" style="font-weight: bold; margin: 0;">________________________</p>';
        $html .= '<p class="signature-position" style="font-style: italic; margin: 0;">Campus Director</p>';
    }
    
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    
    // Footer note
    $html .= '<div style="margin-top: 10px; text-align: center;">';
    $html .= '<p style="font-style: italic; margin-bottom: 5px;">Required Attachment: Activity Proposal</p>';
    $html .= '<p style="font-style: italic; margin-bottom: 0;">Cc: GAD Central, Office of the College Dean</p>';
    $html .= '</div>';
    
    $html .= '</div>'; // End signatures section
    
    $html .= '</div>'; // End proposal container
    
    // Return the HTML
    echo $html;
    
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>An error occurred: " . $e->getMessage() . "</div>";
    exit;
}
?>