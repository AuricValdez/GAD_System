<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include_once '../includes/db_connection.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: Narrative ID is required.";
    exit();
}

$narrativeId = $_GET['id'];
$username = $_SESSION['username'];

try {
    // Get narrative details
    $query = "SELECT n.*, p.title as ppas_title, p.location as ppas_location, p.duration as ppas_duration 
              FROM narrative_forms n 
              JOIN ppas_forms p ON n.ppas_id = p.id 
              WHERE n.id = :id AND (n.username = :username OR :username = 'Central')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $narrativeId);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo "Error: Narrative not found or you do not have permission to view it.";
        exit();
    }
    
    $narrative = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Parse JSON fields
    $serviceAgenda = json_decode($narrative['service_agenda'], true) ?? [];
    $sdg = json_decode($narrative['sdg'], true) ?? [];
    $beneficiaries = json_decode($narrative['beneficiaries'], true) ?? [];
    $tasks = json_decode($narrative['tasks'], true) ?? [];
    $photos = json_decode($narrative['photos'], true) ?? [];
    
    // Get project team from PPAS form
    $teamQuery = "SELECT project_team FROM ppas_forms WHERE id = :id";
    $teamStmt = $conn->prepare($teamQuery);
    $teamStmt->bindParam(':id', $narrative['ppas_id']);
    $teamStmt->execute();
    $teamData = $teamStmt->fetch(PDO::FETCH_ASSOC);
    
    $projectTeam = [];
    if ($teamData && !empty($teamData['project_team'])) {
        $projectTeam = json_decode($teamData['project_team'], true) ?? [];
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Report - <?php echo htmlspecialchars($narrative['ppas_title']); ?></title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
            height: auto;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .photo-container {
            width: 100%;
        }
        .photo {
            width: 100%;
            height: auto;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .btn-print {
            margin-bottom: 20px;
        }
        @media print {
            .btn-print {
                display: none;
            }
            body {
                padding: 0;
                font-size: 12px;
            }
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-primary btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
        
        <div class="header">
            <div class="logo-container">
                <img src="../images/Batangas_State_Logo.png" alt="BatState Logo" class="logo">
                <div>
                    <div class="title">Batangas State University</div>
                    <div class="subtitle">Gender and Development Office</div>
                </div>
                <div style="width:100px;"></div> <!-- Empty div for alignment -->
            </div>
            <h1>Narrative Report</h1>
        </div>
        
        <div class="section">
            <div class="section-title">Activity Details</div>
            <div class="info-row">
                <div class="info-label">Title:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['ppas_title']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Location:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['ppas_location']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Duration:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['ppas_duration']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Implementing Office:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['implementing_office']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Partner Agency:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['partner_agency'] ?: 'N/A'); ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Type of Extension Service Agenda</div>
            <ul>
                <?php if (empty($serviceAgenda)): ?>
                    <li>None selected</li>
                <?php else: ?>
                    <?php foreach ($serviceAgenda as $service): ?>
                        <li><?php echo htmlspecialchars($service); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="section">
            <div class="section-title">Sustainable Development Goals</div>
            <ul>
                <?php if (empty($sdg)): ?>
                    <li>None selected</li>
                <?php else: ?>
                    <?php foreach ($sdg as $goal): ?>
                        <li><?php echo htmlspecialchars($goal); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="section">
            <div class="section-title">Number of Beneficiaries</div>
            <?php if (empty($beneficiaries)): ?>
                <p>No beneficiary data recorded</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">Type of Participants</th>
                            <th colspan="2">Internal</th>
                            <th colspan="2">External</th>
                            <th colspan="2">Total</th>
                        </tr>
                        <tr>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beneficiaries as $beneficiary): ?>
                            <?php 
                                $totalMale = intval($beneficiary['internal_male']) + intval($beneficiary['external_male']);
                                $totalFemale = intval($beneficiary['internal_female']) + intval($beneficiary['external_female']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($beneficiary['type']); ?></td>
                                <td><?php echo intval($beneficiary['internal_male']); ?></td>
                                <td><?php echo intval($beneficiary['internal_female']); ?></td>
                                <td><?php echo intval($beneficiary['external_male']); ?></td>
                                <td><?php echo intval($beneficiary['external_female']); ?></td>
                                <td><?php echo $totalMale; ?></td>
                                <td><?php echo $totalFemale; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-title">Project Team</div>
            <?php if (empty($projectTeam)): ?>
                <p>No project team data recorded</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectTeam as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['role']); ?></td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-title">Task Assignment</div>
            <?php if (empty($tasks)): ?>
                <p>No task assignment data recorded</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Task</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['name']); ?></td>
                                <td><?php echo htmlspecialchars($task['task']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-title">Objectives</div>
            <div class="info-row">
                <div class="info-label">General Objective:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($narrative['general_objective'])); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Specific Objectives:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($narrative['specific_objective'])); ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Activity Narrative</div>
            <div class="info-row">
                <div class="info-label">Activity Title:</div>
                <div class="info-value"><?php echo htmlspecialchars($narrative['activity_title']); ?></div>
            </div>
            <div class="mt-3">
                <h5>Narrative of the Activity:</h5>
                <div class="card">
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($narrative['activity_narrative'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Evaluation</div>
            <div class="card mb-3">
                <div class="card-header">Evaluation Results</div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($narrative['evaluation_result'])); ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Survey Results</div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($narrative['survey_result'])); ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($photos)): ?>
        <div class="section">
            <div class="section-title">Photos</div>
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-container">
                        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Activity Photo" class="photo">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-title">Prepared by:</div>
            <div class="mt-5">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                        <p class="mt-2">Name and Signature</p>
                    </div>
                    <div class="col-md-6 text-center">
                        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                        <p class="mt-2">Date</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 