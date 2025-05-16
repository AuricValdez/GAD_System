<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $output = [];
    $tablesCreated = 0;
    
    // Initialize academic_ranks table
    $checkTable = $pdo->query("SHOW TABLES LIKE 'academic_ranks'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the academic_ranks table
        $pdo->exec("
            CREATE TABLE academic_ranks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                academic_rank VARCHAR(100) NOT NULL,
                salary_grade INT NOT NULL,
                monthly_salary DECIMAL(10,2) NOT NULL,
                hourly_rate DECIMAL(10,2) GENERATED ALWAYS AS (monthly_salary / 176) STORED
            )
        ");
        
        // Insert the academic ranks data
        $insertStmt = $pdo->prepare("
            INSERT INTO academic_ranks (academic_rank, salary_grade, monthly_salary) 
            VALUES (?, ?, ?)
        ");
        
        // Academic ranks data
        $academicRanks = [
            ['Instructor I', 8, 31000],
            ['Instructor II', 9, 35000],
            ['Instructor III', 10, 43000],
            ['College Lecturer', 11, 50000],
            ['Senior Lecturer', 12, 55000],
            ['Master Lecturer', 13, 60000],
            ['Assistant Professor II', 16, 65000],
            ['Associate Professor I', 19, 70000],
            ['Associate Professor II', 20, 75000],
            ['Professor I', 22, 80000],
            ['Professor II', 23, 85000],
            ['Professor III', 24, 90000],
            ['Professor IV', 25, 95000]
        ];
        
        // Insert each rank
        foreach ($academicRanks as $rank) {
            $insertStmt->execute($rank);
        }
        
        $output[] = "Created and populated academic_ranks table";
        $tablesCreated++;
    } else {
        $output[] = "academic_ranks table already exists";
    }
    
    // Initialize quarters table
    $checkTable = $pdo->query("SHOW TABLES LIKE 'quarters'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the quarters table
        $pdo->exec("
            CREATE TABLE quarters (
                id INT AUTO_INCREMENT PRIMARY KEY,
                quarter_number INT NOT NULL,
                year INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                title VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Get the current year
        $currentYear = date('Y');
        
        // Insert quarters data for the current year
        $insertStmt = $pdo->prepare("
            INSERT INTO quarters (quarter_number, year, start_date, end_date, title) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Quarters data
        $quarters = [
            [1, $currentYear, "$currentYear-01-01", "$currentYear-03-31", "1st Quarter $currentYear"],
            [2, $currentYear, "$currentYear-04-01", "$currentYear-06-30", "2nd Quarter $currentYear"],
            [3, $currentYear, "$currentYear-07-01", "$currentYear-09-30", "3rd Quarter $currentYear"],
            [4, $currentYear, "$currentYear-10-01", "$currentYear-12-31", "4th Quarter $currentYear"]
        ];
        
        // Insert each quarter
        foreach ($quarters as $quarter) {
            $insertStmt->execute($quarter);
        }
        
        $output[] = "Created and populated quarters table";
        $tablesCreated++;
    } else {
        $output[] = "quarters table already exists";
    }
    
    // Initialize ppa_details table if it doesn't exist
    $checkTable = $pdo->query("SHOW TABLES LIKE 'ppa_details'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the ppa_details table
        $pdo->exec("
            CREATE TABLE ppa_details (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                quarter_id INT,
                date DATE,
                total_duration FLOAT NOT NULL,
                approved_budget DECIMAL(12,2),
                source_of_budget VARCHAR(100),
                ps_attribution DECIMAL(12,2),
                status VARCHAR(50) DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (quarter_id) REFERENCES quarters(id)
            )
        ");
        
        $output[] = "Created ppa_details table";
        $tablesCreated++;
    } else {
        $output[] = "ppa_details table already exists";
    }
    
    // Initialize ps_attribution table if it doesn't exist
    $checkTable = $pdo->query("SHOW TABLES LIKE 'ps_attribution'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the ps_attribution table
        $pdo->exec("
            CREATE TABLE ps_attribution (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ppa_id INT NOT NULL,
                ps_value DECIMAL(12,2) NOT NULL,
                saved_by VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ppa_id) REFERENCES ppa_details(id)
            )
        ");
        
        $output[] = "Created ps_attribution table";
        $tablesCreated++;
    } else {
        $output[] = "ps_attribution table already exists";
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Tables initialization completed. ' . implode('; ', $output),
        'tables_created' => $tablesCreated
    ]);
} catch(PDOException $e) {
    // Return error response
    error_log("Tables initialization error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 