<?php
// Find the most recent backup file
$backupFiles = glob('print_narrative.php.backup.*');
rsort($backupFiles); // Sort in descending order to get most recent first

if (empty($backupFiles)) {
    die("No backup files found!\n");
}

$latestBackup = $backupFiles[0];
echo "Using latest backup: $latestBackup\n";

// Copy the backup to a temp file
$tempFile = 'print_narrative.php.temp';
copy($latestBackup, $tempFile);

// Read the temp file
$content = file_get_contents($tempFile);

// Specifically add the condition around line 2097 function declaration
$lines = explode("\n", $content);
$inFunction = false;
$startLine = -1;
$endLine = -1;

// Find the second declaration of getDefaultRatings()
$firstFound = false;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'function getDefaultRatings()') !== false) {
        if (!$firstFound) {
            // This is the first declaration, skip it
            $firstFound = true;
            continue;
        }
        
        // This is the second declaration - mark it
        $startLine = $i;
        $inFunction = true;
        continue;
    }
    
    if ($inFunction && trim($lines[$i]) == '}') {
        $endLine = $i;
        break;
    }
}

// If we found the second declaration
if ($startLine > 0 && $endLine > 0) {
    // Add the if(false) condition to the second declaration
    $lines[$startLine] = "if (false) { // Prevent duplicate function declaration\n" . $lines[$startLine];
    $lines[$endLine] = $lines[$endLine] . "\n}";
    
    // Save the modified content back to the temp file
    file_put_contents($tempFile, implode("\n", $lines));
    
    // Replace the original file with the fixed version
    rename($tempFile, 'print_narrative.php');
    echo "Successfully fixed the duplicate function declaration.\n";
} else {
    echo "Could not find the duplicate function declaration.\n";
    unlink($tempFile);
}
?> 