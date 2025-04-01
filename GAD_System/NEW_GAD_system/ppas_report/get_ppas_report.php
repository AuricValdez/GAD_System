<?php
// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../config.php';

try {
    $campus = $_GET['campus'] ?? '';
    $year = $_GET['year'] ?? '';
    $quarter = $_GET['quarter'] ?? '';

    // Validate required parameters
    if (empty($campus) || empty($year) || empty($quarter)) {
        throw new Exception('Missing required parameters');
    }

    // Main query to get PPAS data with gender issues
    $query = "
        SELECT 
            p.*, 
            g.gender_issue,
            GROUP_CONCAT(
                CONCAT(pp.role, ':', pers.name)
                ORDER BY FIELD(pp.role, 'Project Leader', 'Assistant Project Leader', 'Staff', 'Other Internal Participants')
                SEPARATOR '|'
            ) as personnel_list
        FROM ppas_forms p
        LEFT JOIN gpb_entries g ON p.gender_issue_id = g.id
        LEFT JOIN ppas_personnel pp ON p.id = pp.ppas_form_id
        LEFT JOIN personnel pers ON pp.personnel_id = pers.id
        WHERE p.campus = ? 
        AND p.year = ? 
        AND p.quarter = ?
        GROUP BY p.id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('sis', $campus, $year, $quarter);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = array();
    while ($row = $result->fetch_assoc()) {
        // Calculate duration in hours
        $start_time = new DateTime($row['start_time']);
        $end_time = new DateTime($row['end_time']);
        $duration = $row['total_duration_hours'];

        // Format participants data
        $participants = array(
            'students' => array(
                'male' => $row['students_male'],
                'female' => $row['students_female']
            ),
            'faculty' => array(
                'male' => $row['faculty_male'],
                'female' => $row['faculty_female']
            ),
            'external' => array(
                'type' => $row['external_type'],
                'male' => $row['external_male'],
                'female' => $row['external_female']
            )
        );

        // Process personnel list
        $personnel = array();
        if ($row['personnel_list']) {
            $personnel = array_map(function($item) {
                return trim($item);
            }, explode('|', $row['personnel_list']));
        }

        // Format the report data
        $reports[] = array(
            'gender_issue' => $row['gender_issue'],
            'project' => $row['project'],
            'program' => $row['program'],
            'activity' => $row['activity'],
            'start_date' => date('F j, Y', strtotime($row['start_date'])),
            'end_date' => date('F j, Y', strtotime($row['end_date'])),
            'date_conducted' => date('F j, Y', strtotime($row['start_date'])),
            'duration' => $duration,
            'participants' => $participants,
            'location' => $row['location'],
            'personnel' => $personnel,
            'budget' => $row['approved_budget'],
            'actual_cost' => $row['approved_budget'], // Assuming same as budget if no actual cost field
            'ps_attribution' => $row['ps_attribution'],
            'source_of_budget' => $row['source_of_budget']
        );
    }

    echo json_encode([
        'success' => true,
        'data' => $reports
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error generating report: ' . $e->getMessage()
    ]);
} 