<?php
// Function to get titles/activities from PPAS forms
function getTitlesFromPPAS() {
    try {
        global $conn;
        
        // Connect to database if not already connected
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        
        $campus = isset($_POST['campus']) ? sanitize_input($_POST['campus']) : '';
        $year = isset($_POST['year']) ? sanitize_input($_POST['year']) : '';
        
        // Check if ppas_forms table exists
        $tableCheckQuery = "SHOW TABLES LIKE 'ppas_forms'";
        $tableResult = $conn->query($tableCheckQuery);
        
        $activities = [];
        
        if ($tableResult && $tableResult->num_rows > 0) {
            // Build query based on filters
            $query = "SELECT DISTINCT activity FROM ppas_forms WHERE 1=1";
            $params = [];
            $paramTypes = "";
            
            if (!empty($campus)) {
                $query .= " AND campus = ?";
                $params[] = $campus;
                $paramTypes .= "s";
            }
            
            if (!empty($year)) {
                $query .= " AND year = ?";
                $params[] = $year;
                $paramTypes .= "s";
            }
            
            $stmt = $conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($paramTypes, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if this activity has a narrative
                    $hasNarrative = false;
                    
                    // Check if narrative_entries table exists
                    $narrativeTableCheckQuery = "SHOW TABLES LIKE 'narrative_entries'";
                    $narrativeTableResult = $conn->query($narrativeTableCheckQuery);
                    
                    if ($narrativeTableResult && $narrativeTableResult->num_rows > 0) {
                        // Check if this activity has a narrative entry
                        $narrativeQuery = "SELECT COUNT(*) as count FROM narrative_entries WHERE title = ? AND campus = ? AND year = ?";
                        $narrativeStmt = $conn->prepare($narrativeQuery);
                        $narrativeStmt->bind_param("sss", $row['activity'], $campus, $year);
                        $narrativeStmt->execute();
                        $narrativeResult = $narrativeStmt->get_result();
                        
                        if ($narrativeResult && $narrativeResult->num_rows > 0) {
                            $narrativeRow = $narrativeResult->fetch_assoc();
                            $hasNarrative = ($narrativeRow['count'] > 0);
                        }
                    }
                    
                    // Add activity with has_narrative flag
                    $activities[] = [
                        'title' => $row['activity'],
                        'has_narrative' => $hasNarrative
                    ];
                }
            }
        }
        
        // Add default titles if no results
        if (empty($activities)) {
            $defaultTitles = [
                'Gender and Development Training',
                'Women Empowerment Workshop',
                'Gender Sensitivity Seminar',
                'Gender Integration Workshop',
                'Diversity and Inclusion Conference'
            ];
            
            foreach ($defaultTitles as $title) {
                $activities[] = [
                    'title' => $title,
                    'has_narrative' => false
                ];
            }
        }
        
        echo json_encode(['success' => true, 'data' => $activities]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 