<?php
session_start();
error_reporting(0); // Disable error reporting to prevent HTML errors from being output
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Check if format=word parameter is provided
$wordFormat = isset($_GET['format']) && $_GET['format'] === 'word';

// Set appropriate content type
if (!$wordFormat) {
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

if (!$campus || !$year || !$proposal_id) {
    if ($wordFormat) {
        echo "<p>Error: Missing required parameters</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    }
    exit;
}

try {
    // Direct database connection
    $db = new PDO(
        "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    error_log("Database connection successful");
    
    // Get proposal details
    $stmt = $db->prepare("
        SELECT 
            gp.*,
            CONCAT(
                DATE_FORMAT(gp.start_date, '%M %d, %Y'),
                ' to ',
                DATE_FORMAT(gp.end_date, '%M %d, %Y')
            ) as duration,
            CONCAT(
                'Male: ', COALESCE(gp.male_beneficiaries, 0),
                ', Female: ', COALESCE(gp.female_beneficiaries, 0),
                ', Total: ', COALESCE(gp.total_beneficiaries, 0)
            ) as beneficiaries
        FROM gad_proposals gp
        WHERE gp.id = :proposal_id
        AND gp.created_by = :campus
        AND gp.year = :year
    ");
    
    $stmt->execute([
        'proposal_id' => $proposal_id,
        'campus' => $campus,
        'year' => $year
    ]);
    
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
    
    // Get personnel - using the project_leaders, assistant_project_leaders, and project_staff directly from gad_proposals
    $personnel_by_role = [
        'project_leaders' => $proposal['project_leaders'] ?? '',
        'assistant_project_leaders' => $proposal['assistant_project_leaders'] ?? '',
        'project_staff' => $proposal['project_staff'] ?? ''
    ];
    
    error_log("Personnel data: " . json_encode($personnel_by_role));
    
    // Get activities
    $stmt = $db->prepare("
        SELECT 
            title,
            details,
            sequence,
            created_at
        FROM gad_proposal_activities 
        WHERE proposal_id = :proposal_id
        ORDER BY sequence ASC
    ");
    
    $stmt->execute(['proposal_id' => $proposal_id]);
    $activities = $stmt->fetchAll();
    
    error_log("Found activities: " . json_encode($activities));
    
    // Format the response with null checks for all fields
    $response = array(
        'status' => 'success',
        'data' => array(
            'campus' => $campus,
            'year' => $year,
            'quarter' => safe_get($proposal, 'quarter'),
            'sections' => array(
                'title' => safe_get($proposal, 'activity_title'),
                'date_venue' => array(
                    'venue' => safe_get($proposal, 'venue'),
                    'date' => safe_get($proposal, 'duration')
                ),
                'delivery_mode' => safe_get($proposal, 'delivery_mode'),
                'project_team' => array(
                    'project_leaders' => array(
                        'names' => safe_get($proposal, 'project_leaders'),
                        'responsibilities' => safe_get($proposal, 'leader_responsibilities')
                    ),
                    'assistant_project_leaders' => array(
                        'names' => safe_get($proposal, 'assistant_project_leaders'),
                        'responsibilities' => safe_get($proposal, 'assistant_responsibilities')
                    ),
                    'project_staff' => array(
                        'names' => safe_get($proposal, 'project_staff'),
                        'responsibilities' => safe_get($proposal, 'staff_responsibilities')
                    )
                ),
                'partner_offices' => safe_get($proposal, 'partner_offices'),
                'participants' => array(
                    'male' => intval(safe_get($proposal, 'male_beneficiaries', 0)),
                    'female' => intval(safe_get($proposal, 'female_beneficiaries', 0)),
                    'total' => intval(safe_get($proposal, 'total_beneficiaries', 0))
                ),
                'rationale' => safe_get($proposal, 'rationale'),
                'objectives' => array(
                    'general' => safe_get($proposal, 'general_objective'),
                    'specific' => safe_get($proposal, 'specific_objectives') ? explode("\n", $proposal['specific_objectives']) : array()
                ),
                'strategies' => safe_get($proposal, 'strategies'),
                'workplan' => array_map(function($activity) {
                    return array(
                        'activity' => safe_get($activity, 'title'),
                        'timeline' => safe_get($activity, 'details')
                    );
                }, $activities),
                'financial' => array(
                    'source' => safe_get($proposal, 'budget_source'),
                    'total' => floatval(safe_get($proposal, 'total_budget', 0)),
                    'breakdown' => json_decode(safe_get($proposal, 'budget_breakdown'), true) ?: array()
                ),
                'monitoring' => safe_get($proposal, 'monitoring_mechanics'),
                'sustainability' => safe_get($proposal, 'sustainability_plan'),
                'signatures' => array(
                    'prepared_by' => array(
                        'name' => safe_get($proposal, 'prepared_by'),
                        'position' => 'Project Leader'
                    ),
                    'reviewed_by' => array(
                        'name' => safe_get($proposal, 'reviewed_by'),
                        'position' => 'GAD Coordinator'
                    ),
                    'recommending_approval' => array(
                        'name' => safe_get($proposal, 'recommending_approval'),
                        'position' => 'Vice Chancellor for Academic Affairs'
                    ),
                    'approved_by' => array(
                        'name' => safe_get($proposal, 'approved_by'),
                        'position' => 'Campus Director'
                    )
                )
            )
        )
    );
    
    if ($wordFormat) {
        // Return HTML content for Word export instead of JSON
        $data = $response['data'];
        $sections = $data['sections'];
        
        // Create a raw HTML version of the proposal for Word export
        $html = '<div class="proposal-container">';
        
        // Header Section
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr>';
        $html .= '<td style="width: 33.33%; border: 1pt solid black; padding: 5pt;">Reference No.: BatStateU-FO-ESO-09</td>';
        $html .= '<td style="width: 33.33%; border: 1pt solid black; padding: 5pt;">Effectivity Date: August 25, 2023</td>';
        $html .= '<td style="width: 33.33%; border: 1pt solid black; padding: 5pt;">Revision No.: 00</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Title and Checkbox Section
        $html .= '<div style="text-align: center; margin: 15pt 0; border-bottom: 1pt solid black; padding-bottom: 10pt;">';
        $html .= '<div style="font-weight: bold; margin-bottom: 10pt;">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>';
        $html .= '<div style="margin-top: 10pt;">';
        $html .= '☐ Program&nbsp;&nbsp;☐ Project&nbsp;&nbsp;☒ Activity';
        $html .= '</div>';
        $html .= '</div>';
        
        // Main Content Table
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        
        // Add sections
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; width: 25%;">I. Title:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">"' . htmlspecialchars($sections['title'] ?? '') . '"</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">II. Date and Venue:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . htmlspecialchars($sections['date_venue']['date'] ?? '') . ' at ' . htmlspecialchars($sections['date_venue']['venue'] ?? '') . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">III. Mode of delivery (online/face-to-face):</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . htmlspecialchars($sections['delivery_mode'] ?? '') . '</td>';
        $html .= '</tr>';
        
        // Project Team
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; vertical-align: top;">IV. Project Team:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">';
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
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">V. Partner Office/College/Department:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . htmlspecialchars($sections['partner_offices'] ?? '') . '</td>';
        $html .= '</tr>';
        
        // Participants
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; vertical-align: top;">VI. Type of Participants:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">';
        $html .= htmlspecialchars($sections['participants']['type'] ?? '') . '<br>';
        $html .= '<table style="width: 50%; border-collapse: collapse; margin-top: 10pt;">';
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">Male</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; text-align: center;">' . ($sections['participants']['male'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">Female</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; text-align: center;">' . ($sections['participants']['female'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">Total</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; text-align: center;">' . ($sections['participants']['total'] ?? 0) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Add remaining sections similarly
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">VII. Rationale/Background:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . nl2br(htmlspecialchars($sections['rationale'] ?? '')) . '</td>';
        $html .= '</tr>';
        
        // Objectives
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt; vertical-align: top;">VIII. Objectives:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">';
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
        $html .= '<td style="border: 1pt solid black; padding: 5pt; vertical-align: top;">IX. Description, Strategies, and Methods (Activities / Schedule):</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">';
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
        foreach ($sections['workplan'] as $workplan) {
            $html .= '<strong>' . htmlspecialchars($workplan['activity'] ?? '') . '</strong><br>';
            $html .= htmlspecialchars($workplan['timeline'] ?? '') . '<br><br>';
        }
        $html .= '</td>';
        $html .= '</tr>';
        
        // Financial Requirements
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">XI. Financial Requirements and Source of Funds:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">';
        $html .= '<strong>Source:</strong> ' . htmlspecialchars($sections['financial']['source'] ?? '') . '<br>';
        $html .= '<strong>Total Budget:</strong> ₱' . number_format(floatval($sections['financial']['total'] ?? 0), 2);
        $html .= '</td>';
        $html .= '</tr>';
        
        // Monitoring and Evaluation
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">XII. Monitoring and Evaluation Mechanics / Plan:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . nl2br(htmlspecialchars($sections['monitoring'] ?? '')) . '</td>';
        $html .= '</tr>';
        
        // Sustainability Plan
        $html .= '<tr>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">XIII. Sustainability Plan:</td>';
        $html .= '<td style="border: 1pt solid black; padding: 5pt;">' . nl2br(htmlspecialchars($sections['sustainability'] ?? '')) . '</td>';
        $html .= '</tr>';
        
        $html .= '</table>';
        
        // Signature Section
        $html .= '<br>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr>';
        
        // Prepared by
        $html .= '<td style="width: 25%; padding: 15pt; text-align: center; vertical-align: bottom; border: 1pt solid black;">';
        $html .= '<p>Prepared by:</p><br><br>';
        $html .= '<div style="border-bottom: 1pt solid black; margin: 0 auto; width: 80%;"></div>';
        $html .= '<p style="margin: 5pt 0;"><strong>' . htmlspecialchars($sections['signatures']['prepared_by']['name'] ?? '') . '</strong></p>';
        $html .= '<p style="margin: 0;">' . htmlspecialchars($sections['signatures']['prepared_by']['position'] ?? '') . '</p>';
        $html .= '</td>';
        
        // Reviewed by
        $html .= '<td style="width: 25%; padding: 15pt; text-align: center; vertical-align: bottom; border: 1pt solid black;">';
        $html .= '<p>Reviewed by:</p><br><br>';
        $html .= '<div style="border-bottom: 1pt solid black; margin: 0 auto; width: 80%;"></div>';
        $html .= '<p style="margin: 5pt 0;"><strong>' . htmlspecialchars($sections['signatures']['reviewed_by']['name'] ?? '') . '</strong></p>';
        $html .= '<p style="margin: 0;">' . htmlspecialchars($sections['signatures']['reviewed_by']['position'] ?? '') . '</p>';
        $html .= '</td>';
        
        // Recommending Approval
        $html .= '<td style="width: 25%; padding: 15pt; text-align: center; vertical-align: bottom; border: 1pt solid black;">';
        $html .= '<p>Recommending Approval:</p><br><br>';
        $html .= '<div style="border-bottom: 1pt solid black; margin: 0 auto; width: 80%;"></div>';
        $html .= '<p style="margin: 5pt 0;"><strong>' . htmlspecialchars($sections['signatures']['recommending_approval']['name'] ?? '') . '</strong></p>';
        $html .= '<p style="margin: 0;">' . htmlspecialchars($sections['signatures']['recommending_approval']['position'] ?? '') . '</p>';
        $html .= '</td>';
        
        // Approved by
        $html .= '<td style="width: 25%; padding: 15pt; text-align: center; vertical-align: bottom; border: 1pt solid black;">';
        $html .= '<p>Approved by:</p><br><br>';
        $html .= '<div style="border-bottom: 1pt solid black; margin: 0 auto; width: 80%;"></div>';
        $html .= '<p style="margin: 5pt 0;"><strong>' . htmlspecialchars($sections['signatures']['approved_by']['name'] ?? '') . '</strong></p>';
        $html .= '<p style="margin: 0;">' . htmlspecialchars($sections['signatures']['approved_by']['position'] ?? '') . '</p>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="4" style="padding: 15pt 0 0 0; border: 1pt solid black;">';
        $html .= '<p>Date Signed: _________________</p>';
        $html .= '<p style="margin-top: 15pt;">Cc: GAD Central</p>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        $html .= '</div>';
        
        echo $html;
    } else {
        // Output JSON
        echo json_encode($response);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if ($wordFormat) {
        echo "<p>Error: Database error occurred</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
    exit;
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if ($wordFormat) {
        echo "<p>Error: An error occurred</p>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred']);
    }
    exit;
}