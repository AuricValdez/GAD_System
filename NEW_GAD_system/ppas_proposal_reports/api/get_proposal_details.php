<?php
session_start();
error_reporting(0); // Disable error reporting to prevent HTML errors from being output
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Check if format=word parameter is provided (handle multiple formats)
$format = $_GET['format'] ?? '';
$wordFormat = false;

// Check for different variations of the word format parameter
if (!empty($format)) {
    if ($format === 'word' || strpos($format, 'word') === 0) {
        $wordFormat = true;
        error_log("Word format detected: $format");
    }
}

// Set appropriate content type for Word export
if ($wordFormat) {
    // Set content type for Word documents
    header('Content-Type: application/msword');
    header('Content-Disposition: inline; filename="GAD_Proposal.doc"');
} else {
    header('Content-Type: application/json');
}

// Function to safely get array value with null default
function safe_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("Session not found. Current session data: " . print_r($_SESSION, true));
    if ($wordFormat) {
        echo "<p>Error: User not logged in</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    }
    exit;
}

// Get parameters
$campus = $_GET['campus'] ?? null;
$year = $_GET['year'] ?? null;
$proposal_id = $_GET['proposal_id'] ?? null;

error_log("Request parameters: campus=$campus, year=$year, proposal_id=$proposal_id, format=$format");

if (!$campus || !$year || !$proposal_id) {
    error_log("Missing required parameters: campus=$campus, year=$year, proposal_id=$proposal_id");
    if ($wordFormat) {
        echo "<p>Error: Missing required parameters</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    }
    exit;
}

try {
    // Use config file for database connection
    require_once '../../includes/config.php';
    
    // Enable detailed error logging 
    error_log("Using database: host=$servername, dbname=$dbname, user=$username");
    error_log("Parameters: proposal_id=$proposal_id, campus=$campus, year=$year, format=$format");
    
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
    error_log("Parameters: proposal_id=$proposal_id, campus=$campus, year=$year");
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([
            'proposal_id' => $proposal_id,
            'campus' => $campus,
            'year' => $year
        ]);
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        // Try a simpler query to debug
        error_log("Attempting simpler query to debug");
        $simple_query = "SELECT * FROM gad_proposals WHERE proposal_id = :proposal_id";
        $stmt = $db->prepare($simple_query);
        $stmt->execute(['proposal_id' => $proposal_id]);
    }
    
    $proposal = $stmt->fetch();
    
    if (!$proposal) {
        error_log("No proposal found for ID: $proposal_id, Campus: $campus, Year: $year");
        if ($wordFormat) {
            echo "<p>Error: Proposal not found</p>";
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Proposal not found']);
        }
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
    
    error_log("Personnel data: " . json_encode($personnel_by_role));
    
    // No need to fetch from gad_proposal_activities as this table doesn't exist
    // Activities are now stored in JSON fields in the gad_proposals table
    $activities = [];
    
    // Format the response with null checks for all fields
    $response = array(
        'status' => 'success',
        'data' => array(
            'campus' => $campus,
            'year' => $year,
            'quarter' => safe_get($proposal, 'quarter'),
            'sections' => array(
                'title' => safe_get($proposal, 'activity'),
                'date_venue' => array(
                    'venue' => safe_get($proposal, 'venue'),
                    'date' => safe_get($proposal, 'duration')
                ),
                'delivery_mode' => safe_get($proposal, 'mode_of_delivery'),
                'project_team' => array(
                    'project_leaders' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['project_leaders'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'project_leader_responsibilities'), true)
                    ),
                    'assistant_project_leaders' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['assistant_project_leaders'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'assistant_leader_responsibilities'), true)
                    ),
                    'project_staff' => array(
                        'names' => implode(', ', array_map(function($person) { 
                            return $person['name']; 
                        }, $personnel_by_role['project_staff'])),
                        'responsibilities' => json_decode(safe_get($proposal, 'staff_responsibilities'), true)
                    )
                ),
                'partner_offices' => safe_get($proposal, 'partner_office'),
                'participants' => array(
                    'students_male' => intval(safe_get($proposal, 'students_male', 0)),
                    'students_female' => intval(safe_get($proposal, 'students_female', 0)),
                    'faculty_male' => intval(safe_get($proposal, 'faculty_male', 0)),
                    'faculty_female' => intval(safe_get($proposal, 'faculty_female', 0)),
                    'total_internal_male' => intval(safe_get($proposal, 'total_internal_male', 0)),
                    'total_internal_female' => intval(safe_get($proposal, 'total_internal_female', 0)),
                    'external_type' => safe_get($proposal, 'external_type', ''),
                    'external_male' => intval(safe_get($proposal, 'external_male', 0)),
                    'external_female' => intval(safe_get($proposal, 'external_female', 0)),
                    'male' => intval(safe_get($proposal, 'total_male', 0)),
                    'female' => intval(safe_get($proposal, 'total_female', 0)),
                    'total' => intval(safe_get($proposal, 'total_beneficiaries', 0))
                ),
                'rationale' => safe_get($proposal, 'rationale'),
                'description' => safe_get($proposal, 'description'),
                'objectives' => array(
                    'general' => safe_get($proposal, 'general_objectives'),
                    'specific' => json_decode(safe_get($proposal, 'specific_objectives'), true) ?? array()
                ),
                'strategies' => json_decode(safe_get($proposal, 'strategies'), true) ?? array(),
                'methods' => json_decode(safe_get($proposal, 'methods'), true) ?? array(),
                'materials' => json_decode(safe_get($proposal, 'materials'), true) ?? array(),
                'workplan' => json_decode(safe_get($proposal, 'workplan'), true) ?? array(),
                'financial' => array(
                    'source' => safe_get($proposal, 'source_of_budget', 'GAA'),
                    'total' => floatval(safe_get($proposal, 'approved_budget', 0)),
                    'breakdown' => safe_get($proposal, 'budget_breakdown') 
                                     ? (json_decode($proposal['budget_breakdown'], true) !== null 
                                        ? json_decode($proposal['budget_breakdown'], true) 
                                        : $proposal['budget_breakdown']) 
                                     : ''
                ),
                'monitoring_evaluation' => json_decode(safe_get($proposal, 'monitoring_items'), true) ?? array(),
                'sustainability' => safe_get($proposal, 'sustainability_plan', ''),
                'specific_plans' => json_decode(safe_get($proposal, 'specific_plans'), true) ?? array()
            )
        )
    );
    
    if ($wordFormat) {
        // Return HTML content for Word export instead of JSON
        $data = $response['data'];
        $sections = $data['sections'];
        
        // Create a raw HTML version of the proposal for Word export with Word-specific styling
        $html = '<div class="proposal-container" style="width:100%; margin:0; padding:0;">';
        
        // Header Section
        $html .= '<table class="header-table" style="width:100%; border-collapse:collapse; mso-table-layout:fixed; mso-border-alt:solid black 1.0pt;">';
        $html .= '<tr>';
        $html .= '<td style="width:33.33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Reference No.: BatStateU-FO-ESO-09</td>';
        $html .= '<td style="width:33.33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Effectivity Date: August 25, 2023</td>';
        $html .= '<td style="width:33.33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Revision No.: 00</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Title and Checkbox Section
        $html .= '<div style="text-align:center; margin:15.0pt 0; border-bottom:solid black 1.0pt; padding-bottom:10.0pt; mso-border-bottom-alt:solid black 1.0pt;">';
        $html .= '<div style="font-weight:bold; margin-bottom:10.0pt;">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>';
        $html .= '<div style="margin-top:10.0pt; mso-element:para-border-div;">';
        $html .= '☐ Program&nbsp;&nbsp;☐ Project&nbsp;&nbsp;☒ Activity';
        $html .= '</div>';
        $html .= '</div>';
        
        // Main Content Table
        $html .= '<table style="width:100%; border-collapse:collapse; mso-table-layout:fixed; mso-border-alt:solid black 1.0pt;">';
        
        // Add sections
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; width:25%;">I. Title:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">"' . htmlspecialchars($sections['title'] ?? '') . '"</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">II. Date and Venue:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($sections['date_venue']['date'] ?? '') . ' at ' . htmlspecialchars($sections['date_venue']['venue'] ?? '') . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">III. Mode of delivery (online/face-to-face):</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($sections['delivery_mode'] ?? '') . '</td>';
        $html .= '</tr>';
        
        // Project Team
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; vertical-align: top;">IV. Project Team:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= '<strong>Project Leaders:</strong> ' . htmlspecialchars($sections['project_team']['project_leaders']['names'] ?? '') . '<br>';
        $html .= '<strong>Responsibilities:</strong><br>';
        $html .= nl2br(htmlspecialchars($sections['project_team']['project_leaders']['responsibilities'] ?? '')) . '<br><br>';
        
        $html .= '<strong>Asst. Project Leaders:</strong> ' . htmlspecialchars($sections['project_team']['assistant_project_leaders']['names'] ?? '') . '<br>';
        $html .= '<strong>Responsibilities:</strong><br>';
        $html .= nl2br(htmlspecialchars($sections['project_team']['assistant_project_leaders']['responsibilities'] ?? '')) . '<br><br>';
        
        $html .= '<strong>Project Staff:</strong><br>';
        $html .= htmlspecialchars($sections['project_team']['project_staff']['names'] ?? '') . '<br><br>';
        $html .= '<strong>Responsibilities:</strong><br>';
        $html .= nl2br(htmlspecialchars($sections['project_team']['project_staff']['responsibilities'] ?? ''));
        $html .= '</td>';
        $html .= '</tr>';
        
        // Continue adding other sections...
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">V. Partner Office/College/Department:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($sections['partner_offices'] ?? '') . '</td>';
        $html .= '</tr>';
        
        // Participants
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; vertical-align: top;">VI. Type of Participants:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= htmlspecialchars($sections['participants']['type'] ?? '') . '<br>';
        $html .= '<table style="width: 50%; border-collapse: collapse; margin-top: 10pt;">';
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Male</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align: center;">' . ($sections['participants']['male'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Female</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align: center;">' . ($sections['participants']['female'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">Total</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align: center;">' . ($sections['participants']['total'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Add remaining sections similarly
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">VII. Rationale/Background:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . nl2br(htmlspecialchars($sections['rationale'] ?? '')) . '</td>';
        $html .= '</tr>';
        
        // Objectives
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; vertical-align: top;">VIII. Objectives:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= '<strong>General Objective:</strong><br>';
        $html .= nl2br(htmlspecialchars($sections['objectives']['general'] ?? '')) . '<br><br>';
        $html .= '<strong>Specific Objectives:</strong><br>';
        $html .= 'The specific objectives of this project include:<br>';
        $html .= '<ul style="margin: 5pt 0 5pt 20pt; padding: 0;">';
        foreach ($sections['objectives']['specific'] as $objective) {
            $html .= '<li>' . htmlspecialchars($objective) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Strategies and Methods
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; vertical-align: top;">IX. Description, Strategies, and Methods (Activities / Schedule):</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= '<strong>Strategies:</strong><br>';
        if (!empty($sections['strategies'])) {
            $strategies = explode("\n", $sections['strategies']);
            $html .= '<ul style="margin: 5pt 0 5pt 20pt; padding: 0;">';
            foreach ($strategies as $strategy) {
                $html .= '<li>' . htmlspecialchars($strategy) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '<br><strong>Methods (Activities / Schedule):</strong><br>';
        if (!empty($sections['methods'])) {
            $html .= '<ul style="margin: 5pt 0 5pt 20pt; padding: 0;">';
            foreach ($sections['methods'] as $method) {
                if (is_array($method)) {
                    $html .= '<li><strong>' . htmlspecialchars($method[0] ?? '') . '</strong>';
                    if (isset($method[1]) && is_array($method[1])) {
                        $html .= '<ul>';
                        foreach ($method[1] as $detail) {
                            $html .= '<li>' . htmlspecialchars($detail) . '</li>';
                        }
                        $html .= '</ul>';
                    } else if (isset($method[1])) {
                        $html .= ': ' . htmlspecialchars($method[1]);
                    }
                    $html .= '</li>';
                } else {
                    $html .= '<li>' . htmlspecialchars($method) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        
        // Workplan
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">X. Work Plan (Timeline of Activities/Gantt Chart):</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10pt;">';
        $html .= '<tr>';
        $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color: #f2f2f2;">Activity</th>';
        $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color: #f2f2f2;">Timeline</th>';
        $html .= '</tr>';
        
        if (!empty($sections['workplan'])) {
            foreach ($sections['workplan'] as $work) {
                if (is_array($work) && count($work) >= 2) {
                    $activity = $work[0] ?? '';
                    $dates = $work[1] ?? [];
                    
                    $html .= '<tr>';
                    $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($activity) . '</td>';
                    $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
                    
                    if (is_array($dates)) {
                        $html .= htmlspecialchars(implode(', ', $dates));
                    } else {
                        $html .= htmlspecialchars($dates);
                    }
                    
                    $html .= '</td>';
                    $html .= '</tr>';
                }
            }
        } else {
            $html .= '<tr><td colspan="2" style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align: center;">No work plan data available</td></tr>';
        }
        
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Financial Requirements
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">XI. Financial Requirements and Source of Funds:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        $html .= '<strong>Source:</strong> ' . htmlspecialchars($sections['financial']['source'] ?? '') . '<br>';
        $html .= '<strong>Total Budget:</strong> ₱' . number_format(floatval($sections['financial']['total'] ?? 0), 2);
        $html .= '</td>';
        $html .= '</tr>';
        
        // Monitoring and Evaluation
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">XII. Monitoring and Evaluation Mechanics / Plan:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        
        if (!empty($sections['monitoring_evaluation'])) {
            $html .= '<table style="width:100%; border-collapse:collapse; mso-table-layout:fixed; mso-border-alt:solid black 1.0pt;" class="monitoring-table">';
            $html .= '<tr>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Objectives</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Performance Indicators</th>'; 
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Baseline Data</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Performance Target</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Data Source</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Collection Method</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Frequency</th>';
            $html .= '<th style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; background-color:#f2f2f2; mso-pattern:gray-15 auto; font-weight:bold; text-align:center;">Responsible</th>';
            $html .= '</tr>';
            
            // Add a fallback row if monitoring_evaluation exists but has no items
            if (count($sections['monitoring_evaluation']) === 0) {
                $html .= '<tr>';
                $html .= '<td colspan="8" style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align:center;">No monitoring data available</td>';
                $html .= '</tr>';
            } else {
                foreach ($sections['monitoring_evaluation'] as $item) {
                    if (is_array($item) && count($item) >= 8) {
                        $html .= '<tr>';
                        for ($i = 0; $i < 7; $i++) {
                            $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($item[$i] ?? '') . '</td>';
                        }
                        // Special styling for the Responsible column
                        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($item[7] ?? '') . '</td>';
                        $html .= '</tr>';
                    } else if (is_array($item)) {
                        // Handle case where item array doesn't have enough elements
                        $html .= '<tr>';
                        for ($i = 0; $i < 7; $i++) {
                            $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($item[$i] ?? '') . '</td>';
                        }
                        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">' . htmlspecialchars($item[7] ?? '') . '</td>';
                        $html .= '</tr>';
                    }
                }
            }
            
            $html .= '</table>';
        } else {
            $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10pt;" class="monitoring-table">';
            $html .= '<tr>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Objectives</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Performance Indicators</th>'; 
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Baseline Data</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Performance Target</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Data Source</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Collection Method</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Frequency</th>';
            $html .= '<th style="border: 0.5pt solid black; padding: 5px; background-color: #f2f2f2; font-weight: bold; text-align: center;">Responsible</th>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan="8" style="border: 0.5pt solid black; padding: 5px; text-align: center;">No monitoring data available</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }
        
        $html .= '</td>';
        $html .= '</tr>';
        
        // Sustainability Plan
        $html .= '<tr>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">XIII. Sustainability Plan:</td>';
        $html .= '<td style="border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt;">';
        
        if (!empty($sections['sustainability']) && is_array($sections['sustainability'])) {
            $html .= '<ul style="margin: 5pt 0 5pt 20pt; padding: 0;">';
            foreach ($sections['sustainability'] as $plan) {
                $html .= '<li>' . htmlspecialchars($plan) . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= htmlspecialchars($sections['sustainability'] ?? 'No sustainability plan available.');
        }
        
        $html .= '</td>';
        $html .= '</tr>';
        
        $html .= '</table>';
        
        // Signature Section (if available)
        $html .= '<div style="margin-top:20pt;">';
        $html .= '<table class="signatures-table" style="width:100%; border-collapse:collapse; mso-table-layout:fixed; border:none;">';
        $html .= '<tr>';
        
        // Prepared by
        $html .= '<td style="width:33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align:center; vertical-align:bottom;">';
        $html .= '<div style="margin-top:30pt;"></div>';  // Space for signature
        $html .= '<div style="border-bottom:solid black 1.0pt; width:80%; margin:0 auto;"></div>';
        $html .= '<p style="margin:5pt 0 0 0; font-weight:bold;">Prepared by:</p>';
        $html .= '<p style="margin:0; font-style:italic;">Project Leader</p>';
        $html .= '</td>';
        
        // Reviewed by
        $html .= '<td style="width:33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align:center; vertical-align:bottom;">';
        $html .= '<div style="margin-top:30pt;"></div>';  // Space for signature
        $html .= '<div style="border-bottom:solid black 1.0pt; width:80%; margin:0 auto;"></div>';
        $html .= '<p style="margin:5pt 0 0 0; font-weight:bold;">Reviewed by:</p>';
        $html .= '<p style="margin:0; font-style:italic;">Department Chairperson</p>';
        $html .= '</td>';
        
        // Approved by
        $html .= '<td style="width:33%; border:solid black 1.0pt; mso-border-alt:solid black 1.0pt; padding:5.0pt; text-align:center; vertical-align:bottom;">';
        $html .= '<div style="margin-top:30pt;"></div>';  // Space for signature
        $html .= '<div style="border-bottom:solid black 1.0pt; width:80%; margin:0 auto;"></div>';
        $html .= '<p style="margin:5pt 0 0 0; font-weight:bold;">Approved by:</p>';
        $html .= '<p style="margin:0; font-style:italic;">Dean/Director</p>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Add a small note about attachments
        $html .= '<div style="margin-top:10pt; font-style:italic; border:none !important;">';
        $html .= '<p style="border:none !important; margin:0;">Required Attachment: Copy of project/activity proposal/letter</p>';
        $html .= '<p style="border:none !important; margin:0;">Cc: GAD Central</p>';
        $html .= '<p style="border:none !important; margin:0;">Office of the College Dean</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // End of the document
        $html .= '</div>'; // End of proposal-container

        // Output the HTML directly
        echo $html;
        exit;
    } else {
        // Output JSON
        echo json_encode($response);
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if ($wordFormat) {
        echo "<p>Error: Database error occurred - " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database error occurred: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }
    exit;
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if ($wordFormat) {
        echo "<p>Error: An error occurred - " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'An error occurred: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }
    exit;
}