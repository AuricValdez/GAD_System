<?php
session_start();
require_once '../../vendor/autoload.php'; // Assuming Composer is used for dependency management

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Check if the required parameters are provided
if (!isset($_POST['campus']) || !isset($_POST['year']) || !isset($_POST['narrative_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

// Get parameters
$campus = $_POST['campus'];
$year = $_POST['year'];
$narrative_id = $_POST['narrative_id'];

// Database connection
try {
    require_once '../../config/db_connect.php';
    
    // Get narrative data
    $query = "SELECT * FROM gad_narrative_reports WHERE id = ? AND campus = ? AND YEAR(created_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isi", $narrative_id, $campus, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Narrative report not found']);
        exit();
    }
    
    $narrative = $result->fetch_assoc();
    
    // Create new PDF instance using mPDF
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_header' => 10,
        'margin_footer' => 10,
    ]);
    
    // Set document information
    $mpdf->SetTitle('GAD Narrative Report');
    $mpdf->SetAuthor('BatStateU');
    $mpdf->SetCreator('GAD System');
    
    // Define logo path
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/NEW_GAD_systemMarch 17/images/Batangas_State_Logo.png';
    $logoData = '';
    
    // Check if logo exists and read it
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoData = base64_encode($logoData);
    }
    
    // Set up page numbering in the footer
    $mpdf->setFooter('{PAGENO} of {nbpg}');
    
    // Process JSON fields
    $projectLeaderResp = is_string($narrative['project_leader_responsibilities']) ? 
        json_decode($narrative['project_leader_responsibilities'], true) : $narrative['project_leader_responsibilities'];
    if (!is_array($projectLeaderResp)) $projectLeaderResp = [];
    
    $asstProjectLeaderResp = is_string($narrative['asst_project_leader_responsibilities']) ? 
        json_decode($narrative['asst_project_leader_responsibilities'], true) : $narrative['asst_project_leader_responsibilities'];
    if (!is_array($asstProjectLeaderResp)) $asstProjectLeaderResp = [];
    
    $projectStaff = is_string($narrative['project_staff']) ? 
        json_decode($narrative['project_staff'], true) : $narrative['project_staff'];
    if (!is_array($projectStaff)) $projectStaff = [];
    
    $projectStaffResp = is_string($narrative['project_staff_responsibilities']) ? 
        json_decode($narrative['project_staff_responsibilities'], true) : $narrative['project_staff_responsibilities'];
    if (!is_array($projectStaffResp)) $projectStaffResp = [];
    
    $specificObjectives = is_string($narrative['specific_objectives']) ? 
        json_decode($narrative['specific_objectives'], true) : $narrative['specific_objectives'];
    if (!is_array($specificObjectives)) $specificObjectives = [];
    
    $strategies = is_string($narrative['strategies']) ? 
        json_decode($narrative['strategies'], true) : $narrative['strategies'];
    if (!is_array($strategies)) $strategies = [];
    
    $activities = is_string($narrative['activities']) ? 
        json_decode($narrative['activities'], true) : $narrative['activities'];
    if (!is_array($activities)) $activities = [];
    
    $workplan = is_string($narrative['workplan']) ? 
        json_decode($narrative['workplan'], true) : $narrative['workplan'];
    if (!is_array($workplan)) $workplan = [];
    
    $monitoringPlan = is_string($narrative['monitoring_plan']) ? 
        json_decode($narrative['monitoring_plan'], true) : $narrative['monitoring_plan'];
    if (!is_array($monitoringPlan)) $monitoringPlan = [];
    
    // Build HTML content for PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: "Times New Roman", Times, serif;
                font-size: 11pt;
                line-height: 1.3;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0;
            }
            th, td {
                border: 1px solid #000;
                padding: 5px;
                vertical-align: top;
                text-align: left;
            }
            .no-border {
                border: none;
            }
            .text-center {
                text-align: center;
            }
            .header-cell {
                vertical-align: middle;
                text-align: center;
            }
            .logo {
                width: 70px;
                height: auto;
            }
            .main-title {
                font-weight: bold;
                text-align: center;
                font-size: 12pt;
                text-transform: uppercase;
                padding: 5px;
            }
            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 5px;
                position: relative;
            }
            .checkbox.checked:after {
                content: "✓";
                position: absolute;
                top: -3px;
                left: 1px;
            }
            ul {
                margin: 5px 0 5px 15px;
                padding-left: 10px;
            }
            li {
                margin-bottom: 3px;
            }
            .signature-line {
                border-top: 1px solid #000;
                width: 90%;
                margin: 40px auto 5px;
            }
            .signature-text {
                text-align: center;
                margin: 0;
                font-weight: bold;
            }
            .signature-position {
                text-align: center;
                font-style: normal;
                margin: 0;
            }
            .page-number {
                text-align: right;
                font-size: 9pt;
            }
        </style>
    </head>
    <body>';
    
    // Header section
    $html .= '
    <table>
        <tr>
            <td style="width: 20%; border: 1px solid black; text-align: center; padding: 5px;">
                <img src="data:image/png;base64,' . $logoData . '" class="logo">
            </td>
            <td style="width: 30%; border: 1px solid black; text-align: center; padding: 5px;">
                Reference No.: BatStateU-FO-ESO-01
            </td>
            <td style="width: 30%; border: 1px solid black; text-align: center; padding: 5px;">
                Effectivity Date: August 25, 2023
            </td>
            <td style="width: 20%; border: 1px solid black; text-align: center; padding: 5px;">
                Revision No.: 03
            </td>
        </tr>
    </table>';
    
    // Title section
    $html .= '
    <table>
        <tr>
            <td class="main-title">GAD NARRATIVE REPORT</td>
        </tr>
    </table>';
    
    // Request Type section
    $html .= '
    <table>
        <tr>
            <td style="border: 1px solid black; padding: 5px;">
                <span class="checkbox checked"></span> Extension Service Program/Project/Activity is requested by clients.
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 5px;">
                <span class="checkbox"></span> Extension Service Program/Project/Activity is Department\'s initiative.
            </td>
        </tr>
    </table>';
    
    // PPA Type section
    $html .= '
    <table>
        <tr>
            <td style="width: 33.33%; border: 1px solid black; text-align: center; padding: 5px;">
                <span class="checkbox"></span> Program
            </td>
            <td style="width: 33.33%; border: 1px solid black; text-align: center; padding: 5px;">
                <span class="checkbox checked"></span> Project
            </td>
            <td style="width: 33.33%; border: 1px solid black; text-align: center; padding: 5px;">
                <span class="checkbox"></span> Activity
            </td>
        </tr>
    </table>';
    
    // Main content with numbered sections
    $html .= '
    <table>
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">I.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Title:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <strong>' . htmlspecialchars($narrative['activity_title'] ?? '') . '</strong>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">II.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Location:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                ' . htmlspecialchars($narrative['venue'] ?? '') . '
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">III.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Duration (Date and Time):</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                ' . nl2br(htmlspecialchars($narrative['duration'] ?? 'Not specified')) . '<br>
                8:00 am-5:00 pm
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">IV.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Type of Extension Service Agenda:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <p><em>Choose the MOST (only one) applicable Extension Agenda from the following:</em></p>
                <p>
                    <span class="checkbox"></span> BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program<br>
                    <span class="checkbox"></span> Livelihood and other Entrepreneurship related on Agri-Fisheries (LEAF)<br>
                    <span class="checkbox"></span> Environment and Natural resources Conservation, Protection and Rehabilitation Program<br>
                    <span class="checkbox"></span> Smart Analytics and Engineering Innovation<br>
                    <span class="checkbox"></span> Adopt-a Municipality/Barangay/School/Social Development Thru BIDANI Implementation<br>
                    <span class="checkbox"></span> Community Outreach<br>
                    <span class="checkbox"></span> Technical- Vocational Education and Training (TVET) Program<br>
                    <span class="checkbox"></span> Technology Transfer and Adoption/Utilization Program<br>
                    <span class="checkbox checked"></span> Technical Assistance and Advisory Services Program<br>
                    <span class="checkbox"></span> Parents\' Empowerment through Social Development (PESODEV)<br>
                    <span class="checkbox"></span> Gender and Development<br>
                    <span class="checkbox"></span> Disaster Risk Reduction and Management and Disaster Preparedness and Response/Climate Change Adaptation (DRRM and DPR-CCA)
                </p>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">V.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Sustainable Development Goals (SDG):</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <p><em>Choose the applicable SDG/s to your extension project:</em></p>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; border: none; padding: 2px;">
                            <span class="checkbox"></span> No Poverty<br>
                            <span class="checkbox"></span> Zero Hunger<br>
                            <span class="checkbox"></span> Good Health and Well-Being<br>
                            <span class="checkbox checked"></span> Quality Education<br>
                            <span class="checkbox"></span> Gender Equality<br>
                            <span class="checkbox"></span> Clean Water and Sanitation<br>
                            <span class="checkbox"></span> Affordable and Clean Energy<br>
                            <span class="checkbox"></span> Decent Work and Economic Growth<br>
                            <span class="checkbox"></span> Industry, Innovation and Infrastructure<br>
                        </td>
                        <td style="width: 50%; border: none; padding: 2px;">
                            <span class="checkbox"></span> Reduced Inequalities<br>
                            <span class="checkbox"></span> Sustainable Cities and Communities<br>
                            <span class="checkbox"></span> Responsible Consumption and Production<br>
                            <span class="checkbox"></span> Climate Action<br>
                            <span class="checkbox"></span> Life Below Water<br>
                            <span class="checkbox"></span> Life on Land<br>
                            <span class="checkbox"></span> Peace, Justice and Strong Institutions<br>
                            <span class="checkbox"></span> Partnerships for the Goals<br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">VI.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Office/s / College/s / Organization/s Involved:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                College of Informatics and Computing Sciences
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">VII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Program/s Involved:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <em>(specify the programs under the college implementing the project):</em><br>
                Bachelor of Science in Information Technology (BS IT)<br>
                Bachelor of Science in Computer Science (BS CS)
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">VIII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Project Leader, Assistant Project Leader and Coordinators:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <strong>Project Leader:</strong> ' . htmlspecialchars($narrative['project_leader'] ?? '') . '<br>
                <strong>Assistant Project Leader:</strong><br>
                ' . htmlspecialchars($narrative['asst_project_leader'] ?? '') . '<br>
            </td>
        </tr>';

    // Project Staff and Assigned Tasks
    $html .= '
        <tr>
            <td style="border: none;"></td>
            <td colspan="2" style="border: 1px solid black; padding: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 25%; border: none; vertical-align: top; padding: 5px;">
                            <strong>Project Staff:</strong><br>';
    
    // Add project staff
    foreach ($projectStaff as $staff) {
        $html .= htmlspecialchars($staff) . '<br>';
    }
    
    $html .= '
                        </td>
                        <td style="width: 75%; border-left: 1px solid black; vertical-align: top; padding: 5px;">
                            <strong>Assigned Tasks:</strong><br>
                            <table style="width: 100%; border-collapse: collapse; border: none;">
                                <tr>
                                    <td style="width: 30%; border: none; padding: 5px; vertical-align: top;">
                                        ' . htmlspecialchars($narrative['project_leader'] ?? '') . '
                                    </td>
                                    <td style="width: 70%; border: none; padding: 5px; vertical-align: top;">
                                        <ul>';
    
    // Add project leader responsibilities
    foreach ($projectLeaderResp as $resp) {
        $html .= '<li>' . htmlspecialchars($resp) . '</li>';
    }
    
    $html .= '
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; border: none; padding: 5px; vertical-align: top;">
                                        ' . htmlspecialchars($narrative['asst_project_leader'] ?? '') . '
                                    </td>
                                    <td style="width: 70%; border: none; padding: 5px; vertical-align: top;">
                                        <ul>';
    
    // Add assistant project leader responsibilities
    foreach ($asstProjectLeaderResp as $resp) {
        $html .= '<li>' . htmlspecialchars($resp) . '</li>';
    }
    
    $html .= '
                                        </ul>
                                    </td>
                                </tr>';
    
    // Add other staff and their responsibilities
    $staffCount = count($projectStaff);
    $respCount = count($projectStaffResp);
    for ($i = 0; $i < $staffCount; $i++) {
        $html .= '
                                <tr>
                                    <td style="width: 30%; border: none; padding: 5px; vertical-align: top;">
                                        ' . htmlspecialchars($projectStaff[$i]) . '
                                    </td>
                                    <td style="width: 70%; border: none; padding: 5px; vertical-align: top;">
                                        <ul>';
        
        // If there's a matching responsibility, add it
        if ($i < $respCount) {
            $html .= '<li>' . htmlspecialchars($projectStaffResp[$i]) . '</li>';
        }
        
        $html .= '
                                        </ul>
                                    </td>
                                </tr>';
    }
    
    $html .= '
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>';
    
    // Continue with remaining sections
    $html .= '
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">IX.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Partner Agencies:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                ' . htmlspecialchars($narrative['implementing_office'] ?? 'Department of Information and Communications Technology') . '
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">X.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Beneficiaries:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <em>(Type and Number of Male and Female):</em><br>
                Participants of ' . htmlspecialchars($narrative['venue'] ?? '') . '<br>
                Male: ' . intval($narrative['male_count'] ?? 10) . ' Participants<br>
                Female: ' . intval($narrative['female_count'] ?? 17) . ' Participants<br><br>
                
                Participants from BatStateU-TNEU Lipa Extensionists and DICT Officials<br>
                Male: 5 BatStateU TNEU Lipa Extensionists and DICT Officials<br>
                Female: 5 BatStateU TNEU Lipa Extensionists and DICT Officials
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XI.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Total Cost:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                The total cost for the implementation of this will be shoulder from DICT and ' . htmlspecialchars($narrative['venue'] ?? 'Local') . ' Municipality
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Source of fund:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <span class="checkbox checked"></span> STF<br>
                <span class="checkbox"></span> MDS<br>
                <span class="checkbox"></span> Others, ( Please specify): ___________________
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XIII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Rationale:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <em>(brief description of the situation):</em><br>
                ' . nl2br(htmlspecialchars($narrative['rationale'] ?? '')) . '
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XIV.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Objectives:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <strong>(General and Specific):</strong><br>
                ' . nl2br(htmlspecialchars($narrative['general_objective'] ?? '')) . '<br><br>
                <strong>Specific Objectives:</strong><br>
                <ul>';
    
    // Add specific objectives
    foreach ($specificObjectives as $objective) {
        $html .= '<li>' . htmlspecialchars($objective) . '</li>';
    }
    
    $html .= '
                </ul>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XV.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Program/Project Expected Output:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                ' . nl2br(htmlspecialchars($narrative['expected_output'] ?? 'Data Governance enhancement in academic institutions')) . '
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XVI.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Description, Strategies and Methods:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <strong>(Activities / Schedule):</strong><br>
                <p>' . nl2br(htmlspecialchars($narrative['description'] ?? '')) . '</p>
                
                <strong>Strategies:</strong><br>
                <ul>';
    
    // Add strategies
    foreach ($strategies as $strategy) {
        $html .= '<li>' . htmlspecialchars($strategy) . '</li>';
    }
    
    $html .= '
                </ul>
                
                <strong>Methods (Activities):</strong><br>
                <ul>';
    
    // Add activities
    foreach ($activities as $activity) {
        if (is_array($activity) && isset($activity['title'])) {
            $html .= '<li><strong>' . htmlspecialchars($activity['title']) . '</strong>';
            if (isset($activity['description'])) {
                $html .= ' - ' . htmlspecialchars($activity['description']);
            }
            $html .= '</li>';
        } else {
            $html .= '<li>' . htmlspecialchars($activity) . '</li>';
        }
    }
    
    $html .= '
                </ul>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XVII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Financial Plan:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                N/A
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XVIII.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Functional Relationships with the Partner Agencies:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <strong>(Duties / Tasks of the Partner Agencies):</strong><br>
                ' . nl2br(htmlspecialchars($narrative['functional_relationships'] ?? 'The partner agency will provide technical support and resources to ensure successful implementation.')) . '
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XIX.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Monitoring and Evaluation Mechanics / Plan:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Objectives</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Performance Indicators</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Baseline Data</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Performance Target</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Data Source</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Collection Method</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Frequency of Data Collection</td>
                        <td style="border: 1px solid black; background-color: #f2f2f2; padding: 5px; font-weight: bold;">Office/ Persons Responsible</td>
                    </tr>';
    
    // Add monitoring plan data
    foreach ($monitoringPlan as $item) {
        if (is_array($item)) {
            $html .= '<tr>';
            $fields = ['objective', 'indicator', 'baseline', 'target', 'source', 'method', 'frequency', 'responsible'];
            
            foreach ($fields as $field) {
                $html .= '<td style="border: 1px solid black; padding: 5px;">' . htmlspecialchars($item[$field] ?? '') . '</td>';
            }
            
            $html .= '</tr>';
        }
    }
    
    // If no monitoring plan data, add a default row
    if (count($monitoringPlan) == 0) {
        $html .= '
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">Impact</td>
                        <td style="border: 1px solid black; padding: 5px;">Percentage decrease in the number of data breaches or security incidents</td>
                        <td style="border: 1px solid black; padding: 5px;">Properly governed data can lead to personalized learning experiences and improved services for students</td>
                        <td style="border: 1px solid black; padding: 5px;">This target represents the measurable outcome of implementing effective data governance measures within the platform</td>
                        <td style="border: 1px solid black; padding: 5px;">Impact is on the highest level of change or benefit that is expected to achieve</td>
                        <td style="border: 1px solid black; padding: 5px;">Questionnaire, Focus Group Discussion</td>
                        <td style="border: 1px solid black; padding: 5px;">1 year</td>
                        <td style="border: 1px solid black; padding: 5px;">ESO, DICT, College of Informatics and Computing Sciences</td>
                    </tr>';
    }
    
    $html .= '
                </table>
            </td>
        </tr>
        
        <tr>
            <td style="width: 5%; border: 1px solid black; padding: 5px; vertical-align: top;">XX.</td>
            <td style="width: 15%; border: 1px solid black; padding: 5px; vertical-align: top;">Sustainability Plan:</td>
            <td style="width: 80%; border: 1px solid black; padding: 5px;">
                ' . nl2br(htmlspecialchars($narrative['sustainability_plan'] ?? '')) . '
                <ul>
                    <li>Specify roles and responsibilities for data stewards, data owners, and other relevant stakeholders.</li>
                    <li>Conduct a thorough inventory of all data collected, processed, and stored.</li>
                    <li>Implement privacy considerations at the initial stages of any data-related project or system development.</li>
                    <li>Implement robust security protocols to protect data from unauthorized access, breaches, or leaks.</li>
                    <li>Utilize encryption, access controls, and multi-factor authentication to safeguard sensitive information.</li>
                    <li>Establish procedures for data validation, cleaning, and quality assurance to ensure accuracy and reliability.</li>
                    <li>Regularly monitor and audit data to identify and rectify any discrepancies or anomalies.</li>
                </ul>
            </td>
        </tr>
    </table>';
    
    // Add page number to bottom of first page
    $html .= '<div class="page-number">Page {PAGENO} of {nbpg}</div>';
    
    // Signature section
    $html .= '
    <div style="page-break-before: always;"></div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="width: 50%; border: 1px solid black; vertical-align: bottom; padding: 15px;">
                <p>Prepared by:</p>
                <div class="signature-line"></div>
                <p class="signature-text">DR. RYNDEL V. AMORADO</p>
                <p class="signature-position">Dean, CICS</p>
                <p>Date Signed: NOV 2 4 2023</p>
            </td>
            <td style="width: 50%; border: 1px solid black; vertical-align: bottom; padding: 15px;">
                <p>Reviewed by:</p>
                <div class="signature-line"></div>
                <p class="signature-text">MS. BABY LIEZEL A. ROSALES</p>
                <p class="signature-position">Head, Extension Services</p>
                <p>Date Signed: NOV 2 4 2023</p>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; border: 1px solid black; vertical-align: bottom; padding: 15px;">
                <p>Recommending Approval:</p>
                <div class="signature-line"></div>
                <p class="signature-text">DR. FRANCIS G. BALAZON</p>
                <p class="signature-position">Vice Chancellor for Research, Development<br>and Extension Services</p>
                <p>Date Signed:</p>
            </td>
            <td style="width: 50%; border: 1px solid black; vertical-align: bottom; padding: 15px;">
                <p>Approved by:</p>
                <div class="signature-line"></div>
                <p class="signature-text">Atty. ALVIN R. DE SILVA</p>
                <p class="signature-position">Chancellor</p>
                <p>Date Signed:</p>
            </td>
        </tr>
    </table>
    
    <p style="font-size: 9pt; margin-top: 15px;"><em>Required Attachment: If Extension Service Program/Project/Activity is requested by clients, attach the letter of request with endorsement from the University President.</em></p>
    <p style="font-size: 9pt;">Cc: (1) Office of the College Dean/Head, Academic Affairs for UCBC</p>
    <div class="page-number">Page {PAGENO} of {nbpg}</div>
    
    </body>
    </html>';
    
    // Write HTML to PDF
    $mpdf->WriteHTML($html);
    
    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Extension_Program_Plan.pdf"');
    $mpdf->Output('Extension_Program_Plan.pdf', 'I');
    
} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error generating PDF: ' . $e->getMessage()]);
} 