<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("get_gpb_report.php accessed");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in in get_gpb_report.php");
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    // Database connection
    require_once '../config.php';
    // Use PDO connection from config.php
    
    // Get parameters and clean them
    $campus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
    $year = isset($_GET['year']) ? trim($_GET['year']) : '';
    $all_campuses = isset($_GET['all_campuses']) && $_GET['all_campuses'] == 1;

    // Debug log
    error_log("Raw parameters - campus: '" . $campus . "', year: '" . $year . "', all_campuses: " . ($all_campuses ? 'true' : 'false'));
    
    // Force year to be treated as integer to avoid type mismatches
    $year = (int)$year;
    error_log("Year after casting to integer: " . $year);

    // Validate parameters
    if (empty($year) || (empty($campus) && !$all_campuses)) {
        error_log("Missing required parameters in get_gpb_report.php");
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit();
    }

    // For "All Campuses" selection, we'll modify our query
    $whereClause = $all_campuses ? "g.year = ?" : "g.campus = ? AND g.year = ?";
    $whereParams = $all_campuses ? [$year] : [$campus, $year];

    // Check if data exists for the campus/year
    $checkQuery = "SELECT COUNT(*) FROM gpb_entries g WHERE " . $whereClause;
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute($whereParams);
    $count = $checkStmt->fetchColumn();

    if ($all_campuses) {
        error_log("Found {$count} records for all campuses and year '{$year}'");
    } else {
        error_log("Found {$count} records for campus '{$campus}' and year '{$year}'");
    }

    if ($count === 0) {
        if ($all_campuses) {
            echo json_encode([
                'status' => 'error',
                'message' => "No data found for any campus in year '{$year}'"
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => "No data found for campus '{$campus}' and year '{$year}'"
            ]);
        }
        exit();
    }

    // Check if status field exists in gpb_entries table
    $checkStatusField = "SHOW COLUMNS FROM gpb_entries LIKE 'status'";
    $checkStatusStmt = $pdo->prepare($checkStatusField);
    $checkStatusStmt->execute();
    $statusFieldExists = $checkStatusStmt->rowCount() > 0;
    
    // Main query - using the actual column names from the table
    $query = "SELECT 
        g.id as gpb_id,
        g.category,
        g.gender_issue,
        g.cause_of_issue,
        g.gad_objective,
        g.relevant_agency,
        g.generic_activity,
        g.specific_activities,
        g.male_participants,
        g.female_participants,
        g.total_participants,
        g.gad_budget,
        g.source_of_budget,
        g.responsible_unit,
        g.created_at,
        g.campus,
        g.year,";
        
    // Only include status if the field exists
    if ($statusFieldExists) {
        $query .= "g.status,";
    }
    
    $query .= "t.total_gaa,
        t.total_gad_fund,
        /* Adding actual results data from ppas_forms table */
        (SELECT COALESCE(SUM(p.grand_total_male), 0) FROM ppas_forms p WHERE p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year) as actual_male,
        (SELECT COALESCE(SUM(p.grand_total_female), 0) FROM ppas_forms p WHERE p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year) as actual_female,
        (SELECT COALESCE(SUM(p.grand_total), 0) FROM ppas_forms p WHERE p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year) as actual_participants,
        (SELECT COALESCE(SUM(p.approved_budget), 0) FROM ppas_forms p WHERE p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year) as actual_budget,
        /* Count of activities completed */
        (SELECT COUNT(*) FROM ppas_forms p WHERE p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year) as activities_completed
    FROM gpb_entries g
    LEFT JOIN target t ON BINARY g.campus = BINARY t.campus 
        AND g.year = t.year
    WHERE " . $whereClause;
    
    // Only add status constraint if field exists
    if ($statusFieldExists) {
        $query .= " AND (g.status IS NULL OR g.status = 'approved' OR g.status = 'Approved')";
    }
    
    $query .= " ORDER BY " . ($all_campuses ? "g.campus, " : "") . "g.id";

    error_log("Executing query: " . $query);
    if ($all_campuses) {
        error_log("With parameters - year: {$year} (All Campuses)");
    } else {
        error_log("With parameters - campus: {$campus}, year: {$year}");
    }

    $stmt = $pdo->prepare($query);
    
    if (!$stmt) {
        $error = $pdo->errorInfo();
        error_log("Prepare statement failed: " . print_r($error, true));
        throw new Exception("Failed to prepare statement: " . $error[2]);
    }

    // Bind parameters based on whether we're querying all campuses or a specific one
    if ($all_campuses) {
        $stmt->bindParam(1, $year, PDO::PARAM_STR);
    } else {
        $stmt->bindParam(1, $campus, PDO::PARAM_STR);
        $stmt->bindParam(2, $year, PDO::PARAM_STR);
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->errorInfo();
        error_log("Execute statement failed: " . print_r($error, true));
        throw new Exception("Failed to execute statement: " . $error[2]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Successfully fetched " . count($results) . " items");

    // Now fetch all activities for these gender issues
    if (count($results) > 0) {
        // Extract all gender issue IDs
        $genderIssueIds = array_map(function($item) {
            return $item['gpb_id'];
        }, $results);
        
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($genderIssueIds) - 1) . '?';
        
        // Debug with explicit logging
        error_log("Gender issue IDs for activity lookup: " . implode(", ", $genderIssueIds));
        
        // For all campuses, we need to adjust our query to join against the original data
        if ($all_campuses) {
            $activityQuery = "SELECT p.gender_issue_id, p.activity, p.grand_total_male, p.grand_total_female, p.ps_attribution, 
                                     p.start_date, p.end_date, p.location, p.approved_budget, p.campus, p.year
                             FROM ppas_forms p 
                             JOIN gpb_entries g ON p.gender_issue_id = g.id AND p.campus = g.campus AND p.year = g.year
                             WHERE p.gender_issue_id IN ($placeholders) 
                             AND p.year = ?
                             ORDER BY p.campus, p.gender_issue_id, p.activity";
                
            $activityParams = array_merge($genderIssueIds, [$year]);
        } else {
            $activityQuery = "SELECT p.gender_issue_id, p.activity, p.grand_total_male, p.grand_total_female, p.ps_attribution, 
                                     p.start_date, p.end_date, p.location, p.approved_budget
                             FROM ppas_forms p 
                             WHERE p.gender_issue_id IN ($placeholders) 
                             AND p.campus = ? AND p.year = ?
                             ORDER BY p.gender_issue_id, p.activity";
                
            $activityParams = array_merge($genderIssueIds, [$campus, $year]);
        }
        
        $activityStmt = $pdo->prepare($activityQuery);
        
        if (!$activityStmt) {
            error_log("Failed to prepare activity query: " . print_r($pdo->errorInfo(), true));
        } else {
            error_log("Activity query: " . $activityQuery);
            error_log("Activity params: " . print_r($activityParams, true));
            
            if (!$activityStmt->execute($activityParams)) {
                error_log("Failed to execute activity query: " . print_r($activityStmt->errorInfo(), true));
            } else {
                $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Fetched " . count($activities) . " activities");
                
                // Group activities by gender issue ID
                $activitiesByGenderIssue = [];
                foreach ($activities as $activity) {
                    $genderIssueId = $activity['gender_issue_id'];
                    if (!isset($activitiesByGenderIssue[$genderIssueId])) {
                        $activitiesByGenderIssue[$genderIssueId] = [];
                    }
                    $activitiesByGenderIssue[$genderIssueId][] = $activity;
                }
                
                // Add activities to each result
                foreach ($results as &$result) {
                    $genderIssueId = $result['gpb_id'];
                    $result['activities'] = isset($activitiesByGenderIssue[$genderIssueId]) ? 
                        $activitiesByGenderIssue[$genderIssueId] : [];
                        
                    // Log each result's activities count for debugging
                    error_log("Gender issue ID {$genderIssueId} has " . count($result['activities']) . " activities");
                }
            }
            
            $activityStmt = null;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (PDOException $e) {
    error_log("PDO Error in get_gpb_report.php: " . $e->getMessage());
    error_log("PDO Error Code: " . $e->getCode());
    error_log("PDO Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_gpb_report.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// PDO connections are automatically closed when the script ends
$stmt = null;
$pdo = null; 