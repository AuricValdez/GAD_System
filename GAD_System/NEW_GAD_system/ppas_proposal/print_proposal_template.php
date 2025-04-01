<?php
session_start();

// Debug session information
error_log("Session data in print_proposal_template.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

// Check if user is Central or a specific campus user
$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// For non-Central users, their username is their campus
$userCampus = $isCentral ? '' : $_SESSION['username'];

// Store campus in session for consistency
$_SESSION['campus'] = $userCampus;

// Get proposal data
$proposal_id = $_GET['proposal_id'] ?? null;
$campus = $_GET['campus'] ?? $userCampus;
$year = $_GET['year'] ?? date('Y');

if (!$proposal_id) {
    echo json_encode(['error' => 'Missing required proposal ID']);
    exit;
}

// Fetch proposal data
$proposal = null;
try {
    // Database connection
    $db = new PDO(
        "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Get proposal details
    $stmt = $db->prepare("
        SELECT 
            gp.*,
            CONCAT(
                DATE_FORMAT(gp.start_date, '%M %d, %Y'),
                ' to ',
                DATE_FORMAT(gp.end_date, '%M %d, %Y')
            ) as duration,
            CONCAT(
                'Male: ', COALESCE(gp.male_beneficiaries, 0),
                ', Female: ', COALESCE(gp.female_beneficiaries, 0),
                ', Total: ', COALESCE(gp.total_beneficiaries, 0)
            ) as beneficiaries
        FROM gad_proposals gp
        WHERE gp.id = :proposal_id
        AND gp.created_by = :campus
        AND gp.year = :year
    ");
    
    $stmt->execute([
        'proposal_id' => $proposal_id,
        'campus' => $campus,
        'year' => $year
    ]);
    
    $proposal = $stmt->fetch();
    
    if (!$proposal) {
        throw new Exception("Proposal not found");
    }
    
    // Get activities
    $stmt = $db->prepare("
        SELECT 
            title,
            details,
            sequence,
            created_at
        FROM gad_proposal_activities 
        WHERE proposal_id = :proposal_id
        ORDER BY sequence ASC
    ");
    
    $stmt->execute(['proposal_id' => $proposal_id]);
    $activities = $stmt->fetchAll();
    
} catch (Exception $e) {
    header("Location: gad_proposal.php?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}

// Helper function to safely get array value
function safe_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Format data for display
$maleCount = intval(safe_get($proposal, 'male_beneficiaries', 0));
$femaleCount = intval(safe_get($proposal, 'female_beneficiaries', 0));
$totalCount = intval(safe_get($proposal, 'total_beneficiaries', 0));

// Get specific objectives as array
$specificObjectives = [];
if (!empty($proposal['specific_objectives'])) {
    $specificObjectives = explode("\n", $proposal['specific_objectives']);
}

// Get strategies as array
$strategies = [];
if (!empty($proposal['strategies'])) {
    $strategies = explode("\n", $proposal['strategies']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD Proposal - Print View</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Base styles for both screen and print */
        .proposal-container {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid black !important;
            border-collapse: collapse !important;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Table styles for both screen and print */
        .proposal-container table {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border-collapse: collapse !important;
            border: none !important;
        }

        .proposal-container td,
        .proposal-container th {
            border: 1px solid black !important;
            padding: 8px !important;
            margin: 0 !important;
        }

        /* Remove spacing between sections */
        .header-section,
        .main-section {
            margin: 0 !important;
            padding: 0 !important;
        }

        .header-section table,
        .main-section table {
            margin: 0 !important;
            border: none !important;
        }

        /* Remove any margins between rows */
        .proposal-container tr {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Print-specific styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .proposal-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 1px solid black !important;
                background: white !important;
            }

            .proposal-container table {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border-collapse: collapse !important;
                page-break-inside: avoid !important;
            }

            .proposal-container td,
            .proposal-container th {
                border: 1px solid black !important;
                padding: 8px !important;
                margin: 0 !important;
                font-size: 12pt !important;
                line-height: 1.3 !important;
            }

            /* Force background colors */
            .proposal-container th,
            .proposal-container td[style*="background-color: #f8f9fa"] {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide non-printable elements */
            .no-print {
                display: none !important;
            }

            /* Force colors and remove decorations */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
        }

        /* Control buttons */
        .control-buttons {
            margin: 20px 0;
            text-align: right;
        }

        .control-buttons button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-4">
        <div class="control-buttons no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-secondary" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <div class="proposal-container">
            <!-- Header Section -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                <tr>
                    <td style="width: 33.33%; border: 1px solid black; padding: 5px;">Reference No.: BatStateU-FO-ESO-09</td>
                    <td style="width: 33.33%; border: 1px solid black; padding: 5px;">Effectivity Date: August 25, 2023</td>
                    <td style="width: 33.33%; border: 1px solid black; padding: 5px;">Revision No.: 00</td>
                </tr>
            </table>

            <!-- Title and Checkbox Section -->
            <div style="text-align: center; margin: 15px 0; border-bottom: 1px solid black; padding-bottom: 10px;">
                <div style="font-weight: bold; margin-bottom: 10px;">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>
                <div style="margin-top: 10px;">
                    <?php $activityType = safe_get($proposal, 'activity_type', 'Activity'); ?>
                    <span style="margin: 0 10px;">☐ Program</span>
                    <span style="margin: 0 10px;">☐ Project</span>
                    <span style="margin: 0 10px;">☒ Activity</span>
                </div>
            </div>

            <!-- Main Content Table -->
            <table style="width: 100%; border-collapse: collapse;">
                <!-- Title Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; width: 25%;">I. Title:</td>
                    <td style="border: 1px solid black; padding: 5px;">"<?php echo htmlspecialchars(safe_get($proposal, 'activity_title', '')); ?>"</td>
                </tr>

                <!-- Date and Venue Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">II. Date and Venue:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <?php echo htmlspecialchars(safe_get($proposal, 'duration', '')); ?> at 
                        <?php echo htmlspecialchars(safe_get($proposal, 'venue', '')); ?>
                    </td>
                </tr>

                <!-- Mode of Delivery -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">III. Mode of delivery (online/face-to-face):</td>
                    <td style="border: 1px solid black; padding: 5px;"><?php echo htmlspecialchars(safe_get($proposal, 'delivery_mode', '')); ?></td>
                </tr>

                <!-- Project Team Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; vertical-align: top;">IV. Project Team:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <strong>Project Leaders:</strong> <?php echo htmlspecialchars(safe_get($proposal, 'project_leaders', '')); ?><br>
                        <strong>Responsibilities:</strong><br>
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'leader_responsibilities', ''))); ?>
                        
                        <br><br><strong>Asst. Project Leaders:</strong> <?php echo htmlspecialchars(safe_get($proposal, 'assistant_project_leaders', '')); ?><br>
                        <strong>Responsibilities:</strong><br>
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'assistant_responsibilities', ''))); ?>
                        
                        <br><br><strong>Project Staff:</strong><br>
                        <?php echo htmlspecialchars(safe_get($proposal, 'project_staff', '')); ?>
                        <br><br><strong>Responsibilities:</strong><br>
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'staff_responsibilities', ''))); ?>
                    </td>
                </tr>

                <!-- Partner Office Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">V. Partner Office/College/Department:</td>
                    <td style="border: 1px solid black; padding: 5px;"><?php echo htmlspecialchars(safe_get($proposal, 'partner_offices', '')); ?></td>
                </tr>

                <!-- Participants Section with Table -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; vertical-align: top;">VI. Type of Participants:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <?php echo htmlspecialchars(safe_get($proposal, 'beneficiaries_type', '')); ?>
                        <table style="width: 50%; border-collapse: collapse; margin-top: 10px;">
                            <tr>
                                <td style="border: 1px solid black; padding: 5px;">Male</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;"><?php echo $maleCount; ?></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black; padding: 5px;">Female</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;"><?php echo $femaleCount; ?></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black; padding: 5px;">Total</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;"><?php echo $totalCount; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Rationale Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">VII. Rationale/Background:</td>
                    <td style="border: 1px solid black; padding: 5px;"><?php echo nl2br(htmlspecialchars(safe_get($proposal, 'rationale', ''))); ?></td>
                </tr>

                <!-- Objectives Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; vertical-align: top;">VIII. Objectives:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <strong>General Objective:</strong><br>
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'general_objective', ''))); ?><br><br>
                        
                        <strong>Specific Objectives:</strong><br>
                        The specific objectives of this project include:<br>
                        <ul style="margin: 5px 0 5px 20px; padding: 0;">
                            <?php foreach ($specificObjectives as $objective): ?>
                                <li><?php echo htmlspecialchars($objective); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>

                <!-- Description and Strategies Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; vertical-align: top;">IX. Description, Strategies, and Methods (Activities / Schedule):</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'description', ''))); ?><br><br>
                        
                        <strong>Strategies:</strong><br>
                        <ul style="margin: 5px 0 5px 20px; padding: 0;">
                            <?php foreach ($strategies as $strategy): ?>
                                <li><?php echo htmlspecialchars($strategy); ?></li>
                            <?php endforeach; ?>
                        </ul><br>
                        
                        <strong>Methods (Activities / Schedule):</strong><br>
                        <?php foreach ($activities as $activity): ?>
                            <strong><?php echo htmlspecialchars(safe_get($activity, 'title', '')); ?></strong><br>
                            <?php echo nl2br(htmlspecialchars(safe_get($activity, 'details', ''))); ?><br><br>
                        <?php endforeach; ?>
                    </td>
                </tr>

                <!-- Work Plan Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px; vertical-align: top;">X. Work Plan (Timeline of Activities/Gantt Chart):</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border: 1px solid black; padding: 5px; font-weight: bold;">Activities</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;">Week 1</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;">Week 2</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;">Week 3</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;">Week 4</td>
                            </tr>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td style="border: 1px solid black; padding: 5px;"><?php echo htmlspecialchars(safe_get($activity, 'title', '')); ?></td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    <?php echo strpos($activity['details'] ?? '', 'Week 1') !== false ? '✓' : ''; ?>
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    <?php echo strpos($activity['details'] ?? '', 'Week 2') !== false ? '✓' : ''; ?>
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    <?php echo strpos($activity['details'] ?? '', 'Week 3') !== false ? '✓' : ''; ?>
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    <?php echo strpos($activity['details'] ?? '', 'Week 4') !== false ? '✓' : ''; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>

                <!-- Financial Requirements Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">XI. Financial Requirements and Source of Funds:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <strong>Source:</strong> <?php echo htmlspecialchars(safe_get($proposal, 'budget_source', '')); ?><br>
                        <strong>Total Budget:</strong> ₱<?php echo number_format(floatval(safe_get($proposal, 'total_budget', 0)), 2); ?>
                    </td>
                </tr>

                <!-- Monitoring and Evaluation Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">XII. Monitoring and Evaluation Mechanics / Plan:</td>
                    <td style="border: 1px solid black; padding: 5px;">
                        <?php echo nl2br(htmlspecialchars(safe_get($proposal, 'monitoring_mechanics', ''))); ?>
                    </td>
                </tr>

                <!-- Sustainability Plan Section -->
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">XIII. Sustainability Plan:</td>
                    <td style="border: 1px solid black; padding: 5px;"><?php echo nl2br(htmlspecialchars(safe_get($proposal, 'sustainability_plan', ''))); ?></td>
                </tr>
            </table>

            <!-- Signature Section -->
            <br>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom; border: 1px solid black;">
                        <p>Prepared by:</p><br><br>
                        <div style="border-bottom: 1px solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars(safe_get($proposal, 'prepared_by', '')); ?></strong></p>
                        <p style="margin: 0;">Project Leader</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom; border: 1px solid black;">
                        <p>Reviewed by:</p><br><br>
                        <div style="border-bottom: 1px solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars(safe_get($proposal, 'reviewed_by', '')); ?></strong></p>
                        <p style="margin: 0;">GAD Coordinator</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom; border: 1px solid black;">
                        <p>Recommending Approval:</p><br><br>
                        <div style="border-bottom: 1px solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars(safe_get($proposal, 'recommending_approval', '')); ?></strong></p>
                        <p style="margin: 0;">Vice Chancellor for Academic Affairs</p>
                    </td>
                    <td style="width: 25%; padding: 15px; text-align: center; vertical-align: bottom; border: 1px solid black;">
                        <p>Approved by:</p><br><br>
                        <div style="border-bottom: 1px solid black; margin: 0 auto; width: 80%;"></div>
                        <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars(safe_get($proposal, 'approved_by', '')); ?></strong></p>
                        <p style="margin: 0;">Campus Director</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding: 15px 0 0 0; border: 1px solid black;">
                        <p>Date Signed: _________________</p>
                        <p style="margin-top: 15px;">Cc: GAD Central</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Font Awesome for icons in buttons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</body>
</html> 