<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if session already has username
echo "<h1>Session Authentication Test</h1>";
echo "<h2>Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

// Set the username in session if not present
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Lipa'; // Default to Lipa campus
    echo "<div style='color: green; font-weight: bold;'>Created test session with username: Lipa</div>";
} else {
    echo "<div style='color: blue; font-weight: bold;'>Session already has username: " . $_SESSION['username'] . "</div>";
}

// Include config file
$configFile = '../../includes/config.php';
echo "<h2>Config File</h2>";
echo "Path: $configFile<br>";
echo "Exists: " . (file_exists($configFile) ? 'Yes' : 'No') . "<br>";

if (file_exists($configFile)) {
    require_once $configFile;
    echo "Config loaded successfully<br>";
} else {
    echo "<div style='color: red; font-weight: bold;'>Config file not found!</div>";
    exit;
}

// Test getting years manually
echo "<h2>Manual API Test</h2>";
echo "Trying to get years for campus: " . $_SESSION['username'] . "<br>";

try {
    // Create database connection
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database<br>";
    
    // Query for years
    $query = "SELECT DISTINCT year FROM gad_proposals WHERE created_by = :campus ORDER BY year DESC";
    $stmt = $db->prepare($query);
    $stmt->execute(['campus' => $_SESSION['username']]);
    
    echo "Query executed<br>";
    
    // Fetch results
    $years = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $years[] = $row['year'];
    }
    
    echo "Found " . count($years) . " years:<br>";
    if (count($years) > 0) {
        echo "<ul>";
        foreach ($years as $year) {
            echo "<li>$year</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: orange;'>No years found for this campus</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>";
}

// Provides a link to test the actual API
echo "<h2>API Test Links</h2>";
$apiUrl = "get_proposal_years.php?campus=" . urlencode($_SESSION['username']);
echo "<a href='$apiUrl' target='_blank'>Test get_proposal_years.php API</a><br>";
echo "<div>Click the link above to test if the API works with your current session.</div>";

// Add button to reload page
echo "<h2>Actions</h2>";
echo "<button onclick='window.location.reload()'>Refresh Page</button> ";
echo "<button onclick='window.location=\"/ppas_proposal/print_proposal.php\"'>Go to Print Proposal Page</button>"; 