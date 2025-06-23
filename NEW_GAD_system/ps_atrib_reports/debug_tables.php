<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $output = [];
    $actions = [];
    
    // Check if ppas_forms table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'ppas_forms'");
    $ppasFormsExists = $stmt->rowCount() > 0;
    $output[] = "ppas_forms table exists: " . ($ppasFormsExists ? "Yes" : "No");
    
    if ($ppasFormsExists) {
        // Check ppas_forms structure
        $stmt = $pdo->query("DESCRIBE ppas_forms");
        $ppasFormsStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>ppas_forms structure:</strong>";
        foreach ($ppasFormsStructure as $column) {
            $output[] = "{$column['Field']} ({$column['Type']})";
        }
        
        // Get sample data
        $stmt = $pdo->query("SELECT id, title, ps_attribution FROM ppas_forms LIMIT 5");
        $ppasFormsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>Sample ppas_forms data:</strong>";
        foreach ($ppasFormsData as $row) {
            $output[] = "ID: {$row['id']}, Title: {$row['title']}, PS Attribution: {$row['ps_attribution']}";
        }
    }
    
    // Check if ppas_personnel table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'ppas_personnel'");
    $ppasPersonnelExists = $stmt->rowCount() > 0;
    $output[] = "<br><br>ppas_personnel table exists: " . ($ppasPersonnelExists ? "Yes" : "No");
    
    if ($ppasPersonnelExists) {
        // Check ppas_personnel structure
        $stmt = $pdo->query("DESCRIBE ppas_personnel");
        $ppasPersonnelStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>ppas_personnel structure:</strong>";
        foreach ($ppasPersonnelStructure as $column) {
            $output[] = "{$column['Field']} ({$column['Type']})";
        }
        
        // Get sample data
        $stmt = $pdo->query("SELECT id, ppas_id, personnel_id, role FROM ppas_personnel LIMIT 5");
        $ppasPersonnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>Sample ppas_personnel data:</strong>";
        foreach ($ppasPersonnelData as $row) {
            $output[] = "ID: {$row['id']}, PPAS ID: {$row['ppas_id']}, Personnel ID: {$row['personnel_id']}, Role: {$row['role']}";
        }
        
        // Count personnel by role
        $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM ppas_personnel GROUP BY role");
        $roleCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>Personnel count by role:</strong>";
        foreach ($roleCount as $row) {
            $output[] = "Role: {$row['role']}, Count: {$row['count']}";
        }
    }
    
    // Check if academic_ranks table exists (plural form)
    $stmt = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $academicRanksExists = $stmt->rowCount() > 0;
    $output[] = "<br><br>academic_ranks table exists: " . ($academicRanksExists ? "Yes" : "No");
    
    if (!$academicRanksExists) {
        $actions[] = "<button class='btn btn-primary mt-3' onclick='createAcademicRanks()'>Create Academic Ranks Table</button>";
        $output[] = "<br><strong class='text-danger'>Academic ranks table is missing! Use the button below to create it.</strong>";
    } else {
        // Check academic_ranks structure
        $stmt = $pdo->query("DESCRIBE academic_ranks");
        $academicRanksStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>academic_ranks structure:</strong>";
        foreach ($academicRanksStructure as $column) {
            $output[] = "{$column['Field']} ({$column['Type']})";
        }
        
        // Get sample data
        $stmt = $pdo->query("SELECT id, academic_rank, monthly_salary FROM academic_ranks LIMIT 10");
        $academicRanksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>Academic ranks data:</strong>";
        
        if (count($academicRanksData) == 0) {
            $output[] = "No academic ranks found in the table!";
            $actions[] = "<button class='btn btn-warning mt-3' onclick='createAcademicRanks()'>Initialize Academic Ranks Data</button>";
        } else {
            foreach ($academicRanksData as $row) {
                $output[] = "ID: {$row['id']}, Rank: {$row['academic_rank']}, Monthly Salary: {$row['monthly_salary']}";
            }
        }
    }
    
    // Check if personnel table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'personnel'");
    $personnelExists = $stmt->rowCount() > 0;
    $output[] = "<br><br>personnel table exists: " . ($personnelExists ? "Yes" : "No");
    
    if ($personnelExists) {
        // Check personnel structure
        $stmt = $pdo->query("DESCRIBE personnel");
        $personnelStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>personnel structure:</strong>";
        foreach ($personnelStructure as $column) {
            $output[] = "{$column['Field']} ({$column['Type']})";
        }
        
        // Get sample data - use * instead of specific column names
        $stmt = $pdo->query("SELECT * FROM personnel LIMIT 5");
        $personnelData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[] = "<br><strong>Sample personnel data:</strong>";
        foreach ($personnelData as $row) {
            // Build output dynamically based on available columns
            $outputStr = "ID: {$row['id']}";
            if (isset($row['full_name'])) {
                $outputStr .= ", Name: {$row['full_name']}";
            } else if (isset($row['name'])) {
                $outputStr .= ", Name: {$row['name']}";
            }
            if (isset($row['rank_id'])) {
                $outputStr .= ", Rank ID: {$row['rank_id']}";
            }
            if (isset($row['active'])) {
                $outputStr .= ", Active: {$row['active']}";
            }
            $output[] = $outputStr;
        }
    }
    
    // Add JavaScript for actions
    if (!empty($actions)) {
        $output[] = "<script>
        function createAcademicRanks() {
            fetch('create_academic_ranks.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            confirmButtonText: 'Reload Debug'
                        }).then(() => {
                            debugTables();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to create academic ranks: ' + error
                    });
                });
        }
        </script>";
        
        $output[] = "<div class='text-center mb-4'>" . implode(' ', $actions) . "</div>";
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => implode("<br>", $output)
    ]);
} catch(PDOException $e) {
    // Return error response
    error_log("Debug error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 