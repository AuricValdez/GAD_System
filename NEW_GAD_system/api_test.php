<?php
// Disable auth requirement temporarily for testing
session_start();
$_SESSION['username'] = 'Lipa'; // Simulate login

echo "<h1>API Test Results</h1>";

// Database connection
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        echo "<p>Database connection error: " . $e->getMessage() . "</p>";
        die();
    }
}

// First check the database tables directly
echo "<h2>Direct Database Check</h2>";
$conn = getConnection();

// Check ppas_forms table structure
$stmt = $conn->query("DESCRIBE ppas_forms");
$ppas_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<p>PPAS Forms table has the following columns: " . implode(", ", $ppas_columns) . "</p>";

// Check for year column in ppas_forms
if (in_array('year', $ppas_columns)) {
    echo "<p style='color:green'>✅ PPAS Forms table has 'year' column</p>";
} else {
    echo "<p style='color:red'>❌ PPAS Forms table doesn't have 'year' column</p>";
}

// Get a sample of data from ppas_forms
$campus = "Lipa";
$year = "2026";
$ppas_sql = "SELECT id, campus, year, activity FROM ppas_forms WHERE campus = :campus AND year = :year LIMIT 5";
$ppas_stmt = $conn->prepare($ppas_sql);
$ppas_stmt->execute([':campus' => $campus, ':year' => $year]);
$ppas_data = $ppas_stmt->fetchAll();

echo "<h3>Sample data from ppas_forms</h3>";
if (count($ppas_data) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Campus</th><th>Year</th><th>Activity</th></tr>";
    foreach ($ppas_data as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['campus'] . "</td>";
        echo "<td>" . $row['year'] . "</td>";
        echo "<td>" . $row['activity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in ppas_forms for campus='$campus' and year='$year'</p>";
    
    // Try without year filter
    $ppas_sql = "SELECT id, campus, year, activity FROM ppas_forms WHERE campus = :campus LIMIT 5";
    $ppas_stmt = $conn->prepare($ppas_sql);
    $ppas_stmt->execute([':campus' => $campus]);
    $ppas_data = $ppas_stmt->fetchAll();
    
    if (count($ppas_data) > 0) {
        echo "<p>Found data without year filter:</p>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Campus</th><th>Year</th><th>Activity</th></tr>";
        foreach ($ppas_data as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['campus'] . "</td>";
            echo "<td>" . $row['year'] . "</td>";
            echo "<td>" . $row['activity'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test get_proposals.php
echo "<h2>Testing get_proposals.php</h2>";
$url = "http://localhost:8000/narrative_reports/api/get_proposals.php?campus=$campus&year=$year";
echo "<p>Requesting: $url</p>";

$proposals_json = file_get_contents($url);
$proposals = json_decode($proposals_json, true);

echo "<pre>";
print_r($proposals);
echo "</pre>";

if (isset($proposals['data']) && count($proposals['data']) > 0) {
    echo "<p>Found " . count($proposals['data']) . " proposals.</p>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>PPAS Form ID</th><th>Activity Title</th><th>Campus</th><th>Year</th></tr>";
    
    foreach ($proposals['data'] as $proposal) {
        echo "<tr>";
        echo "<td>" . $proposal['id'] . "</td>";
        echo "<td>" . $proposal['ppas_form_id'] . "</td>";
        echo "<td>" . $proposal['activity_title'] . "</td>";
        echo "<td>" . $proposal['campus'] . "</td>";
        echo "<td>" . $proposal['year'] . "</td>";
        echo "</tr>";
        
        // Now test get_narrative.php with this proposal
        $ppas_id = $proposal['ppas_form_id'];
        break; // Just test the first one
    }
    
    echo "</table>";
    
    // Test get_narrative.php with the first proposal
    if (isset($ppas_id)) {
        echo "<h2>Testing get_narrative.php</h2>";
        $narrative_url = "http://localhost:8000/narrative_reports/api/get_narrative.php?ppas_form_id=$ppas_id&campus=$campus";
        echo "<p>Requesting: $narrative_url</p>";
        
        $narrative_json = file_get_contents($narrative_url);
        $narrative = json_decode($narrative_json, true);
        
        echo "<pre>";
        print_r($narrative);
        echo "</pre>";
        
        if (isset($narrative['data'])) {
            echo "<p>Successfully retrieved narrative data!</p>";
            echo "<p>Year from API: " . $narrative['data']['year'] . "</p>";
            
            // Double check by querying the database directly
            $check_sql = "SELECT year FROM ppas_forms WHERE id = :id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([':id' => $ppas_id]);
            $ppas_year = $check_stmt->fetchColumn();
            
            echo "<p>Year directly from ppas_forms table: " . $ppas_year . "</p>";
            
            if ($narrative['data']['year'] == $ppas_year) {
                echo "<p style='color:green'>✅ YEARS MATCH! Year data is correctly coming from ppas_forms table.</p>";
            } else {
                echo "<p style='color:red'>❌ YEARS DON'T MATCH! Year in API: " . $narrative['data']['year'] . 
                     ", Year in ppas_forms: " . $ppas_year . "</p>";
            }
        }
    }
}
?> 