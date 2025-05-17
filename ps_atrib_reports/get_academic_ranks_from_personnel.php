<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    // Get PPAS ID from the request
    $ppaId = isset($_GET['ppaId']) ? (int)$_GET['ppaId'] : 0;
    
    if ($ppaId <= 0) {
        throw new Exception('Invalid PPAS ID');
    }
    
    // Use academic_ranks (plural) table which has all the fields we need
    $checkTable = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $ranksTableExists = $checkTable->rowCount() > 0;
    
    if ($ranksTableExists) {
        // Fetch from academic_ranks table
        $stmt = $pdo->query("SELECT id, academic_rank as rank_name, monthly_salary FROM academic_ranks ORDER BY monthly_salary ASC");
        $academicRanks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($academicRanks)) {
            // Get the count of personnel for each role in the ppas_personnel table
            $personnelCountStmt = $pdo->prepare("
                SELECT role, COUNT(*) as count 
                FROM ppas_personnel 
                WHERE ppas_id = ?
                GROUP BY role
            ");
            $personnelCountStmt->execute([$ppaId]);
            $roleCounts = $personnelCountStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Create a roles count map
            $roleCountMap = [];
            foreach ($roleCounts as $role) {
                $roleCountMap[$role['role']] = (int)$role['count'];
            }
            
            // Count the total number of personnel in this PPAS
            $totalPersonnelStmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM ppas_personnel
                WHERE ppas_id = ?
            ");
            $totalPersonnelStmt->execute([$ppaId]);
            $totalPersonnel = (int)$totalPersonnelStmt->fetchColumn();
            
            // Now prepare ranks with personnel counts
            $ranks = [];
            
            // Get the title of the PPAS
            $titleStmt = $pdo->prepare("
                SELECT title 
                FROM ppas_forms 
                WHERE id = ?
            ");
            $titleStmt->execute([$ppaId]);
            $ppasTitle = $titleStmt->fetchColumn();
            
            error_log("PPAS Title: " . $ppasTitle);
            error_log("Total Personnel in PPAS: " . $totalPersonnel);
            error_log("Role Counts: " . print_r($roleCountMap, true));
            
            // Distribute personnel based on academic ranks
            // Assign higher rank to project leaders, mid-rank to assistants, lower ranks to staff
            $projectLeaderCount = $roleCountMap['project_leader'] ?? 0;
            $assistantLeaderCount = $roleCountMap['asst_project_leader'] ?? 0;
            $staffCount = $roleCountMap['project_staff'] ?? 0;
            $participantCount = $roleCountMap['other_participant'] ?? 0;
            
            // Sort academic ranks by monthly salary (descending)
            usort($academicRanks, function($a, $b) {
                return $b['monthly_salary'] - $a['monthly_salary'];
            });
            
            // Total number of ranks
            $rankCount = count($academicRanks);
            
            // Divide ranks into tiers
            $highRanks = array_slice($academicRanks, 0, ceil($rankCount / 3));
            $midRanks = array_slice($academicRanks, ceil($rankCount / 3), ceil($rankCount / 3));
            $lowRanks = array_slice($academicRanks, 2 * ceil($rankCount / 3));
            
            // Initialize personnel counts to 0
            foreach ($academicRanks as $rank) {
                $ranks[$rank['rank_name']] = [
                    'rank_name' => $rank['rank_name'],
                    'monthly_salary' => floatval($rank['monthly_salary']),
                    'personnel_count' => 0
                ];
            }
            
            // Distribute project leaders among high ranks
            if ($projectLeaderCount > 0 && !empty($highRanks)) {
                $perRank = ceil($projectLeaderCount / count($highRanks));
                foreach ($highRanks as $rank) {
                    $allocate = min($perRank, $projectLeaderCount);
                    $ranks[$rank['rank_name']]['personnel_count'] += $allocate;
                    $projectLeaderCount -= $allocate;
                    if ($projectLeaderCount <= 0) break;
                }
            }
            
            // Distribute assistant leaders among mid ranks
            if ($assistantLeaderCount > 0 && !empty($midRanks)) {
                $perRank = ceil($assistantLeaderCount / count($midRanks));
                foreach ($midRanks as $rank) {
                    $allocate = min($perRank, $assistantLeaderCount);
                    $ranks[$rank['rank_name']]['personnel_count'] += $allocate;
                    $assistantLeaderCount -= $allocate;
                    if ($assistantLeaderCount <= 0) break;
                }
            }
            
            // Distribute staff among low ranks
            if ($staffCount > 0 && !empty($lowRanks)) {
                $perRank = ceil($staffCount / count($lowRanks));
                foreach ($lowRanks as $rank) {
                    $allocate = min($perRank, $staffCount);
                    $ranks[$rank['rank_name']]['personnel_count'] += $allocate;
                    $staffCount -= $allocate;
                    if ($staffCount <= 0) break;
                }
            }
            
            // Distribute remaining participants among all ranks
            if ($participantCount > 0) {
                $perRank = ceil($participantCount / count($academicRanks));
                foreach ($academicRanks as $rank) {
                    $allocate = min($perRank, $participantCount);
                    $ranks[$rank['rank_name']]['personnel_count'] += $allocate;
                    $participantCount -= $allocate;
                    if ($participantCount <= 0) break;
                }
            }
            
            // Convert to array for output
            $ranksArray = array_values($ranks);
            
            // Sort by monthly salary (ascending)
            usort($ranksArray, function($a, $b) {
                return $a['monthly_salary'] - $b['monthly_salary'];
            });
            
            error_log("Academic ranks with personnel counts from PPAS: " . print_r($ranksArray, true));
            echo json_encode($ranksArray);
            exit;
        }
    }
    
    // If we get here, the academic_ranks table doesn't exist or is empty, use default ranks
    $ranks = [
        ['rank_name' => 'Instructor I', 'monthly_salary' => 31000, 'personnel_count' => 0],
        ['rank_name' => 'Instructor II', 'monthly_salary' => 35000, 'personnel_count' => 0],
        ['rank_name' => 'Instructor III', 'monthly_salary' => 43000, 'personnel_count' => 0],
        ['rank_name' => 'College Lecturer', 'monthly_salary' => 50000, 'personnel_count' => 0],
        ['rank_name' => 'Senior Lecturer', 'monthly_salary' => 55000, 'personnel_count' => 0],
        ['rank_name' => 'Master Lecturer', 'monthly_salary' => 60000, 'personnel_count' => 0],
        ['rank_name' => 'Assistant Professor II', 'monthly_salary' => 65000, 'personnel_count' => 0],
        ['rank_name' => 'Associate Professor I', 'monthly_salary' => 70000, 'personnel_count' => 0],
        ['rank_name' => 'Associate Professor II', 'monthly_salary' => 75000, 'personnel_count' => 0],
        ['rank_name' => 'Professor I', 'monthly_salary' => 80000, 'personnel_count' => 0],
        ['rank_name' => 'Professor II', 'monthly_salary' => 85000, 'personnel_count' => 0],
        ['rank_name' => 'Professor III', 'monthly_salary' => 90000, 'personnel_count' => 0],
        ['rank_name' => 'Professor IV', 'monthly_salary' => 95000, 'personnel_count' => 0]
    ];
    
    error_log("Using default academic ranks with zero personnel counts: " . print_r($ranks, true));
    echo json_encode($ranks);
    
} catch(Exception $e) {
    error_log("Error in get_academic_ranks_from_personnel.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 