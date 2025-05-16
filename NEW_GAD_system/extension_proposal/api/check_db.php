<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
try {
    $conn = new PDO('mysql:host=localhost;dbname=gad_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Connected to database successfully\n";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check tables
try {
    // Check if gad_proposals table exists
    $result = $conn->query("SHOW TABLES LIKE 'gad_proposals'");
    if ($result->rowCount() > 0) {
        echo "✅ gad_proposals table exists\n";
        
        // Check for campus values in gad_proposals
        $query = "SELECT DISTINCT pf.campus FROM gad_proposals gp JOIN ppas_forms pf ON gp.ppas_form_id = pf.id";
        $result = $conn->query($query);
        $campuses = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Campus values in joined tables: " . print_r($campuses, true) . "\n";
        
        // Check for year values
        $query = "SELECT DISTINCT pf.year FROM gad_proposals gp JOIN ppas_forms pf ON gp.ppas_form_id = pf.id";
        $result = $conn->query($query);
        $years = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Year values in joined tables: " . print_r($years, true) . "\n";
        
        // Count records
        $query = "SELECT pf.campus, COUNT(*) as count FROM gad_proposals gp JOIN ppas_forms pf ON gp.ppas_form_id = pf.id GROUP BY pf.campus";
        $result = $conn->query($query);
        $counts = $result->fetchAll();
        
        echo "Record counts by campus:\n";
        foreach ($counts as $row) {
            echo "- {$row['campus']}: {$row['count']} records\n";
        }
        
        // Test the exact query from get_proposal_years.php
        echo "\nTesting get_proposal_years.php query:\n";
        $campus = isset($campuses[0]) ? $campuses[0] : 'Lipa'; // Use first campus or default to Lipa
        
        $query = "SELECT DISTINCT pf.year 
                  FROM gad_proposals gp
                  JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
                  WHERE pf.campus = :campus 
                  ORDER BY pf.year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute(['campus' => $campus]);
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Years for campus '$campus': " . print_r($years, true) . "\n";
        
        if (empty($years)) {
            echo "⚠️ No years found for campus: $campus\n";
            
            // Check if the campus exists in ppas_forms
            $query = "SELECT COUNT(*) FROM ppas_forms WHERE campus = :campus";
            $stmt = $conn->prepare($query);
            $stmt->execute(['campus' => $campus]);
            $count = $stmt->fetchColumn();
            
            echo "Records in ppas_forms for campus '$campus': $count\n";
            
            // Check if ppas_form_id in gad_proposals exists in ppas_forms
            $query = "SELECT gp.ppas_form_id, COUNT(*) 
                      FROM gad_proposals gp 
                      LEFT JOIN ppas_forms pf ON gp.ppas_form_id = pf.id 
                      WHERE pf.id IS NULL
                      GROUP BY gp.ppas_form_id";
            $result = $conn->query($query);
            $orphaned = $result->fetchAll();
            
            if (!empty($orphaned)) {
                echo "⚠️ Found orphaned ppas_form_id values in gad_proposals:\n";
                foreach ($orphaned as $row) {
                    echo "- ppas_form_id {$row['ppas_form_id']}: {$row['COUNT(*)']} records\n";
                }
            } else {
                echo "✅ All ppas_form_id values in gad_proposals have matching records in ppas_forms\n";
            }
        }
        
        // Direct examination of tables
        echo "\n⏩ DIRECT TABLE EXAMINATION:\n";
        
        // Examine gad_proposals directly
        $result = $conn->query("SELECT COUNT(*) FROM gad_proposals");
        $count = $result->fetchColumn();
        echo "Total records in gad_proposals: $count\n";
        
        if ($count > 0) {
            $result = $conn->query("SELECT * FROM gad_proposals LIMIT 5");
            $proposals = $result->fetchAll();
            echo "Sample gad_proposals records:\n";
            foreach ($proposals as $idx => $row) {
                echo "RECORD #" . ($idx + 1) . ":\n";
                foreach ($row as $key => $value) {
                    if (is_string($value) && strlen($value) > 100) {
                        $value = substr($value, 0, 50) . "... [truncated]";
                    }
                    echo "  - $key: $value\n";
                }
                echo "\n";
            }
            
            // Get all ppas_form_ids from gad_proposals
            $result = $conn->query("SELECT DISTINCT ppas_form_id FROM gad_proposals");
            $form_ids = $result->fetchAll(PDO::FETCH_COLUMN);
            echo "All ppas_form_ids in gad_proposals: " . implode(', ', $form_ids) . "\n";
        }
        
        // Examine ppas_forms directly
        $result = $conn->query("SELECT COUNT(*) FROM ppas_forms");
        $count = $result->fetchColumn();
        echo "Total records in ppas_forms: $count\n";
        
        if ($count > 0) {
            $result = $conn->query("SELECT * FROM ppas_forms LIMIT 5");
            $forms = $result->fetchAll();
            echo "Sample ppas_forms records:\n";
            foreach ($forms as $idx => $row) {
                echo "RECORD #" . ($idx + 1) . ":\n";
                foreach ($row as $key => $value) {
                    if (is_string($value) && strlen($value) > 100) {
                        $value = substr($value, 0, 50) . "... [truncated]";
                    }
                    echo "  - $key: $value\n";
                }
                echo "\n";
            }
        }
        
        // Try with different join conditions
        echo "\n⏩ EXPLORING JOIN ISSUES:\n";
        
        // Check all matching records (if any)
        $query = "SELECT gp.proposal_id, gp.ppas_form_id, pf.id, pf.campus, pf.year
                 FROM gad_proposals gp
                 JOIN ppas_forms pf ON gp.ppas_form_id = pf.id";
        $result = $conn->query($query);
        $matches = $result->fetchAll();
        
        echo "Records that match between gad_proposals and ppas_forms: " . count($matches) . "\n";
        foreach ($matches as $row) {
            echo "- proposal_id: {$row['proposal_id']}, ppas_form_id: {$row['ppas_form_id']}, " .
                 "pf.id: {$row['id']}, campus: {$row['campus']}, year: {$row['year']}\n";
        }
        
        // Try with id=1,2 from ppas_forms to see if they exist
        echo "\nChecking if ppas_forms has records with id 1 and 2:\n";
        $result = $conn->query("SELECT id, campus, year FROM ppas_forms WHERE id IN (1, 2)");
        $forms = $result->fetchAll();
        if (count($forms) > 0) {
            foreach ($forms as $row) {
                echo "- id: {$row['id']}, campus: {$row['campus']}, year: {$row['year']}\n";
            }
        } else {
            echo "No ppas_forms records with id 1 or 2 exist.\n";
        }
        
        // Try with different campus
        echo "\n⏩ TRYING WITH DIFFERENT CAMPUS VALUES:\n";
        $test_campuses = ['Lipa', 'lipa', 'LIPA', 'Central', 'central', 'CENTRAL'];
        
        foreach ($test_campuses as $test_campus) {
            $query = "SELECT DISTINCT pf.year 
                     FROM gad_proposals gp
                     JOIN ppas_forms pf ON gp.ppas_form_id = pf.id
                     WHERE pf.campus = :campus 
                     ORDER BY pf.year DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute(['campus' => $test_campus]);
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "Years for campus '$test_campus': " . print_r($years, true) . "\n";
        }
        
    } else {
        echo "❌ gad_proposals table does not exist\n";
    }
    
    // Check if ppas_forms table exists
    $result = $conn->query("SHOW TABLES LIKE 'ppas_forms'");
    if ($result->rowCount() > 0) {
        echo "✅ ppas_forms table exists\n";
        
        // Check for campus values in ppas_forms
        $query = "SELECT DISTINCT campus FROM ppas_forms";
        $result = $conn->query($query);
        $campuses = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Campus values in ppas_forms: " . print_r($campuses, true) . "\n";
    } else {
        echo "❌ ppas_forms table does not exist\n";
    }
} catch(PDOException $e) {
    echo "❌ Query error: " . $e->getMessage() . "\n";
} 