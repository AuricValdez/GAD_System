<?php
// Include only the config file which already has the database connection
require_once '../config.php';
header('Content-Type: application/json');

try {
    // Make sure we have a working database connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        // Try to reconnect
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Had to reconnect to database in get_narrative_personnel.php");
    }
    
    if (!isset($_GET['narrative_id'])) {
        throw new Exception('Narrative ID is required');
    }

    $narrativeId = intval($_GET['narrative_id']);
    error_log("Processing request for narrative ID: {$narrativeId}");
    
    // First, get the PPAS form ID, PS data, and other_internal_personnel from narrative_entries
    $narrativeQuery = "SELECT ppas_form_id, ps_attribution, other_internal_personnel FROM narrative_entries WHERE id = ?";
    $narrativeStmt = $pdo->prepare($narrativeQuery);
    $narrativeStmt->execute([$narrativeId]);
    $narrativeData = $narrativeStmt->fetch(PDO::FETCH_ASSOC);
    
    $ppasFormId = null;
    $narrativePS = 0;
    $selectedPersonnelIds = [];
    $otherInternalPersonnelData = null;
    
    if ($narrativeData) {
        if (!empty($narrativeData['ppas_form_id'])) {
            $ppasFormId = $narrativeData['ppas_form_id'];
            error_log("Found PPAS Form ID for narrative {$narrativeId}: {$ppasFormId}");
        }
        
        // Get PS from narrative_entries as requested
        $narrativePS = floatval($narrativeData['ps_attribution'] ?? 0);
        error_log("Using PS from narrative_entries: {$narrativePS}");
        
        // Get selected personnel from other_internal_personnel field
        if (!empty($narrativeData['other_internal_personnel'])) {
            $otherInternalPersonnelData = $narrativeData['other_internal_personnel'];
            error_log("Found other_internal_personnel data: " . substr($otherInternalPersonnelData, 0, 100) . "...");
            
            $personnelData = json_decode($otherInternalPersonnelData, true);
            
            if (is_array($personnelData)) {
                foreach ($personnelData as $person) {
                    if (isset($person['id'])) {
                        $selectedPersonnelIds[] = intval($person['id']);
                    }
                }
                error_log("Found " . count($selectedPersonnelIds) . " selected personnel in other_internal_personnel");
            } else {
                error_log("Failed to parse other_internal_personnel JSON: " . json_last_error_msg());
                error_log("Raw data: " . $otherInternalPersonnelData);
            }
        }
    } else {
        error_log("No narrative data found for ID {$narrativeId}");
    }
    
    // Get PPAS info if available (duration)
    $ppasData = null;
    $ppasDuration = 0;
    $ppasPS = 0;
    
    if ($ppasFormId) {
        $ppasQuery = "
            SELECT 
                COALESCE(CAST(NULLIF(TRIM(total_duration), '') AS DECIMAL(10,2)), 0.00) as total_duration,
                COALESCE(CAST(NULLIF(TRIM(ps_attribution), '') AS DECIMAL(10,2)), 0.00) as ps_attribution
            FROM ppas_forms 
            WHERE id = ?
        ";
        
        $ppasStmt = $pdo->prepare($ppasQuery);
        $ppasStmt->execute([$ppasFormId]);
        $ppasData = $ppasStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ppasData) {
            $ppasDuration = floatval($ppasData['total_duration']);
            $ppasPS = floatval($ppasData['ps_attribution']);
            error_log("PPAS duration found: {$ppasDuration}, PPAS PS attribution: {$ppasPS}");
        }
    }

    // Get personnel information
    $personnel = [];

    if (!empty($selectedPersonnelIds)) {
        // Query personnel based on selected IDs from other_internal_personnel
        $placeholders = implode(',', array_fill(0, count($selectedPersonnelIds), '?'));
        
        $query = "
            SELECT 
                p.id as personnel_id,
                COALESCE(p.name, 'Unknown') as name,
                COALESCE(p.academic_rank, 'N/A') as academic_rank,
                COALESCE(ROUND(ar.monthly_salary / 176, 2), 0) as hourly_rate
            FROM personnel p
            LEFT JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
            WHERE p.id IN ({$placeholders})
            ORDER BY p.name ASC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($selectedPersonnelIds);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Fetched " . count($personnel) . " personnel records out of " . count($selectedPersonnelIds) . " selected IDs");
    }
    
    // If no personnel found from IDs or no IDs available, but we have JSON data
    if (empty($personnel) && !empty($otherInternalPersonnelData)) {
        error_log("No personnel found in database, attempting to use data directly from other_internal_personnel");
        
        // Try to extract personnel directly from the JSON
        $jsonPersonnel = json_decode($otherInternalPersonnelData, true);
        
        if (is_array($jsonPersonnel)) {
            foreach ($jsonPersonnel as $person) {
                if (isset($person['id'])) {
                    // Create a personnel entry directly from the JSON data
                    $personnel[] = [
                        'personnel_id' => $person['id'],
                        'name' => $person['name'] ?? 'Unknown',
                        'academic_rank' => $person['rank'] ?? 'N/A',
                        'hourly_rate' => $person['hourlyRate'] ?? 0
                    ];
                }
            }
            error_log("Created " . count($personnel) . " personnel entries directly from JSON data");
        }
    }
    
    // If still no personnel, try narrative_personnel table
    if (empty($personnel)) {
        error_log("No personnel found from other_internal_personnel, checking narrative_personnel table");
        
        try {
            // See if the table exists
            $checkTableStmt = $pdo->query("SHOW TABLES LIKE 'narrative_personnel'");
            $tableExists = $checkTableStmt->rowCount() > 0;
            
            if ($tableExists) {
                // If table exists, get personnel from narrative_personnel table
                $query = "
                    SELECT 
                        np.personnel_id,
                        COALESCE(p.name, 'Unknown') as name,
                        COALESCE(p.academic_rank, 'N/A') as academic_rank,
                        COALESCE(ROUND(ar.monthly_salary / 176, 2), 0) as hourly_rate
                    FROM narrative_personnel np
                    LEFT JOIN personnel p ON np.personnel_id = p.id
                    LEFT JOIN academic_ranks ar ON p.academic_rank = ar.academic_rank
                    WHERE np.narrative_id = ?
                    ORDER BY p.name ASC
                ";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([$narrativeId]);
                $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($personnel)) {
                    error_log("Found " . count($personnel) . " personnel in narrative_personnel table");
                }
            }
        } catch (PDOException $e) {
            error_log("Error checking narrative_personnel table: " . $e->getMessage());
        }
    }
    
    // Calculate total PS from personnel
    $totalPersonnelPS = 0;
    $updatedPersonnel = [];
    
    foreach ($personnel as $person) {
        // Log each person's data for debugging
        error_log("Processing personnel: ID=" . ($person['personnel_id'] ?? 'unknown') . 
                 ", Name=" . ($person['name'] ?? 'unknown') . 
                 ", Rank=" . ($person['academic_rank'] ?? 'unknown') . 
                 ", Rate=" . ($person['hourly_rate'] ?? 0));
        
        // Use PPAS duration if available
        $duration = $ppasDuration > 0 ? $ppasDuration : 0;
        
        // Calculate PS based on duration and hourly rate
        $hourlyRate = floatval($person['hourly_rate'] ?? 0);
        $ps = $duration * $hourlyRate;
        
        // Update person data
        $person['duration'] = $duration;
        $person['ps_attribution'] = $ps;
        
        $updatedPersonnel[] = $person;
        $totalPersonnelPS += $ps;
    }
    
    // Calculate total PS (personnel + narrative PS)
    $totalPS = $totalPersonnelPS + $narrativePS;
    
    error_log("Total personnel PS: {$totalPersonnelPS}, Narrative PS: {$narrativePS}, Total PS: {$totalPS}");
    error_log("Returning " . count($updatedPersonnel) . " personnel records");

    echo json_encode([
        'status' => 'success',
        'data' => $updatedPersonnel,
        'total_ps' => $totalPS,
        'narrative_ps' => $narrativePS,
        'ppas_ps' => $ppasPS,
        'ppas_info' => $ppasData,
        'ppas_form_id' => $ppasFormId,
        'debug_info' => [
            'personnel_count' => count($updatedPersonnel),
            'selected_ids' => $selectedPersonnelIds,
            'has_other_internal_personnel' => !empty($otherInternalPersonnelData),
            'ps_source' => $ppasPS > 0 ? 'ppas_forms' : 'calculated'
        ]
    ]);

} catch (PDOException $e) {
    error_log("PDO Error in get_narrative_personnel.php: " . $e->getMessage());
    http_response_code(500); // Server error for database issues
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_narrative_personnel.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}