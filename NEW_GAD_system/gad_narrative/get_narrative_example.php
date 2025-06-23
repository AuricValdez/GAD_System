<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Include the function file
require_once('get_activity_narrative.php');

// Get PPAS form ID from query parameter
$ppas_form_id = isset($_GET['ppas_form_id']) ? intval($_GET['ppas_form_id']) : 0;

if ($ppas_form_id <= 0) {
    echo "Please provide a valid PPAS Form ID.";
    exit;
}

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug information
echo "<div style='background-color: #f8f9fa; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;'>";
echo "<h3>Debug Information</h3>";
echo "<p>PPAS Form ID: " . $ppas_form_id . "</p>";

// Get the narrative fields with debug enabled
$debug_mode = true;
$narrative_fields = getActivityNarrativeFields($ppas_form_id, $debug_mode);

echo "</div>";

// Display the results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Narrative Fields</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .section h2 {
            margin-top: 0;
            color: #0066cc;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .empty {
            color: #999;
            font-style: italic;
        }
        .debug {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Activity Narrative Fields for PPAS Form #<?php echo $ppas_form_id; ?></h1>
    
    <div class="section">
        <h2>Results</h2>
        <?php if (!empty($narrative_fields['results'])): ?>
            <p><?php echo nl2br(htmlspecialchars($narrative_fields['results'])); ?></p>
        <?php else: ?>
            <p class="empty">No results data available</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Lessons Learned</h2>
        <?php if (!empty($narrative_fields['lessons'])): ?>
            <p><?php echo nl2br(htmlspecialchars($narrative_fields['lessons'])); ?></p>
        <?php else: ?>
            <p class="empty">No lessons learned data available</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>What Worked</h2>
        <?php if (!empty($narrative_fields['what_worked'])): ?>
            <p><?php echo nl2br(htmlspecialchars($narrative_fields['what_worked'])); ?></p>
        <?php else: ?>
            <p class="empty">No what worked data available</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Issues and Concerns</h2>
        <?php if (!empty($narrative_fields['issues'])): ?>
            <p><?php echo nl2br(htmlspecialchars($narrative_fields['issues'])); ?></p>
        <?php else: ?>
            <p class="empty">No issues and concerns data available</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Recommendations</h2>
        <?php if (!empty($narrative_fields['recommendations'])): ?>
            <p><?php echo nl2br(htmlspecialchars($narrative_fields['recommendations'])); ?></p>
        <?php else: ?>
            <p class="empty">No recommendations data available</p>
        <?php endif; ?>
    </div>
    
    <p><a href="javascript:history.back()">« Back</a></p>
</body>
</html> 