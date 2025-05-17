<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing MySQL Date Formatting ===\n\n";

try {
    // Connect to database
    $conn = new PDO("mysql:host=localhost;dbname=gad_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully\n\n";
    
    echo "Testing different date formats:\n";
    
    // Test 1: Check standard date format with DATE_FORMAT
    $query1 = "SELECT 
               '2023-01-15' as standard_date,
               DATE_FORMAT('2023-01-15', '%M %d, %Y') as formatted_std_date";
    $result1 = $conn->query($query1)->fetch(PDO::FETCH_ASSOC);
    echo "Standard date format (YYYY-MM-DD):\n";
    print_r($result1);
    
    // Test 2: Check US format with DATE_FORMAT
    $query2 = "SELECT 
               '12/16/2030' as us_date,
               STR_TO_DATE('12/16/2030', '%m/%d/%Y') as parsed_date,
               DATE_FORMAT(STR_TO_DATE('12/16/2030', '%m/%d/%Y'), '%M %d, %Y') as formatted_us_date";
    $result2 = $conn->query($query2)->fetch(PDO::FETCH_ASSOC);
    echo "\nUS date format (MM/DD/YYYY):\n";
    print_r($result2);
    
    // Test 3: Check date from ppas_forms table
    $query3 = "SELECT 
               id, 
               start_date, 
               end_date,
               STR_TO_DATE(start_date, '%m/%d/%Y') as parsed_start_date,
               DATE_FORMAT(STR_TO_DATE(start_date, '%m/%d/%Y'), '%M %d, %Y') as formatted_start_date,
               CONCAT(
                  DATE_FORMAT(STR_TO_DATE(start_date, '%m/%d/%Y'), '%M %d, %Y'),
                  ' to ',
                  DATE_FORMAT(STR_TO_DATE(end_date, '%m/%d/%Y'), '%M %d, %Y')
               ) as duration
               FROM ppas_forms 
               WHERE id = 28
               LIMIT 1";
    $result3 = $conn->query($query3)->fetch(PDO::FETCH_ASSOC);
    echo "\nActual date from ppas_forms (ID=28):\n";
    print_r($result3);

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} 