<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Process form submission
$message = '';
$success = false;
$ppas_form_id = isset($_GET['ppas_form_id']) ? (int)$_GET['ppas_form_id'] : '';

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        // Check if narrative_entries table exists
        $table_check = $pdo->query("SHOW TABLES LIKE 'narrative_entries'");
        if ($table_check->rowCount() === 0) {
            // Create the table if it doesn't exist
            $pdo->exec("CREATE TABLE `narrative_entries` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ppas_form_id` int(11) DEFAULT NULL,
                `campus` varchar(255) DEFAULT NULL,
                `year` varchar(255) DEFAULT NULL,
                `title` varchar(255) DEFAULT NULL,
                `results` text DEFAULT NULL,
                `lessons` text DEFAULT NULL,
                `what_worked` text DEFAULT NULL,
                `issues` text DEFAULT NULL,
                `recommendations` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_ppas_form_id` (`ppas_form_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $message .= "Created narrative_entries table. ";
        }
        
        // Get form data
        $ppas_form_id = $_POST['ppas_form_id'];
        $campus = $_POST['campus'];
        $year = $_POST['year'];
        $title = $_POST['title'];
        $results = $_POST['results'];
        $lessons = $_POST['lessons'];
        $what_worked = $_POST['what_worked'];
        $issues = $_POST['issues'];
        $recommendations = $_POST['recommendations'];
        
        // Check if entry already exists for this PPAS form ID
        $check_stmt = $pdo->prepare("SELECT id FROM narrative_entries WHERE ppas_form_id = :ppas_form_id");
        $check_stmt->execute([':ppas_form_id' => $ppas_form_id]);
        $existing_entry = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_entry) {
            // Update existing entry
            $stmt = $pdo->prepare("UPDATE narrative_entries SET 
                campus = :campus,
                year = :year,
                title = :title,
                results = :results,
                lessons = :lessons,
                what_worked = :what_worked,
                issues = :issues,
                recommendations = :recommendations
                WHERE ppas_form_id = :ppas_form_id");
                
            $stmt->execute([
                ':campus' => $campus,
                ':year' => $year,
                ':title' => $title,
                ':results' => $results,
                ':lessons' => $lessons,
                ':what_worked' => $what_worked,
                ':issues' => $issues,
                ':recommendations' => $recommendations,
                ':ppas_form_id' => $ppas_form_id
            ]);
            
            $message = "Successfully updated narrative entry for PPAS Form #$ppas_form_id";
        } else {
            // Insert new entry
            $stmt = $pdo->prepare("INSERT INTO narrative_entries 
                (ppas_form_id, campus, year, title, results, lessons, what_worked, issues, recommendations)
                VALUES 
                (:ppas_form_id, :campus, :year, :title, :results, :lessons, :what_worked, :issues, :recommendations)");
                
            $stmt->execute([
                ':ppas_form_id' => $ppas_form_id,
                ':campus' => $campus,
                ':year' => $year,
                ':title' => $title,
                ':results' => $results,
                ':lessons' => $lessons,
                ':what_worked' => $what_worked,
                ':issues' => $issues,
                ':recommendations' => $recommendations
            ]);
            
            $message = "Successfully added new narrative entry for PPAS Form #$ppas_form_id";
        }
        
        $success = true;
        
        // Also check if any columns in the alternative format exist and need to be updated
        $columns_query = $pdo->query("DESCRIBE narrative_entries");
        $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
        $field_names = array_column($columns, 'Field');
        
        $alternatives = [
            'expected_results' => 'results',
            'lessons_learned' => 'lessons',
            'issues_concerns' => 'issues'
        ];
        
        foreach ($alternatives as $alt_field => $standard_field) {
            if (in_array($alt_field, $field_names)) {
                // Copy data to alternative field format too
                $update_stmt = $pdo->prepare("UPDATE narrative_entries SET 
                    $alt_field = :value
                    WHERE ppas_form_id = :ppas_form_id");
                    
                $update_stmt->execute([
                    ':value' => $_POST[$standard_field],
                    ':ppas_form_id' => $ppas_form_id
                ]);
                
                $message .= " Updated alternative field $alt_field.";
            }
        }
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Pre-populate form if ppas_form_id is provided
$form_data = [
    'ppas_form_id' => $ppas_form_id,
    'campus' => '',
    'year' => date('Y'),
    'title' => '',
    'results' => '',
    'lessons' => '',
    'what_worked' => '',
    'issues' => '',
    'recommendations' => ''
];

// Try to get PPAS form data if ppas_form_id is provided
if ($ppas_form_id) {
    try {
        $pdo = getConnection();
        
        // First try to get existing narrative entry
        $stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id");
        $stmt->execute([':ppas_form_id' => $ppas_form_id]);
        $narrative_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($narrative_entry) {
            // Use existing entry data
            $form_data = [
                'ppas_form_id' => $ppas_form_id,
                'campus' => $narrative_entry['campus'] ?? '',
                'year' => $narrative_entry['year'] ?? date('Y'),
                'title' => $narrative_entry['title'] ?? '',
                'results' => $narrative_entry['results'] ?? '',
                'lessons' => $narrative_entry['lessons'] ?? '',
                'what_worked' => $narrative_entry['what_worked'] ?? '',
                'issues' => $narrative_entry['issues'] ?? '',
                'recommendations' => $narrative_entry['recommendations'] ?? ''
            ];
        } else {
            // Try to get PPAS form data to pre-populate
            $stmt = $pdo->prepare("SELECT * FROM ppas_forms WHERE id = :id");
            $stmt->execute([':id' => $ppas_form_id]);
            $ppas_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ppas_data) {
                $form_data = [
                    'ppas_form_id' => $ppas_form_id,
                    'campus' => $ppas_data['campus'] ?? '',
                    'year' => $ppas_data['year'] ?? date('Y'),
                    'title' => $ppas_data['activity'] ?? '',
                    'results' => '',
                    'lessons' => '',
                    'what_worked' => '',
                    'issues' => '',
                    'recommendations' => ''
                ];
            }
        }
    } catch (Exception $e) {
        $message = "Error loading form data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit Narrative Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add/Edit Narrative Entry</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="ppas_form_id">PPAS Form ID:</label>
                <input type="number" id="ppas_form_id" name="ppas_form_id" value="<?php echo htmlspecialchars($form_data['ppas_form_id']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="campus">Campus:</label>
                <input type="text" id="campus" name="campus" value="<?php echo htmlspecialchars($form_data['campus']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="year">Year:</label>
                <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($form_data['year']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="title">Activity Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($form_data['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="results">Results:</label>
                <textarea id="results" name="results"><?php echo htmlspecialchars($form_data['results']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="lessons">Lessons Learned:</label>
                <textarea id="lessons" name="lessons"><?php echo htmlspecialchars($form_data['lessons']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="what_worked">What Worked:</label>
                <textarea id="what_worked" name="what_worked"><?php echo htmlspecialchars($form_data['what_worked']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="issues">Issues and Concerns:</label>
                <textarea id="issues" name="issues"><?php echo htmlspecialchars($form_data['issues']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="recommendations">Recommendations:</label>
                <textarea id="recommendations" name="recommendations"><?php echo htmlspecialchars($form_data['recommendations']); ?></textarea>
            </div>
            
            <button type="submit">Save Narrative Entry</button>
        </form>
        
        <div class="links">
            <a href="get_narrative_example.php?ppas_form_id=<?php echo urlencode($form_data['ppas_form_id']); ?>">View Narrative Entry</a>
            <a href="check_narrative_entries.php">Check Database</a>
            <a href="javascript:history.back()">« Back</a>
        </div>
    </div>
</body>
</html> 