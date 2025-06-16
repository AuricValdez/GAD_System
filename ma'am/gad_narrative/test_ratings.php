<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Include the functions
include_once('get_activity_ratings.php');

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get database connection
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
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Get all available PPAS form IDs for testing
function getPpasFormIds() {
    try {
        $conn = getConnection();
        $stmt = $conn->query("SELECT id, activity, campus, year FROM ppas_forms ORDER BY id DESC LIMIT 20");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Get all available narrative entries for testing
function getNarrativeEntries() {
    try {
        $conn = getConnection();
        $stmt = $conn->query("SELECT id, title, campus, year FROM narrative_entries ORDER BY id DESC LIMIT 20");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Process form submission to test the function
$test_results = null;
$form_id = isset($_GET['ppas_form_id']) ? $_GET['ppas_form_id'] : '';
$narrative_id = isset($_GET['narrative_id']) ? $_GET['narrative_id'] : '';
$update_mode = isset($_GET['update_mode']) ? true : false;
$update_message = '';

if ($form_id) {
    // Test with the provided PPAS form ID
    $test_results = getActivityRatings($form_id, true);
}

// Check for narrative entry linking
if ($narrative_id && $form_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE narrative_entries SET ppas_form_id = :form_id WHERE id = :id");
        $stmt->execute([':form_id' => $form_id, ':id' => $narrative_id]);
        
        $update_message = "<div class='alert alert-success'>
            Successfully linked narrative ID $narrative_id to PPAS form ID $form_id
        </div>";
        
        // Now test with the linked form ID
        $test_results = getActivityRatings($form_id, true);
    } catch (Exception $e) {
        $update_message = "<div class='alert alert-danger'>
            Error linking IDs: " . $e->getMessage() . "
        </div>";
    }
}

// Update ratings data if requested
if ($update_mode && $form_id) {
    try {
        $conn = getConnection();
        
        // Get the narrative entry associated with this PPAS form
        $stmt = $conn->prepare("SELECT id FROM narrative_entries WHERE ppas_form_id = :form_id LIMIT 1");
        $stmt->execute([':form_id' => $form_id]);
        $narrative_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($narrative_entry) {
            $narrative_id = $narrative_entry['id'];
            
            // Example ratings data structure
            $activity_ratings = [
                'Excellent' => ['BatStateU' => 22, 'Others' => 15],
                'Very Satisfactory' => ['BatStateU' => 18, 'Others' => 12],
                'Satisfactory' => ['BatStateU' => 8, 'Others' => 5],
                'Fair' => ['BatStateU' => 3, 'Others' => 2],
                'Poor' => ['BatStateU' => 1, 'Others' => 1]
            ];
            
            // Same structure for timeliness ratings (in this example we use the same values)
            $timeliness_ratings = $activity_ratings;
            
            // Update the narrative entry with JSON encoded ratings
            $stmt = $conn->prepare("UPDATE narrative_entries SET 
                activity_ratings = :activity_ratings,
                timeliness_ratings = :timeliness_ratings
                WHERE id = :id");
                
            $stmt->execute([
                ':activity_ratings' => json_encode($activity_ratings),
                ':timeliness_ratings' => json_encode($timeliness_ratings),
                ':id' => $narrative_id
            ]);
            
            $update_message = "<div class='alert alert-success'>
                Successfully updated ratings data for narrative ID $narrative_id
            </div>";
            
            // Refresh the test results
            $test_results = getActivityRatings($form_id, true);
        } else {
            $update_message = "<div class='alert alert-warning'>
                No narrative entry found for PPAS form ID $form_id. Please link a narrative entry first.
            </div>";
        }
    } catch (Exception $e) {
        $update_message = "<div class='alert alert-danger'>
            Error updating ratings: " . $e->getMessage() . "
        </div>";
    }
}

// Get lists of available IDs
$ppas_forms = getPpasFormIds();
$narrative_entries = getNarrativeEntries();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Activity Ratings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .result-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .ratings-table-container {
            margin-bottom: 30px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9em;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Activity Ratings</h1>
        <p class="lead">This tool helps test and debug the activity ratings functionality.</p>
        
        <?php echo $update_message; ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Test with PPAS Form ID</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="form-group">
                                <label for="ppas_form_id">Select PPAS Form ID:</label>
                                <select name="ppas_form_id" id="ppas_form_id" class="form-control">
                                    <option value="">-- Select PPAS Form ID --</option>
                                    <?php foreach ($ppas_forms as $form): ?>
                                    <option value="<?php echo $form['id']; ?>" <?php echo ($form_id == $form['id']) ? 'selected' : ''; ?>>
                                        ID: <?php echo $form['id']; ?> - <?php echo htmlspecialchars($form['activity']); ?> 
                                        (<?php echo htmlspecialchars($form['campus']); ?>, <?php echo $form['year']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Ratings</button>
                            <button type="submit" class="btn btn-warning" name="update_mode" value="1">Update with Test Data</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Link Narrative Entry to PPAS Form</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="form-group">
                                <label for="narrative_id">Select Narrative Entry:</label>
                                <select name="narrative_id" id="narrative_id" class="form-control">
                                    <option value="">-- Select Narrative Entry --</option>
                                    <?php foreach ($narrative_entries as $entry): ?>
                                    <option value="<?php echo $entry['id']; ?>">
                                        ID: <?php echo $entry['id']; ?> - <?php echo htmlspecialchars($entry['title']); ?> 
                                        (<?php echo htmlspecialchars($entry['campus']); ?>, <?php echo $entry['year']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ppas_form_id">Link to PPAS Form ID:</label>
                                <select name="ppas_form_id" class="form-control">
                                    <option value="">-- Select PPAS Form ID --</option>
                                    <?php foreach ($ppas_forms as $form): ?>
                                    <option value="<?php echo $form['id']; ?>">
                                        ID: <?php echo $form['id']; ?> - <?php echo htmlspecialchars($form['activity']); ?> 
                                        (<?php echo htmlspecialchars($form['campus']); ?>, <?php echo $form['year']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Link & Test</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($test_results): ?>
        <div class="result-box">
            <h3>Test Results for PPAS Form ID: <?php echo $form_id; ?></h3>
            
            <div class="row">
                <div class="col-md-6">
                    <?php echo displayRatingsTable($test_results['activity_ratings'], 'Number of beneficiaries/participants who rated the activity as:'); ?>
                </div>
                
                <div class="col-md-6">
                    <?php echo displayRatingsTable($test_results['timeliness_ratings'], 'Number of beneficiaries/participants who rated the timeliness of the activity as:'); ?>
                </div>
            </div>
            
            <div class="mt-4">
                <h4>Raw Ratings Data</h4>
                <p>This is the raw data structure that will be used in your code:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Activity Ratings:</h5>
                        <pre><?php print_r($test_results['activity_ratings']); ?></pre>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Timeliness Ratings:</h5>
                        <pre><?php print_r($test_results['timeliness_ratings']); ?></pre>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <p><strong>How to use this data in your code:</strong></p>
                    <pre>
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
$total_respondents = $activity_ratings['Total']['Total'];</pre>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="narrative_field_guide.php" class="btn btn-primary">Back to Guide</a>
            <a href="update_narrative_table.php" class="btn btn-secondary">Update Table Structure</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 