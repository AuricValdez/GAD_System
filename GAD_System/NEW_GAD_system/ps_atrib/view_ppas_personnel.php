<?php
require_once 'db_connection.php';

// Get PPAS ID from request parameter
$ppaId = isset($_GET['ppaId']) ? (int)$_GET['ppaId'] : 0;

// Set content type to HTML for browser viewing
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPAS Personnel Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h1>PPAS Personnel Viewer</h1>
    
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="number" name="ppaId" value="<?php echo $ppaId; ?>" class="form-control" placeholder="Enter PPAS ID">
            <button type="submit" class="btn btn-primary">View Personnel</button>
        </div>
    </form>
    
    <?php if ($ppaId > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3>PPAS Details</h3>
            </div>
            <div class="card-body">
                <?php
                try {
                    // Get PPAS details
                    $ppaStmt = $pdo->prepare("SELECT * FROM ppas_forms WHERE id = ?");
                    $ppaStmt->execute([$ppaId]);
                    $ppa = $ppaStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ppa) {
                        echo '<table class="table table-bordered">';
                        echo '<tr><th>ID</th><td>' . $ppa['id'] . '</td></tr>';
                        echo '<tr><th>Title</th><td>' . htmlspecialchars($ppa['title']) . '</td></tr>';
                        echo '<tr><th>Date</th><td>' . $ppa['start_date'] . '</td></tr>';
                        echo '<tr><th>Duration</th><td>' . $ppa['total_duration'] . ' hours</td></tr>';
                        echo '<tr><th>Budget</th><td>₱' . number_format($ppa['approved_budget'], 2) . '</td></tr>';
                        echo '<tr><th>PS Attribution</th><td>₱' . number_format($ppa['ps_attribution'] ?? 0, 2) . '</td></tr>';
                        echo '</table>';
                    } else {
                        echo '<div class="alert alert-warning">PPAS not found!</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Personnel List</h3>
            </div>
            <div class="card-body">
                <?php
                try {
                    // Count personnel by role
                    $countStmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM ppas_personnel WHERE ppas_id = ? GROUP BY role");
                    $countStmt->execute([$ppaId]);
                    $roleCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($roleCounts) {
                        echo '<h4>Role Counts</h4>';
                        echo '<table class="table table-bordered mb-4">';
                        echo '<thead><tr><th>Role</th><th>Count</th></tr></thead><tbody>';
                        $totalCount = 0;
                        foreach ($roleCounts as $role) {
                            echo '<tr>';
                            echo '<td>' . $role['role'] . '</td>';
                            echo '<td>' . $role['count'] . '</td>';
                            echo '</tr>';
                            $totalCount += $role['count'];
                        }
                        echo '<tr class="table-active"><td>Total</td><td>' . $totalCount . '</td></tr>';
                        echo '</tbody></table>';
                    }
                    
                    // Get all personnel
                    $personnelStmt = $pdo->prepare("SELECT * FROM ppas_personnel WHERE ppas_id = ? ORDER BY role");
                    $personnelStmt->execute([$ppaId]);
                    $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($personnel) {
                        echo '<h4>Personnel Details</h4>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr><th>ID</th><th>Personnel ID</th><th>Name</th><th>Role</th></tr></thead><tbody>';
                        foreach ($personnel as $person) {
                            echo '<tr>';
                            echo '<td>' . $person['id'] . '</td>';
                            echo '<td>' . $person['personnel_id'] . '</td>';
                            echo '<td>' . htmlspecialchars($person['personnel_name']) . '</td>';
                            echo '<td>' . $person['role'] . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="alert alert-warning">No personnel found for this PPAS!</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Academic Ranks Distribution</h3>
            </div>
            <div class="card-body">
                <p>This shows how personnel from this PPAS would be distributed across academic ranks:</p>
                
                <?php
                try {
                    // Get academic ranks
                    $ranksStmt = $pdo->query("SELECT id, academic_rank, monthly_salary FROM academic_ranks ORDER BY monthly_salary DESC");
                    $allRanks = $ranksStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!$allRanks) {
                        echo '<div class="alert alert-warning">No academic ranks found in the database!</div>';
                    } else {
                        // Get role counts from the PPAS
                        $roleCountStmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM ppas_personnel WHERE ppas_id = ? GROUP BY role");
                        $roleCountStmt->execute([$ppaId]);
                        $roleCounts = $roleCountStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $roleCountMap = [];
                        foreach ($roleCounts as $role) {
                            $roleCountMap[$role['role']] = (int)$role['count'];
                        }
                        
                        $projectLeaderCount = $roleCountMap['project_leader'] ?? 0;
                        $assistantLeaderCount = $roleCountMap['asst_project_leader'] ?? 0;
                        $staffCount = $roleCountMap['project_staff'] ?? 0;
                        $participantCount = $roleCountMap['other_participant'] ?? 0;
                        
                        $rankCount = count($allRanks);
                        $highRanks = array_slice($allRanks, 0, ceil($rankCount / 3));
                        $midRanks = array_slice($allRanks, ceil($rankCount / 3), ceil($rankCount / 3));
                        $lowRanks = array_slice($allRanks, 2 * ceil($rankCount / 3));
                        
                        // Initialize personnel distribution
                        $rankDistribution = [];
                        foreach ($allRanks as $rank) {
                            $rankDistribution[$rank['academic_rank']] = [
                                'monthly_salary' => $rank['monthly_salary'],
                                'personnel_count' => 0,
                                'tier' => 'unknown'
                            ];
                        }
                        
                        // Mark rank tiers
                        foreach ($highRanks as $rank) {
                            $rankDistribution[$rank['academic_rank']]['tier'] = 'high';
                        }
                        
                        foreach ($midRanks as $rank) {
                            $rankDistribution[$rank['academic_rank']]['tier'] = 'mid';
                        }
                        
                        foreach ($lowRanks as $rank) {
                            $rankDistribution[$rank['academic_rank']]['tier'] = 'low';
                        }
                        
                        // Simulate distribution based on our algorithm
                        // Distribute project leaders among high ranks
                        if ($projectLeaderCount > 0 && !empty($highRanks)) {
                            $perRank = ceil($projectLeaderCount / count($highRanks));
                            foreach ($highRanks as $rank) {
                                $allocate = min($perRank, $projectLeaderCount);
                                $rankDistribution[$rank['academic_rank']]['personnel_count'] += $allocate;
                                $rankDistribution[$rank['academic_rank']]['leaders'] = $allocate;
                                $projectLeaderCount -= $allocate;
                                if ($projectLeaderCount <= 0) break;
                            }
                        }
                        
                        // Distribute assistant leaders among mid ranks
                        if ($assistantLeaderCount > 0 && !empty($midRanks)) {
                            $perRank = ceil($assistantLeaderCount / count($midRanks));
                            foreach ($midRanks as $rank) {
                                $allocate = min($perRank, $assistantLeaderCount);
                                $rankDistribution[$rank['academic_rank']]['personnel_count'] += $allocate;
                                $rankDistribution[$rank['academic_rank']]['assistants'] = $allocate;
                                $assistantLeaderCount -= $allocate;
                                if ($assistantLeaderCount <= 0) break;
                            }
                        }
                        
                        // Distribute staff among low ranks
                        if ($staffCount > 0 && !empty($lowRanks)) {
                            $perRank = ceil($staffCount / count($lowRanks));
                            foreach ($lowRanks as $rank) {
                                $allocate = min($perRank, $staffCount);
                                $rankDistribution[$rank['academic_rank']]['personnel_count'] += $allocate;
                                $rankDistribution[$rank['academic_rank']]['staff'] = $allocate;
                                $staffCount -= $allocate;
                                if ($staffCount <= 0) break;
                            }
                        }
                        
                        // Distribute participants evenly
                        if ($participantCount > 0) {
                            $perRank = ceil($participantCount / count($allRanks));
                            foreach ($allRanks as $rank) {
                                $allocate = min($perRank, $participantCount);
                                $rankDistribution[$rank['academic_rank']]['personnel_count'] += $allocate;
                                $rankDistribution[$rank['academic_rank']]['participants'] = $allocate;
                                $participantCount -= $allocate;
                                if ($participantCount <= 0) break;
                            }
                        }
                        
                        // Display distribution table
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr>';
                        echo '<th>Academic Rank</th>';
                        echo '<th>Tier</th>';
                        echo '<th>Monthly Salary</th>';
                        echo '<th>Total Personnel</th>';
                        echo '<th>Breakdown by Role</th>';
                        echo '</tr></thead><tbody>';
                        
                        // Sort by monthly salary descending
                        uasort($rankDistribution, function($a, $b) {
                            return $b['monthly_salary'] - $a['monthly_salary'];
                        });
                        
                        $totalCount = 0;
                        foreach ($rankDistribution as $rank => $info) {
                            $tierClass = '';
                            switch ($info['tier']) {
                                case 'high': $tierClass = 'table-danger'; break;
                                case 'mid': $tierClass = 'table-warning'; break;
                                case 'low': $tierClass = 'table-success'; break;
                            }
                            
                            echo '<tr class="' . $tierClass . '">';
                            echo '<td>' . $rank . '</td>';
                            echo '<td>' . ucfirst($info['tier']) . '</td>';
                            echo '<td>₱' . number_format($info['monthly_salary'], 2) . '</td>';
                            echo '<td>' . $info['personnel_count'] . '</td>';
                            
                            $breakdown = [];
                            if (isset($info['leaders']) && $info['leaders'] > 0) {
                                $breakdown[] = $info['leaders'] . ' leaders';
                            }
                            if (isset($info['assistants']) && $info['assistants'] > 0) {
                                $breakdown[] = $info['assistants'] . ' assistants';
                            }
                            if (isset($info['staff']) && $info['staff'] > 0) {
                                $breakdown[] = $info['staff'] . ' staff';
                            }
                            if (isset($info['participants']) && $info['participants'] > 0) {
                                $breakdown[] = $info['participants'] . ' participants';
                            }
                            
                            echo '<td>' . implode(', ', $breakdown) . '</td>';
                            echo '</tr>';
                            
                            $totalCount += $info['personnel_count'];
                        }
                        
                        echo '<tr class="table-active"><td colspan="3">Total Personnel</td><td>' . $totalCount . '</td><td></td></tr>';
                        echo '</tbody></table>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Enter a PPAS ID to view its personnel.
        </div>
    <?php endif; ?>
    
    <p>
        <a href="ps.php" class="btn btn-secondary">Back to PS Attribution</a>
    </p>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 