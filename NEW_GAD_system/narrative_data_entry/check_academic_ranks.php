<?php
$rootDir = dirname(dirname(__FILE__));
require_once($rootDir . '/includes/db_connection.php');

try {
    // Check if table exists
    $query = "SHOW TABLES LIKE 'academic_ranks'";
    $stmt = $conn->query($query);
    $tableExists = $stmt->fetch(PDO::FETCH_NUM);

    if (!$tableExists) {
        echo "Table 'academic_ranks' does not exist. Creating table...\n";
        
        // Create the table
        $createTable = "CREATE TABLE academic_ranks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            academic_rank VARCHAR(100) NOT NULL,
            monthly_salary DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($createTable);
        
        // Insert some sample data
        $insertData = "INSERT INTO academic_ranks (academic_rank, monthly_salary) VALUES 
            ('Instructor I', 25000.00),
            ('Instructor II', 27000.00),
            ('Instructor III', 29000.00),
            ('Assistant Professor I', 31000.00),
            ('Assistant Professor II', 33000.00),
            ('Assistant Professor III', 35000.00),
            ('Associate Professor I', 37000.00),
            ('Associate Professor II', 39000.00),
            ('Associate Professor III', 41000.00),
            ('Professor I', 43000.00),
            ('Professor II', 45000.00),
            ('Professor III', 47000.00)";
        $conn->exec($insertData);
        
        echo "Table created and sample data inserted.\n";
    } else {
        echo "Table 'academic_ranks' exists. Checking data...\n";
        
        // Check if table has data
        $query = "SELECT * FROM academic_ranks";
        $stmt = $conn->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "Table is empty. Inserting sample data...\n";
            
            // Insert sample data
            $insertData = "INSERT INTO academic_ranks (academic_rank, monthly_salary) VALUES 
                ('Instructor I', 25000.00),
                ('Instructor II', 27000.00),
                ('Instructor III', 29000.00),
                ('Assistant Professor I', 31000.00),
                ('Assistant Professor II', 33000.00),
                ('Assistant Professor III', 35000.00),
                ('Associate Professor I', 37000.00),
                ('Associate Professor II', 39000.00),
                ('Associate Professor III', 41000.00),
                ('Professor I', 43000.00),
                ('Professor II', 45000.00),
                ('Professor III', 47000.00)";
            $conn->exec($insertData);
            
            echo "Sample data inserted.\n";
        } else {
            echo "Table has data. Current records:\n";
            foreach ($rows as $row) {
                echo "{$row['academic_rank']}: {$row['monthly_salary']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 