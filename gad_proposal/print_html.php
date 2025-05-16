<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('../includes/db_connection.php');

// Function to show error message
function showError($message) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Error - GAD Proposal Print</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>Error</h4>
                        </div>
                        <div class="card-body">
                            <p class="card-text">' . htmlspecialchars($message) . '</p>
                            <a href="gad_proposal.php" class="btn btn-primary">Return to GAD Proposal Form</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Check if proposal ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showError('Proposal ID is required to print the document.');
}

$proposalId = intval($_GET['id']);
$debug_file = __DIR__ . '/html_print_debug.log';

try {
    // Log debug info
    file_put_contents($debug_file, "HTML Print request started for proposal ID: $proposalId at " . date('Y-m-d H:i:s') . "\n");
    
    // Get proposal data
    $sql = "SELECT * FROM gad_proposals WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $proposalId]);
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proposal) {
        file_put_contents($debug_file, "Error: Proposal with ID $proposalId not found\n", FILE_APPEND);
        showError('Proposal not found. The requested proposal may have been deleted or does not exist.');
    }
    
    // Get activities data
    $activitySql = "SELECT * FROM gad_proposal_activities WHERE proposal_id = :id ORDER BY sequence ASC";
    $activityStmt = $conn->prepare($activitySql);
    $activityStmt->execute([':id' => $proposalId]);
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get personnel data
    $personnelSql = "SELECT gpp.id, gpp.personnel_id, gpp.role, 
                      COALESCE(pl.name, pp.personnel_name) as name, 
                      COALESCE(pl.gender, 'Unspecified') as gender,
                      ar.rank_name
                      FROM gad_proposal_personnel gpp
                      LEFT JOIN personnel_list pl ON gpp.personnel_id = pl.id
                      LEFT JOIN ppas_personnel pp ON pp.personnel_id = gpp.personnel_id AND pp.ppas_id = :ppas_id
                      LEFT JOIN academic_rank ar ON pl.academic_rank_id = ar.id
                      WHERE gpp.proposal_id = :id
                      ORDER BY gpp.role ASC, COALESCE(pl.name, pp.personnel_name) ASC";
    $personnelStmt = $conn->prepare($personnelSql);
    $personnelStmt->execute([
        ':id' => $proposalId,
        ':ppas_id' => $proposal['ppas_id'] ?? null
    ]);
    $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group personnel by role
    $groupedPersonnel = [
        'project_leader' => [],
        'assistant_project_leader' => [],
        'project_staff' => []
    ];

    // If we have no personnel data but have names stored directly in the proposal fields,
    // use those as a fallback
    if (empty($personnel)) {
        // Project leaders
        if (!empty($proposal['project_leaders'])) {
            $leaders = explode(',', $proposal['project_leaders']);
            foreach ($leaders as $leader) {
                $groupedPersonnel['project_leader'][] = [
                    'name' => trim($leader),
                    'gender' => 'Unspecified',
                    'role' => 'project_leader'
                ];
            }
        }
        
        // Assistant project leaders
        if (!empty($proposal['assistant_project_leaders'])) {
            $assistants = explode(',', $proposal['assistant_project_leaders']);
            foreach ($assistants as $assistant) {
                $groupedPersonnel['assistant_project_leader'][] = [
                    'name' => trim($assistant),
                    'gender' => 'Unspecified',
                    'role' => 'assistant_project_leader'
                ];
            }
        }
        
        // Project staff
        if (!empty($proposal['project_staff'])) {
            $staff = explode(',', $proposal['project_staff']);
            foreach ($staff as $member) {
                $groupedPersonnel['project_staff'][] = [
                    'name' => trim($member),
                    'gender' => 'Unspecified',
                    'role' => 'project_staff'
                ];
            }
        }
    } else {
        foreach ($personnel as $person) {
            // Handle role mapping for ppas_personnel
            $role = $person['role'];
            if ($role == 'project_leader' || $role == 'assistant_project_leader' || $role == 'project_staff') {
                $groupedPersonnel[$role][] = $person;
            } else if ($role == 'asst_project_leader') {
                $groupedPersonnel['assistant_project_leader'][] = $person;
            }
        }
    }

    // Get project leader name
    $project_leader = "";
    if (!empty($groupedPersonnel['project_leader'])) {
        $project_leader = $groupedPersonnel['project_leader'][0]['name'];
    } else {
        $project_leader = $proposal['project_leader'] ?? '';
    }

    // Get assistant project leader name
    $asst_project_leader = "";
    if (!empty($groupedPersonnel['assistant_project_leader'])) {
        $asst_project_leader = $groupedPersonnel['assistant_project_leader'][0]['name'];
    } else {
        $asst_project_leader = $proposal['assistant_project_leader'] ?? '';
    }

    // Get project staff
    $project_staff = [];
    if (!empty($groupedPersonnel['project_staff'])) {
        foreach ($groupedPersonnel['project_staff'] as $staff) {
            $project_staff[] = $staff['name'];
        }
    } else if (!empty($proposal['project_staff'])) {
        $project_staff = explode(',', $proposal['project_staff']);
    }

    // Extract activity data
    $activity_list = [];
    foreach ($activities as $index => $activity) {
        $activity_list[] = [
            "name" => $activity['title'],
            "details" => explode("\n", $activity['details'])
        ];
    }

    // Set default values
    $reference_no = "BatStateU-FO-ESO-09";
    $effectivity_date = "August 25, 2023";
    $revision_no = "00";
    $tracking_number = "___________________";
    
    // Determine proposal type
    $is_program = false;
    $is_project = false;
    $is_activity = true;

    // Extract other data from proposal
    $title = $proposal['title'] ?? '';
    $venue = $proposal['venue'] ?? '';
    $date = !empty($proposal['start_date']) ? date("F d, Y", strtotime($proposal['start_date'])) : '';
    $mode_of_delivery = $proposal['delivery_mode'] ?? 'Face-to-face';
    $partner_offices = $proposal['partner_offices'] ?? '';
    $participant_type = $proposal['participants'] ?? '';
    $male_participants = $proposal['male_beneficiaries'] ?? '0';
    $female_participants = $proposal['female_beneficiaries'] ?? '0';
    $total_participants = $proposal['total_beneficiaries'] ?? '0';
    
    // Prepare responsibilities arrays
    $project_leader_responsibilities = !empty($proposal['leader_responsibilities']) ? 
        explode("\n", $proposal['leader_responsibilities']) : 
        ["Spearhead the activity", "Monitor the flow of the activity"];
    
    $asst_project_leader_responsibilities = !empty($proposal['assistant_responsibilities']) ? 
        explode("\n", $proposal['assistant_responsibilities']) : 
        ["Assist the project leader", "Delegate work to the project staff"];
    
    $project_staff_responsibilities = !empty($proposal['staff_responsibilities']) ? 
        explode("\n", $proposal['staff_responsibilities']) : 
        ["Coordinate with the team", "Assist in implementation"];
    
    // Prepare rationale background
    $rationale_background = !empty($proposal['rationale']) ? 
        explode("\n\n", $proposal['rationale']) : 
        ["No rationale provided."];
    
    // Ensure we have at least 3 paragraphs
    while (count($rationale_background) < 3) {
        $rationale_background[] = "";
    }
    
    // Prepare objectives
    $main_objective = !empty($proposal['objectives']) ? 
        explode("\n", $proposal['objectives'])[0] : 
        "No main objective provided.";
    
    $specific_objectives = !empty($proposal['specific_objectives']) ? 
        explode("\n", $proposal['specific_objectives']) : 
        ["No specific objectives provided."];

    // Prepare strategies
    $description = !empty($proposal['strategies']) ? 
        explode("\n", $proposal['strategies'])[0] : 
        "No description provided.";
    
    $strategies = !empty($proposal['strategies']) ? 
        array_slice(explode("\n", $proposal['strategies']), 1) : 
        ["No strategies provided."];
    
    // Prepare work plan
    $work_plan = [
        ["Preparation of Proposal", "", "", "", ""],
        ["Preparation of Materials", "", "", "", ""],
        ["Implementation of Activity", "", "", "", ""],
        ["Submission of Activity Report", "", "", "", ""]
    ];
    
    $work_plan_dates = ["Week 1", "Week 2", "Week 3", "Week 4"];
    
    // Prepare financial requirements
    $financial_requirements = $proposal['budget_source'] ?? "No financial requirements provided.";
    
    // Prepare monitoring and evaluation
    $monitoring_evaluation = [
        [
            "objectives" => "Impact<br>Gender issues of stakeholders addressed",
            "indicators" => "Percentage of stakeholders satisfied with PPAs conducted",
            "baseline" => "99.96% (based on previous reports)",
            "target" => "At least 96% of the beneficiaries rated the PPAs satisfactory or higher",
            "source" => "Consolidated Annual GAD Accomplishment Report",
            "method" => "Submission and review of GAD AR",
            "frequency" => "Annual",
            "responsible" => "GAD Office"
        ],
        [
            "objectives" => "Outcome<br>Gender perspective mainstreamed in University PPAs",
            "indicators" => "Percentage of PPAs in GPB implemented",
            "baseline" => "TBD",
            "target" => "At least 90% of PPAs in GPB implemented",
            "source" => "GAD Quarterly Report",
            "method" => "Review of reports",
            "frequency" => "Quarterly",
            "responsible" => "GAD Office"
        ]
    ];
    
    // Prepare sustainability plan
    $sustainability_intro = "To ensure the long-term success and sustainability of the activity, the following strategies will be implemented:";
    
    $sustainability_strategies = !empty($proposal['sustainability_plan']) ? 
        explode("\n", $proposal['sustainability_plan']) : 
        ["No sustainability strategies provided."];
    
    $sustainability_conclusion = "By implementing these strategies, this program can build a solid foundation for sustainability, foster meaningful change, and continue to make a positive impact.";
    
    // Prepare signatures
    $prepared_by = [
        "name" => $project_leader,
        "title" => "Project Leader"
    ];
    
    $reviewed_by = [
        "name" => "HEAD, EXTENSION SERVICES",
        "title" => "Head, Extension Services"
    ];
    
    $recommending_approval = [
        "name" => "VICE CHANCELLOR FOR RDEXT",
        "title" => "Vice Chancellor for Research, Development and Extension Services"
    ];
    
    $na_approval = "N/A";
    
    $approved_by = [
        "name" => "CHANCELLOR",
        "title" => "Chancellor"
    ];
    
    $cc = "GAD Central";

    // Start output buffering
    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD PROPOSAL - <?php echo htmlspecialchars($title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.3;
            margin: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .page-number {
            text-align: right;
        }
        .reference {
            margin-bottom: 10px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }
        .checkbox {
            margin-right: 5px;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
        }
        .indent {
            margin-left: 20px;
        }
        .double-indent {
            margin-left: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        .signature-section {
            margin-top: 20px;
        }
        .signature-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .signature-name {
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
            width: 45%;
        }
        .signature-title {
            text-align: center;
            width: 45%;
        }
        .signature-date {
            width: 30%;
            text-align: center;
        }
        .footer {
            text-align: left;
            margin-top: 20px;
            font-size: 10pt;
        }
        @media print {
            @page {
                size: letter;
                margin: 0.5in;
            }
            body {
                margin: 0;
            }
            .page-break {
                page-break-before: always;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print Document</button>
    
    <div class="header">
        <div>Tracking Number: <?php echo $tracking_number; ?></div>
        <div class="page-number">Page 1 of 4</div>
    </div>
    
    <div class="reference">
        Reference No.: <?php echo $reference_no; ?> Effectivity Date: <?php echo $effectivity_date; ?> Revision No.: <?php echo $revision_no; ?>
    </div>
    
    <div class="title">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>
    
    <div>
        <span class="checkbox"><?php echo $is_program ? '☒' : '☐'; ?></span> Program
        <span class="checkbox"><?php echo $is_project ? '☒' : '☐'; ?></span> Project
        <span class="checkbox"><?php echo $is_activity ? '☒' : '☐'; ?></span> Activity
    </div>
    
    <div class="section">
        <div class="section-title">I. Title: "<?php echo htmlspecialchars($title); ?>"</div>
        <?php if (!empty($proposal['project']) || !empty($proposal['program'])): ?>
        <div class="indent">
            <?php if (!empty($proposal['project'])): ?>
            <div>Project: <?php echo htmlspecialchars($proposal['project']); ?></div>
            <?php endif; ?>
            <?php if (!empty($proposal['program'])): ?>
            <div>Program: <?php echo htmlspecialchars($proposal['program']); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <div class="section-title">II. Date and Venue: <?php echo htmlspecialchars($venue); ?></div>
        <div class="indent"><?php echo htmlspecialchars($date); ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">III. Mode of delivery (online/face-to-face): <?php echo htmlspecialchars($mode_of_delivery); ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">IV. Project Team:</div>
        
        <div class="indent">Project Leaders: <?php echo htmlspecialchars($project_leader); ?></div>
        <div class="indent">Responsibilities:</div>
        <?php foreach ($project_leader_responsibilities as $index => $responsibility): ?>
            <div class="double-indent"><?php echo ($index + 1) . '. ' . htmlspecialchars($responsibility); ?></div>
        <?php endforeach; ?>
        
        <div class="indent">Asst. Project Leaders: <?php echo htmlspecialchars($asst_project_leader); ?></div>
        <div class="indent">Responsibilities:</div>
        <?php foreach ($asst_project_leader_responsibilities as $index => $responsibility): ?>
            <div class="double-indent"><?php echo ($index + 1) . '. ' . htmlspecialchars($responsibility); ?></div>
        <?php endforeach; ?>
        
        <div class="indent">Project Staff: <?php echo (!empty($project_staff)) ? htmlspecialchars($project_staff[0]) : ''; ?></div>
        <?php for ($i = 1; $i < count($project_staff); $i++): ?>
            <div class="double-indent"><?php echo htmlspecialchars($project_staff[$i]); ?></div>
        <?php endfor; ?>
        
        <div class="indent">Responsibilities:</div>
        <?php foreach ($project_staff_responsibilities as $index => $responsibility): ?>
            <div class="double-indent"><?php echo ($index + 1) . '. ' . htmlspecialchars($responsibility); ?></div>
        <?php endforeach; ?>
    </div>
    
    <div class="section">
        <div class="section-title">V. Partner Office/College/Department: <?php echo htmlspecialchars($partner_offices); ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">VI. Type of Participants: <?php echo htmlspecialchars($participant_type); ?></div>
        <table>
            <tr>
                <td></td>
                <td>Total</td>
            </tr>
            <tr>
                <td>Male</td>
                <td><?php echo htmlspecialchars($male_participants); ?></td>
            </tr>
            <tr>
                <td>Female</td>
                <td><?php echo htmlspecialchars($female_participants); ?></td>
            </tr>
            <tr>
                <td>Total</td>
                <td><?php echo htmlspecialchars($total_participants); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">VII. Rationale/Background:</div>
        <div class="indent">
            <?php echo nl2br(htmlspecialchars($rationale_background[0])); ?>
        </div>
        <div class="indent">
            <?php echo nl2br(htmlspecialchars($rationale_background[1])); ?>
        </div>
    </div>
    
    <div class="page-break"></div>
    
    <div class="header">
        <div></div>
        <div class="page-number">Page 2 of 4</div>
    </div>
    
    <div class="indent">
        <?php echo nl2br(htmlspecialchars($rationale_background[2])); ?>
    </div>
    
    <div class="section">
        <div class="section-title">VIII. Objectives:</div>
        <div class="indent">
            <?php echo nl2br(htmlspecialchars($main_objective)); ?>
        </div>
        
        <div class="indent">Specific Objectives:</div>
        <div class="double-indent">The specific objectives of this project include:</div>
        <?php foreach ($specific_objectives as $objective): ?>
            <div class="double-indent">• <?php echo htmlspecialchars($objective); ?></div>
        <?php endforeach; ?>
    </div>
    
    <div class="section">
        <div class="section-title">IX. Description, Strategies, and Methods (Activities / Schedule):</div>
        <div class="indent">
            <?php echo nl2br(htmlspecialchars($description)); ?>
        </div>
        
        <div class="indent">Strategies:</div>
        <?php foreach ($strategies as $strategy): ?>
            <div class="double-indent">• <?php echo htmlspecialchars($strategy); ?></div>
        <?php endforeach; ?>
        
        <div class="indent">Methods (Activities / Schedule):</div>
        <?php foreach ($activity_list as $index => $activity): ?>
            <div class="double-indent">Activity <?php echo $index + 1; ?>: <?php echo htmlspecialchars($activity['name']); ?></div>
            <?php foreach ($activity['details'] as $detail): ?>
                <div class="double-indent">• <?php echo htmlspecialchars($detail); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    
    <div class="page-break"></div>
    
    <div class="header">
        <div></div>
        <div class="page-number">Page 3 of 4</div>
    </div>
    
    <div class="section">
        <div class="section-title">X. Work Plan (Timeline of Activities/Gantt Chart):</div>
        <table>
            <tr>
                <th>Activities</th>
                <?php foreach ($work_plan_dates as $date): ?>
                    <th><?php echo htmlspecialchars($date); ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($work_plan as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">XI. Financial Requirements and Source of Funds:</div>
        <div class="indent">
            <?php echo nl2br(htmlspecialchars($financial_requirements)); ?>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">XII. Monitoring and Evaluation Mechanics / Plan:</div>
        <table>
            <tr>
                <th>Objectives</th>
                <th>Performance Indicators</th>
                <th>Baseline Data</th>
                <th>Performance Target</th>
                <th>Data Source</th>
                <th>Collection Method</th>
                <th>Frequency of Data Collection</th>
                <th>Office/Persons Responsible</th>
            </tr>
            <?php foreach ($monitoring_evaluation as $row): ?>
                <tr>
                    <td><?php echo $row['objectives']; ?></td>
                    <td><?php echo $row['indicators']; ?></td>
                    <td><?php echo $row['baseline']; ?></td>
                    <td><?php echo $row['target']; ?></td>
                    <td><?php echo $row['source']; ?></td>
                    <td><?php echo $row['method']; ?></td>
                    <td><?php echo $row['frequency']; ?></td>
                    <td><?php echo $row['responsible']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">XIII. Sustainability Plan:</div>
        <div class="indent">
            <?php echo htmlspecialchars($sustainability_intro); ?>
        </div>
        <?php foreach ($sustainability_strategies as $strategy): ?>
            <div class="indent">• <?php echo htmlspecialchars($strategy); ?></div>
        <?php endforeach; ?>
    </div>
    
    <div class="page-break"></div>
    
    <div class="header">
        <div></div>
        <div class="page-number">Page 4 of 4</div>
    </div>
    
    <div class="indent">
        <?php echo htmlspecialchars($sustainability_conclusion); ?>
    </div>
    
    <div class="signature-section">
        <div class="signature-line">
            <div class="signature-name">
                Prepared by:<br><br>
                <?php echo htmlspecialchars($prepared_by['name']); ?><br>
                <?php echo htmlspecialchars($prepared_by['title']); ?>
            </div>
            <div class="signature-date">Date Signed:</div>
        </div>
        
        <div class="signature-line">
            <div class="signature-name">
                Reviewed by:<br><br>
                <?php echo htmlspecialchars($reviewed_by['name']); ?><br>
                <?php echo htmlspecialchars($reviewed_by['title']); ?>
            </div>
            <div class="signature-date">Date Signed:</div>
        </div>
        
        <div class="signature-line">
            <div class="signature-name">
                Recommending Approval:<br><br>
                <?php echo htmlspecialchars($recommending_approval['name']); ?><br>
                <?php echo $recommending_approval['title']; ?>
            </div>
            <div class="signature-date">Date Signed:</div>
        </div>
        
        <div class="signature-line">
            <div class="signature-name">
                <?php echo htmlspecialchars($na_approval); ?>
            </div>
            <div class="signature-date">Date Signed:</div>
        </div>
        
        <div class="signature-line">
            <div class="signature-name">
                Approved by:<br><br><br>
                <?php echo htmlspecialchars($approved_by['name']); ?><br>
                <?php echo htmlspecialchars($approved_by['title']); ?>
            </div>
            <div class="signature-date">Date Signed:</div>
        </div>
    </div>
    
    <div class="footer">
        Cc: <?php echo htmlspecialchars($cc); ?>
    </div>
    
    <script>
        // Auto-print after page loads (optional - uncomment to enable)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // };
    </script>
</body>
</html>
<?php
    $output = ob_get_clean();
    echo $output;
    
} catch (Exception $e) {
    error_log('HTML Generation Error: ' . $e->getMessage());
    file_put_contents($debug_file, "Error generating HTML: " . $e->getMessage() . "\n", FILE_APPEND);
    showError('An error occurred while generating the HTML: ' . $e->getMessage());
}
?> 