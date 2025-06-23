<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update this form']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $conn = getConnection();
    $userCampus = $_SESSION['username'];

    // Validate required fields
    if (empty($_POST['id'])) {
        throw new Exception('Missing required entry ID');
    }
    $id = intval($_POST['id']);
    if ($id <= 0) {
        throw new Exception('Invalid entry ID');
    }
    if (empty($_POST['gender_issue_id']) || empty($_POST['year']) || empty($_POST['quarter'])) {
        throw new Exception('Missing required gender issue information');
    }

    // 1. Gender Issue Section
$campus = $userCampus;
$year = isset($_POST['year']) ? htmlspecialchars(trim($_POST['year']), ENT_QUOTES, 'UTF-8') : '';
$quarter = isset($_POST['quarter']) ? htmlspecialchars(trim($_POST['quarter']), ENT_QUOTES, 'UTF-8') : '';
$gender_issue_id = filter_input(INPUT_POST, 'gender_issue_id', FILTER_SANITIZE_NUMBER_INT);
$program = isset($_POST['program']) ? htmlspecialchars(trim($_POST['program']), ENT_QUOTES, 'UTF-8') : '';
$project = isset($_POST['project']) ? htmlspecialchars(trim($_POST['project']), ENT_QUOTES, 'UTF-8') : '';
// Check the activity field
$activity = '';
if (isset($_POST['activity']) && !empty($_POST['activity'])) {
    $activity = htmlspecialchars(trim($_POST['activity']), ENT_QUOTES, 'UTF-8');
    error_log("Using 'activity' field: " . $activity);
} else if (isset($_POST['form_activity']) && !empty($_POST['form_activity'])) {
    $activity = htmlspecialchars(trim($_POST['form_activity']), ENT_QUOTES, 'UTF-8');
    error_log("Using 'form_activity' field: " . $activity);
} else {
    error_log("Activity field not found in POST data. POST contents: " . print_r($_POST, true));
}

// Debug activity value
error_log("Update PPAS Form - Activity value submitted: " . $activity);
// Ensure we're using the correct activity value, not a fallback value
if (empty($activity)) {
    throw new Exception('Activity value cannot be empty');
}

// IMPORTANT: Don't overwrite activity with hardcoded value

    // 2. Basic Info Section
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8') : '';
    $startMonth = filter_input(INPUT_POST, 'startMonth', FILTER_SANITIZE_NUMBER_INT);
    $startDay = filter_input(INPUT_POST, 'startDay', FILTER_SANITIZE_NUMBER_INT);
    $startYear = filter_input(INPUT_POST, 'startYear', FILTER_SANITIZE_NUMBER_INT);
    // Store dates without leading zeros to avoid parsing issues
    $start_date = sprintf("%d/%d/%04d", (int)$startMonth, (int)$startDay, $startYear);
    $endMonth = filter_input(INPUT_POST, 'endMonth', FILTER_SANITIZE_NUMBER_INT);
    $endDay = filter_input(INPUT_POST, 'endDay', FILTER_SANITIZE_NUMBER_INT);
    $endYear = filter_input(INPUT_POST, 'endYear', FILTER_SANITIZE_NUMBER_INT);
    $end_date = sprintf("%d/%d/%04d", (int)$endMonth, (int)$endDay, $endYear);
    $start_time = isset($_POST['startTime']) ? htmlspecialchars(trim($_POST['startTime']), ENT_QUOTES, 'UTF-8') : '';
    $end_time = isset($_POST['endTime']) ? htmlspecialchars(trim($_POST['endTime']), ENT_QUOTES, 'UTF-8') : '';
    $lunch_break = isset($_POST['lunchBreak']) ? 1 : 0;
    $total_duration = isset($_POST['totalDuration']) ? htmlspecialchars(trim($_POST['totalDuration']), ENT_QUOTES, 'UTF-8') : '';
    $mode_of_delivery = isset($_POST['modeOfDelivery']) ? htmlspecialchars(trim($_POST['modeOfDelivery']), ENT_QUOTES, 'UTF-8') : '';

    // 3. Agenda
    $agenda = isset($_POST['agenda']) ? htmlspecialchars(trim($_POST['agenda']), ENT_QUOTES, 'UTF-8') : '';

    // 4. SDGs
    $sdg = isset($_POST['sdg']) ? json_encode($_POST['sdg']) : json_encode([]);

    // 5. Office and Program
    $officeData = isset($_POST['officeCollegeOrg']) && is_array($_POST['officeCollegeOrg']) ? $_POST['officeCollegeOrg'] : [];
    $office_college_organization = json_encode($officeData);
    $programData = isset($_POST['programList']) && is_array($_POST['programList']) ? $_POST['programList'] : [];
    $program_list = json_encode($programData);

    // 6. Project Team
    $projectLeaderData = isset($_POST['projectLeader']) && is_array($_POST['projectLeader']) ? $_POST['projectLeader'] : [];
    $project_leader = json_encode($projectLeaderData);
    $projectLeaderResponsibilitiesData = isset($_POST['projectLeaderResponsibilities']) && is_array($_POST['projectLeaderResponsibilities']) ? $_POST['projectLeaderResponsibilities'] : [];
    $project_leader_responsibilities = json_encode($projectLeaderResponsibilitiesData);
    $assistantProjectLeaderData = isset($_POST['assistantProjectLeader']) && is_array($_POST['assistantProjectLeader']) ? $_POST['assistantProjectLeader'] : [];
    $assistant_project_leader = json_encode($assistantProjectLeaderData);
    $assistantProjectLeaderResponsibilitiesData = isset($_POST['assistantProjectLeaderResponsibilities']) && is_array($_POST['assistantProjectLeaderResponsibilities']) ? $_POST['assistantProjectLeaderResponsibilities'] : [];
    $assistant_project_leader_responsibilities = json_encode($assistantProjectLeaderResponsibilitiesData);
    $projectStaffData = isset($_POST['projectStaff']) && is_array($_POST['projectStaff']) ? $_POST['projectStaff'] : [];
    $project_staff_coordinator = json_encode($projectStaffData);
    $projectStaffResponsibilitiesData = isset($_POST['projectStaffResponsibilities']) && is_array($_POST['projectStaffResponsibilities']) ? $_POST['projectStaffResponsibilities'] : [];
    $project_staff_coordinator_responsibilities = json_encode($projectStaffResponsibilitiesData);

    // 7. Agency and Participants
    $internal_type = isset($_POST['internalType']) ? htmlspecialchars(trim($_POST['internalType']), ENT_QUOTES, 'UTF-8') : '';
    $internal_male = filter_input(INPUT_POST, 'internalMale', FILTER_SANITIZE_NUMBER_INT);
    $internal_female = filter_input(INPUT_POST, 'internalFemale', FILTER_SANITIZE_NUMBER_INT);
    $internal_total = filter_input(INPUT_POST, 'internalTotal', FILTER_SANITIZE_NUMBER_INT);
    $external_type = isset($_POST['externalType']) ? htmlspecialchars(trim($_POST['externalType']), ENT_QUOTES, 'UTF-8') : '';
    $external_male = filter_input(INPUT_POST, 'externalMale', FILTER_SANITIZE_NUMBER_INT);
    $external_female = filter_input(INPUT_POST, 'externalFemale', FILTER_SANITIZE_NUMBER_INT);
    $external_total = filter_input(INPUT_POST, 'externalTotal', FILTER_SANITIZE_NUMBER_INT);
    $grand_total_male = filter_input(INPUT_POST, 'grandTotalMale', FILTER_SANITIZE_NUMBER_INT);
    $grand_total_female = filter_input(INPUT_POST, 'grandTotalFemale', FILTER_SANITIZE_NUMBER_INT);
    $grand_total = filter_input(INPUT_POST, 'grandTotal', FILTER_SANITIZE_NUMBER_INT);

    // 8. Program Description
    $rationale = isset($_POST['rationale']) ? htmlspecialchars(trim($_POST['rationale']), ENT_QUOTES, 'UTF-8') : '';
    $general_objectives = isset($_POST['generalObjectives']) ? htmlspecialchars(trim($_POST['generalObjectives']), ENT_QUOTES, 'UTF-8') : '';
    $specificObjectivesData = isset($_POST['specificObjectives']) && is_array($_POST['specificObjectives']) ? $_POST['specificObjectives'] : [];
    $specific_objectives = json_encode($specificObjectivesData);
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') : '';
    $strategyData = isset($_POST['strategy']) && is_array($_POST['strategy']) ? $_POST['strategy'] : [];
    $strategy = json_encode($strategyData);
    $expectedOutputData = isset($_POST['expectedOutput']) && is_array($_POST['expectedOutput']) ? $_POST['expectedOutput'] : [];
    $expected_output = json_encode($expectedOutputData);
    $functional_requirements = isset($_POST['functionalRequirements']) ? htmlspecialchars(trim($_POST['functionalRequirements']), ENT_QUOTES, 'UTF-8') : '';
    $sustainability_plan = isset($_POST['sustainabilityPlan']) ? htmlspecialchars(trim($_POST['sustainabilityPlan']), ENT_QUOTES, 'UTF-8') : '';
    $specificPlanData = isset($_POST['specificPlan']) && is_array($_POST['specificPlan']) ? $_POST['specificPlan'] : [];
    $specific_plan = json_encode($specificPlanData);

    // 9. Workplan
    $workplanActivityData = isset($_POST['workplanActivity']) && is_array($_POST['workplanActivity']) ? $_POST['workplanActivity'] : [];
    
    // Debug logging to see exact values
    error_log("PPAS Update Workplan Activities (before processing): " . print_r($workplanActivityData, true));
    
    // Check for and remove any duplicates in the array
    if (count($workplanActivityData) > 0) {
        $uniqueActivities = [];
        $seenActivities = [];
        
        foreach ($workplanActivityData as $index => $workplanItem) {
            // Skip any empty activities
            if (empty(trim($workplanItem))) continue;
            
            // Only keep the first occurrence of each activity
            if (!in_array($workplanItem, $seenActivities)) {
                $uniqueActivities[] = $workplanItem;
                $seenActivities[] = $workplanItem;
            } else {
                error_log("Removing duplicate activity: " . $workplanItem);
            }
        }
        
        // Replace the array with the deduplicated version
        if (count($uniqueActivities) < count($workplanActivityData)) {
            $workplanActivityData = $uniqueActivities;
            error_log("Removed " . (count($workplanActivityData) - count($uniqueActivities)) . " duplicate activities");
        }
    }
    
    error_log("PPAS Update Workplan Activities (after processing): " . print_r($workplanActivityData, true));
    
    $workplan_activity = json_encode($workplanActivityData);
    $workplanDateData = [];
    if (isset($_POST['workplanDate']) && is_array($_POST['workplanDate'])) {
        // Debug the raw date data coming in
        error_log("PPAS Update Raw Workplan Dates: " . print_r($_POST['workplanDate'], true));
        
        // If we removed duplicate activities, we need to adjust the date array to match
        if (count($_POST['workplanDate']) > count($workplanActivityData)) {
            error_log("Workplan dates count (" . count($_POST['workplanDate']) . ") is greater than activities count (" . count($workplanActivityData) . "). Adjusting...");
            // Trim the date array to match the activity array
            $_POST['workplanDate'] = array_slice($_POST['workplanDate'], 0, count($workplanActivityData));
        }
        
        foreach ($_POST['workplanDate'] as $index => $dateString) {
            $dates = explode(',', trim($dateString));
            $cleanDates = [];
            foreach ($dates as $date) {
                $date = trim($date);
                if (!empty($date)) {
                    // Ensure consistent three-letter month names
                    if (strpos($date, 'January') !== false) {
                        $date = str_replace('January', 'Jan', $date);
                    } elseif (strpos($date, 'February') !== false) {
                        $date = str_replace('February', 'Feb', $date);
                    } elseif (strpos($date, 'March') !== false) {
                        $date = str_replace('March', 'Mar', $date);
                    } elseif (strpos($date, 'April') !== false) {
                        $date = str_replace('April', 'Apr', $date);
                    } elseif (strpos($date, 'May') !== false && strlen($date) > 5) {
                        // Only replace if it's the full month name (avoid replacing just "May 1" since "May" is already short)
                        $date = str_replace('May', 'May', $date);
                    } elseif (strpos($date, 'June') !== false) {
                        $date = str_replace('June', 'Jun', $date);
                    } elseif (strpos($date, 'July') !== false) {
                        $date = str_replace('July', 'Jul', $date);
                    } elseif (strpos($date, 'August') !== false) {
                        $date = str_replace('August', 'Aug', $date);
                    } elseif (strpos($date, 'September') !== false) {
                        $date = str_replace('September', 'Sep', $date);
                    } elseif (strpos($date, 'October') !== false) {
                        $date = str_replace('October', 'Oct', $date);
                    } elseif (strpos($date, 'November') !== false) {
                        $date = str_replace('November', 'Nov', $date);
                    } elseif (strpos($date, 'December') !== false) {
                        $date = str_replace('December', 'Dec', $date);
                    }
                    $cleanDates[] = $date;
                }
            }
            $workplanDateData[$index] = !empty($cleanDates) ? implode(',', $cleanDates) : '';
        }
        
        // Debug the processed date data
        error_log("PPAS Update Processed Workplan Dates: " . print_r($workplanDateData, true));
    }
    $workplan_date = json_encode($workplanDateData);

    // 10. Financial Requirements
    $financial_plan = isset($_POST['financialPlan']) && $_POST['financialPlan'] === 'withFinancialPlan' ? 1 : 0;
    if ($financial_plan === 1) {
        $financialPlanItemsData = isset($_POST['financialPlanItems']) && is_array($_POST['financialPlanItems']) ? $_POST['financialPlanItems'] : [];
        $financialPlanQuantityData = isset($_POST['financialPlanQuantity']) && is_array($_POST['financialPlanQuantity']) ? $_POST['financialPlanQuantity'] : [];
        $financialPlanUnitData = isset($_POST['financialPlanUnit']) && is_array($_POST['financialPlanUnit']) ? $_POST['financialPlanUnit'] : [];
        $financialPlanUnitCostData = isset($_POST['financialPlanUnitCost']) && is_array($_POST['financialPlanUnitCost']) ? $_POST['financialPlanUnitCost'] : [];
        $financial_total_cost = isset($_POST['financialTotalCost']) ? htmlspecialchars(trim($_POST['financialTotalCost']), ENT_QUOTES, 'UTF-8') : '0';
    } else {
        $financialPlanItemsData = ["none"];
        $financialPlanQuantityData = ["none"];
        $financialPlanUnitData = ["none"];
        $financialPlanUnitCostData = ["none"];
        $financial_total_cost = "0";
    }
    $financial_plan_items = json_encode($financialPlanItemsData);
    $financial_plan_quantity = json_encode($financialPlanQuantityData);
    $financial_plan_unit = json_encode($financialPlanUnitData);
    $financial_plan_unit_cost = json_encode($financialPlanUnitCostData);
    $sourceOfFundData = [];
    if (isset($_POST['sourceOfFund']) && is_array($_POST['sourceOfFund'])) {
        $sourceString = $_POST['sourceOfFund'][0];
        $sourceOfFundData = array_unique(explode(',', $sourceString));
    }
    $source_of_fund = json_encode($sourceOfFundData);
    $financial_note = isset($_POST['financialNote']) ? htmlspecialchars(trim($_POST['financialNote']), ENT_QUOTES, 'UTF-8') : '';
    $approved_budget = isset($_POST['approvedBudget']) ? htmlspecialchars(trim($_POST['approvedBudget']), ENT_QUOTES, 'UTF-8') : '0.00';
    $ps_attribution = isset($_POST['psAttribution']) ? htmlspecialchars(trim($_POST['psAttribution']), ENT_QUOTES, 'UTF-8') : '';

    // 11. Monitoring
    $monitoringObjectivesData = isset($_POST['monitoringObjectives']) && is_array($_POST['monitoringObjectives']) ? $_POST['monitoringObjectives'] : [];
    $monitoringBaselineDataData = isset($_POST['monitoringBaselineData']) && is_array($_POST['monitoringBaselineData']) ? $_POST['monitoringBaselineData'] : [];
    $monitoringDataSourceData = isset($_POST['monitoringDataSource']) && is_array($_POST['monitoringDataSource']) ? $_POST['monitoringDataSource'] : [];
    $monitoringFrequencyDataCollectionData = isset($_POST['monitoringFrequencyDataCollection']) && is_array($_POST['monitoringFrequencyDataCollection']) ? $_POST['monitoringFrequencyDataCollection'] : [];
    $monitoringPerformanceIndicatorsData = isset($_POST['monitoringPerformanceIndicators']) && is_array($_POST['monitoringPerformanceIndicators']) ? $_POST['monitoringPerformanceIndicators'] : [];
    $monitoringPerformanceTargetData = isset($_POST['monitoringPerformanceTarget']) && is_array($_POST['monitoringPerformanceTarget']) ? $_POST['monitoringPerformanceTarget'] : [];
    $monitoringCollectionMethodData = isset($_POST['monitoringCollectionMethod']) && is_array($_POST['monitoringCollectionMethod']) ? $_POST['monitoringCollectionMethod'] : [];
    $monitoringOfficePersonsInvolvedData = isset($_POST['monitoringOfficePersonsInvolved']) && is_array($_POST['monitoringOfficePersonsInvolved']) ? $_POST['monitoringOfficePersonsInvolved'] : [];
    $monitoring_objectives = json_encode($monitoringObjectivesData);
    $monitoring_baseline_data = json_encode($monitoringBaselineDataData);
    $monitoring_data_source = json_encode($monitoringDataSourceData);
    $monitoring_frequency_data_collection = json_encode($monitoringFrequencyDataCollectionData);
    $monitoring_performance_indicators = json_encode($monitoringPerformanceIndicatorsData);
    $monitoring_performance_target = json_encode($monitoringPerformanceTargetData);
    $monitoring_collection_method = json_encode($monitoringCollectionMethodData);
    $monitoring_office_persons_involved = json_encode($monitoringOfficePersonsInvolvedData);

    // Prepare SQL UPDATE statement
    $sql = "UPDATE ppas_forms SET
        year = :year,
        quarter = :quarter,
        gender_issue_id = :gender_issue_id,
        program = :program,
        project = :project,
        activity = :activity,
        location = :location,
        start_date = :start_date,
        end_date = :end_date,
        start_time = :start_time,
        end_time = :end_time,
        lunch_break = :lunch_break,
        total_duration = :total_duration,
        mode_of_delivery = :mode_of_delivery,
        agenda = :agenda,
        sdg = :sdg,
        office_college_organization = :office_college_organization,
        program_list = :program_list,
        project_leader = :project_leader,
        project_leader_responsibilities = :project_leader_responsibilities,
        assistant_project_leader = :assistant_project_leader,
        assistant_project_leader_responsibilities = :assistant_project_leader_responsibilities,
        project_staff_coordinator = :project_staff_coordinator,
        project_staff_coordinator_responsibilities = :project_staff_coordinator_responsibilities,
        internal_type = :internal_type,
        internal_male = :internal_male,
        internal_female = :internal_female,
        internal_total = :internal_total,
        external_type = :external_type,
        external_male = :external_male,
        external_female = :external_female,
        external_total = :external_total,
        grand_total_male = :grand_total_male,
        grand_total_female = :grand_total_female,
        grand_total = :grand_total,
        rationale = :rationale,
        general_objectives = :general_objectives,
        specific_objectives = :specific_objectives,
        description = :description,
        strategy = :strategy,
        expected_output = :expected_output,
        functional_requirements = :functional_requirements,
        sustainability_plan = :sustainability_plan,
        specific_plan = :specific_plan,
        workplan_activity = :workplan_activity,
        workplan_date = :workplan_date,
        financial_plan = :financial_plan,
        financial_plan_items = :financial_plan_items,
        financial_plan_quantity = :financial_plan_quantity,
        financial_plan_unit = :financial_plan_unit,
        financial_plan_unit_cost = :financial_plan_unit_cost,
        financial_total_cost = :financial_total_cost,
        source_of_fund = :source_of_fund,
        financial_note = :financial_note,
        approved_budget = :approved_budget,
        ps_attribution = :ps_attribution,
        monitoring_objectives = :monitoring_objectives,
        monitoring_baseline_data = :monitoring_baseline_data,
        monitoring_data_source = :monitoring_data_source,
        monitoring_frequency_data_collection = :monitoring_frequency_data_collection,
        monitoring_performance_indicators = :monitoring_performance_indicators,
        monitoring_performance_target = :monitoring_performance_target,
        monitoring_collection_method = :monitoring_collection_method,
        monitoring_office_persons_involved = :monitoring_office_persons_involved,
        updated_at = CURRENT_TIMESTAMP
        WHERE id = :id AND campus = :campus";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':quarter', $quarter);
    $stmt->bindParam(':gender_issue_id', $gender_issue_id);
    $stmt->bindParam(':program', $program);
    $stmt->bindParam(':project', $project);
    $stmt->bindParam(':activity', $activity);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':lunch_break', $lunch_break);
    $stmt->bindParam(':total_duration', $total_duration);
    $stmt->bindParam(':mode_of_delivery', $mode_of_delivery);
    $stmt->bindParam(':agenda', $agenda);
    $stmt->bindParam(':sdg', $sdg);
    $stmt->bindParam(':office_college_organization', $office_college_organization);
    $stmt->bindParam(':program_list', $program_list);
    $stmt->bindParam(':project_leader', $project_leader);
    $stmt->bindParam(':project_leader_responsibilities', $project_leader_responsibilities);
    $stmt->bindParam(':assistant_project_leader', $assistant_project_leader);
    $stmt->bindParam(':assistant_project_leader_responsibilities', $assistant_project_leader_responsibilities);
    $stmt->bindParam(':project_staff_coordinator', $project_staff_coordinator);
    $stmt->bindParam(':project_staff_coordinator_responsibilities', $project_staff_coordinator_responsibilities);
    $stmt->bindParam(':internal_type', $internal_type);
    $stmt->bindParam(':internal_male', $internal_male);
    $stmt->bindParam(':internal_female', $internal_female);
    $stmt->bindParam(':internal_total', $internal_total);
    $stmt->bindParam(':external_type', $external_type);
    $stmt->bindParam(':external_male', $external_male);
    $stmt->bindParam(':external_female', $external_female);
    $stmt->bindParam(':external_total', $external_total);
    $stmt->bindParam(':grand_total_male', $grand_total_male);
    $stmt->bindParam(':grand_total_female', $grand_total_female);
    $stmt->bindParam(':grand_total', $grand_total);
    $stmt->bindParam(':rationale', $rationale);
    $stmt->bindParam(':general_objectives', $general_objectives);
    $stmt->bindParam(':specific_objectives', $specific_objectives);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':strategy', $strategy);
    $stmt->bindParam(':expected_output', $expected_output);
    $stmt->bindParam(':functional_requirements', $functional_requirements);
    $stmt->bindParam(':sustainability_plan', $sustainability_plan);
    $stmt->bindParam(':specific_plan', $specific_plan);
    $stmt->bindParam(':workplan_activity', $workplan_activity);
    $stmt->bindParam(':workplan_date', $workplan_date);
    $stmt->bindParam(':financial_plan', $financial_plan);
    $stmt->bindParam(':financial_plan_items', $financial_plan_items);
    $stmt->bindParam(':financial_plan_quantity', $financial_plan_quantity);
    $stmt->bindParam(':financial_plan_unit', $financial_plan_unit);
    $stmt->bindParam(':financial_plan_unit_cost', $financial_plan_unit_cost);
    $stmt->bindParam(':financial_total_cost', $financial_total_cost);
    $stmt->bindParam(':source_of_fund', $source_of_fund);
    $stmt->bindParam(':financial_note', $financial_note);
    $stmt->bindParam(':approved_budget', $approved_budget);
    $stmt->bindParam(':ps_attribution', $ps_attribution);
    $stmt->bindParam(':monitoring_objectives', $monitoring_objectives);
    $stmt->bindParam(':monitoring_baseline_data', $monitoring_baseline_data);
    $stmt->bindParam(':monitoring_data_source', $monitoring_data_source);
    $stmt->bindParam(':monitoring_frequency_data_collection', $monitoring_frequency_data_collection);
    $stmt->bindParam(':monitoring_performance_indicators', $monitoring_performance_indicators);
    $stmt->bindParam(':monitoring_performance_target', $monitoring_performance_target);
    $stmt->bindParam(':monitoring_collection_method', $monitoring_collection_method);
    $stmt->bindParam(':monitoring_office_persons_involved', $monitoring_office_persons_involved);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':campus', $campus);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'PPAS form updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or entry not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating form: ' . $e->getMessage()]);
} 