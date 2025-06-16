<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Fields Implementation Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
        }
        .step {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 5px solid #007bff;
        }
        .code-block {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            margin: 15px 0;
        }
        .tool-card {
            margin-bottom: 20px;
        }
        h2 {
            color: #007bff;
            margin-top: 40px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Narrative Fields Implementation Guide</h1>
        
        <div class="alert alert-info">
            <h4 class="alert-heading">Overview</h4>
            <p>This guide explains how to properly implement and troubleshoot the narrative fields functionality in the GAD system. The system retrieves narrative data (results, lessons, what_worked, issues, recommendations) from the narrative_entries table.</p>
        </div>
        
        <h2>Understanding the Issue</h2>
        <div class="step">
            <h4>The Problem</h4>
            <p>The system is not retrieving narrative fields correctly. This could be due to several reasons:</p>
            <ul>
                <li>The <code>narrative_entries</code> table does not exist in the database</li>
                <li>The required fields are missing in the table</li>
                <li>Different field names are used (e.g., <code>expected_results</code> instead of <code>results</code>)</li>
                <li>No data exists for the requested PPAS form ID</li>
                <li>The narrative entries are not correctly linked to PPAS forms</li>
                <li>Activity ratings and timeliness ratings are not being formatted correctly</li>
            </ul>
        </div>
        
        <h2>Solution Tools</h2>
        <p>We've created several tools to help diagnose and fix these issues:</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">update_narrative_table.php</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Purpose:</strong> Ensures the narrative_entries table has all required fields.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>Creates the table if it doesn't exist</li>
                            <li>Adds missing fields</li>
                            <li>Syncs data between standard and alternative field names</li>
                            <li>Adds indexes for better performance</li>
                        </ul>
                        <a href="update_narrative_table.php" class="btn btn-primary">Run Tool</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">test_narrative_fields.php</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Purpose:</strong> Tests the narrative field retrieval function with detailed diagnostics.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>Tests field retrieval with any PPAS form ID</li>
                            <li>Links narrative entries to PPAS forms</li>
                            <li>Displays detailed debug information</li>
                            <li>Shows the contents of each field</li>
                        </ul>
                        <a href="test_narrative_fields.php" class="btn btn-success">Run Tool</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">test_ratings.php</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Purpose:</strong> Tests and fixes the activity ratings functionality.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>Displays formatted ratings tables</li>
                            <li>Shows raw ratings data structure</li>
                            <li>Updates ratings with test data</li>
                            <li>Links narrative entries to PPAS forms</li>
                        </ul>
                        <a href="test_ratings.php" class="btn btn-info">Run Tool</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">check_narrative_entries.php</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Purpose:</strong> Diagnoses issues with the narrative_entries table.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>Verifies if the table exists</li>
                            <li>Lists table structure</li>
                            <li>Counts entries</li>
                            <li>Checks for required fields</li>
                        </ul>
                        <a href="check_narrative_entries.php" class="btn btn-info">Run Tool</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">add_test_narrative.php</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Purpose:</strong> Adds test data to the narrative_entries table.</p>
                        <p><strong>Features:</strong></p>
                        <ul>
                            <li>Creates the table if it doesn't exist</li>
                            <li>Inserts or updates narrative entries</li>
                            <li>Pre-populates form with PPAS data</li>
                            <li>Handles alternative field names</li>
                        </ul>
                        <a href="add_test_narrative.php" class="btn btn-dark">Run Tool</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h2>Step-by-Step Implementation Guide</h2>
        
        <div class="step">
            <h4>Step 1: Update the Table Structure</h4>
            <p>First, run the update_narrative_table.php tool to ensure your database has the correct table structure:</p>
            <ol>
                <li>Go to <a href="update_narrative_table.php">update_narrative_table.php</a></li>
                <li>The tool will automatically add any missing fields and display the results</li>
                <li>Verify that all required fields are listed in the table structure</li>
            </ol>
        </div>
        
        <div class="step">
            <h4>Step 2: Check for Existing Data</h4>
            <p>Use the check_narrative_entries.php tool to confirm if you have data in the narrative_entries table:</p>
            <ol>
                <li>Go to <a href="check_narrative_entries.php">check_narrative_entries.php</a></li>
                <li>Check the "Total entries" count</li>
                <li>Review sample entries if available</li>
            </ol>
        </div>
        
        <div class="step">
            <h4>Step 3: Add Test Data (if needed)</h4>
            <p>If you don't have any narrative entries or want to test with new data:</p>
            <ol>
                <li>Go to <a href="add_test_narrative.php">add_test_narrative.php</a></li>
                <li>Select a PPAS form ID to associate with the narrative</li>
                <li>Fill in the form with test data for all fields</li>
                <li>Submit the form to create a new entry</li>
            </ol>
        </div>
        
        <div class="step">
            <h4>Step 4: Test the Field Retrieval</h4>
            <p>Use the test_narrative_fields.php tool to verify that the fields can be retrieved correctly:</p>
            <ol>
                <li>Go to <a href="test_narrative_fields.php">test_narrative_fields.php</a></li>
                <li>Select the PPAS form ID you want to test</li>
                <li>Review the debug information to see how the system searches for narrative data</li>
                <li>Check if all narrative fields are successfully retrieved</li>
            </ol>
        </div>
        
        <div class="step">
            <h4>Step 5: Link Narrative Entries to PPAS Forms (if needed)</h4>
            <p>If the test shows that data exists but isn't being found, use the linking feature:</p>
            <ol>
                <li>In test_narrative_fields.php, use the "Link Narrative Entry to PPAS Form" section</li>
                <li>Select a narrative entry and PPAS form ID to link them</li>
                <li>Click "Link & Test" to establish the connection and test retrieval</li>
            </ol>
        </div>
        
        <h2>Troubleshooting Common Issues</h2>
        
        <div class="accordion" id="troubleshootingAccordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne">
                            "Narrative data from narrative_entries: null" error
                        </button>
                    </h5>
                </div>
                <div id="collapseOne" class="collapse" data-parent="#troubleshootingAccordion">
                    <div class="card-body">
                        <p>This error means the system couldn't find any narrative data for the requested PPAS form ID.</p>
                        <p><strong>Solutions:</strong></p>
                        <ol>
                            <li>Check if the narrative_entries table exists and has the required fields</li>
                            <li>Verify that you have data in the table for the requested PPAS form</li>
                            <li>Link the narrative entry to the PPAS form using the test_narrative_fields.php tool</li>
                            <li>Add a new entry using add_test_narrative.php if no data exists</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseTwo">
                            Some fields are empty while others have data
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" data-parent="#troubleshootingAccordion">
                    <div class="card-body">
                        <p>This happens when only some of the narrative fields contain data.</p>
                        <p><strong>Solutions:</strong></p>
                        <ol>
                            <li>Use add_test_narrative.php to update the existing entry with data for all fields</li>
                            <li>Run update_narrative_table.php to ensure all fields exist and are synchronized</li>
                            <li>Check if data exists with alternative field names (expected_results vs results)</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseThree">
                            Function declaration errors
                        </button>
                    </h5>
                </div>
                <div id="collapseThree" class="collapse" data-parent="#troubleshootingAccordion">
                    <div class="card-body">
                        <p>If you see "Cannot redeclare getActivityNarrativeFields()" errors, it means the function is defined multiple times.</p>
                        <p><strong>Solutions:</strong></p>
                        <ol>
                            <li>Make sure each function is declared with the condition <code>if (!function_exists('functionName'))</code></li>
                            <li>Include the get_activity_narrative.php file only once in your code</li>
                            <li>Check for duplicate function definitions in your code</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <h2>Technical Implementation</h2>
        <p>The primary function used to retrieve narrative fields is <code>getActivityNarrativeFields()</code> in get_activity_narrative.php:</p>
        
        <div class="code-block">
// Usage example:
$ppas_form_id = 1; // Replace with your PPAS form ID
$narrative_fields = getActivityNarrativeFields($ppas_form_id);

// Access individual fields
$results = $narrative_fields['results'];
$lessons = $narrative_fields['lessons'];
$what_worked = $narrative_fields['what_worked'];
$issues = $narrative_fields['issues'];
$recommendations = $narrative_fields['recommendations'];

// For debugging, enable the debug parameter
$narrative_fields = getActivityNarrativeFields($ppas_form_id, true);</div>
        
        <p class="mt-4">For activity and timeliness ratings, use the <code>getActivityRatings()</code> function in get_activity_ratings.php:</p>
        
        <div class="code-block">
// Usage example:
$ppas_form_id = 1; // Replace with your PPAS form ID
$ratings = getActivityRatings($ppas_form_id);

// Access activity ratings
$activity_ratings = $ratings['activity_ratings'];
$excellent_batstateu = $activity_ratings['Excellent']['BatStateU'];
$excellent_others = $activity_ratings['Excellent']['Others'];
$excellent_total = $activity_ratings['Excellent']['Total'];

// Access timeliness ratings
$timeliness_ratings = $ratings['timeliness_ratings'];
$satisfactory_batstateu = $timeliness_ratings['Satisfactory']['BatStateU'];
$satisfactory_others = $timeliness_ratings['Satisfactory']['Others'];
$satisfactory_total = $timeliness_ratings['Satisfactory']['Total'];

// Get totals
$total_batstateu = $activity_ratings['Total']['BatStateU'];
$total_others = $activity_ratings['Total']['Others'];
$total_respondents = $activity_ratings['Total']['Total'];

// Display ratings as an HTML table
echo displayRatingsTable($activity_ratings, 'Number of beneficiaries/participants who rated the activity as:');
echo displayRatingsTable($timeliness_ratings, 'Number of beneficiaries/participants who rated the timeliness of the activity as:');</div>
        
        <div class="mt-5 pt-3 border-top">
            <h4>Need Further Assistance?</h4>
            <p>If you continue to experience issues after following this guide, please check the following:</p>
            <ul>
                <li>Database connection settings in your environment</li>
                <li>Table permissions in MySQL</li>
                <li>PHP error logs for any additional error details</li>
            </ul>
        </div>
        
        <div class="mt-4 mb-5">
            <a href="../../index.php" class="btn btn-primary">Return to Main Page</a>
            <a href="test_narrative_fields.php" class="btn btn-success">Run Test Tool</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 