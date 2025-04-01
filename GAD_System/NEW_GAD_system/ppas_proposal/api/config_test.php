<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuration File Path Test</h1>";

// Try different possible paths
$possible_paths = [
    '../../includes/config.php',
    '../includes/config.php',
    '../../config.php',
    '../config.php',
    '../../../includes/config.php',
    './includes/config.php',
    '/includes/config.php',
    '../../../../includes/config.php'
];

echo "<h2>Current Script Location</h2>";
echo "Current file: " . __FILE__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current directory: " . getcwd() . "<br>";

echo "<h2>Config File Path Tests</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Path</th><th>Absolute Path</th><th>Exists</th><th>Is Readable</th></tr>";

foreach ($possible_paths as $path) {
    $abs_path = realpath($path);
    $exists = file_exists($path);
    $readable = is_readable($path);
    
    echo "<tr>";
    echo "<td>$path</td>";
    echo "<td>" . ($abs_path ? $abs_path : 'N/A') . "</td>";
    echo "<td>" . ($exists ? 'Yes' : 'No') . "</td>";
    echo "<td>" . ($readable ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
    
    // If it exists, check content
    if ($exists && $readable) {
        echo "<tr><td colspan='4'><strong>File contents preview (first 10 lines):</strong><br>";
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $preview = array_slice($lines, 0, 10);
        foreach ($preview as $i => $line) {
            // Sanitize the output by removing actual credentials
            $sanitized = preg_replace('/(password|pass|pwd|user|username|dbname)\s*=\s*[\'"].*?[\'"]/', '$1="REDACTED"', $line);
            echo htmlspecialchars($sanitized) . "<br>";
        }
        echo "</td></tr>";
    }
}
echo "</table>";

// Look for any file named config.php in parent directories
echo "<h2>Search for any config.php file</h2>";
$cmd = "find " . dirname(dirname(__FILE__)) . " -name 'config.php' -type f 2>/dev/null";
echo "Search command: " . htmlspecialchars($cmd) . "<br>";
exec($cmd, $results);

if (count($results) > 0) {
    echo "<ul>";
    foreach ($results as $config_file) {
        echo "<li>" . htmlspecialchars($config_file) . " - Exists: " . (file_exists($config_file) ? 'Yes' : 'No') . "</li>";
    }
    echo "</ul>";
} else {
    echo "No config.php files found.<br>";
}

// Look for includes directory
echo "<h2>Search for includes directory</h2>";
$cmd = "find " . dirname(dirname(__FILE__)) . " -name 'includes' -type d 2>/dev/null";
echo "Search command: " . htmlspecialchars($cmd) . "<br>";
exec($cmd, $results);

if (count($results) > 0) {
    echo "<ul>";
    foreach ($results as $inc_dir) {
        echo "<li>" . htmlspecialchars($inc_dir) . " - Exists: " . (is_dir($inc_dir) ? 'Yes' : 'No') . "</li>";
    }
    echo "</ul>";
} else {
    echo "No includes directories found.<br>";
} 