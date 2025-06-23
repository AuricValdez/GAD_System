<?php
session_start();
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid ID']);
    exit();
}

$id = intval($_GET['id']);

try {
    $conn = getConnection();
    $stmt = $conn->prepare('SELECT * FROM ppas_forms WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
        exit();
    }
    // Decode JSON fields (list all JSON fields here)
    $jsonFields = [
        'sdg',
        'office_college_organization',
        'program_list',
        'project_leader',
        'project_leader_responsibilities',
        'assistant_project_leader',
        'assistant_project_leader_responsibilities',
        'project_staff_coordinator',
        'project_staff_coordinator_responsibilities',
        'specific_objectives',
        'strategy',
        'expected_output',
        'specific_plan',
        'workplan_activity',
        'workplan_date',
        'financial_plan_items',
        'financial_plan_quantity',
        'financial_plan_unit',
        'financial_plan_unit_cost',
        'source_of_fund',
        'monitoring_objectives',
        'monitoring_baseline_data',
        'monitoring_data_source',
        'monitoring_frequency_data_collection',
        'monitoring_performance_indicators',
        'monitoring_performance_target',
        'monitoring_collection_method',
        'monitoring_office_persons_involved'
    ];
    foreach ($jsonFields as $field) {
        if (isset($row[$field])) {
            $row[$field] = json_decode($row[$field], true);
        }
    }
    echo json_encode(['success' => true, 'data' => $row]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
