<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing proposals fetch from ppas_forms ===\n\n";

// Get parameters from command line argv
$campus = isset($argv[1]) ? $argv[1] : 'Lipa';
$year = isset($argv[2]) ? $argv[2] : '2025';
$id = isset($argv[3]) ? $argv[3] : null;

echo "Parameters: campus=$campus, year=$year, id=$id\n\n";

try {
    // Connect to database
    $conn = new PDO("mysql:host=localhost;dbname=gad_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully\n\n";
    
    // Query 1: Get all proposals for the given campus and year
    $query1 = "SELECT id, campus, year, activity FROM ppas_forms WHERE campus = :campus AND year = :year";
    $stmt = $conn->prepare($query1);
    $stmt->execute(['campus' => $campus, 'year' => $year]);
    
    echo "Proposals for campus '$campus' and year '$year':\n";
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "❌ No proposals found.\n\n";
    } else {
        foreach ($results as $row) {
            echo "- ID: {$row['id']}, Campus: {$row['campus']}, Year: {$row['year']}, Activity: {$row['activity']}\n";
        }
        echo "\n";
    }
    
    // Query 2: If ID is provided, test specific proposal fetch
    if ($id !== null) {
        $query2 = "SELECT id, campus, year, activity FROM ppas_forms WHERE id = :id";
        $stmt = $conn->prepare($query2);
        $stmt->execute(['id' => $id]);
        $proposal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Specific proposal lookup for ID '$id':\n";
        if (!$proposal) {
            echo "❌ No proposal found with ID $id.\n\n";
        } else {
            echo "✅ Found proposal:\n";
            echo "- ID: {$proposal['id']}, Campus: {$proposal['campus']}, Year: {$proposal['year']}, Activity: {$proposal['activity']}\n\n";
            
            // Check if it matches the campus and year criteria too
            if ($proposal['campus'] === $campus && $proposal['year'] === $year) {
                echo "✅ Proposal matches campus/year criteria too.\n";
            } else {
                echo "❌ Proposal exists but does NOT match campus/year criteria.\n";
                echo "- Expected: campus=$campus, year=$year\n";
                echo "- Found: campus={$proposal['campus']}, year={$proposal['year']}\n";
            }
        }
    }
    
    // Query 3: Test DATE_FORMAT function (related to our problem)
    echo "\nTesting DATE_FORMAT with a sample date:\n";
    $date_query = "SELECT 
                     '12/16/2030' as original_date,
                     DATE_FORMAT('12/16/2030', '%M %d, %Y') as formatted_date,
                     CONCAT('12/16/2030', ' to ', '12/18/2030') as concat_simple";
    $date_result = $conn->query($date_query)->fetch(PDO::FETCH_ASSOC);
    print_r($date_result);

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} 