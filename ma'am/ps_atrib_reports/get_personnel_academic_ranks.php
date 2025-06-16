<?php
// Include database connection
require_once '../config.php';
header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection failed");
    }
    
    // Get PPA ID from request
    $ppaId = isset($_GET['ppaId']) ? intval($_GET['ppaId']) : 0;
    
    if ($ppaId <= 0) {
        throw new Exception("Invalid PPA ID");
    }
    
    // First fetch personnel names from the PPA
    $query = "SELECT 
                project_leader,
                assistant_project_leader,
                project_staff_coordinator,
                ps_attribution
              FROM ppas_forms 
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ppaId]);
    $ppa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ppa) {
        throw new Exception("PPA not found");
    }
    
    // Decode JSON arrays
    $leaders = json_decode($ppa['project_leader'], true) ?? [];
    $assistants = json_decode($ppa['assistant_project_leader'], true) ?? [];
    $staff = json_decode($ppa['project_staff_coordinator'], true) ?? [];
    
    // Initialize results array
    $personnel_ranks = [
        'leaders' => [],
        'assistants' => [],
        'staff' => []
    ];
    
    // Get academic ranks for each personnel category
    // Leaders
    if (!empty($leaders)) {
        $placeholders = implode(',', array_fill(0, count($leaders), '?'));
        $query = "SELECT p.name, p.academic_rank, ar.monthly_salary 
                  FROM personnel p
                  JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
                  WHERE p.name IN ($placeholders)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($leaders);
        $personnel_ranks['leaders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Assistant leaders
    if (!empty($assistants)) {
        $placeholders = implode(',', array_fill(0, count($assistants), '?'));
        $query = "SELECT p.name, p.academic_rank, ar.monthly_salary 
                  FROM personnel p
                  JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
                  WHERE p.name IN ($placeholders)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($assistants);
        $personnel_ranks['assistants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Staff
    if (!empty($staff)) {
        $placeholders = implode(',', array_fill(0, count($staff), '?'));
        $query = "SELECT p.name, p.academic_rank, ar.monthly_salary 
                  FROM personnel p
                  JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
                  WHERE p.name IN ($placeholders)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($staff);
        $personnel_ranks['staff'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // First get all academic ranks from the database
    $query = "SELECT academic_rank as rank_name, monthly_salary FROM academic_ranks ORDER BY academic_rank ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $all_ranks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize ranks summary with all available ranks (with zero counts)
    $ranks_summary = [];
    foreach ($all_ranks as $rank) {
        $ranks_summary[$rank['rank_name']] = [
            'rank_name' => $rank['rank_name'],
            'personnel_count' => 0,
            'monthly_salary' => floatval($rank['monthly_salary'])
        ];
    }
    
    // Now update the counts for ranks that have personnel
    foreach (['leaders', 'assistants', 'staff'] as $category) {
        foreach ($personnel_ranks[$category] as $person) {
            if (isset($person['academic_rank']) && isset($ranks_summary[$person['academic_rank']])) {
                $ranks_summary[$person['academic_rank']]['personnel_count']++;
            }
        }
    }
    
    // Convert to indexed array
    $result = array_values($ranks_summary);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'academicRanks' => $result,
        'ps_attribution' => $ppa['ps_attribution'] ?? null
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?> 