<?php
session_start();
header('Content-Type: application/json');

// Include database configuration
include_once('../config.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the current user's campus from session
$userCampus = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$isCentral = ($userCampus === 'Central');

// Get parameters from request
$campus = isset($_GET['campus']) ? $_GET['campus'] : '';
$chartType = isset($_GET['chart_type']) ? $_GET['chart_type'] : '';

// If not Central user, can only view own campus
if (!$isCentral && !empty($campus) && $campus !== $userCampus) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Get current year
$currentYear = date('Y');

// Determine current quarter
$currentMonth = date('n');
$currentQuarter = ceil($currentMonth / 3);

// Define quarters to include in cumulative data
$quartersToInclude = array();
for ($i = 1; $i <= $currentQuarter; $i++) {
    $quartersToInclude[] = "'Q$i'"; // Format as 'Q1', 'Q2', etc. with quotes for SQL
}
$quarterFilter = implode(',', $quartersToInclude);

// Initialize response data
$data = [
    'proposed' => 0,
    'actual' => 0,
    'percentage' => 0,
    'relativePercentage' => 0,
    'trend' => 'Not Met'
];

try {
    // Process based on chart type
    if ($chartType == 'budgetChart') {
        // Get GAD Fund from target table for current year
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $gadFundQuery = "SELECT SUM(total_gad_fund) as total_proposed_budget FROM target WHERE year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $gadFundQuery = "SELECT total_gad_fund as total_proposed_budget FROM target WHERE year = '$currentYear' AND campus = '$filterCampus'";
        }
        
        $gadFundResult = mysqli_query($conn, $gadFundQuery);
        if ($gadFundResult && mysqli_num_rows($gadFundResult) > 0) {
            $gadFundRow = mysqli_fetch_assoc($gadFundResult);
            $proposedBudget = floatval($gadFundRow['total_proposed_budget']);
            $data['proposed'] = $proposedBudget;
        }
        
        // Get actual budget usage from ppas_forms
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $actualBudgetQuery = "SELECT SUM(approved_budget) as total_actual_budget FROM ppas_forms WHERE quarter IN ($quarterFilter) AND year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $actualBudgetQuery = "SELECT SUM(approved_budget) as total_actual_budget FROM ppas_forms WHERE quarter IN ($quarterFilter) AND campus = '$filterCampus' AND year = '$currentYear'";
        }
        
        $actualBudgetResult = mysqli_query($conn, $actualBudgetQuery);
        if ($actualBudgetResult && mysqli_num_rows($actualBudgetResult) > 0) {
            $actualBudgetRow = mysqli_fetch_assoc($actualBudgetResult);
            $actualBudget = floatval($actualBudgetRow['total_actual_budget']);
            $data['actual'] = $actualBudget;
        }
        
        // Get detailed budget activities from ppas_forms
        $detailedBudgetData = [];
        if ($isCentral && empty($campus)) {
            $detailedBudgetQuery = "SELECT activity, campus, quarter, approved_budget 
                                   FROM ppas_forms 
                                   WHERE quarter IN ($quarterFilter) 
                                   AND year = '$currentYear' 
                                   ORDER BY campus, quarter, approved_budget DESC";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $detailedBudgetQuery = "SELECT activity, campus, quarter, approved_budget 
                                   FROM ppas_forms 
                                   WHERE quarter IN ($quarterFilter) 
                                   AND campus = '$filterCampus' 
                                   AND year = '$currentYear' 
                                   ORDER BY quarter, approved_budget DESC";
        }
        
        $detailedBudgetResult = mysqli_query($conn, $detailedBudgetQuery);
        if ($detailedBudgetResult && mysqli_num_rows($detailedBudgetResult) > 0) {
            while ($row = mysqli_fetch_assoc($detailedBudgetResult)) {
                $detailedBudgetData[] = [
                    'activity' => $row['activity'],
                    'campus' => $row['campus'],
                    'quarter' => $row['quarter'],
                    'approved_budget' => floatval($row['approved_budget'])
                ];
            }
        }
        
        // Add detailed data to response
        $data['detailed_data'] = $detailedBudgetData;
    }
    else if ($chartType == 'activitiesChart') {
        // Get proposed activities from gpb_entries
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $activitiesQuery = "SELECT SUM(total_activities) as total_proposed_activities FROM gpb_entries WHERE year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $activitiesQuery = "SELECT SUM(total_activities) as total_proposed_activities FROM gpb_entries WHERE campus = '$filterCampus' AND year = '$currentYear'";
        }
        
        $activitiesResult = mysqli_query($conn, $activitiesQuery);
        if ($activitiesResult && mysqli_num_rows($activitiesResult) > 0) {
            $activitiesRow = mysqli_fetch_assoc($activitiesResult);
            $proposedActivities = intval($activitiesRow['total_proposed_activities']);
            $data['proposed'] = $proposedActivities;
        }
        
        // Get actual activities (count of rows in ppas_forms)
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $actualActivitiesQuery = "SELECT COUNT(*) as total_actual_activities FROM ppas_forms WHERE quarter IN ($quarterFilter) AND year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $actualActivitiesQuery = "SELECT COUNT(*) as total_actual_activities FROM ppas_forms WHERE quarter IN ($quarterFilter) AND campus = '$filterCampus' AND year = '$currentYear'";
        }
        
        $actualActivitiesResult = mysqli_query($conn, $actualActivitiesQuery);
        if ($actualActivitiesResult && mysqli_num_rows($actualActivitiesResult) > 0) {
            $actualActivitiesRow = mysqli_fetch_assoc($actualActivitiesResult);
            $actualActivities = intval($actualActivitiesRow['total_actual_activities']);
            $data['actual'] = $actualActivities;
        }
        
        // Get detailed activities by gender issue
        $detailedActivitiesData = [];
        
        // First get all gender issues from gpb_entries
        if ($isCentral && empty($campus)) {
            $genderIssuesQuery = "SELECT id, gender_issue, campus, total_activities 
                                 FROM gpb_entries 
                                 WHERE year = '$currentYear' 
                                 ORDER BY campus, gender_issue";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $genderIssuesQuery = "SELECT id, gender_issue, campus, total_activities 
                                 FROM gpb_entries 
                                 WHERE campus = '$filterCampus' 
                                 AND year = '$currentYear' 
                                 ORDER BY gender_issue";
        }
        
        $genderIssuesResult = mysqli_query($conn, $genderIssuesQuery);
        if ($genderIssuesResult && mysqli_num_rows($genderIssuesResult) > 0) {
            while ($row = mysqli_fetch_assoc($genderIssuesResult)) {
                $genderIssueId = $row['id'];
                $genderIssue = $row['gender_issue'];
                $campus = $row['campus'];
                $proposedActivities = intval($row['total_activities']);
                
                // Count actual activities for this gender issue
                $actualActivitiesQuery = "SELECT COUNT(*) as actual_count 
                                         FROM ppas_forms 
                                         WHERE gender_issue_id = '$genderIssueId' 
                                         AND quarter IN ($quarterFilter) 
                                         AND year = '$currentYear'";
                
                $actualActivitiesResult = mysqli_query($conn, $actualActivitiesQuery);
                $actualActivities = 0;
                
                if ($actualActivitiesResult && mysqli_num_rows($actualActivitiesResult) > 0) {
                    $actualRow = mysqli_fetch_assoc($actualActivitiesResult);
                    $actualActivities = intval($actualRow['actual_count']);
                }
                
                // Calculate remaining and completion percentage
                $remaining = max(0, $proposedActivities - $actualActivities);
                $completion = ($proposedActivities > 0) ? round(($actualActivities / $proposedActivities) * 100) : 0;
                
                $detailedActivitiesData[] = [
                    'gender_issue' => $genderIssue,
                    'campus' => $campus,
                    'proposed_activities' => $proposedActivities,
                    'actual_activities' => $actualActivities,
                    'remaining' => $remaining,
                    'completion' => $completion
                ];
            }
        }
        
        // Add detailed data to response
        $data['detailed_data'] = $detailedActivitiesData;
    }
    else if ($chartType == 'beneficiariesChart') {
        // Get proposed beneficiaries from gpb_entries
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $beneficiariesQuery = "SELECT SUM(total_participants) as total_proposed_beneficiaries FROM gpb_entries WHERE year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $beneficiariesQuery = "SELECT SUM(total_participants) as total_proposed_beneficiaries FROM gpb_entries WHERE campus = '$filterCampus' AND year = '$currentYear'";
        }
        
        $beneficiariesResult = mysqli_query($conn, $beneficiariesQuery);
        if ($beneficiariesResult && mysqli_num_rows($beneficiariesResult) > 0) {
            $beneficiariesRow = mysqli_fetch_assoc($beneficiariesResult);
            $proposedBeneficiaries = intval($beneficiariesRow['total_proposed_beneficiaries']);
            $data['proposed'] = $proposedBeneficiaries;
        }
        
        // Get actual beneficiaries from ppas_forms
        if ($isCentral && empty($campus)) {
            // For Central users with no campus filter, get data from all campuses
            $actualBeneficiariesQuery = "SELECT SUM(total_beneficiaries) as total_actual_beneficiaries FROM ppas_forms WHERE quarter IN ($quarterFilter) AND year = '$currentYear'";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $actualBeneficiariesQuery = "SELECT SUM(total_beneficiaries) as total_actual_beneficiaries FROM ppas_forms WHERE quarter IN ($quarterFilter) AND campus = '$filterCampus' AND year = '$currentYear'";
        }
        
        $actualBeneficiariesResult = mysqli_query($conn, $actualBeneficiariesQuery);
        if ($actualBeneficiariesResult && mysqli_num_rows($actualBeneficiariesResult) > 0) {
            $actualBeneficiariesRow = mysqli_fetch_assoc($actualBeneficiariesResult);
            $actualBeneficiaries = intval($actualBeneficiariesRow['total_actual_beneficiaries']);
            $data['actual'] = $actualBeneficiaries;
        }
        
        // Get detailed beneficiaries by gender issue
        $detailedBeneficiariesData = [];
        
        // First get all gender issues from gpb_entries
        if ($isCentral && empty($campus)) {
            $genderIssuesQuery = "SELECT id, gender_issue, campus, total_participants 
                                 FROM gpb_entries 
                                 WHERE year = '$currentYear' 
                                 ORDER BY campus, gender_issue";
        } else {
            $filterCampus = !empty($campus) ? $campus : $userCampus;
            $genderIssuesQuery = "SELECT id, gender_issue, campus, total_participants 
                                 FROM gpb_entries 
                                 WHERE campus = '$filterCampus' 
                                 AND year = '$currentYear' 
                                 ORDER BY gender_issue";
        }
        
        $genderIssuesResult = mysqli_query($conn, $genderIssuesQuery);
        if ($genderIssuesResult && mysqli_num_rows($genderIssuesResult) > 0) {
            while ($row = mysqli_fetch_assoc($genderIssuesResult)) {
                $genderIssueId = $row['id'];
                $genderIssue = $row['gender_issue'];
                $campus = $row['campus'];
                $proposedBeneficiaries = intval($row['total_participants']);
                
                // Sum actual beneficiaries for this gender issue
                $actualBeneficiariesQuery = "SELECT SUM(total_beneficiaries) as actual_count 
                                           FROM ppas_forms 
                                           WHERE gender_issue_id = '$genderIssueId' 
                                           AND quarter IN ($quarterFilter) 
                                           AND year = '$currentYear'";
                
                $actualBeneficiariesResult = mysqli_query($conn, $actualBeneficiariesQuery);
                $actualBeneficiaries = 0;
                
                if ($actualBeneficiariesResult && mysqli_num_rows($actualBeneficiariesResult) > 0) {
                    $actualRow = mysqli_fetch_assoc($actualBeneficiariesResult);
                    $actualBeneficiaries = intval($actualRow['actual_count'] ?? 0);
                }
                
                // Calculate remaining and completion percentage
                $remaining = max(0, $proposedBeneficiaries - $actualBeneficiaries);
                $completion = ($proposedBeneficiaries > 0) ? round(($actualBeneficiaries / $proposedBeneficiaries) * 100) : 0;
                
                $detailedBeneficiariesData[] = [
                    'gender_issue' => $genderIssue,
                    'campus' => $campus,
                    'proposed_beneficiaries' => $proposedBeneficiaries,
                    'actual_beneficiaries' => $actualBeneficiaries,
                    'remaining' => $remaining,
                    'completion' => $completion
                ];
            }
        }
        
        // Add detailed data to response
        $data['detailed_data'] = $detailedBeneficiariesData;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid chart type']);
        exit();
    }
    
    // Calculate percentages for indicators
    $percentage = ($data['proposed'] > 0) ? round(($data['actual'] / $data['proposed']) * 100) : 0;
    $relativePercentage = ($data['proposed'] > 0) ? round((($data['actual'] - $data['proposed']) / $data['proposed']) * 100) : 0;
    
    $data['percentage'] = $percentage;
    $data['relativePercentage'] = $relativePercentage;
    $data['trend'] = ($relativePercentage >= 0) ? "Met" : "Not Met";
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'data' => $data,
        'filters' => [
            'campus' => $campus,
            'chart_type' => $chartType
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
