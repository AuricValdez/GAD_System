<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['personnelNames']) || !isset($_POST['durations'])) {
        throw new Exception('Personnel names and durations are required');
    }

    $personnelNames = $_POST['personnelNames'];
    $durations = $_POST['durations'];
    $narrativeId = isset($_POST['narrative_id']) ? intval($_POST['narrative_id']) : 0;

    if (count($personnelNames) !== count($durations)) {
        throw new Exception('Personnel names and durations must have the same count');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // First, delete existing personnel entries for this narrative if it exists
        if ($narrativeId > 0) {
            $deleteStmt = $pdo->prepare("DELETE FROM narrative_personnel WHERE narrative_id = ?");
            $deleteStmt->execute([$narrativeId]);
        }

        // Prepare insert statement
        $insertStmt = $pdo->prepare("
            INSERT INTO narrative_personnel 
            (narrative_id, personnel_id, duration, ps_attribution) 
            VALUES (?, ?, ?, ?)
        ");

        $totalPS = 0;

        // Insert each personnel entry
        for ($i = 0; $i < count($personnelNames); $i++) {
            if (empty($personnelNames[$i])) continue;

            $personnelId = intval($personnelNames[$i]);
            $duration = floatval($durations[$i]);

            // Get personnel hourly rate
            $rateStmt = $pdo->prepare("
                SELECT ROUND(ar.monthly_salary / 176, 2) as hourly_rate
                FROM personnel p
                LEFT JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
                WHERE p.id = ?
            ");
            $rateStmt->execute([$personnelId]);
            $rateResult = $rateStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rateResult) {
                $hourlyRate = floatval($rateResult['hourly_rate']);
                $psAttribution = $hourlyRate * $duration;
                $totalPS += $psAttribution;

                $insertStmt->execute([
                    $narrativeId,
                    $personnelId,
                    $duration,
                    $psAttribution
                ]);
            }
        }

        // Update total PS in narratives table
        if ($narrativeId > 0) {
            $updatePS = $pdo->prepare("UPDATE narratives SET total_ps = ? WHERE id = ?");
            $updatePS->execute([$totalPS, $narrativeId]);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Personnel saved successfully',
            'total_ps' => $totalPS
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 