<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../../includes/db_connection.php';

// Check if campus parameter is provided
if (!isset($_GET['campus'])) {
    echo json_encode(['status' => 'error', 'message' => 'Campus parameter is required']);
    exit;
}

$campus = $_GET['campus'];

// Debug log
error_log("Fetching signatories for campus: " . $campus);

// Debug database connection
if (!$conn) {
    error_log("Database connection failed");
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// List all tables to verify signatories table exists
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);
if ($tablesResult) {
    $tables = [];
    while ($tableRow = $tablesResult->fetch_row()) {
        $tables[] = $tableRow[0];
    }
    error_log("Available tables: " . implode(", ", $tables));
}

try {
    // First try: dump all signatories to see what's in the table
    $allQuery = "SELECT * FROM signatories LIMIT 5";
    $allResult = $conn->query($allQuery);
    
    if ($allResult && $allResult->num_rows > 0) {
        error_log("Found some signatories in the table. Sample data:");
        while ($row = $allResult->fetch_assoc()) {
            error_log("Campus: " . ($row['campus'] ?? 'null') . ", Name1: " . ($row['name1'] ?? 'null'));
        }
    } else {
        error_log("No signatories found in the table at all");
    }
    
    // Query to get signatories for the specified campus
    $query = "SELECT * FROM signatories WHERE campus = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $campus);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    // Debug result
    error_log("Signatories query result count for '{$campus}': " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        // Found signatories for this campus
        $signatories = $result->fetch_assoc();
        
        // Debug the data found
        error_log("Signatories data found: " . print_r($signatories, true));
        
        // Format the data for the report - including all three signatories needed for GBP reports
        $formattedSignatories = [
            // Prepared by (left side)
            [
                'name' => $signatories['name1'] ?? $signatories['prepared_by_name'] ?? 'RICHELLE M. SULIT',
                'position' => $signatories['gad_head_secretariat'] ?? $signatories['prepared_by_position'] ?? 'GAD Head Secretariat',
                'side' => 'left'
            ],
            // Approved by (middle)
            [
                'name' => $signatories['name3'] ?? $signatories['approved_by_name'] ?? 'ATTY. ALVIN R. DE SILVA',
                'position' => $signatories['chancellor'] ?? $signatories['approved_by_position'] ?? 'Chancellor',
                'side' => 'middle'
            ],
            // Assistant Director (right side)
            [
                'name' => $signatories['name4'] ?? $signatories['asst_director_name'] ?? 'JOCELYN A. JAUGAN',
                'position' => $signatories['asst_director_gad'] ?? $signatories['asst_director_position'] ?? 'Assistant Director, GAD',
                'side' => 'right'
            ]
        ];
        
        echo json_encode(['status' => 'success', 'data' => $formattedSignatories]);
    } else {
        // Try with different capitalization or trimming
        error_log("No exact match. Trying variations of campus name.");
        
        // List all campuses in the database for comparison
        $campusQuery = "SELECT DISTINCT campus FROM signatories";
        $campusResult = $conn->query($campusQuery);
        $availableCampuses = [];
        
        if ($campusResult && $campusResult->num_rows > 0) {
            while ($campusRow = $campusResult->fetch_assoc()) {
                $availableCampuses[] = $campusRow['campus'];
                // Log comparison data
                error_log("Comparing '{$campus}' with DB campus: '{$campusRow['campus']}'");
            }
            error_log("Available campuses in DB: " . implode(", ", $availableCampuses));
        } else {
            error_log("No campuses found in signatories table");
        }
        
        // Close the previous statement
        $stmt->close();
        
        // Try with LIKE for partial matching
        $searchCampus = "%" . $campus . "%";
        $query = "SELECT * FROM signatories WHERE campus LIKE ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $searchCampus);
        $stmt->execute();
        
        $result = $stmt->get_result();
        error_log("LIKE search found: " . $result->num_rows . " results");
        
        if ($result->num_rows > 0) {
            // Found with LIKE search
            $signatories = $result->fetch_assoc();
            error_log("LIKE search found data: " . print_r($signatories, true));
            
            // Format the data same as above
            $formattedSignatories = [
                // Prepared by (left side)
                [
                    'name' => $signatories['name1'] ?? $signatories['prepared_by_name'] ?? 'RICHELLE M. SULIT',
                    'position' => $signatories['gad_head_secretariat'] ?? $signatories['prepared_by_position'] ?? 'GAD Head Secretariat',
                    'side' => 'left'
                ],
                // Approved by (middle)
                [
                    'name' => $signatories['name3'] ?? $signatories['approved_by_name'] ?? 'ATTY. ALVIN R. DE SILVA',
                    'position' => $signatories['chancellor'] ?? $signatories['approved_by_position'] ?? 'Chancellor',
                    'side' => 'middle'
                ],
                // Assistant Director (right side)
                [
                    'name' => $signatories['name4'] ?? $signatories['asst_director_name'] ?? 'JOCELYN A. JAUGAN',
                    'position' => $signatories['asst_director_gad'] ?? $signatories['asst_director_position'] ?? 'Assistant Director, GAD',
                    'side' => 'right'
                ]
            ];
            
            echo json_encode(['status' => 'success', 'data' => $formattedSignatories]);
        } else {
            // Still no result, try case-insensitive search
            $stmt->close();
            
            // Case-insensitive search
            $query = "SELECT * FROM signatories WHERE LOWER(campus) = LOWER(?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $campus);
            $stmt->execute();
            
            $result = $stmt->get_result();
            error_log("Case-insensitive search found: " . $result->num_rows . " results");
            
            if ($result->num_rows > 0) {
                // Found with case-insensitive
                $signatories = $result->fetch_assoc();
                error_log("Case-insensitive search found data: " . print_r($signatories, true));
                
                // Format the data same as above
                $formattedSignatories = [
                    [
                        'name' => $signatories['name1'] ?? $signatories['prepared_by_name'] ?? 'RICHELLE M. SULIT',
                        'position' => $signatories['gad_head_secretariat'] ?? $signatories['prepared_by_position'] ?? 'GAD Head Secretariat',
                        'side' => 'left'
                    ],
                    [
                        'name' => $signatories['name3'] ?? $signatories['approved_by_name'] ?? 'ATTY. ALVIN R. DE SILVA',
                        'position' => $signatories['chancellor'] ?? $signatories['approved_by_position'] ?? 'Chancellor',
                        'side' => 'middle'
                    ],
                    [
                        'name' => $signatories['name4'] ?? $signatories['asst_director_name'] ?? 'JOCELYN A. JAUGAN',
                        'position' => $signatories['asst_director_gad'] ?? $signatories['asst_director_position'] ?? 'Assistant Director, GAD',
                        'side' => 'right'
                    ]
                ];
                
                echo json_encode(['status' => 'success', 'data' => $formattedSignatories]);
            } else {
                error_log("No signatories found for campus '{$campus}' after all search attempts");
                
                // As a last resort, try to find ANY signatory if the table has data
                $anyQuery = "SELECT * FROM signatories LIMIT 1";
                $anyResult = $conn->query($anyQuery);
                
                if ($anyResult && $anyResult->num_rows > 0) {
                    // At least we found some signatory to use
                    $anySignatory = $anyResult->fetch_assoc();
                    error_log("Using any available signatory: " . print_r($anySignatory, true));
                    
                    $formattedSignatories = [
                        [
                            'name' => $anySignatory['name1'] ?? 'RICHELLE M. SULIT',
                            'position' => $anySignatory['gad_head_secretariat'] ?? 'GAD Head Secretariat',
                            'side' => 'left'
                        ],
                        [
                            'name' => $anySignatory['name3'] ?? 'ATTY. ALVIN R. DE SILVA',
                            'position' => $anySignatory['chancellor'] ?? 'Chancellor',
                            'side' => 'middle'
                        ],
                        [
                            'name' => $anySignatory['name4'] ?? 'JOCELYN A. JAUGAN',
                            'position' => $anySignatory['asst_director_gad'] ?? 'Assistant Director, GAD',
                            'side' => 'right'
                        ]
                    ];
                    
                    echo json_encode([
                        'status' => 'success',
                        'warning' => 'Using default signatories as campus-specific ones were not found',
                        'data' => $formattedSignatories
                    ]);
                } else {
                    // No signatories found at all, return defaults
                    error_log("No signatories found in the table at all. Using hardcoded defaults.");
                    
                    echo json_encode([
                        'status' => 'success',
                        'warning' => 'Using default signatories as none were found in database',
                        'data' => [
                            [
                                'name' => 'RICHELLE M. SULIT',
                                'position' => 'GAD Head Secretariat',
                                'side' => 'left'
                            ],
                            [
                                'name' => 'ATTY. ALVIN R. DE SILVA',
                                'position' => 'Chancellor',
                                'side' => 'middle'
                            ],
                            [
                                'name' => 'JOCELYN A. JAUGAN',
                                'position' => 'Assistant Director, GAD',
                                'side' => 'right'
                            ]
                        ]
                    ]);
                }
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Error fetching signatories: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch signatories: ' . $e->getMessage()]);
}
?> 