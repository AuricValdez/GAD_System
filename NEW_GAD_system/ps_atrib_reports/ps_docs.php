<?php
require_once 'db_connection.php';
$pageTitle = "PS Attribution Documentation";
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Global Styles -->
    <link href="../js/global-styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            padding: 20px;
            line-height: 1.6;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        h1 {
            color: #6a1b9a;
            margin-bottom: 30px;
        }
        h2 {
            color: #7b1fa2;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .formula-box {
            background-color: #f8f9fa;
            border-left: 4px solid #6a1b9a;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .important-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .example-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        th {
            background-color: #f0f0f0;
        }
        .btn-back {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="ps.php" class="btn btn-primary btn-back"><i class="fas fa-arrow-left"></i> Back to PS Attribution</a>
        
        <h1 class="text-center"><i class="fas fa-book"></i> <?php echo $pageTitle; ?></h1>
        
        <div class="card">
            <div class="card-header">
                PS Attribution Overview
            </div>
            <div class="card-body">
                <p>
                    The PS Attribution system calculates the Personal Services (PS) cost attributable to a GAD activity. 
                    This documentation explains the standard calculation method used in the system.
                </p>
                
                <h2>Standard 8-Hour Workday</h2>
                <div class="important-note">
                    <strong>Important:</strong> The PS Attribution calculation uses a standard 8-hour workday regardless 
                    of the actual duration of the event stored in the database. This ensures consistency across all PS 
                    calculations and aligns with standard government accounting practices.
                </div>
                
                <p>
                    While the PPAS Forms may record the actual duration of an event (which could be multiple days 
                    and varying hours), the PS Attribution calculation standardizes this to an 8-hour workday for 
                    salary computation purposes.
                </p>
                
                <h2>Calculation Formula</h2>
                <div class="formula-box">
                    <p><strong>PS = Rate per Hour × 8 hours × Number of Personnel</strong></p>
                    <p>Where:</p>
                    <ul>
                        <li><strong>Rate per Hour</strong> = Monthly Salary ÷ 176 (standard working hours per month)</li>
                        <li><strong>8 hours</strong> = Standard workday duration</li>
                        <li><strong>Number of Personnel</strong> = Count of personnel with that academic rank</li>
                    </ul>
                </div>
                
                <h2>Example Calculation</h2>
                <div class="example-box">
                    <p>For an Associate Professor with:</p>
                    <ul>
                        <li>Monthly Salary: ₱56,000</li>
                        <li>Rate per Hour: ₱56,000 ÷ 176 = ₱318.18</li>
                        <li>Number of Personnel: 2</li>
                    </ul>
                    <p><strong>PS = ₱318.18 × 8 × 2 = ₱5,090.88</strong></p>
                </div>
                
                <h2>Why 8 Hours?</h2>
                <p>
                    The standard 8-hour workday is used because:
                </p>
                <ul>
                    <li>It represents the standard government workday</li>
                    <li>It ensures consistency across all PS Attribution calculations</li>
                    <li>It aligns with standard labor practices and regulations</li>
                    <li>It provides a fair representation of work contribution regardless of the actual event duration</li>
                </ul>
                
                <h2>Actual Event Duration vs. PS Attribution</h2>
                <p>
                    The system records the actual duration of events in the database (which may be more or less than 8 hours) 
                    for accurate documentation purposes. However, for PS Attribution calculations, the standard 8-hour workday 
                    is applied to maintain consistency and fairness.
                </p>
                
                <p>
                    When viewing the PS Attribution form, you will see a note indicating that the standard 8-hour workday 
                    is being used for calculations, along with the actual recorded duration of the event for reference.
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Academic Ranks and Monthly Salaries
            </div>
            <div class="card-body">
                <p>
                    The system retrieves academic ranks and their corresponding monthly salaries from the <code>academic_ranks</code> 
                    table in the database. Below is a sample of the academic ranks used in PS Attribution calculations:
                </p>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Academic Rank</th>
                            <th>Salary Grade</th>
                            <th>Monthly Salary</th>
                            <th>Rate per Hour</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT academic_rank, salary_grade, monthly_salary, hourly_rate FROM academic_ranks ORDER BY salary_grade DESC LIMIT 6");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['academic_rank']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['salary_grade']) . "</td>";
                            echo "<td>₱" . number_format($row['monthly_salary'], 2) . "</td>";
                            echo "<td>₱" . number_format($row['hourly_rate'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='4' class='text-center text-danger'>Error loading academic ranks: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                
                <p class="text-muted">
                    Note: The hourly rate is calculated as Monthly Salary ÷ 176 (standard working hours per month).
                </p>
            </div>
        </div>
        
        <p class="text-center text-muted mt-5">
            <a href="ps.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to PS Attribution</a>
            <br><br>
            GAD System Documentation | Last Updated: <?php echo date("F Y"); ?>
        </p>
    </div>
</body>
</html> 