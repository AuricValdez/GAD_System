<?php
session_start();
require_once('../includes/db_connection.php');

header('Content-Type: application/json');

if (!isset($_GET['activity_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No activity ID provided',
        'total_duration' => '0.00',
        'ps_attribution' => '0.00'
    ]);
    exit;
}

$activity_id = $_GET['activity_id'];

try {
    // First check PPAS forms table for PS attribution and duration
    $query = "SELECT 
        COALESCE(CAST(NULLIF(TRIM(total_duration), '') AS DECIMAL(10,2)), 0.00) as total_duration,
        COALESCE(CAST(NULLIF(TRIM(ps_attribution), '') AS DECIMAL(10,2)), 0.00) as ps_attribution 
    FROM ppas_forms 
    WHERE id = :activity_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $ps_source = 'none';
    $total_duration = '0.00';
    $ps_attribution = '0.00';

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Log the raw values for debugging
        error_log("Raw values for activity {$activity_id} from ppas_forms: " . print_r($row, true));
        
        // Ensure total_duration is a valid decimal number
        $total_duration = str_replace(',', '.', $row['total_duration']); // Replace comma with dot if present
        $total_duration = preg_replace('/[^0-9.]/', '', $total_duration); // Remove any non-numeric chars except dot
        $total_duration = number_format((float)$total_duration, 2, '.', '');
        
        // Get PS attribution from ppas_forms
        $ps_attribution = (float)$row['ps_attribution'];
        
        // If we have PS attribution from ppas_forms, use it
        if ($ps_attribution > 0) {
            $ps_source = 'ppas_forms';
            error_log("Using PS attribution from ppas_forms: {$ps_attribution} for activity {$activity_id}");
        } else {
            // If there's no PS in ppas_forms, check narrative_entries as fallback
            $narrative_query = "SELECT ps_attribution FROM narrative_entries 
                               WHERE ppas_form_id = :activity_id 
                               ORDER BY id DESC LIMIT 1";
            
            $narrative_stmt = $conn->prepare($narrative_query);
            $narrative_stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
            $narrative_stmt->execute();
            
            if ($narrative_row = $narrative_stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($narrative_row['ps_attribution'])) {
                    $ps_attribution = (float)$narrative_row['ps_attribution'];
                    $ps_source = 'narrative_entries';
                    error_log("Using PS attribution from narrative_entries: {$ps_attribution} for activity {$activity_id}");
                }
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'total_duration' => $total_duration,
            'ps_attribution' => number_format($ps_attribution, 2, '.', ''),
            'ps_source' => $ps_source
        ]);
    } else {
        error_log("No data found for activity {$activity_id} in ppas_forms table");
        
        // Check narrative_entries as fallback
        $narrative_query = "SELECT ps_attribution FROM narrative_entries 
                           WHERE ppas_form_id = :activity_id 
                           ORDER BY id DESC LIMIT 1";
        
        $narrative_stmt = $conn->prepare($narrative_query);
        $narrative_stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
        $narrative_stmt->execute();
        
        if ($narrative_row = $narrative_stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($narrative_row['ps_attribution'])) {
                $ps_attribution = (float)$narrative_row['ps_attribution'];
                $ps_source = 'narrative_entries';
                error_log("No PPAS record found, but using PS from narrative_entries: {$ps_attribution}");
                
                echo json_encode([
                    'status' => 'success',
                    'total_duration' => '0.00',
                    'ps_attribution' => number_format($ps_attribution, 2, '.', ''),
                    'ps_source' => $ps_source
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No PS data found for this activity',
                    'total_duration' => '0.00',
                    'ps_attribution' => '0.00'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data found for this activity',
                'total_duration' => '0.00',
                'ps_attribution' => '0.00'
            ]);
        }
    }
} catch (PDOException $e) {
    error_log("Error in get_ppas_duration.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'total_duration' => '0.00',
        'ps_attribution' => '0.00'
    ]);
} 