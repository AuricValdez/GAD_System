<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get database connection
function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gad_db";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize result
$result = [
    'status' => 'waiting',
    'message' => 'Form ready for submission'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();
        
        // Get form data
        $ppas_form_id = $_POST['ppas_form_id'] ?? null;
        $narrative_id = $_POST['narrative_id'] ?? null;
        
        // Prepare rating data
        $activity_ratings = [
            'Excellent' => [
                'BatStateU' => $_POST['excellent_batstateu'] ?? 1,
                'Others' => $_POST['excellent_others'] ?? 2
            ],
            'Very Satisfactory' => [
                'BatStateU' => $_POST['very_satisfactory_batstateu'] ?? 1,
                'Others' => $_POST['very_satisfactory_others'] ?? 2
            ],
            'Satisfactory' => [
                'BatStateU' => $_POST['satisfactory_batstateu'] ?? 1,
                'Others' => $_POST['satisfactory_others'] ?? 2
            ],
            'Fair' => [
                'BatStateU' => $_POST['fair_batstateu'] ?? 1,
                'Others' => $_POST['fair_others'] ?? 2
            ],
            'Poor' => [
                'BatStateU' => $_POST['poor_batstateu'] ?? 1,
                'Others' => $_POST['poor_others'] ?? 2
            ]
        ];
        
        // Same structure for timeliness ratings
        $timeliness_ratings = $activity_ratings;
        
        // Convert to JSON
        $activity_ratings_json = json_encode($activity_ratings);
        $timeliness_ratings_json = json_encode($timeliness_ratings);
        
        // Check if narrative_id is provided (update existing record)
        if (!empty($narrative_id)) {
            // Update narrative entry
            $stmt = $conn->prepare("UPDATE narrative_entries SET 
                                       activity_ratings = :activity_ratings,
                                       timeliness_ratings = :timeliness_ratings
                                     WHERE id = :id");
            $stmt->bindParam(':activity_ratings', $activity_ratings_json);
            $stmt->bindParam(':timeliness_ratings', $timeliness_ratings_json);
            $stmt->bindParam(':id', $narrative_id);
            $stmt->execute();
            
            $result['status'] = 'success';
            $result['message'] = 'Successfully updated ratings data for narrative ID ' . $narrative_id;
        } 
        // Check if we should create an entry from scratch
        else if (!empty($ppas_form_id) && isset($_POST['create_new']) && $_POST['create_new'] == 1) {
            // Get PPAS form data to help create the narrative
            $stmt = $conn->prepare("SELECT * FROM ppas_forms WHERE id = :id");
            $stmt->bindParam(':id', $ppas_form_id);
            $stmt->execute();
            $ppas_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ppas_data) {
                $activity_title = $ppas_data['activity'] ?? ($ppas_data['activity_title'] ?? 'Untitled Activity');
                $campus = $ppas_data['campus'] ?? '';
                
                // Insert new narrative entry with test data
                $stmt = $conn->prepare("INSERT INTO narrative_entries 
                                        (ppas_form_id, title, campus, background, participants, topics, results, lessons, 
                                         what_worked, issues, recommendations, activity_ratings, timeliness_ratings) 
                                        VALUES 
                                        (:ppas_form_id, :title, :campus, :background, :participants, :topics, :results, :lessons,
                                         :what_worked, :issues, :recommendations, :activity_ratings, :timeliness_ratings)");
                
                $background = "Test background for " . $activity_title;
                $participants = "Test participants for " . $activity_title;
                $topics = "Test topics for " . $activity_title;
                $results = "Test results for " . $activity_title;
                $lessons = "Test lessons for " . $activity_title;
                $what_worked = "Test what worked for " . $activity_title;
                $issues = "Test issues for " . $activity_title;
                $recommendations = "Test recommendations for " . $activity_title;
                
                $stmt->bindParam(':ppas_form_id', $ppas_form_id);
                $stmt->bindParam(':title', $activity_title);
                $stmt->bindParam(':campus', $campus);
                $stmt->bindParam(':background', $background);
                $stmt->bindParam(':participants', $participants);
                $stmt->bindParam(':topics', $topics);
                $stmt->bindParam(':results', $results);
                $stmt->bindParam(':lessons', $lessons);
                $stmt->bindParam(':what_worked', $what_worked);
                $stmt->bindParam(':issues', $issues);
                $stmt->bindParam(':recommendations', $recommendations);
                $stmt->bindParam(':activity_ratings', $activity_ratings_json);
                $stmt->bindParam(':timeliness_ratings', $timeliness_ratings_json);
                
                $stmt->execute();
                
                $new_id = $conn->lastInsertId();
                
                $result['status'] = 'success';
                $result['message'] = 'Successfully created new narrative entry with ID ' . $new_id;
                $result['new_id'] = $new_id;
            } else {
                $result['status'] = 'error';
                $result['message'] = 'PPAS form ID ' . $ppas_form_id . ' not found';
            }
        } else {
            $result['status'] = 'error';
            $result['message'] = 'Either narrative ID or PPAS form ID with create_new flag must be provided';
        }
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Check for narrative entries with the provided PPAS form ID
$existing_narrative = null;
$ppas_form_id = $_GET['ppas_form_id'] ?? ($_POST['ppas_form_id'] ?? null);

if ($ppas_form_id) {
    try {
        $conn = getConnection();
        
        // Check if narrative entry already exists
        $stmt = $conn->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_id LIMIT 1");
        $stmt->bindParam(':ppas_id', $ppas_form_id);
        $stmt->execute();
        $existing_narrative = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $result['check_error'] = 'Error checking for existing narrative: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Ratings Data Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .status {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <h1>Test Ratings Data Tool</h1>
    
    <?php if ($result['status'] === 'success'): ?>
        <div class="status success">
            <p><?php echo $result['message']; ?></p>
            <p><a href="debug_narrative.php?ppas_form_id=<?php echo $ppas_form_id; ?>" target="_blank">View Debug Output</a></p>
            <p><a href="db_diagnostic.php?ppas_form_id=<?php echo $ppas_form_id; ?>" target="_blank">Run Database Diagnostic</a></p>
        </div>
    <?php elseif ($result['status'] === 'error'): ?>
        <div class="status error">
            <p><?php echo $result['message']; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <?php if ($existing_narrative): ?>
            <div class="status info">
                <p>Found existing narrative entry for PPAS form ID <?php echo $ppas_form_id; ?> with ID <?php echo $existing_narrative['id']; ?></p>
                <p>Title: <?php echo $existing_narrative['title']; ?></p>
                <p>Campus: <?php echo $existing_narrative['campus']; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="ppas_form_id">PPAS Form ID:</label>
                <input type="text" id="ppas_form_id" name="ppas_form_id" value="<?php echo $ppas_form_id; ?>" required>
            </div>
            
            <?php if ($existing_narrative): ?>
                <input type="hidden" name="narrative_id" value="<?php echo $existing_narrative['id']; ?>">
            <?php else: ?>
                <div class="form-group">
                    <label for="create_new">
                        <input type="checkbox" id="create_new" name="create_new" value="1" checked>
                        Create new narrative entry if none exists
                    </label>
                </div>
            <?php endif; ?>
            
            <h2>Activity Ratings</h2>
            <table>
                <tr>
                    <th>Rating</th>
                    <th>BatStateU</th>
                    <th>Others</th>
                </tr>
                <tr>
                    <td>Excellent</td>
                    <td><input type="number" name="excellent_batstateu" value="1" min="0"></td>
                    <td><input type="number" name="excellent_others" value="2" min="0"></td>
                </tr>
                <tr>
                    <td>Very Satisfactory</td>
                    <td><input type="number" name="very_satisfactory_batstateu" value="1" min="0"></td>
                    <td><input type="number" name="very_satisfactory_others" value="2" min="0"></td>
                </tr>
                <tr>
                    <td>Satisfactory</td>
                    <td><input type="number" name="satisfactory_batstateu" value="1" min="0"></td>
                    <td><input type="number" name="satisfactory_others" value="2" min="0"></td>
                </tr>
                <tr>
                    <td>Fair</td>
                    <td><input type="number" name="fair_batstateu" value="1" min="0"></td>
                    <td><input type="number" name="fair_others" value="2" min="0"></td>
                </tr>
                <tr>
                    <td>Poor</td>
                    <td><input type="number" name="poor_batstateu" value="1" min="0"></td>
                    <td><input type="number" name="poor_others" value="2" min="0"></td>
                </tr>
            </table>
            
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    
    <div class="container">
        <h2>Ratings Sample Format</h2>
        <pre>
{
    "Excellent": {
        "BatStateU": 1,
        "Others": 2
    },
    "Very Satisfactory": {
        "BatStateU": 1,
        "Others": 2
    },
    "Satisfactory": {
        "BatStateU": 1,
        "Others": 2
    },
    "Fair": {
        "BatStateU": 1,
        "Others": 2
    },
    "Poor": {
        "BatStateU": 1,
        "Others": 2
    }
}
        </pre>
    </div>
</body>
</html> 