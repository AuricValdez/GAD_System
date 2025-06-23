<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Include the function file
include_once('get_activity_narrative.php');

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

if ($form_id) {
    // Test with the provided PPAS form ID
    $test_results = getActivityNarrativeFields($form_id, true);
}

// Test with direct narrative ID to PPAS form ID mapping
if ($narrative_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE narrative_entries SET ppas_form_id = :form_id WHERE id = :id");
        $stmt->execute([':form_id' => $form_id, ':id' => $narrative_id]);
        
        echo "<div class='alert alert-success'>
            Successfully linked narrative ID $narrative_id to PPAS form ID $form_id
        </div>";
        
        // Now test with the linked form ID
        $test_results = getActivityNarrativeFields($form_id, true);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>
            Error linking IDs: " . $e->getMessage() . "
        </div>";
    }
}

// Get lists of available IDs
$ppas_forms = getPpasFormIds();
$narrative_entries = getNarrativeEntries();

// Check for missing ppas_form_id column in narrative_entries
$ppas_form_id_column_exists = false;
try {
    $conn = getConnection();
    $columns_query = $conn->query("DESCRIBE narrative_entries");
    $columns = $columns_query->fetchAll(PDO::FETCH_COLUMN);
    $ppas_form_id_column_exists = in_array('ppas_form_id', $columns);
    
    // Add the column if it doesn't exist
    if (!$ppas_form_id_column_exists) {
        $conn->exec("ALTER TABLE narrative_entries ADD COLUMN ppas_form_id INT DEFAULT NULL, ADD INDEX (ppas_form_id)");
        $ppas_form_id_column_exists = true;
        echo "<div class='alert alert-success'>
            Added ppas_form_id column to narrative_entries table
        </div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        Error checking or adding column: " . $e->getMessage() . "
    </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Narrative Fields</title>
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
        .field-box {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 3px;
        }
        .field-content {
            max-height: 100px;
            overflow-y: auto;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .empty-field {
            color: #dc3545;
            font-style: italic;
        }
        .has-content {
            color: #28a745;
        }
        .card-columns {
            column-count: 2;
        }
        @media (max-width: 768px) {
            .card-columns {
                column-count: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Narrative Fields</h1>
        <p class="lead">This tool helps test and debug the getActivityNarrativeFields function.</p>
        
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
                            <button type="submit" class="btn btn-primary">Test Function</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if ($ppas_form_id_column_exists): ?>
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
            <?php endif; ?>
        </div>
        
        <?php if ($test_results): ?>
        <div class="result-box">
            <h3>Test Results for PPAS Form ID: <?php echo $form_id; ?></h3>
            
            <div class="card-columns mt-4">
                <?php foreach ($test_results as $field => $content): ?>
                <div class="card">
                    <div class="card-header <?php echo empty($content) ? 'bg-warning' : 'bg-success text-white'; ?>">
                        <h5><?php echo ucfirst($field); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($content)): ?>
                            <p class="empty-field">No content found</p>
                        <?php else: ?>
                            <div class="field-content">
                                <?php echo nl2br(htmlspecialchars($content)); ?>
                            </div>
                            <p class="mt-2 has-content">Content length: <?php echo strlen($content); ?> characters</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <h3>Database Information</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>PPAS Forms Table</h5>
                        </div>
                        <div class="card-body">
                            <p>Total forms: <?php echo count($ppas_forms); ?></p>
                            <?php if (count($ppas_forms) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Activity</th>
                                            <th>Campus</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ppas_forms as $form): ?>
                                        <tr>
                                            <td><?php echo $form['id']; ?></td>
                                            <td><?php echo htmlspecialchars($form['activity']); ?></td>
                                            <td><?php echo htmlspecialchars($form['campus']); ?></td>
                                            <td><?php echo $form['year']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-danger">No PPAS forms found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Narrative Entries Table</h5>
                        </div>
                        <div class="card-body">
                            <p>Total entries: <?php echo count($narrative_entries); ?></p>
                            <?php if (count($narrative_entries) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Campus</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($narrative_entries as $entry): ?>
                                        <tr>
                                            <td><?php echo $entry['id']; ?></td>
                                            <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                            <td><?php echo htmlspecialchars($entry['campus']); ?></td>
                                            <td><?php echo $entry['year']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-danger">No narrative entries found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 