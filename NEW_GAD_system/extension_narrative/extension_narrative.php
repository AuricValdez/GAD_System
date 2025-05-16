<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

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

// Add this function before the HTML section
function getSignatories($campus) {
    try {
        $conn = getConnection();
        
        // Fetch signatories data for the specified campus
        $sql = "SELECT * FROM signatories WHERE campus = :campus";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no results found for the campus, try a default fetch
        if (!$result) {
            error_log("No signatories found for campus: $campus - trying to fetch default");
            
            // Try to fetch any record to use as default
            $defaultSql = "SELECT * FROM signatories LIMIT 1";
            $defaultStmt = $conn->prepare($defaultSql);
            $defaultStmt->execute();
            $result = $defaultStmt->fetch(PDO::FETCH_ASSOC);
            
            // If still no records found, return structured defaults
            if (!$result) {
                error_log("No signatories found at all - using empty defaults");
                return [
                    'name1' => '',
                    'name3' => '',
                    'name4' => ''
                ];
            }
            
            error_log("Using default signatory record");
        }
        
        // Log the signatories data retrieved
        error_log('Retrieved signatories data: ' . print_r($result, true));
        
        return $result;
    } catch (Exception $e) {
        error_log('Error fetching signatories: ' . $e->getMessage());
        return [
            'name1' => '',
            'name3' => '',
            'name4' => ''
        ];
    }
}

// Get signatories for the current campus
$signatories = getSignatories(isset($_SESSION['username']) ? $_SESSION['username'] : '');

// Debug log the signatories data
error_log('Signatories data in print_narrative.php: ' . print_r($signatories, true));

// Debug: Add a comment with all field names from the database
$signatories['debug_all_fields'] = json_encode($signatories);

// Data Entry button now moved next to the Print button

// Add this function at the top of the file, after any existing includes
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
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Add this function to fetch personnel data
function getPersonnelData($ppas_form_id) {
    try {
        echo "<div style='background:#f0f8ff;padding:10px;margin:10px;border:1px solid #ccc;'>";
        echo "<h3>Personnel Data Debugging</h3>";
        echo "<p>PPAS Form ID: $ppas_form_id</p>";
        
        $conn = getConnection();
        
        // First try primary personnel query with improved field selection
        $sql = "
            SELECT 
                pp.personnel_id,
                pp.role,
                p.name,
                p.gender,
                p.academic_rank,
                p.designation,
                p.email,
                p.phone
            FROM ppas_personnel pp 
            JOIN personnel p ON pp.personnel_id = p.id
            WHERE pp.ppas_form_id = :ppas_form_id
            ORDER BY pp.role, p.name
        ";
        
        echo "<p>Primary query:<br><code>" . htmlspecialchars($sql) . "</code></p>";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ppas_form_id', $ppas_form_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Primary personnel query results: <strong>" . count($personnel) . " records found</strong></p>";
        
        // Group personnel by role
        $personnel_by_role = [
            'project_leaders' => [],
            'assistant_project_leaders' => [],
            'project_staff' => []
        ];
        
        foreach ($personnel as $person) {
            if ($person['role'] == 'Project Leader') {
                $personnel_by_role['project_leaders'][] = $person;
            } elseif ($person['role'] == 'Assistant Project Leader') {
                $personnel_by_role['assistant_project_leaders'][] = $person;
            } elseif ($person['role'] == 'Staff' || $person['role'] == 'Other Internal Participants') {
                $personnel_by_role['project_staff'][] = $person;
            }
        }
        
        // Show summary of personnel found in main table
        echo "<p>Primary personnel table breakdown:</p>";
        echo "<ul>";
        echo "<li>Project Leaders: " . count($personnel_by_role['project_leaders']) . "</li>";
        echo "<li>Assistant Project Leaders: " . count($personnel_by_role['assistant_project_leaders']) . "</li>";
        echo "<li>Project Staff: " . count($personnel_by_role['project_staff']) . "</li>";
        echo "</ul>";
        
        // If no personnel found, try alternative tables and fallback options
        if (empty($personnel_by_role['project_leaders']) && empty($personnel_by_role['assistant_project_leaders']) && empty($personnel_by_role['project_staff'])) {
            echo "<p><strong>No personnel found in primary table. Trying fallback methods...</strong></p>";
            
            // Try a more efficient combined query approach
            try {
                // Get proposal data; narrative data has been moved to narrative_entries table
                $sql = "
                    SELECT 
                        g.project_leader_responsibilities,
                        g.assistant_leader_responsibilities,
                        g.staff_responsibilities,
                        p.activity,
                        p.year
                    FROM ppas_forms p
                    LEFT JOIN gad_proposals g ON p.id = g.ppas_form_id
                    WHERE p.id = :ppas_form_id
                    LIMIT 1
                ";
                
                echo "<p>Fallback query:<br><code>" . htmlspecialchars($sql) . "</code></p>";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ppas_form_id', $ppas_form_id, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data) {
                    echo "<p>Fallback query returned data</p>";
                    
                    // Show the raw data for debugging
                    echo "<p>Raw data found:</p>";
                    echo "<ul>";
                    foreach ($data as $key => $value) {
                        echo "<li>" . htmlspecialchars($key) . ": " . 
                            (strlen($value) > 50 ? 
                                htmlspecialchars(substr($value, 0, 50)) . "..." : 
                                htmlspecialchars($value)
                            ) . "</li>";
                    }
                    echo "</ul>";
                    
                    // Process project leaders
                    if (!empty($data['project_leader_responsibilities'])) {
                        try {
                            echo "<p>Found project_leader_responsibilities data</p>";
                            
                            $leaders = json_decode($data['project_leader_responsibilities'], true);
                            if (is_array($leaders)) {
                                echo "<p>Successfully decoded leader data as array: " . count($leaders) . " leaders</p>";
                                
                                foreach ($leaders as $leader) {
                                    if (!empty($leader)) {
                                        $personnel_by_role['project_leaders'][] = [
                                            'name' => $leader,
                                            'role' => 'Project Leader'
                                        ];
                                    }
                                }
                            } elseif (is_string($leaders) && !empty($leaders)) {
                                echo "<p>Decoded leader data as string</p>";
                                
                                $personnel_by_role['project_leaders'][] = [
                                    'name' => $leaders,
                                    'role' => 'Project Leader'
                                ];
                            } else {
                                echo "<p>Failed to decode leader data properly</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p>Error decoding project leader responsibilities: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log('Error decoding project leader responsibilities: ' . $e->getMessage());
                            
                            // Try as string if JSON decode failed
                            if (is_string($data['project_leader_responsibilities']) && !empty($data['project_leader_responsibilities'])) {
                                $personnel_by_role['project_leaders'][] = [
                                    'name' => $data['project_leader_responsibilities'],
                                    'role' => 'Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process assistant leaders
                    if (!empty($data['assistant_leader_responsibilities'])) {
                        try {
                            echo "<p>Found assistant_leader_responsibilities data</p>";
                            
                            $assistants = json_decode($data['assistant_leader_responsibilities'], true);
                            if (is_array($assistants)) {
                                echo "<p>Successfully decoded assistant leader data as array: " . count($assistants) . " assistants</p>";
                                
                                foreach ($assistants as $assistant) {
                                    if (!empty($assistant)) {
                                        $personnel_by_role['assistant_project_leaders'][] = [
                                            'name' => $assistant,
                                            'role' => 'Assistant Project Leader'
                                        ];
                                    }
                                }
                            } elseif (is_string($assistants) && !empty($assistants)) {
                                echo "<p>Decoded assistant leader data as string</p>";
                                
                                $personnel_by_role['assistant_project_leaders'][] = [
                                    'name' => $assistants,
                                    'role' => 'Assistant Project Leader'
                                ];
                            } else {
                                echo "<p>Failed to decode assistant leader data properly</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p>Error decoding assistant leader responsibilities: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log('Error decoding assistant leader responsibilities: ' . $e->getMessage());
                            
                            // Try as string if JSON decode failed
                            if (is_string($data['assistant_leader_responsibilities']) && !empty($data['assistant_leader_responsibilities'])) {
                                $personnel_by_role['assistant_project_leaders'][] = [
                                    'name' => $data['assistant_leader_responsibilities'],
                                    'role' => 'Assistant Project Leader'
                                ];
                            }
                        }
                    }
                    
                    // Process staff
                    if (!empty($data['staff_responsibilities'])) {
                        try {
                            echo "<p>Found staff_responsibilities data</p>";
                            
                            $staff = json_decode($data['staff_responsibilities'], true);
                            if (is_array($staff)) {
                                echo "<p>Successfully decoded staff data as array: " . count($staff) . " staff</p>";
                                
                                foreach ($staff as $member) {
                                    if (!empty($member)) {
                                        $personnel_by_role['project_staff'][] = [
                                            'name' => $member,
                                            'role' => 'Staff'
                                        ];
                                    }
                                }
                            } elseif (is_string($staff) && !empty($staff)) {
                                echo "<p>Decoded staff data as string</p>";
                                
                                $personnel_by_role['project_staff'][] = [
                                    'name' => $staff,
                                    'role' => 'Staff'
                                ];
                            } else {
                                echo "<p>Failed to decode staff data properly</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p>Error decoding staff responsibilities: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log('Error decoding staff responsibilities: ' . $e->getMessage());
                            
                            // Try as string if JSON decode failed
                            if (is_string($data['staff_responsibilities']) && !empty($data['staff_responsibilities'])) {
                                $personnel_by_role['project_staff'][] = [
                                    'name' => $data['staff_responsibilities'],
                                    'role' => 'Staff'
                                ];
                            }
                        }
                    }
                    
                    // If still empty, try fetching from narrative_entries table
                    if (empty($personnel_by_role['project_leaders']) && isset($data['activity']) && isset($data['year'])) {
                        try {
                            echo "<p>Trying to fetch data from narrative_entries table</p>";
                            
                            // Try to fetch personnel information from narrative_entries
                            $narrative_sql = "SELECT * FROM narrative_entries WHERE title = :title AND year = :year LIMIT 1";
                            $narrative_stmt = $conn->prepare($narrative_sql);
                            $narrative_stmt->bindParam(':title', $data['activity'], PDO::PARAM_STR);
                            $narrative_stmt->bindParam(':year', $data['year'], PDO::PARAM_STR);
                            $narrative_stmt->execute();
                            
                            $narrative_data = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($narrative_data) {
                                echo "<p>Found matching narrative entry in narrative_entries table</p>";
                                
                                // For now, add placeholder personnel
                                $personnel_by_role['project_leaders'][] = [
                                    'name' => 'Project Leader (from narrative_entries)',
                                    'role' => 'Project Leader'
                                ];
                                
                                // In the future, you could add code here to extract personnel information
                                // from fields in the narrative_entries table if available
                            } else {
                                echo "<p>No matching narrative entry found in narrative_entries table</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p>Error fetching from narrative_entries: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log('Error fetching from narrative_entries: ' . $e->getMessage());
                        }
                    }
                    
                    // If assistant leaders empty and we found narrative data, add a placeholder
                    if (empty($personnel_by_role['assistant_project_leaders']) && isset($narrative_data)) {
                        // Add placeholder assistant project leader
                        $personnel_by_role['assistant_project_leaders'][] = [
                            'name' => 'Assistant Project Leader (from narrative_entries)',
                            'role' => 'Assistant Project Leader'
                        ];
                        // In the future, you could extract actual assistant leader information from narrative_entries
                    }
                    
                    // If project staff is empty and we found narrative data, add a placeholder
                    if (empty($personnel_by_role['project_staff']) && isset($narrative_data)) {
                        // Add placeholder project staff
                        $personnel_by_role['project_staff'][] = [
                            'name' => 'Project Staff (from narrative_entries)',
                            'role' => 'Staff'
                        ];
                        // In the future, you could extract actual staff information from narrative_entries
                    }
                    
                    // After all fallback attempts, show final results
                    echo "<p>Final personnel count after fallbacks:</p>";
                    echo "<ul>";
                    echo "<li>Project Leaders: " . count($personnel_by_role['project_leaders']) . "</li>";
                    echo "<li>Assistant Project Leaders: " . count($personnel_by_role['assistant_project_leaders']) . "</li>";
                    echo "<li>Project Staff: " . count($personnel_by_role['project_staff']) . "</li>";
                    echo "</ul>";
                } else {
                    echo "<p>Fallback query did not return any data</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error in fallback query: " . htmlspecialchars($e->getMessage()) . "</p>";
                error_log('Error fetching alternative personnel data: ' . $e->getMessage());
            }
        }
        
        // Final fallback - add a default value if nothing was found
        if (empty($personnel_by_role['project_leaders'])) {
            echo "<p style='color:orange;'>No project leaders found - adding placeholder</p>";
            $personnel_by_role['project_leaders'][] = [
                'name' => 'Not specified',
                'role' => 'Project Leader'
            ];
        }
        
        // Also ensure assistant project leaders and staff have fallbacks
        if (empty($personnel_by_role['assistant_project_leaders'])) {
            echo "<p style='color:orange;'>No assistant project leaders found - adding placeholder</p>";
            $personnel_by_role['assistant_project_leaders'][] = [
                'name' => 'Not specified',
                'role' => 'Assistant Project Leader'
            ];
        }
        
        if (empty($personnel_by_role['project_staff'])) {
            echo "<p style='color:orange;'>No project staff found - adding placeholder</p>";
            $personnel_by_role['project_staff'][] = [
                'name' => 'Not specified',
                'role' => 'Staff'
            ];
        }
        
        echo "</div>";
        
        return $personnel_by_role;
    } catch (Exception $e) {
        echo "<div style='background:#ffeeee;padding:10px;margin:10px;border:1px solid #ffaaaa;'>";
        echo "<h3>Error Fetching Personnel</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
        
        error_log('Error fetching personnel data: ' . $e->getMessage());
        return [
            'project_leaders' => [['name' => 'Error retrieving personnel', 'role' => 'Project Leader']],
            'assistant_project_leaders' => [],
            'project_staff' => [],
            'error' => $e->getMessage()
        ];
    }
}

// Add this function to get narrative data with filtered extension_service_agenda = 1
function getNarrativeData($ppas_form_id) {
    try {
        // Debug output directly for immediate visibility
        echo "<div style='background:#f8f8f8;padding:10px;margin:10px;border:1px solid #ddd;'>";
        echo "<h3>Debugging Information</h3>";
        echo "<p>PPAS Form ID: $ppas_form_id</p>";
        
        $conn = getConnection();
        
        // First, verify if the PPAS form exists and display info about it
        $check_sql = "SELECT id, activity, location, year, quarter, start_date, end_date, start_time, end_time, approved_budget, 
                     source_of_fund, ps_attribution, sdg as sdgs, mode_of_delivery FROM ppas_forms WHERE id = :id LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
        $check_stmt->execute();
        
        $ppas_form = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ppas_form) {
            echo "<p style='color:red'>Error: PPAS Form ID $ppas_form_id does not exist in database</p>";
            echo "</div>";
            error_log("PPAS Form ID $ppas_form_id does not exist in ppas_forms table");
            return ['error' => 'PPAS Form ID does not exist', 'ppas_form_id' => $ppas_form_id];
        }
        
        echo "<p>PPAS Form found: Activity: <strong>" . htmlspecialchars($ppas_form['activity']) . "</strong></p>";
        echo "<p>Location: <strong>" . htmlspecialchars($ppas_form['location']) . "</strong></p>";
        
        // Get related narrative_entries
        $narrative_sql = "SELECT * FROM narrative_entries WHERE title = :title AND year = :year LIMIT 1";
        $narrative_stmt = $conn->prepare($narrative_sql);
        $narrative_stmt->bindParam(':title', $ppas_form['activity'], PDO::PARAM_STR);
        $narrative_stmt->bindParam(':year', $ppas_form['year'], PDO::PARAM_STR);
        $narrative_stmt->execute();
        
        $narrative_entries = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($narrative_entries) {
            echo "<p>Found matching narrative entry in narrative_entries table ID: " . $narrative_entries['id'] . "</p>";
        } else {
            echo "<p style='color:orange'>No matching narrative entry found in narrative_entries table</p>";
            
            // Additional attempt to find narrative entry by searching for similar titles
            $fuzzy_search_sql = "SELECT * FROM narrative_entries WHERE title LIKE :fuzzy_title AND year = :year LIMIT 1";
            $fuzzy_search_stmt = $conn->prepare($fuzzy_search_sql);
            $fuzzy_title = "%" . substr($ppas_form['activity'], 0, 15) . "%"; // Search using first 15 chars
            $fuzzy_search_stmt->bindParam(':fuzzy_title', $fuzzy_title, PDO::PARAM_STR);
            $fuzzy_search_stmt->bindParam(':year', $ppas_form['year'], PDO::PARAM_STR);
            $fuzzy_search_stmt->execute();
            $narrative_entries = $fuzzy_search_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($narrative_entries) {
                echo "<p>Found narrative entry with fuzzy title match in narrative_entries table ID: " . $narrative_entries['id'] . "</p>";
            }
        }
        
        // Get personnel data (with additional effort to load fallback data)
        $personnel_data = getPersonnelData($ppas_form_id);
        echo "<p>Personnel data retrieved: " . (is_array($personnel_data) ? count($personnel_data) : 'None') . "</p>";
        
        // Debug output personnel data for visibility
        if (is_array($personnel_data)) {
            echo "<p>Personnel data structure: " . implode(", ", array_keys($personnel_data)) . "</p>";
            foreach ($personnel_data as $role => $personnel) {
                echo "<p>$role: " . (is_array($personnel) ? count($personnel) : 'None') . " records</p>";
            }
        }
        
        // Get relevant objectives
        $objectives = findObjectiveFields($ppas_form_id);
        echo "<p>Objectives retrieved: " . count($objectives) . " found</p>";
        
                    // Extract activity narrative from correct field
            $activity_narrative = '';
            try {
                // First check if activity_narrative field exists in narrative_entries
                if ($narrative_entries && !empty($narrative_entries['activity_narrative'])) {
                    $activity_narrative = $narrative_entries['activity_narrative'];
                    echo "<p>Activity narrative fetched from narrative_entries.activity_narrative</p>";
                } 
                // Then check narrative table
                else {
                    $narrative_query = "SELECT activity_narrative FROM narrative WHERE ppas_form_id = :id LIMIT 1";
                    $narrative_stmt = $conn->prepare($narrative_query);
                    $narrative_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
                    $narrative_stmt->execute();
                    $narrative_data = $narrative_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($narrative_data && !empty($narrative_data['activity_narrative'])) {
                        $activity_narrative = $narrative_data['activity_narrative'];
                        echo "<p>Activity narrative fetched from narrative.activity_narrative</p>";
                    } 
                    // Fallback to gad_proposals as last resort
                    else {
                        $proposal_query = "SELECT description FROM gad_proposals WHERE ppas_form_id = :id LIMIT 1";
                        $proposal_stmt = $conn->prepare($proposal_query);
                        $proposal_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
                        $proposal_stmt->execute();
                        $proposal_data = $proposal_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($proposal_data && !empty($proposal_data['description'])) {
                            $activity_narrative = $proposal_data['description'];
                            echo "<p>Activity narrative fetched from gad_proposals.description</p>";
                        } else {
                            $activity_narrative = "No narrative available for this activity.";
                            echo "<p>Using default activity narrative</p>";
                        }
                    }
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>Error fetching activity narrative: " . $e->getMessage() . "</p>";
                $activity_narrative = "Error retrieving narrative.";
            }
        
        echo "</div>";

        // Process personnel data
        $personnel_by_role = [
            'project_leaders' => isset($personnel_data['project_leaders']) ? $personnel_data['project_leaders'] : [],
            'assistant_project_leaders' => isset($personnel_data['assistant_project_leaders']) ? $personnel_data['assistant_project_leaders'] : [],
            'project_staff' => isset($personnel_data['project_staff']) ? $personnel_data['project_staff'] : []
        ];
        
        // Prepare data from PPAS form
        $data = $ppas_form;
        
        // Decode JSON fields if they are JSON strings
        $decoded_data = [];
        foreach ($data as $key => $value) {
            if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
                try {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $decoded_data[$key] = $decoded;
                } else {
                        $decoded_data[$key] = $value;
                }
            } catch (Exception $e) {
                    $decoded_data[$key] = $value;
                }
            } else {
                $decoded_data[$key] = $value;
            }
        }
        
        // Organize narrative sections from narrative_entries table correctly
        $narrative_sections = [
            'background_rationale' => $narrative_entries['background_rationale'] ?? $narrative_entries['background'] ?? '',
            'description_participants' => $narrative_entries['description_participants'] ?? $narrative_entries['participants'] ?? '',
            'narrative_topics' => $narrative_entries['narrative_topics'] ?? $narrative_entries['topics'] ?? '',
            'expected_results' => $narrative_entries['expected_results'] ?? $narrative_entries['results'] ?? '',
            'lessons_learned' => $narrative_entries['lessons_learned'] ?? $narrative_entries['lessons'] ?? '',
            'what_worked' => $narrative_entries['what_worked'] ?? '',
            'issues_concerns' => $narrative_entries['issues_concerns'] ?? $narrative_entries['issues'] ?? '',
            'recommendations' => $narrative_entries['recommendations'] ?? ''
        ];
        
        // Process photo paths from narrative_entries if available
        $photo_paths = [];
        if ($narrative_entries && isset($narrative_entries['photo_paths'])) {
            if (is_string($narrative_entries['photo_paths'])) {
                try {
                    $photo_paths = json_decode($narrative_entries['photo_paths'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $photo_paths = [];
                    }
                } catch (Exception $e) {
                    $photo_paths = [];
                }
            } else if (is_array($narrative_entries['photo_paths'])) {
                $photo_paths = $narrative_entries['photo_paths'];
            }
        }
        
        // Additional sections being fetched (II-XI)
        $date_venue = [
            'date' => $ppas_form['start_date'] ?? '',
            'end_date' => $ppas_form['end_date'] ?? '',
            'venue' => $ppas_form['location'] ?? ''
        ];
        
        $mode_of_delivery = $ppas_form['mode_of_delivery'] ?? '';
        
        $project_team = $personnel_by_role;
        
        // More robust partner office handling with multiple fallbacks
        $partner_office = null;
        // First try narrative_entries.partner_office
        if (isset($narrative_entries['partner_office']) && !empty($narrative_entries['partner_office'])) {
            $partner_office = $narrative_entries['partner_office'];
            echo "<p>Partner office from narrative_entries.partner_office: " . (is_array($partner_office) ? json_encode($partner_office) : $partner_office) . "</p>";
        } 
        // Then try decoded_data.office_college_organization
        else if (isset($decoded_data['office_college_organization']) && !empty($decoded_data['office_college_organization'])) {
            $partner_office = $decoded_data['office_college_organization'];
            echo "<p>Partner office from decoded_data.office_college_organization</p>";
        }
        // Then try gad_proposals.partner_office
        else {
            try {
                $partner_sql = "SELECT partner_office FROM gad_proposals WHERE ppas_form_id = :id LIMIT 1";
                $partner_stmt = $conn->prepare($partner_sql);
                $partner_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
                $partner_stmt->execute();
                $partner_data = $partner_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($partner_data && !empty($partner_data['partner_office'])) {
                    $partner_office = $partner_data['partner_office'];
                    echo "<p>Partner office found in gad_proposals.partner_office: " . $partner_office . "</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error fetching partner office: " . $e->getMessage() . "</p>";
            }
        }
        
        // Default if still null
        if ($partner_office === null) {
            $partner_office = 'Not specified';
            echo "<p>No partner office found - using default</p>";
        }
        
        $type_participants = isset($narrative_entries['participants']) ? $narrative_entries['participants'] : '';
        
        // Get general and specific objectives from the database
        $objectives_data = [];
        
        // First try to get objectives from narrative_entries
        if (isset($narrative_entries['general_objectives'])) {
            $objectives_data[] = $narrative_entries['general_objectives'];
        }
        
        // Then try to get from ppas_forms
        try {
            $objectives_query = "SELECT general_objectives, specific_objectives FROM ppas_forms WHERE id = :id LIMIT 1";
            $objectives_stmt = $conn->prepare($objectives_query);
            $objectives_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
            $objectives_stmt->execute();
            $objectives_data_from_db = $objectives_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($objectives_data_from_db && !empty($objectives_data_from_db['general_objectives'])) {
                $objectives_data[] = $objectives_data_from_db['general_objectives'];
            }
            
            if ($objectives_data_from_db && !empty($objectives_data_from_db['specific_objectives'])) {
                $specific_objectives = json_decode($objectives_data_from_db['specific_objectives'], true);
                if (is_array($specific_objectives)) {
                    $objectives_data = array_merge($objectives_data, $specific_objectives);
                }
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Error fetching objectives: " . $e->getMessage() . "</p>";
        }
        
        // If no objectives were found, use what was passed to the function or default to empty array
        if (empty($objectives_data) && isset($objectives)) {
            $objectives_data = $objectives;
        }
        
        $evaluation_results = [];
        // First try from narrative_entries
        if ($narrative_entries && isset($narrative_entries['activity_ratings'])) {
            if (is_string($narrative_entries['activity_ratings'])) {
                try {
                    $evaluation_results = json_decode($narrative_entries['activity_ratings'], true);
                } catch (Exception $e) {
                    $evaluation_results = [];
                }
            } else if (is_array($narrative_entries['activity_ratings'])) {
                $evaluation_results = $narrative_entries['activity_ratings'];
            }
        } 
        // Then try from narrative table
        else if (isset($narrative_data) && isset($narrative_data['activity_ratings'])) {
            if (is_string($narrative_data['activity_ratings'])) {
                try {
                    $evaluation_results = json_decode($narrative_data['activity_ratings'], true);
                } catch (Exception $e) {
                    $evaluation_results = [];
                }
            } else if (is_array($narrative_data['activity_ratings'])) {
                $evaluation_results = $narrative_data['activity_ratings'];
            }
        }
        
        $timeliness_ratings = [];
        // First try from narrative_entries
        if ($narrative_entries && isset($narrative_entries['timeliness_ratings'])) {
            if (is_string($narrative_entries['timeliness_ratings'])) {
                try {
                    $timeliness_ratings = json_decode($narrative_entries['timeliness_ratings'], true);
                } catch (Exception $e) {
                    $timeliness_ratings = [];
                }
            } else if (is_array($narrative_entries['timeliness_ratings'])) {
                $timeliness_ratings = $narrative_entries['timeliness_ratings'];
            }
        }
        // Then try from narrative table
        else if (isset($narrative_data) && isset($narrative_data['timeliness_ratings'])) {
            if (is_string($narrative_data['timeliness_ratings'])) {
                try {
                    $timeliness_ratings = json_decode($narrative_data['timeliness_ratings'], true);
                } catch (Exception $e) {
                    $timeliness_ratings = [];
                }
            } else if (is_array($narrative_data['timeliness_ratings'])) {
                $timeliness_ratings = $narrative_data['timeliness_ratings'];
            }
        }
        
        $narrative_of_project = $activity_narrative;
        
        // Enhanced financial requirements handling
        $financial_requirements = [
            'budget' => 0,
            'ps_attribution' => 0,
            'source_of_fund' => []
        ];
        
        // First try ppas_forms data
        if (isset($ppas_form['approved_budget']) && !empty($ppas_form['approved_budget'])) {
            $financial_requirements['budget'] = floatval($ppas_form['approved_budget']);
            echo "<p>Budget from ppas_forms.approved_budget: " . $financial_requirements['budget'] . "</p>";
        }
        
        if (isset($ppas_form['ps_attribution']) && !empty($ppas_form['ps_attribution'])) {
            $financial_requirements['ps_attribution'] = floatval($ppas_form['ps_attribution']);
            echo "<p>PS Attribution from ppas_forms.ps_attribution: " . $financial_requirements['ps_attribution'] . "</p>";
        }
        
        if (isset($ppas_form['source_of_fund']) && !empty($ppas_form['source_of_fund'])) {
            try {
                $source = is_string($ppas_form['source_of_fund']) ? 
                    json_decode($ppas_form['source_of_fund'], true) : 
                    $ppas_form['source_of_fund'];
                    
                if (is_array($source)) {
                    $financial_requirements['source_of_fund'] = $source;
                } else {
                    $financial_requirements['source_of_fund'] = [$ppas_form['source_of_fund']];
                }
                echo "<p>Source of fund from ppas_forms.source_of_fund</p>";
            } catch (Exception $e) {
                $financial_requirements['source_of_fund'] = [$ppas_form['source_of_fund']];
                echo "<p>Error decoding source of fund: " . $e->getMessage() . "</p>";
            }
        }
        
        // If still no budget, try gad_proposals table
        if ($financial_requirements['budget'] == 0) {
            try {
                $budget_sql = "SELECT total_budget, budget_source FROM gad_proposals WHERE ppas_form_id = :id LIMIT 1";
                $budget_stmt = $conn->prepare($budget_sql);
                $budget_stmt->bindParam(':id', $ppas_form_id, PDO::PARAM_INT);
                $budget_stmt->execute();
                $budget_data = $budget_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($budget_data && !empty($budget_data['total_budget'])) {
                    $financial_requirements['budget'] = floatval($budget_data['total_budget']);
                    echo "<p>Budget found in gad_proposals.total_budget: " . $financial_requirements['budget'] . "</p>";
                    
                    if (!empty($budget_data['budget_source'])) {
                        try {
                            $source = is_string($budget_data['budget_source']) ? 
                                json_decode($budget_data['budget_source'], true) : 
                                $budget_data['budget_source'];
                                
                            if (is_array($source)) {
                                $financial_requirements['source_of_fund'] = $source;
                            } else {
                                $financial_requirements['source_of_fund'] = [$budget_data['budget_source']];
                            }
                            echo "<p>Source of fund from gad_proposals.budget_source</p>";
                        } catch (Exception $e) {
                            $financial_requirements['source_of_fund'] = [$budget_data['budget_source']];
                            echo "<p>Error decoding gad_proposals budget source: " . $e->getMessage() . "</p>";
                        }
                    }
                }
            } catch (Exception $e) {
                echo "<p>Error fetching budget information: " . $e->getMessage() . "</p>";
            }
        }
        
        // Default source of fund if still empty
        if (empty($financial_requirements['source_of_fund'])) {
            $financial_requirements['source_of_fund'] = ['GAD'];
            echo "<p>No source of fund found - using GAD default</p>";
        }
        
        $photo_documentation = [];
        foreach ($photo_paths as $path) {
            if (is_string($path)) {
                $photo_documentation[] = [
                    'path' => $path,
                    'caption' => $narrative_entries['photo_caption'] ?? ''
                ];
            }
        }
        
        // Build the final result object
        $result = [
            'id' => $ppas_form_id,
            'activity' => $data['activity'] ?? 'Unknown Activity',
            'location' => $data['location'] ?? 'Unknown Location',
            'date_range' => [
                'start' => $data['start_date'] ?? '',
                'end' => $data['end_date'] ?? '',
                'start_time' => $data['start_time'] ?? '',
                'end_time' => $data['end_time'] ?? ''
            ],
            
            // Personnel information
            'personnel' => $personnel_by_role,
            
            // Narrative content
            'activity_narrative' => $activity_narrative,
            'narrative_sections' => $narrative_sections,
            
            // Financial information
            'financial_requirements' => $data['approved_budget'] ?? 0,
            'source_of_budget' => isset($data['source_of_fund']) ? json_decode($data['source_of_fund'], true) : 'Not specified',
            'ps_attribution' => $data['ps_attribution'] ?? 0,
            
            // Media and additional info
            'activity_images' => 
                // First try photo_paths from narrative_entries
                isset($narrative_entries['photo_paths']) ? 
                array_map(function($path) {
                    // Clean up JSON string quotes if present and ensure proper path format
                    if (is_string($path)) {
                        $path = trim($path, '"');
                        // If path doesn't already include the photos/ prefix, add it
                        if (strpos($path, 'photos/') !== 0) {
                            return 'photos/' . $path;
                        }
                    }
                    return $path;
                    }, json_decode($narrative_entries['photo_paths'], true) ?: []) 
                // Then try activity_images from narrative
                : (isset($narrative_data['activity_images']) ? 
                    (is_string($narrative_data['activity_images']) ?
                        array_map(function($path) {
                            if (is_string($path)) {
                                $path = trim($path, '"');
                                if (strpos($path, 'photos/') !== 0) {
                                    return 'photos/' . $path;
                                }
                            }
                            return $path;
                        }, json_decode($narrative_data['activity_images'], true) ?: [])
                        : $narrative_data['activity_images']) 
                    : []),
            'extension_service_agenda' => isset($narrative_entries['extension_service_agenda']) ? 
                (is_string($narrative_entries['extension_service_agenda']) ? 
                    explode(',', $narrative_entries['extension_service_agenda']) : 
                    $narrative_entries['extension_service_agenda']) : 
                (isset($narrative_entries['gender_issue']) ? 
                    explode(',', $narrative_entries['gender_issue']) : []),
            'partner_agency' => 'Not specified', // No direct equivalent in new schema
            'leader_tasks' => [], // No direct equivalent in new schema
            'assistant_tasks' => [], // No direct equivalent in new schema
            'staff_tasks' => [], // No direct equivalent in new schema
            'sdg' => $decoded_data['sdgs'] ?? [],
            
            // Additional requested sections (II-XI)
            'date_venue' => $date_venue,
            'mode_of_delivery' => $mode_of_delivery,
            'project_team' => $project_team,
            'partner_office' => $partner_office,
            'type_participants' => $type_participants,
            'objectives' => $objectives_data,
            'evaluation_results' => $evaluation_results,
            'timeliness_ratings' => $timeliness_ratings,
            'narrative_of_project' => $narrative_of_project,
            'financial_requirements_detail' => $financial_requirements,
            'photo_documentation' => $photo_documentation
        ];
        
        return $result;
    } catch (Exception $e) {
        // Show error in UI for debugging
        echo "<div style='background:#ffeeee;padding:10px;margin:10px;border:1px solid #ffaaaa;'>";
        echo "<h3>Error Fetching Data</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
        
        error_log("Error in getNarrativeData: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'error' => 'Failed to fetch narrative data: ' . $e->getMessage(),
            'ppas_form_id' => $ppas_form_id
        ];
    }
}

// Debug helper function to find objective fields in the database
function findObjectiveFields($ppas_form_id) {
    try {
        $conn = getConnection();
        $results = [];
        
        // Get all tables in the database
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Look for tables that might contain objective data
        foreach ($tables as $table) {
            // Get columns for this table
            $stmt = $conn->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Check if table has potentially relevant columns
            $relevant_columns = array_filter($columns, function($col) {
                return stripos($col, 'objective') !== false || 
                       $col === 'id' || 
                       stripos($col, 'ppas') !== false;
            });
            
            if (!empty($relevant_columns)) {
                $results[$table] = ['columns' => $relevant_columns];
                
                // If this table has id or ppas_form_id column, query for our specific record
                if (in_array('id', $columns) || in_array('ppas_form_id', $columns)) {
                    try {
                        $where_clause = in_array('ppas_form_id', $columns) ? 
                            "WHERE ppas_form_id = :id" : 
                            (in_array('id', $columns) && $table !== 'users' ? "WHERE id = :id" : "LIMIT 0");
                        
                        if ($where_clause !== "LIMIT 0") {
                            $query = "SELECT * FROM `$table` $where_clause LIMIT 1";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':id', $ppas_form_id);
                            $stmt->execute();
                            $data = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($data) {
                                // Extract only objective-related fields to keep the log smaller
                                $objective_data = array_filter($data, function($value, $key) {
                                    return stripos($key, 'objective') !== false && !empty($value);
                                }, ARRAY_FILTER_USE_BOTH);
                                
                                if (!empty($objective_data)) {
                                    $results[$table]['data'] = $objective_data;
                                }
                            }
                        }
    } catch (Exception $e) {
                        // Skip if query fails
                        $results[$table]['error'] = $e->getMessage();
                    }
                }
            }
        }
        
        error_log("[DEBUG] Potential objective fields in database: " . json_encode($results));
        return $results;
    } catch (Exception $e) {
        error_log("[ERROR] Error searching for objective fields: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

// Get the PPAS form ID from the URL for direct querying
$ppas_form_id = isset($_GET['id']) ? $_GET['id'] : null;

// Find objective fields if we have a PPAS form ID
if ($ppas_form_id) {
    $objective_fields = findObjectiveFields($ppas_form_id);
}

// Check if user is logged in, etc...
// ... existing code ...
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Reports - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Global Styles -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
        }
        
        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e9ecef;
            --sidebar-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --hover-color: rgba(106, 27, 154, 0.1);
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
        }

        body.fade-out {
    opacity: 0;
}

        

        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 20px;
            background: var(--sidebar-bg);
            color: var(--text-primary);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1;
        }

        .main-content {
    margin-left: calc(var(--sidebar-width) + 20px);
    padding: 15px;
    height: calc(100vh - 30px);
    max-height: calc(100vh - 30px);
    background: var(--bg-primary);
    border-radius: 20px;
    position: relative;
    overflow-y: auto;
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.main-content::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Chrome, Safari and Opera */
body::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Firefox */
html {
    scrollbar-width: none;
}

        .nav-link {
            color: var(--text-primary);
            padding: 10px 15px;
            border-radius: 12px;
            margin-bottom: 3px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 0 2px 2px 0;
        }

        /* Add hover state for active nav links in dark mode */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white;
        }

        .nav-item {
            position: relative;
        }
        
        .nav-item .dropdown-menu .dropdown-item {
            padding: 6px 48px; 
        }

        .nav-item .dropdown-menu {
            position: static !important;
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            padding: 8px 0;
            margin: 5px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            transform: none !important;
            display: none;
            overflow: visible;
            max-height: none;
        }

        /* Dropdown submenu styles */
        /* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -8px;
    margin-left: 1px;
    border-radius: 0 6px 6px 6px;
    display: none;
}

/* Add click-based display */
.dropdown-submenu.show > .dropdown-menu {
    display: block;
}

.approval-link {
    margin-top: 5px; 
    margin-bottom: 10px; 
}

.bottom-controls {
    margin-top: 15px; 
}

.dropdown-submenu > a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: var(--text-primary);
    margin-top: 5px;
    margin-right: -10px;
}

/* Update hover effect for arrow */
.dropdown-submenu.show > a:after {
    border-left-color: var(--accent-color);
}

/* Mobile styles for dropdown submenu */
@media (max-width: 991px) {
    .dropdown-submenu .dropdown-menu {
        position: static !important;
        left: 0;
        margin-left: 20px;
        margin-top: 0;
        border-radius: 0;
        border-left: 2px solid var(--accent-color);
    }
    
    .dropdown-submenu > a:after {
        transform: rotate(90deg);
        margin-top: 8px;
    }
}
        
        /* End of dropdown submenu styles */

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 8px 48px;
            color: var(--text-primary);
            position: relative;
            opacity: 0.85;
            background: transparent;
        }

        .nav-item .dropdown-menu .dropdown-item::before {
            content: '•';
            position: absolute;
            left: 35px;
            color: var(--accent-color);
        }

        .nav-item .dropdown-menu .dropdown-item:hover {
            background: var(--hover-color);
            color: white;
            opacity: 1;
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .logo-container {
            padding: 20px 0;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .logo-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: -25px;
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .datetime-container {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--horizontal-bar);
        }

        .datetime-container .date {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .datetime-container .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.4) transparent;
            overflow-x: hidden; 
        }

        .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.4);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.7);
        }

        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .dropdown-menu {
            outline: none !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .dropdown-item:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Bottom controls container */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Logout button styles */
        .logout-button {
            flex: 1;
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Theme switch button */
        .theme-switch-button {
            width: 46.5px;
            height: 50px;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

                /* Light theme specific styles for bottom controls */
                [data-bs-theme="light"] .logout-button,
        [data-bs-theme="light"] .theme-switch-button {
            background: #f2f2f2;
            border-width: 1.5px;
        }

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .logout-button:active,
        .theme-switch-button:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 6px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Theme switch button icon size */
        .theme-switch-button i {
            font-size: 1rem; 
        }

        .theme-switch-button:hover i {
            transform: scale(1.1);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .page-title i {
            color: var(--accent-color);
            font-size: 2.2rem;
        }

        .page-title h2 {
            margin: 0;
            font-weight: 600;
        }

        .show>.nav-link {
            background: transparent !important;
            color: var(--accent-color) !important;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 240px;
            }

            body {
                padding: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                left: 0;
                top: 0;
                height: 100vh;
                position: fixed;
                padding-top: 70px;
                border-radius: 0;
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                border-radius: 0;
                box-shadow: none;
            }

            .mobile-nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--card-bg);
                border: none;
                border-radius: 8px;
                color: var(--text-primary);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                cursor: pointer;
            }

            .mobile-nav-toggle:hover {
                background: var(--hover-color);
                color: var(--accent-color);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .theme-switch {
                position: fixed;
                bottom: 30px;
                right: 30px;
            }

        }

        @media (max-width: 576px) {
            :root {
                --sidebar-width: 100%;
            }

            .sidebar {
                left: 0;
                top: 0;
                width: 100%;
                height: 100vh;
                padding-top: 60px;
            }

            .mobile-nav-toggle {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
            }

            .theme-switch-button {
                padding: 8px 15px;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                margin-top: 10px;
            }

            .page-title h2 {
                font-size: 1.5rem;
            }
        }

        /* Modern Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #ppasForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
        }

        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            overflow: hidden;
        }

        /* Fix for card header corners */
        .card-header {
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        .card-title {
            margin-bottom: 0;
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1 1 200px;
        }


        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state */
        #addBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

#viewBtn {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

#viewBtn:hover {
    background: #0d6efd;
    color: white;
}

/* Optional: Add disabled state for view button */
#viewBtn.disabled {
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Add these styles for disabled buttons */
.btn-disabled {
    border-color: #6c757d !important;
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Dark mode styles */
[data-bs-theme="dark"] .btn-disabled {
    background-color: #495057 !important;
    border-color: #495057 !important;
    color: #adb5bd !important;
}

.swal-blur-container {
    backdrop-filter: blur(5px);
}

/* Add print-specific styles */
@media print {
    @page {
        size: 8.5in 13in;
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Force ALL colors to black - no exceptions */
    *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
    [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
    [style*="color: brown"], [style*="color: blue"], [style*="color: red"], 
    .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, .sustainability-plan li,
    .signature-label, .signature-position {
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* First page footer with tracking number and page number */
    @page:first {
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        border-top: 3px solid black !important;
        border-bottom: 3px solid black !important;
    }
    
    /* Ensure proper spacing for the footer */
    .proposal-container {
        margin-bottom: 1.5cm !important;
    }

    /* Disable "keep-with-next" behavior */
    * {
        orphans: 2 !important;
        widows: 2 !important;
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Specific overrides for elements that should break */
    p, h1, h2, h3, h4, h5, h6, li, tr, div {
        page-break-after: auto !important;
        page-break-before: auto !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
        break-before: auto !important;
        break-after: auto !important;
    }
    
    /* Tables should break naturally */
    table {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    td, th {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    
    /* Override any avoid settings */
    [style*="page-break-inside: avoid"], 
    [style*="break-inside: avoid"] {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        /* Remove border */
        border: none;
        box-sizing: border-box;
        min-height: calc(100% - 2cm);
        width: calc(100% - 3.81cm);
        margin-top: 1.52cm !important;
        margin-bottom: 2cm !important;
        margin-left: 1.78cm !important;
        margin-right: 2.03cm !important;
        background-clip: padding-box;
        box-shadow: none;
    }
}

/* Add these styles for compact form */
.compact-form .form-group {
    margin-bottom: 0.5rem !important;
}

.compact-form label {
    margin-bottom: 0.25rem !important;
    font-size: 0.85rem !important;
}

.compact-form .form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.compact-form .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Additional styles to match get_gpb_report.php */
.compact-form select.form-control-sm,
.compact-form input.form-control-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

#campus, #year, #proposal {
    font-size: 1rem !important;
    height: 38px !important;
}

.compact-form .btn-sm {
    font-size: 1rem !important;
    height: 38px !important;
    padding: 0.375rem 0.75rem !important;
}

.form-group label, .form-label {
    font-size: 1rem !important;
    margin-bottom: 0.5rem !important;
}

/* Make the card more compact */
.card {
    min-height: auto !important;
}
    </style>
    <style>
        /* Specific styles for GAD Proposal preview */
        .proposal-container {
            border: 1px solid #000;
            padding: 20px;
            margin: 20px auto;
            max-width: 1100px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            background-color: #fff;
            color: #000;
        }

        /* Header table styles */
        .header-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 5px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        /* Section heading styles */
        .section-heading {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        /* Table styles */
        .data-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        /* Checkbox styles */
        .checkbox-container {
            display: flex;
            justify-content: center;
            margin: 10px 0;
        }

        .checkbox-option {
            margin: 0 20px;
            font-size: 12pt;
        }

        /* Signature table styles */
        .signatures-table {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border-collapse: collapse !important;
            page-break-inside: avoid !important;
            position: relative !important;
            left: 0 !important;
            right: 0 !important;
        }

        .signatures-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: top;
            height: 80px;
        }

        /* Heading styles */
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* List styles */
        .proposal-container ol, .proposal-container ul {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 25px;
        }

        .proposal-container li {
            margin-bottom: 2px;
        }

        /* Responsibilities section */
        .responsibilities {
            margin-left: 20px;
        }

        /* Sustainability Plan - blue text */
        .sustainability-plan {
            color: blue;
        }

        .sustainability-plan ol li {
            color: blue;
        }

        /* Signature name styles */
        .signature-name {
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 0;
        }

        .signature-position {
            color: blue !important;
            margin-top: 0;
        }

        .signature-label {
            font-weight: bold;
            color: brown !important;
        }

        /* Page numbering and tracking */
        .page-footer {
            text-align: right;
            margin-top: 20px;
            font-size: 10pt;
        }

        /* Gantt chart cell styling */
        .gantt-filled {
            background-color: black !important;
        }

        /* Brown text for labels */
        .brown-text {
            color: brown !important;
        }

        /* Add page break styles */
        .page-break {
            page-break-before: always;
        }
        
        /* Print-specific styles */
        @media print {
            @page {
                size: 8.5in 13in;
                margin-top: 1.52cm;
                margin-bottom: 2cm;
                margin-left: 1.78cm;
                margin-right: 2.03cm;
                border-top: 3px solid black !important;
                border-bottom: 3px solid black !important;
            }
            
            /* Force ALL colors to black - no exceptions */
            *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
            [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
            [style*="color: brown"], [style*="color: blue"], [style*="color: red"], 
            .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, .sustainability-plan li,
            .signature-label, .signature-position {
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* First page footer with tracking number and page number */
            @page:first {
                margin-top: 1.52cm;
                margin-bottom: 2cm;
                margin-left: 1.78cm;
                margin-right: 2.03cm;
                border-top: 3px solid black !important;
                border-bottom: 3px solid black !important;
            }
            
            /* Ensure proper spacing for the footer */
            .proposal-container {
                margin-bottom: 1.5cm !important;
            }

            /* Disable "keep-with-next" behavior */
            * {
                orphans: 2 !important;
                widows: 2 !important;
                page-break-after: auto !important;
                page-break-before: auto !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                break-before: auto !important;
                break-after: auto !important;
            }
            
            /* Specific overrides for elements that should break */
            p, h1, h2, h3, h4, h5, h6, li, tr, div {
                page-break-after: auto !important;
                page-break-before: auto !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                break-before: auto !important;
                break-after: auto !important;
            }
            
            /* Tables should break naturally */
            table {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            
            td, th {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            
            /* Override any avoid settings */
            [style*="page-break-inside: avoid"], 
            [style*="break-inside: avoid"] {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                /* Remove border */
                border: none;
                box-sizing: border-box;
                min-height: calc(100% - 2cm);
                width: calc(100% - 3.81cm);
                margin-top: 1.52cm !important;
                margin-bottom: 2cm !important;
                margin-left: 1.78cm !important;
                margin-right: 2.03cm !important;
                background-clip: padding-box;
                box-shadow: none;
            }
        }

        /* Specific dark mode styles */
        [data-bs-theme="dark"] .proposal-container {
            background-color: #333 !important;
            color: #fff !important;
            border: 1px solid #555 !important;
        }

        [data-bs-theme="dark"] .header-table td,
        [data-bs-theme="dark"] .data-table th,
        [data-bs-theme="dark"] .data-table td,
        [data-bs-theme="dark"] .signatures-table td {
            border-color: #555 !important;
            background-color: #333 !important;
            color: #fff !important;
        }

        /* Override colors for dark mode */
        @media (prefers-color-scheme: dark) {
            [data-bs-theme="dark"] .sustainability-plan,
            [data-bs-theme="dark"] .sustainability-plan * {
                color: #5eb5ff !important;
            }
            
            [data-bs-theme="dark"] .signature-position {
                color: #5eb5ff !important;
            }
            
            [data-bs-theme="dark"] .signature-label,
            [data-bs-theme="dark"] .brown-text {
                color: #ff9d7d !important;
            }
        }

        /* Remove page number line on last page */
        @page:last {
            border-bottom: none !important;
        }

        /* Special styling for the approval link - only visible to Central users */
        .approval-link {
            background-color: var(--accent-color);
            color: white !important;
            border-radius: 12px;
            margin-top: 10px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .approval-link::before {
            content: '';
            position: absolute;
            right: -20px;
            top: 0;
            width: 40px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: skewX(-25deg);
            opacity: 0.7;
            transition: all 0.5s ease;
        }

        .approval-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        .approval-link:hover::before {
            right: 100%;
        }

        /* Ensure the icon in approval link stands out */
        .approval-link i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .approval-link:hover i {
            transform: scale(1.2);
        }

        /* Dark theme adjustments for approval link */
        [data-bs-theme="dark"] .approval-link {
            background-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .approval-link:hover {
            background-color: var(--accent-hover) !important;
        }

        /* Revamped active state - distinctive but elegant */
        .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid white;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: visible;
        }

        .approval-link.active::before {
            display: none;
        }

        .approval-link.active i {
            color: white;
        }

        /* Dark theme revamped active state */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid #e0b6ff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: #e0b6ff;
        }

        /* Fixed active state using accent color */
        .approval-link.active {
            background-color: transparent !important;
            color: var(--accent-color) !important;
            border: 2px solid var(--accent-color);
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .approval-link.active i {
            color: var(--accent-color);
        }

        /* Dark theme with accent color */
        [data-bs-theme="dark"] .approval-link.active {
            background-color: transparent !important;
            color: white !important;
            border: 2px solid var(--accent-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }

        [data-bs-theme="dark"] .approval-link.active i {
            color: var(--accent-color);
        }

/* Notification Badge */
.notification-badge {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Dark mode support */
[data-bs-theme="dark"] .notification-badge {
    background-color: #ff5c6c;
}

/* Active state styling */
.nav-link.active .notification-badge {
    background-color: white;
    color: var(--accent-color);
}

    </style>
</head>
<body>
    <script>
        // Immediately disable all buttons as soon as the page loads
        window.onload = function() {
            for (let quarter = 1; quarter <= 4; quarter++) {
                const printBtn = document.getElementById(`printBtn${quarter}`);
                const exportBtn = document.getElementById(`exportBtn${quarter}`);
                if (printBtn) printBtn.disabled = true;
                if (exportBtn) exportBtn.disabled = true;
            }
        };
    </script>

    <!-- Mobile Navigation Toggle -->
    <button class="mobile-nav-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo-title">GAD SYSTEM</div>
            <div class="logo-image">
                <img src="../images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
        <div class="datetime-container">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="nav-content">
        <nav class="nav flex-column">
                <a href="../dashboard/dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                        <li><a class="dropdown-item" href="../signatory/sign.php">Signatory</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> GPB
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">Data Entry</a></li>
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Generate Form</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link active dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice me-2"></i> PPAs
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../ppas_form/ppas.php">Data Entry</a></li>
                        <li><a class="dropdown-item" href="../gad_proposal/gad_proposal.php">GAD Proposal</a></li>
                        <li><a class="dropdown-item" href="../gad_narrative/gad_narrative.php">GAD Narrative</a></li>
                        <li><a class="dropdown-item" href="../extension_proposal/extension_proposal.php">Extension Proposal</a></li>
                        <li><a class="dropdown-item" href="../extension_narrative/extension_narrative.php">Extension Narrative</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">                       
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly Report</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib_reports/ps.php">PS Attribution</a></li>
                        <li><a class="dropdown-item" href="../annual_reports/annual_report.php">Annual Report</a></li>
                    </ul>
                </div>
                <?php 
$currentPage = basename($_SERVER['PHP_SELF']);
if($isCentral): 
?>
<a href="../approval/approval.php" class="nav-link approval-link">
    <i class="fas fa-check-circle me-2"></i> Approval
    <span id="approvalBadge" class="notification-badge" style="display: none;">0</span>
</a>
<?php endif; ?>
            </nav>
        </div>
        <div class="bottom-controls">
            <a href="../index.php" class="logout-button" onclick="handleLogout(event)">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <button class="theme-switch-button" onclick="toggleTheme()">
                <i class="fas fa-sun" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <i class="fas fa-file-alt"></i>
            <h2>External  Print GAD Narrative</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4" style="min-height: auto; max-height: fit-content;">
            <div class="card-body py-3">
                <form id="reportForm" class="compact-form">
                    <div class="row align-items-start">
                        <div class="col-md-3">
                            <label for="campus" class="form-label"><i class="fas fa-university me-1"></i> Campus</label>
                            <select class="form-control" id="campus" required style="height: 38px;">
                                <option value="">Select Campus</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label"><i class="fas fa-calendar-alt me-1"></i> Year</label>
                            <select class="form-control" id="year" required style="height: 38px;">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="prepared_by" class="form-label"><i class="fas fa-user-edit me-1"></i> Prepared By Position</label>
                            <select class="form-control" id="prepared_by" disabled style="height: 38px;">
                                <option value="">Select Position</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="proposal" class="form-label"><i class="fas fa-file-alt me-1"></i> Proposal</label>
                            <div class="position-relative">
                                <input type="text" 
                                      class="form-control" 
                                      id="proposal" 
                                      placeholder="Search for a proposal..." 
                                      autocomplete="off"
                                      style="height: 38px;"
                                      disabled
                                      required>
                                <div id="proposalDropdown" class="dropdown-menu w-100" style="display:none; max-height: 150px; overflow-y: auto;"></div>
                                <input type="hidden" id="proposal_id">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Proposal Preview</h5>
                    <div class="btn-group">
                     
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                            </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Proposal content will be loaded here -->
                    <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus, year, and proposal to generate the preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            loadCampusOptions();
            
            // Handle form submission
            $('#reportForm').on('submit', function(e) {
                    e.preventDefault();
                const selectedProposalId = $('#proposal_id').val();
                console.log('Form submitted. Proposal ID:', selectedProposalId);
                
                if (!selectedProposalId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select a proposal first.'
                    });
                    return;
                }
                
                generateReport();
            });

            // Handle proposal search input
            let searchTimeout;
            $('#proposal').on('input', function() {
                const searchTerm = $(this).val();
                const selectedCampus = $('#campus').val();
                const selectedYear = $('#year').val();
                const selectedPosition = $('#prepared_by').val();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Clear proposal ID when input changes
                $('#proposal_id').val('');
                
                if (!selectedCampus || !selectedYear || !selectedPosition) {
                    console.log('Campus, Year, or Prepared By not selected');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select campus, year, and prepared by position first.'
                    });
                    return;
                }
                
                if (searchTerm.length < 1) {
                    $('#proposalDropdown').hide().empty();
                    return;
                }
                
                // Set new timeout
                searchTimeout = setTimeout(() => {
                    console.log('Searching for:', searchTerm);
                    $.ajax({
                        url: 'api/get_proposals.php',
                        method: 'GET',
                        data: {
                            search: searchTerm,
                            campus: selectedCampus,
                            year: selectedYear,
                            position: selectedPosition
                        },
                        dataType: 'json',
                        success: function(response) {
                            try {
                                console.log('Search response:', response);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                
                                // Make sure response is an object if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                if (response && response.status === 'success' && Array.isArray(response.data) && response.data.length > 0) {
                                    // Store proposals globally
                                    window.proposals = response.data;
                                    
                                    console.log('Found', response.data.length, 'proposals');
                                    
                                    // Add proposals to dropdown
                                    response.data.forEach(function(proposal) {
                                        const item = $('<div class="dropdown-item"></div>')
                                            .text(proposal.activity_title)
                                            .attr('data-id', proposal.id)
                                            .click(function() {
                                                // Set input value
                                                $('#proposal').val(proposal.activity_title);
                                                // Set hidden proposal_id
                                                $('#proposal_id').val(proposal.id);
                                                // Hide dropdown
                                                dropdown.hide();
                                                console.log('Selected proposal:', proposal.activity_title, 'with ID:', proposal.id);
                                                
                                                // Auto-generate report when proposal is selected
                                                generateReport();
                                            });
                                        
                                        dropdown.append(item);
                                    });
                                    
                                    // Show dropdown
                                    dropdown.show();
                                    console.log('Updated dropdown with', response.data.length, 'options');
                            } else {
                                    console.log('No proposals found - Response data:', JSON.stringify(response));
                                    // Show "no results" message
                                    dropdown.append('<div class="dropdown-item disabled">No proposals found</div>');
                                    dropdown.show();
                                }
                            } catch (error) {
                                console.error('Error processing response:', error);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                dropdown.append('<div class="dropdown-item disabled">Error processing response</div>');
                                dropdown.show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Search error:', error);
                            const dropdown = $('#proposalDropdown');
                            dropdown.empty();
                            dropdown.append('<div class="dropdown-item disabled">Error loading proposals</div>');
                            dropdown.show();
                        }
                    });
                }, 300);
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#proposal, #proposalDropdown').length) {
                    $('#proposalDropdown').hide();
                }
            });

            // Clear form button (optional - you can add this to your HTML)
            function clearProposalForm() {
                $('#proposal').val('');
                $('#proposal_id').val('');
                $('#proposalDropdown').hide();
            }

            // Handle proposal selection
            $('#proposal').on('change', function() {
                const selectedTitle = $(this).val();
                console.log('Selected title:', selectedTitle);
                
                const proposals = window.proposals || [];
                console.log('Available proposals:', proposals);
                
                const selectedProposal = proposals.find(p => p.activity_title === selectedTitle);
                console.log('Found proposal:', selectedProposal);

                if (selectedProposal) {
                    $('#proposal_id').val(selectedProposal.id);
                    console.log('Set proposal ID to:', selectedProposal.id);
                    } else {
                    $('#proposal_id').val('');
                    if (selectedTitle) {
                        console.log('No matching proposal found for title:', selectedTitle);
                    }
                }
            });

            // Handle campus change
            $('#campus').on('change', function() {
                const selectedCampus = $(this).val();
                if (selectedCampus) {
                    loadYearOptions();
                    
                    // Only show the placeholder if there's no existing preview content
                    if ($('#reportPreview').is(':empty')) {
                        $('#reportPreview').html(`
                            <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>Select a campus, year, and proposal to generate the preview</p>
                            </div>
                        `);
                    }
                } else {
                    $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                }
            });
            
            // Handle year change
            $('#year').on('change', function() {
                const selectedYear = $(this).val();
                
                if (selectedYear) {
                    // Enable positions dropdown
                    loadPositionOptions();
                } else {
                    // Reset subsequent fields
                    $('#prepared_by').val('').prop('disabled', true);
                    $('#proposal').val('').prop('disabled', true);
                    $('#proposal_id').val('');
                }
                
                clearProposalForm();
            });
            
            // Add handler for prepared_by change
            $('#prepared_by').on('change', function() {
                const selectedPosition = $(this).val();
                if (selectedPosition) {
                    $('#proposal').prop('disabled', false);
                    
                    // If a proposal is already selected, regenerate the report with the new position
                    const selectedProposalId = $('#proposal_id').val();
                    if (selectedProposalId) {
                        console.log('Prepared By changed, regenerating report with new position:', selectedPosition);
                        // Show loading indicator
                        $('#reportPreview').html(`
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Refreshing narrative report...</p>
                            </div>
                        `);
                        
                        // Regenerate report with new position
                        setTimeout(() => {
                            generateReport();
                        }, 300);
                    }
                } else {
                    $('#proposal').val('').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });
        });
        
        // Load campus options
        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        campusSelect.empty().append('<option value="">Select Campus</option>');
                        if (response.status === 'success' && response.data) {
                            console.log('Available campuses:', response.data);
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                        }
                    });
                }
                        campusSelect.prop('disabled', false);
                        
                        // Add a change event listener to the campus dropdown
                        campusSelect.off('change').on('change', function() {
                            console.log("Campus changed to:", $(this).val());
                            const selectedCampus = $(this).val();
                            
                            if (selectedCampus) {
                                loadYearOptions();
                                // Ensure the placeholder is visible
                                $('#reportPreview').html(`
                                    <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <p>Select a campus, year, and proposal to generate the preview</p>
                                    </div>
                                `);
                            } else {
                                $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                                $('#proposal').val(null).trigger('change').prop('disabled', true);
                                $('#proposal_id').val('');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:', error);
                        campusSelect.empty().append('<option value="">Error loading campuses</option>');
                    }
                });
            } else {
                campusSelect.empty().append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true);
                loadYearOptions();
            }
        }

        // Load year options
        function loadYearOptions() {
            const yearSelect = $('#year');
            const selectedCampus = $('#campus').val();
            
            yearSelect.prop('disabled', true);
            yearSelect.html('<option value="">Loading years...</option>');
            
            $.ajax({
                url: 'api/get_proposal_years.php',
                method: 'GET',
                data: { campus: selectedCampus },
                dataType: 'json',
                success: function(response) {
                    console.log('Year response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    
                    if (response.status === 'error') {
                        console.error('API Error:', response.message);
                        yearSelect.html(`<option value="">${response.message || 'Error loading years'}</option>`);
                        
                        // Show error to user
                    Swal.fire({
                        icon: 'error',
                            title: 'Error Loading Years',
                            text: response.message || 'Failed to load year data. Please ensure you are logged in.',
                            confirmButtonColor: '#6c757d'
                        });
                        return;
                    }
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        response.data.sort((a, b) => b.year - a.year).forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });
                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.html('<option value="">No years available</option>');
                        
                        // Optional: Display friendly message about no data
                Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: 'No proposal years found for this campus. You may need to create proposals first.',
                            confirmButtonColor: '#6c757d'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load years. Please try again.';
                    
                    // Try to parse error message from response if possible
                    try {
                        const responseJson = JSON.parse(xhr.responseText);
                        if (responseJson && responseJson.message) {
                            errorMessage = responseJson.message;
                        }
                        } catch (e) {
                        // Handle case where response is not JSON
                        if (xhr.status === 500) {
                            errorMessage = 'Server error. Please check database connection or contact administrator.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'API endpoint not found. Please check system configuration.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your connection.';
                        }
                    }
                    
                    yearSelect.html(`<option value="">Error: ${errorMessage}</option>`);
                    
                    // Show error to user
                Swal.fire({
                    icon: 'error',
                        title: 'Error Loading Years',
                        text: errorMessage,
                        footer: 'Status Code: ' + xhr.status,
                        confirmButtonColor: '#6c757d'
                    });
                }
            });
        }

        // Load position options for the "Prepared By" dropdown
        function loadPositionOptions() {
            const preparedBySelect = $('#prepared_by');
            preparedBySelect.empty();
            
            // Add options
            preparedBySelect.append(`
                <option value="">Select Position</option>
                <option value="Faculty">Faculty</option>
                <option value="Extension Coordinator">Extension Coordinator</option>
                <option value="GAD Head Secretariat">GAD Head Secretariat</option>
                <option value="Director, Extension Services">Director, Extension Services</option>
                <option value="Vice President for RDES">Vice President for RDES</option>
                <option value="Vice President for AF">Vice President for AF</option>
                <option value="Vice Chancellor for AF">Vice Chancellor for AF</option>
            `);
            
            // Enable the dropdown
            preparedBySelect.prop('disabled', false);
        }

        // Generate proposal report
        function generateReport() {
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedProposalId = $('#proposal_id').val();
            const selectedPosition = $('#prepared_by').val();
            
            if (!selectedCampus || !selectedYear || !selectedProposalId || !selectedPosition) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the proposal.'
                });
                return;
            }
            
            // Show loading state
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading narrative report...</p>
                </div>
            `);
            
            // Fetch narrative data from database
            $.ajax({
                url: 'api/get_narrative.php',
                method: 'GET',
                data: {
                    ppas_form_id: selectedProposalId,
                    campus: selectedCampus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        // Store the selected position in the report data
                        response.data.preparedByPosition = selectedPosition;
                        displayNarrativeReport(response.data);
                        } else {
                        $('#reportPreview').html(`
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p><strong>Error:</strong> ${response.message || 'Failed to load narrative data'}</p>
                                <p>Please make sure a narrative report exists for this PPAS form.</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p><strong>Error:</strong> Failed to load narrative data. Please try again.</p>
                            <p><small>Status: ${xhr.status} ${status}</small></p>
                        </div>
                    `);
                }
            });
        }

        // Print report function
        function printReport() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            let reportContent = $('#reportPreview').html();
            
            // SPECIAL FIX: Remove any empty divs or spaces that might cause empty boxes
            reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
            reportContent = reportContent.replace(/<pre[\s\S]*?<\/pre>/g, '');
            
            // Always force print to be in light mode for consistent output
            const printStyles = `
                <style>
                    @page {
                        size: 8.5in 13in;
                        margin-top: 1.52cm;
                        margin-bottom: 2cm;
                        margin-left: 1.78cm;
                        margin-right: 2.03cm;
                        border-top: 3px solid black !important;
                        border-bottom: 3px solid black !important;
                    }
                    
                    /* First page footer with tracking number */
                    @page:first {
                        @bottom-left {
                            content: "Tracking Number:___________________" !important;
                            font-family: 'Times New Roman', Times, serif !important;
                            font-size: 10pt !important;
                            color: black !important;
                        }
                        
                        @bottom-right {
                            content: "Page " counter(page) " of " counter(pages);
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 10pt;
                            color: black;
                        }
                    }
                    
                    /* Remove any inline tracking numbers */
                    div[style*="Tracking Number"] {
                        display: none !important;
                    }
                    
                    body {
                        background-color: white !important;
                        color: black !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 12pt !important;
                        line-height: 1.2 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    
                    /* Explicit tracking number at bottom of page */
                    .tracking-footer {
                        position: fixed !important;
                        bottom: 0.5cm !important;
                        left: 0 !important;
                        width: 100% !important;
                        text-align: center !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 10pt !important;
                        color: black !important;
                        z-index: 1000 !important;
                    }
                    
                    /* Proposal container */
                    .proposal-container {
                        background-color: white !important;
                        color: black !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: none !important;
                    }
                    
                    /* Container for signatures with no margins */
                    div[style*="width: 100%"] {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
                    }
                    
                    /* Disable "keep-with-next" behavior */
                    * {
                        orphans: 2 !important;
                        widows: 2 !important;
                        page-break-after: auto !important;
                        page-break-before: auto !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                        break-before: auto !important;
                        break-after: auto !important;
                    }
                    
                    /* Specific overrides for elements that should break */
                    p, h1, h2, h3, h4, h5, h6, li, tr, div {
                        page-break-after: auto !important;
                        page-break-before: auto !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                        break-before: auto !important;
                        break-after: auto !important;
                    }
                    
                    table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                    }
                    
                    td, th {
                        border: 1px solid black !important;
                        padding: 5px !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                        background-color: white !important;
                        color: black !important;
                    }
                    
                    /* Force specific colors */
                    [style*="color: blue"], .sustainability-plan, .sustainability-plan *,
                    [style*="color: blue;"], ol[style*="color: blue"] li, li[style*="color: blue"],
                    [style*="GAD Head"], [style*="Extension Services"],
                    [style*="Vice Chancellor"], [style*="Chancellor"] {
                        color: blue !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Force browns */
                    [style*="color: brown"], [style*="color: brown;"],
                    div[style*="color: brown"], div[style*="color: brown;"] {
                        color: brown !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Ensure black cells in Gantt chart */
                    td[style*="background-color: black"] {
                        background-color: black !important;
                        color: white !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    /* Show tracking number only on first page */
                    .tracking-number {
                        position: absolute;
                        bottom: 20px;
                        left: 20px;
                        font-size: 10pt;
                    }
                    
                    /* Page breaks */
                    .page-break-before {
                        page-break-before: always !important;
                    }
                    
                    .page-break-after {
                        page-break-after: always !important;
                    }
                    
                    /* Page numbers - show on all pages */
                            @page {
                                @bottom-right {
                                    content: "Page " counter(page) " of " counter(pages);
                                    font-family: 'Times New Roman', Times, serif;
                                    font-size: 10pt;
                            }
                        }
                    </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    ${printStyles}
                    <style>
                        @page {
                            size: 8.5in 13in;
                            margin-top: 1.52cm;
                            margin-bottom: 2cm;
                            margin-left: 1.78cm;
                            margin-right: 2.03cm;
                            border: 3px solid black !important;
                        }
                        
                        /* Force all colors to be black */
                        * { color: black !important; }
                        
                        /* The only exception is black background cells for Gantt chart */
                        td[style*="background-color: black"] {
                            background-color: black !important;
                        }
                    </style>
                </head>
                <body>
                    <div class="WordSection1">
                        ${reportContent}
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Add event listener to close the window after printing is complete
                printWindow.addEventListener('afterprint', function() {
                    printWindow.close();
                });
            }, 500);
        }

        // Function to check proposal information directly
        function checkProposalDirectly(proposalId) {
            if (!proposalId) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Proposal ID',
                    text: 'No proposal ID provided to check.'
                });
                return;
            }
            
            // Show loading
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Checking proposal in database...</p>
                </div>
            `);
            
            // Use an existing API endpoint instead of a specialized debugging endpoint
            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    proposal_id: proposalId,
                    campus: $('#campus').val(),
                    year: $('#year').val()
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Debug response:', response);
                    
                    if (response.status === 'success') {
                        // If proposal found, show a mockup of the GAD proposal with the data
                        let mockupProposal = `
                            <div class="proposal-container">
                                <!-- Header Section -->
                                <table class="header-table">
                                    <tr>
                                        <td style="width: 15%; text-align: center;">
                                            <img src="../images/Batangas_State_Logo.png" alt="BatState-U Logo" style="max-width: 80px;">
                                        </td>
                                        <td style="width: 70%; text-align: center;">
                                            <div style="font-size: 14pt; font-weight: bold;">BATANGAS STATE UNIVERSITY</div>
                                            <div style="font-size: 12pt;">THE NATIONAL ENGINEERING UNIVERSITY</div>
                                            <div style="font-size: 11pt; font-style: italic;">${response.data.campus || 'Unknown Campus'}</div>
                                            <div style="font-size: 12pt; font-weight: bold; margin-top: 10px;">GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</div>
                                        </td>
                                        <td style="width: 15%; text-align: center;">
                                            <div style="font-size: 10pt;">Reference No.: BatStateU-FO-ESU-09</div>
                                            <div style="font-size: 10pt;">Effectivity Date: August 25, 2023</div>
                                            <div style="font-size: 10pt;">Revision No.: 00</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Add tracking number to first page -->
                                <div style="text-align: left; margin-top: 5px; margin-bottom: 5px; font-size: 10pt;">
                                    Tracking Number:___________________
                                </div>

                                <!-- Activity Type Checkboxes -->
                                <div style="width: 100%; text-align: center; margin: 10px 0;">
                                    <span style="display: inline-block; margin: 0 20px;">☐ Program</span>
                                    <span style="display: inline-block; margin: 0 20px;">☐ Project</span>
                                    <span style="display: inline-block; margin: 0 20px;">☒ Activity</span>
                                </div>

                                <!-- Proposal Details -->
                                <table class="data-table">
                                    <tr>
                                        <td style="width: 25%; font-weight: bold;">I. Title:</td>
                                        <td style="width: 75%;">${response.data.activity_title || response.data.title || response.data.activity || 'Test Activity'}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">II. Date and Venue:</td>
                                        <td>${response.data.date_venue ? response.data.date_venue.venue + '<br>' + response.data.date_venue.date : 'Not specified'}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">III. Mode of Delivery:</td>
                                        <td>${response.data.delivery_mode || 'Not specified'}</td>
                                    </tr>
                                </table>
                                
                                <div class="section-heading">IV. Project Team:</div>
                                <div style="margin-left: 20px;">
                                    <div><strong>Project Leader/s:</strong> ${response.data.project_team ? response.data.project_team.project_leaders.names : 'Not specified'}</div>
                                    <div class="responsibilities">
                                        <div><strong>Responsibilities:</strong></div>
                                        <ol>
                                            ${response.data.project_team && response.data.project_team.project_leaders.responsibilities ? 
                                              response.data.project_team.project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('') : 
                                              '<li>No responsibilities specified</li>'}
                                        </ol>
                                    </div>
                                </div>

                                <h5 class="mt-4">Debug Information</h5>
                                <div class="alert alert-success">
                                    <p><strong>The proposal was found in the database!</strong></p>
                                    <p>Try using the "Generate Proposal" button again to view the complete proposal.</p>
                                </div>
                            </div>
                        `;
                        
                        $('#reportPreview').html(mockupProposal);
                        
                        // Now let's try to generate the full report
                        setTimeout(() => {
                            generateReport();
                        }, 1000);
                            } else {
                        let errorOutput = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Proposal Not Found</h5>
                                <p>${response.message || 'The proposal could not be found in the database.'}</p>
                                <div class="card mb-3">
                                    <div class="card-header">Troubleshooting Information</div>
                                    <div class="card-body">
                                        <p><strong>Proposal ID:</strong> ${proposalId}</p>
                                        <p><strong>Campus:</strong> ${$('#campus').val()}</p>
                                        <p><strong>Year:</strong> ${$('#year').val()}</p>
                                        <p>Please verify these values are correct in the database.</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-primary" onclick="$('#proposal').val(''); $('#proposal_id').val(''); $('#proposalDropdown').hide();">
                                        Clear Selection
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#reportPreview').html(errorOutput);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Debug error:', error);
                    
                    $('#reportPreview').html(`
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-circle"></i> Error Checking Proposal</h5>
                            <p>Could not check the proposal information: ${error}</p>
                            <pre>${xhr.responseText || 'No response details available'}</pre>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary" onclick="generateReport()">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `);
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Update proposal preview container styling based on theme
            const previewContent = $('#reportPreview .proposal-container');
            if (previewContent.length > 0) {
                if (newTheme === 'dark') {
                    previewContent.addClass('dark-mode-proposal').removeClass('light-mode-proposal');
                    } else {
                    previewContent.addClass('light-mode-proposal').removeClass('dark-mode-proposal');
                }
            }
        }

        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Handle dropdown submenu
            document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    
                    // Toggle the submenu
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle('show');
                    
                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('dropdown-menu')) {
                        if (submenu.style.display === 'block') {
                            submenu.style.display = 'none';
                        } else {
                            submenu.style.display = 'block';
                        }
                    }
                });
            });
            
            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    const openSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    openSubmenus.forEach(menu => {
                        menu.classList.remove('show');
                        const submenu = menu.querySelector('.dropdown-menu');
                        if (submenu) {
                            submenu.style.display = 'none';
                        }
                    });
                }
            });
        });

        function handleLogout(event) {
            event.preventDefault();
            
                Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                backdrop: `
                    rgba(0,0,0,0.7)
                `,
                allowOutsideClick: true,
                customClass: {
                    container: 'swal-blur-container',
                    popup: 'logout-swal'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');
                    
                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 10); // Changed from 50 to 10 - make it super fast
                }
            });
        }

        function fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId) {
            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    proposal_id: selectedProposalId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayProposal(response.data);
                    } else {
                        // Handle API error with more details
                        console.error('API Error:', response);
                        $('#reportPreview').html(`
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p><strong>Error:</strong> ${response.message || 'Failed to load proposal data'}</p>
                                ${response.code ? `<p><small>Error code: ${response.code}</small></p>` : ''}
                                <p class="mt-3">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify(response)})">
                                        Show Technical Details
                                    </button>
                                </p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    // Try to parse the response if it's JSON
                    let errorMessage = 'Error loading proposal. Please try again.';
                    let errorDetails = '';
                    
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                        errorDetails = JSON.stringify(errorResponse, null, 2);
                    } catch (e) {
                        errorDetails = xhr.responseText || error;
                    }
                    
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p><strong>Error:</strong> ${errorMessage}</p>
                            <p><small>Status: ${xhr.status} ${status}</small></p>
                            <p class="mt-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify({error: errorDetails})})">
                                    Show Technical Details
                                </button>
                            </p>
                        </div>
                    `);
                }
            });
        }
        
        // Debug helper function
        function showDebugInfo(data) {
            Swal.fire({
                title: 'Technical Details',
                html: `<pre style="text-align: left; max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(data, null, 2)}</code></pre>`,
                width: '60%',
                confirmButtonText: 'Close'
            });
        }

        function displayProposal(data) {
            if (!data || !data.sections) {
                $('#reportPreview').html('<p>No proposal data available</p>');
                return;
            }

            const sections = data.sections;
            const now = new Date();
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            const currentTime = now.toLocaleTimeString('en-US', timeOptions);
            
            // Dynamically check the current theme state
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const themeClass = isDarkMode ? 'dark-mode-proposal' : 'light-mode-proposal';
            
            // Get the selected campus
            const selectedCampus = $('#campus').val();
            
            // Fetch signatories for the selected campus when in central mode
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            
            // Log whether we have campus signatories
            if (isCentral) {
                console.log("Central user in displayProposal, campusSignatories:", window.campusSignatories);
            }
            
            // Use theme class without inline styling to allow CSS to control colors
            let html = `
            <div class="proposal-container ${themeClass}" style="margin-top: 0; padding-top: 0;">
                <!-- Header Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 15%; text-align: center; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 60px;">
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Reference No.: BatStateU-FO-ESO-09
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Effectivity Date: August 25, 2023
                        </td>
                        <td style="width: 25%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Revision No.: 00
                        </td>
                    </tr>
                </table>

                <!-- Title Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0;">
                    <tr>
                        <td style="text-align: center; padding: 10px; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <strong>GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</strong>
                        </td>
                    </tr>
                </table>

                <!-- Checkbox Section with fixed styling -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0; border-left: 0.1px solid black; border-right: 0.1px solid black; border-top: 0.1px solid black; border-bottom: 0.1px solid black;">
                    <tr>
                        <td style="padding: 10px 0; border: none;">
                            <div style="display: flex; width: 100%; text-align: center;">
                                <div style="flex: 1; padding: 5px 10px;">☐ Program</div>
                                <div style="flex: 1; padding: 5px 10px;">☐ Project</div>
                                <div style="flex: 1; padding: 5px 10px;">☒ Activity</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Main Content -->
                <div style="padding: 20px; border: 0.1px solid black; border-top: none;">
                    <p><strong>I. Title:</strong> ${sections.title || 'N/A'}</p>

                    <p><strong>II. Date and Venue:</strong> ${sections.date_venue.venue || 'N/A'}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${sections.date_venue.date || 'N/A'}</p>

                    <p><strong>III. Mode of Delivery:</strong> ${sections.delivery_mode || 'N/A'}</p>

                    <p><strong>IV. Project Team:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Project Leaders:</strong> ${sections.project_team.project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_leaders.responsibilities) 
                                ? sections.project_team.project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Asst. Project Leaders:</strong> ${sections.project_team.assistant_project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.assistant_project_leaders.responsibilities)
                                ? sections.project_team.assistant_project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.assistant_project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Project Staff:</strong> ${sections.project_team.project_staff.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_staff.responsibilities)
                                ? sections.project_team.project_staff.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_staff.responsibilities || 'N/A'}</li>`
                            }
                        </ol>
                    </div>

                    <p><strong>V. Partner Office/College/Department:</strong> ${sections.partner_offices || 'N/A'}</p>

                    <p><strong>VI. Type of Participants:</strong></p>
                    <div style="text-align: center;">
                        <p><strong>External Type:</strong> ${sections.participants.external_type || 'N/A'}</p>
                        <table style="width: 40%; border-collapse: collapse; margin-top: 10px; margin-left: 20px;">
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">Total</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Female</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${sections.participants.female || '0'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Male</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${sections.participants.male || '0'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black; font-weight: bold;">Total</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${sections.participants.total || '0'}</td>
                            </tr>
                        </table>
                    </div>

                    <p><strong>VII. Rationale/Background:</strong><br>
                    ${sections.rationale || 'N/A'}</p>

                    <p><strong>VIII. Objectives:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>General Objective:</strong> <span style="text-align: justify;">${sections.objectives.general || 'N/A'}</span></p>
                        
                        <p><strong>Specific Objectives:</strong></p>
                        ${formatSpecificObjectives(sections.objectives.specific)}
                    </div>

                    <p><strong>IX. Description, Strategies, and Methods:</strong></p>
                    <p><strong>Description:</strong></p>
                    <div style="margin-left: 20px;">
                        <p>${sections.description || 'N/A'}</p>
                    </div>
                    
                    <p><strong>Strategies:</strong></p>
                    <ol style="margin-left: 20px;">
                        ${(Array.isArray(sections.strategies)) 
                            ? sections.strategies.map(strat => `<li>${strat}</li>`).join('')
                            : `<li>${sections.strategies || 'N/A'}</li>`
                        }
                    </ol>
                    
                    <p><strong>Methods (Activities / Schedule):</strong></p>
                    <ul>
                        ${(Array.isArray(sections.methods)) 
                            ? sections.methods.map((method, index) => {
                                if (Array.isArray(method) && method.length > 1) {
                                    const activityName = method[0];
                                    const details = method[1];
                                    if (Array.isArray(details)) {
                                        return `
                                            <li>
                                                <strong>${activityName}</strong>
                                                <ul>
                                                    ${details.map(detail => `<li>${detail}</li>`).join('')}
                                                </ul>
                                            </li>
                                        `;
                                    } else {
                                        return `<li><strong>${activityName}</strong>: ${details}</li>`;
                                    }
                                } else {
                                    return `<li>${method}</li>`;
                                }
                            }).join('')
                            : `<li>${sections.methods || 'N/A'}</li>`
                        }
                    </ul>
                    
                    <p><strong>Materials Needed:</strong></p>
                    <ul>
                        ${(Array.isArray(sections.materials)) 
                            ? sections.materials.map(material => `<li>${material}</li>`).join('')
                            : `<li>${sections.materials || 'N/A'}</li>`
                        }
                    </ul>

                    <p><strong>X. Work Plan (Timeline of Activities/Gantt Chart):</strong></p>
                    ${(Array.isArray(sections.workplan) && sections.workplan.length > 0) ? (() => {
                        // Extract all dates from workplan
                        const allDates = [];
                        sections.workplan.forEach(item => {
                            if (Array.isArray(item) && item.length > 1 && Array.isArray(item[1])) {
                                item[1].forEach(date => {
                                    if (!allDates.includes(date)) {
                                        allDates.push(date);
                                    }
                                });
                            }
                        });
                        
                        // Sort dates
                        allDates.sort();
                        
                        // Generate table
                        return `
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <th style="border: 0.1px solid black; padding: 5px; width: 40%;">Activities</th>
                                    ${allDates.map(date => 
                                        `<th style="border: 0.1px solid black; padding: 5px; text-align: center;">${date}</th>`
                                    ).join('')}
                                </tr>
                                ${sections.workplan.map(item => {
                                    if (Array.isArray(item) && item.length > 1) {
                                        const activity = item[0];
                                        const dates = Array.isArray(item[1]) ? item[1] : [item[1]];
                                        
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${activity || 'N/A'}</td>
                                                ${allDates.map(date => {
                                                    const isScheduled = dates.includes(date);
                                                    return `<td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: ${isScheduled ? 'black' : 'white'};"></td>`;
                                                }).join('')}
                                            </tr>
                                        `;
                                    } else {
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${item || 'N/A'}</td>
                                                ${allDates.map(() => 
                                                    `<td style="border: 0.1px solid black; padding: 5px;"></td>`
                                                ).join('')}
                                            </tr>
                                        `;
                                    }
                                }).join('')}
                            </table>
                        `;
                    })() : `
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; width: 40%;">Activities</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-03</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-04</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-05</th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">2025-04-06</th>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Work plan 1</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Work plan 2</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: black;"></td>
                            </tr>
                        </table>
                    `}

                    <p><strong>XI. Financial Requirements and Source of Funds:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Source of Funds:</strong> ${sections.financial.source || 'N/A'}</p>
                        <p><strong>Total Budget:</strong> ₱${parseFloat(sections.financial.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>

                    <p><strong>XII. Monitoring and Evaluation Mechanics / Plan:</strong></p>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                        <tr>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Objectives</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Indicators</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Baseline Data</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Target</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Data Source</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Collection Method</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 10%;">Frequency</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 14%;">Responsible</th>
                        </tr>
                        ${(Array.isArray(sections.monitoring_evaluation)) 
                            ? sections.monitoring_evaluation.map((item, index) => {
                                if (Array.isArray(item) && item.length >= 8) {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[0] || 'Objectives ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[1] || 'Performance Indicators ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[2] || 'Baseline Data ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[3] || 'Performance Target ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[4] || 'Data Source'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[5] || 'Collection Method ' + (index + 1)}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[6] || 'Frequency of Data'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[7] || 'Office/Person Responsible ' + (index + 1)}</td>
                                        </tr>
                                    `;
                                } else {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method ${index + 1}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible ${index + 1}</td>
                                        </tr>
                                    `;
                                }
                            }).join('')
                            : `
                                <tr>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method 1</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible 1</td>
                                </tr>
                                <tr>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Objectives 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Indicators 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Baseline Data 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Performance Target 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Data Source</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Collection Method 2</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Frequency of Data</td>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;">Office/Person Responsible 2</td>
                                </tr>
                            `
                        }
                    </table>

                    <p><strong>XIII. Sustainability Plan:</strong></p>
                    <div>
                        ${(sections.sustainability) 
                            ? Array.isArray(sections.sustainability) 
                                ? `<ul>${sections.sustainability.map(item => `<li>${item}</li>`).join('')}</ul>`
                                : `<p>${sections.sustainability}</p>` 
                            : `<p>No sustainability plan provided.</p>`
                        }
                    </div>

                    <!-- Add specific plans from database with bullets -->
                    <p><strong>Specific Plans:</strong></p>
                    <div>
                        ${(sections.specific_plans) 
                            ? Array.isArray(sections.specific_plans) 
                                ? `<ul>${sections.specific_plans.map(item => `<li>${item}</li>`).join('')}</ul>`
                                : `<p>${sections.specific_plans}</p>` 
                            : `<ul>
                                <li>Regular monitoring of project activities</li>
                                <li>Continuous engagement with stakeholders</li>
                                <li>Documentation of lessons learned</li>
                                <li>Capacity building for sustainability</li>
                                <li>Resource allocation for maintenance</li>
                              </ul>`
                        }
                    </div>

                    <!-- II. Date and Venue -->
                    <h2>II. Date and Venue</h2>
                    <div>
                        ${sections.date_venue ? 
                            `<p>Date: ${sections.date_venue.date || 'Not specified'} ${sections.date_venue.end_date ? `to ${sections.date_venue.end_date}` : ''}</p>
                             <p>Venue: ${sections.date_venue.venue || 'Not specified'}</p>` 
                            : '<p>No date and venue information available.</p>'
                        }
                    </div>

                    <!-- III. Mode of delivery -->
                    <h2>III. Mode of Delivery</h2>
                    <div>
                        <p>${sections.mode_of_delivery || 'Not specified'}</p>
                    </div>

                    <!-- IV. Project Team -->
                    <h2>IV. Project Team</h2>
                    <div>
                        ${sections.project_team && sections.project_team.project_leaders && sections.project_team.project_leaders.length > 0 ?
                            `<p><strong>Project Leaders:</strong></p>
                             <ul>${sections.project_team.project_leaders.map(leader => `<li>${leader.name || leader}</li>`).join('')}</ul>` : ''}
                        
                        ${sections.project_team && sections.project_team.assistant_project_leaders && sections.project_team.assistant_project_leaders.length > 0 ?
                            `<p><strong>Assistant Project Leaders:</strong></p>
                             <ul>${sections.project_team.assistant_project_leaders.map(leader => `<li>${leader.name || leader}</li>`).join('')}</ul>` : ''}
                        
                        ${sections.project_team && sections.project_team.project_staff && sections.project_team.project_staff.length > 0 ?
                            `<p><strong>Project Staff:</strong></p>
                             <ul>${sections.project_team.project_staff.map(staff => `<li>${staff.name || staff}</li>`).join('')}</ul>` : ''}
                    </div>

                    <!-- V. Partner Office/College/Department -->
                    <h2>V. Partner Office/College/Department</h2>
                    <div>
                        ${Array.isArray(sections.partner_office) && sections.partner_office.length > 0 ? 
                            `<ul>${sections.partner_office.map(partner => `<li>${partner}</li>`).join('')}</ul>` : 
                            `<p>${sections.partner_office || 'No partner offices specified.'}</p>`
                        }
                    </div>

                    <!-- VI. Type of Participants -->
                    <h2>VI. Type of Participants</h2>
                    <div>
                        <p>${sections.type_participants || 'Not specified'}</p>
                    </div>

                    <!-- VII. Objectives -->
                    <h2>VII. Objectives</h2>
                    <div>
                        ${sections.objectives && Array.isArray(sections.objectives) && sections.objectives.length > 0 ? 
                            `<ul>${sections.objectives.map(obj => `<li>${obj}</li>`).join('')}</ul>` : 
                            `<p>No objectives specified.</p>`
                        }
                    </div>

                    <!-- VIII. Evaluation Results -->
                    <h2>VIII. Evaluation Results</h2>
                    <div>
                        <table style="width:100%; border-collapse: collapse; border: 1px solid black; margin-top: 5px;">
                            <tr style="background-color: #f2f2f2; border: 1px solid black;">
                                <th style="border: 1px solid black; padding: 5px; text-align: center; width: 20%;">CRITERIA</th>
                                <th colspan="2" style="border: 1px solid black; padding: 5px; text-align: center;">EXCELLENT</th>
                                <th colspan="2" style="border: 1px solid black; padding: 5px; text-align: center;">VERY SATISFACTORY</th>
                                <th colspan="2" style="border: 1px solid black; padding: 5px; text-align: center;">SATISFACTORY</th>
                                <th colspan="2" style="border: 1px solid black; padding: 5px; text-align: center;">FAIR</th>
                                <th colspan="2" style="border: 1px solid black; padding: 5px; text-align: center;">POOR</th>
                            </tr>
                            <tr style="border: 1px solid black;">
                                <th style="border: 1px solid black; padding: 5px;"></th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">BatStateU</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">Others</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">BatStateU</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">Others</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">BatStateU</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">Others</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">BatStateU</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">Others</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">BatStateU</th>
                                <th style="border: 1px solid black; padding: 5px; text-align: center;">Others</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black; padding: 5px; font-weight: bold;">Activity</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Excellent?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Excellent?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.["Very Satisfactory"]?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.["Very Satisfactory"]?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Satisfactory?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Satisfactory?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Fair?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Fair?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Poor?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.evaluation_results?.Poor?.Others || 0}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black; padding: 5px; font-weight: bold;">Timeliness</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Excellent?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Excellent?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.["Very Satisfactory"]?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.["Very Satisfactory"]?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Satisfactory?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Satisfactory?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Fair?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Fair?.Others || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Poor?.BatStateU || 0}
                                </td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                                    ${sections.timeliness_ratings?.Poor?.Others || 0}
                                </td>
                            </tr>
                        </table>
                        
                        <div style="margin-top: 10px;">
                            <p>Use BatStateU-ESC-ESO-12: Training-Seminar Evaluation Form</p>
                        </div>
                    </div>

                    <!-- IX. Narrative of the Project/Activity -->
                    <h2>IX. Narrative of the Project/Activity</h2>
                    <div>
                        <strong>A. Background and rationale</strong>
                        <p>${sections.narrative_project?.background_rationale || 'Not specified'}</p>
                        
                        <strong>B. Brief description of participants</strong>
                        <p>${sections.narrative_project?.participant_description || 'Not specified'}</p>
                        
                        <strong>C. Narrative of topic/s discussed</strong>
                        <p>${sections.narrative_project?.topics_discussed || 'Not specified'}</p>
                        
                        <strong>D. Matrix of the results of pre-test and post-tests (if necessary)</strong>
                        <p>${sections.narrative_project?.pretest_posttest || 'Not applicable for this activity'}</p>
                        
                        <strong>E. Expected results, actual outputs, and outcomes (actual accomplishments vis-à-vis targets)</strong>
                        <p>${sections.narrative_project?.expected_actual_results || 'Not specified'}</p>
                        
                        <strong>F. Lessons learned and insights on the conduct of PPA</strong>
                        <p>${sections.narrative_project?.lessons_learned || 'Not specified'}</p>
                        
                        <strong>G. What worked and did not work (identify any challenges which may have been encountered in implementing the activity)</strong>
                        <p>${sections.narrative_project?.what_worked || 'Not specified'}</p>
                        
                        <strong>H. Issues and concerns raised and how addressed</strong>
                        <p>${sections.narrative_project?.issues_concerns || 'Not specified'}</p>
                        
                        <strong>I. Recommendations</strong>
                        <p>${sections.narrative_project?.recommendations || 'Not specified'}</p>
                    </div>

                    <!-- X. Financial Requirements and Source of Funds -->
                    <h2>X. Financial Requirements and Source of Funds</h2>
                    <div>
                        <table style="width: 80%; border-collapse: collapse; margin-top: 10px; margin-left: 20px;">
                            <tr>
                                <td style="padding: 5px; border: 1px solid black; font-weight: bold;"></td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">Amount</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">Budget Source</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Approved Budget as proposed</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php ${(sections.financial_requirements_detail && sections.financial_requirements_detail.budget) ? Number(sections.financial_requirements_detail.budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Actual Budget Utilized</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php 0.00</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Personal Services (PS) Attribution</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php ${(sections.financial_requirements_detail && sections.financial_requirements_detail.ps_attribution) ? Number(sections.financial_requirements_detail.ps_attribution).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- XI. Photo Documentation with Caption/s -->
                    <h2>XI. Photo Documentation with Caption/s</h2>
                    <div>
                        ${sections.photo_documentation && sections.photo_documentation.length > 0 ? 
                            sections.photo_documentation.map(photo => 
                                `<div style="margin-bottom: 20px;">
                                    <img src="${photo.path}" alt="Activity photo" style="max-width: 100%; height: auto;" />
                                    <p>${photo.caption || 'No caption provided'}</p>
                                </div>`
                            ).join('') : 
                            `<p>No photos available.</p>`
                        }
                    </div>

                    <!-- Add page break before signatures -->
                    <div class="page-break"></div>
                </div>
                
                <!-- Signatures table -->
                <table class="signatures-table" style="width: 100%; margin: 0; padding: 0; border-collapse: collapse; page-break-inside: avoid; border: 1px solid black;">
                    <tr>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Prepared by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">${signatories.name1 || 'Ms. RICHELLE M. SULIT'}</p>
                            <p style="margin: 0; text-align: center;">GAD Head Secretariat</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Reviewed by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">${signatories.name3 || 'Mr. REXON S. HERNANDEZ'}</p>
                            <p style="margin: 0; text-align: center;">Head, Extension Services</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Approved by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center; font-weight: bold;">${signatories.name4 || 'Dr. FRANCIS G. BALAZON'}</p>
                            <p style="margin: 0; text-align: center;">Vice Chancellor for Research, Development and Extension Services</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_________________</p>
                        </td>
                        <td style="width: 50%; border: 1px solid black; padding: 15px;">
                            <p style="margin: 0; font-weight: bold; text-align: center;">Remarks:</p>
                            <br><br><br><br><br>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Add tracking number at bottom of first page -->
            <div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 10pt;">
               
            </div>`;

            $('#reportPreview').html(html);
            
            // Update page numbers dynamically
            const totalPages = 3; // or calculate based on content
            document.querySelectorAll('.total-pages').forEach(el => {
                el.textContent = totalPages;
            });
            
            // Set current page numbers sequentially
            document.querySelectorAll('.page-number').forEach((el, index) => {
                el.textContent = index + 1;
            });
        }
        
        // Function to print the proposal with proper formatting
        function printProposal() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            let reportContent = $('#reportPreview').html();
            
            // SPECIAL FIX: Remove any empty divs or spaces that might cause empty boxes
            reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
            reportContent = reportContent.replace(/<pre[\s\S]*?<\/pre>/g, '');
            
            // Remove any print buttons that might be in the content
            reportContent = reportContent.replace(/<button[^>]*id="printProposalBtn"[^>]*>[\s\S]*?<\/button>/g, '');
            
            // Ensure tracking number is included
            if (!reportContent.includes('Tracking Number:')) {
                // Insert tracking number at end of first page
                const pageBreakIndex = reportContent.indexOf('<div class="page-break"></div>');
                if (pageBreakIndex !== -1) {
                    reportContent = reportContent.substring(0, pageBreakIndex) + 
                        '<div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-family: \'Times New Roman\', Times, serif; font-size: 10pt;">Tracking Number:___________________</div>' + 
                        reportContent.substring(pageBreakIndex);
                }
            }
            
            // Always force print to be in light mode for consistent output
            const printStyles = `
                <style>
                    @page {
                        size: 8.5in 13in;
                        margin-top: 1.52cm;
                        margin-bottom: 2cm;
                        margin-left: 1.78cm;
                        margin-right: 2.03cm;
                        border-top: 3px solid black !important;
                        border-bottom: 3px solid black !important;
                    }
                    
                    body {
                        background-color: white !important;
                        color: black !important;
                        font-family: 'Times New Roman', Times, serif !important;
                        font-size: 12pt !important;
                        line-height: 1.2 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    
                    /* Container for signatures with no margins */
                    div[style*="width: 100%"] {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
                    }
                    
                    /* Fix for signatures table */
                    .signatures-table {
                        width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border-collapse: collapse !important;
                    }
                    
                    /* Force ALL COLORS to be black - no exceptions */
                    *, p, span, div, td, th, li, ul, ol, strong, em, b, i, a, h1, h2, h3, h4, h5, h6,
                    [style*="color:"], [style*="color="], [style*="color :"], [style*="color ="],
                    [style*="color: brown"], [style*="color: blue"], [style*="color: red"],
                    .brown-text, .blue-text, .sustainability-plan, .sustainability-plan p, 
                    .signature-label, .signature-position {
                        color: black !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                    
                    /* Preserve black-filled cells in Gantt chart */
                    table td[style*="background-color: black"] {
                        background-color: black !important;
                    }
                        
                    /* When printing */
                    @media print {
                        @page {
                            border-top: 3px solid black !important;
                            border-bottom: 3px solid black !important;
                        }
                        
                        /* Remove line on last page */
                        @page:last {
                            border-bottom: none !important;
                        }
                        
                        /* First page footer with tracking number and page number on same line */
                        @page:first {
                            @bottom-left {
                                content: "Tracking Number:___________________";
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                left: 1.78cm;
                            }
                            
                            @bottom-right {
                                content: "Page " counter(page) " of " counter(pages);
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                right: 2.03cm;
                            }
                        }
                        
                        /* Other pages footer with just page number */
                        @page {
                            @bottom-right {
                                content: "Page " counter(page) " of " counter(pages);
                                font-family: 'Times New Roman', Times, serif;
                                font-size: 10pt;
                                color: black;
                                position: fixed;
                                bottom: 0.4cm;
                                right: 2.03cm;
                            }
                        }
                    }
                </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    ${printStyles}
                    <style>
                        /* Additional print-specific fixes */
                        .page-break {
                            page-break-before: always;
                        }
                        
                        /* Ensure signatures are properly positioned */
                        div[style*="width: 100%"] {
                            margin: 0 !important;
                            padding: 0 !important;
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                        
                        /* Additional fix for tracking number */
                        @page:first {
                            @bottom-left {
                                content: "";
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="WordSection1">
                        ${reportContent}
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Add event listener to close the window after printing is complete
                printWindow.addEventListener('afterprint', function() {
                    printWindow.close();
                });
            }, 250);
        }

        // Add print button to the UI
        $(document).ready(function() {
            // Wait for the document to be ready
            setTimeout(function() {
                // Remove print button functionality as requested
                $('#printProposalBtn').remove();
                
                // No longer adding the print button to the UI
                
                // Attach event handler for programmatic printing if needed
                $(document).on('keydown', function(e) {
                    // Ctrl+P alternative if needed internally
                    if (e.ctrlKey && e.key === 'p') {
                        printProposal();
                    }
                });
            }, 500); // Wait a little to ensure the page is loaded
        });

        // Function to display the narrative report
        function displayNarrativeReport(data) {
            if (!data) {
                $('#reportPreview').html('<p>No narrative data available</p>');
                return;
            }

            // Log details about the data for debugging
            console.log('Narrative data received:', data);
            
            // Transform ratings data to proper format if needed
            function transformRatingsToProperFormat(ratingsData) {
                console.log('Transforming ratings data:', ratingsData);
                
                // Initialize proper ratings structure with zeros
                const properRatings = {
                    "Excellent": { "BatStateU": 0, "Others": 0 },
                    "Very Satisfactory": { "BatStateU": 0, "Others": 0 },
                    "Satisfactory": { "BatStateU": 0, "Others": 0 },
                    "Fair": { "BatStateU": 0, "Others": 0 },
                    "Poor": { "BatStateU": 0, "Others": 0 }
                };

                try {
                    // If ratingsData is a string, try to parse it
                    let ratings = ratingsData;
                    if (typeof ratingsData === 'string') {
                        try {
                            ratings = JSON.parse(ratingsData);
                        } catch (e) {
                            console.error('Failed to parse ratings JSON:', e);
                            return properRatings;
                        }
                    }
                    
                    if (!ratings) {
                        console.log('No ratings data provided');
                        return properRatings;
                    }

                    // Directly map the data from the database structure
                    for (const rating in ratings) {
                        if (properRatings[rating]) {
                            properRatings[rating].BatStateU = parseInt(ratings[rating].BatStateU) || 0;
                            properRatings[rating].Others = parseInt(ratings[rating].Others) || 0;
                        }
                    }

                    console.log('Final transformed ratings:', properRatings);
                    return properRatings;
                } catch (e) {
                    console.error('Error transforming ratings:', e);
                    return properRatings;
                }
            }

            // Process the data for the report
            const transformedActivityRatings = transformRatingsToProperFormat(data.activity_ratings);
            const transformedTimelinessRatings = transformRatingsToProperFormat(data.timeliness_ratings);
            
            console.log('Transformed activity ratings:', transformedActivityRatings);
            console.log('Transformed timeliness ratings:', transformedTimelinessRatings);
            
            // Add the transformed ratings back to the data
            data.activity_ratings = transformedActivityRatings;
            data.timeliness_ratings = transformedTimelinessRatings;

            // Check specifically for objectives data
            console.log('Objectives data check:',
                'general_objectives present:', Boolean(data.general_objectives),
                'specific_objectives present:', Boolean(data.specific_objectives),
                'general_objectives value:', data.general_objectives,
                'specific_objectives value:', data.specific_objectives
            );

            // Format extension service agenda from the data
            const agendaIndex = data.extension_service_agenda ? data.extension_service_agenda : [];
            
            // Organize the sections for the report
            const sections = {
                title: data.activity_title || 'Not specified',
                date_venue: {
                    start_date: data.date_range?.start || '',
                    end_date: data.date_range?.end || '',
                    venue: data.location || data.campus || 'Not specified'
                },
                activity_narrative: data.activity_narrative || 'Not specified',
                narrative_project: {
                    background_rationale: data.narrative_sections?.background_rationale || 'Not specified',
                    participant_description: data.narrative_sections?.description_participants || 'Not specified',
                    topics_discussed: data.narrative_sections?.narrative_topics || 'Not specified',
                    pretest_posttest: 'Not applicable for this activity',
                    expected_actual_results: data.narrative_sections?.expected_results || 'Not specified',
                    lessons_learned: data.narrative_sections?.lessons_learned || 'Not specified',
                    what_worked: data.narrative_sections?.what_worked || 'Not specified',
                    issues_concerns: data.narrative_sections?.issues_concerns || 'Not specified',
                    recommendations: data.narrative_sections?.recommendations || 'Not specified',
                },
                financial_requirements: data.financial_requirements || '0.00',
                ps_attribution: data.ps_attribution || '0.00',
                source_of_budget: data.source_of_budget || 'Not specified',
                activity_images: data.activity_images || [],
                // Additional sections data
                mode_of_delivery: data.mode_of_delivery || 'Face-to-face',
                project_team: data.project_team || data.personnel || {},
                partner_office: data.partner_office || [],
                type_participants: data.type_participants || 'Not specified',
                objectives: data.objectives || [],
                evaluation_results: data.evaluation_results || {},
                timeliness_ratings: data.timeliness_ratings || {},
                narrative_of_project: data.narrative_of_project || data.activity_narrative || 'Not specified',
                financial_requirements_detail: data.financial_requirements_detail || {
                    budget: data.financial_requirements || 0,
                    ps_attribution: data.ps_attribution || 0,
                    source_of_fund: data.source_of_budget || []
                },
                photo_documentation: data.photo_documentation || (data.activity_images ? 
                    data.activity_images.map(img => ({ path: img, caption: '' })) : [])
            };
            
            const signatories = data.signatories || {
                name1: 'Ms. RICHELLE M. SULIT',
                name2: 'Dr. TIRSO A. RONQUILLO',
                name3: 'Mr. REXON S. HERNANDEZ',
                name4: 'Dr. FRANCIS G. BALAZON'
            };
            
            // Generate the HTML for the report
            const html = `
            <div class="proposal-container">
                <!-- Header Section with Logo and Reference Numbers -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                    <tr>
                        <td style="width: 15%; text-align: center; padding: 10px; border: 1px solid black;">
                            <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 60px;">
                        </td>
                        <td style="width: 45%; text-align: center; padding: 10px; border: 1px solid black;">
                            <div style="font-size: 12pt;">Reference No.: BatStateU-FO-ESO-10</div>
                        </td>
                        <td style="width: 25%; text-align: center; padding: 10px; border: 1px solid black;">
                            <div style="font-size: 10pt;">Effectivity Date: August 25, 2023</div>
                        </td>
                        <td style="width: 15%; text-align: center; padding: 10px; border: 1px solid black;">
                            <div style="font-size: 10pt;">Revision No.: 00</div>
                        </td>
                    </tr>
                </table>
                
                <!-- Report Type -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                    <tr>
                        <td style="text-align: center; padding: 10px; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; font-weight: bold;">
                            NARRATIVE REPORT (EXTERNAL GAD PROGRAM/PROJECT/ACTIVITY)
                        </td>
                    </tr>
                </table>

                <!-- Activity Type Checkboxes -->
                <div style="text-align: center; padding: 10px; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
                    <span style="display: inline-block; margin: 0 20px;">☐ Program</span>
                    <span style="display: inline-block; margin: 0 20px;">☐ Project</span>
                    <span style="display: inline-block; margin: 0 20px;">☒ Activity</span>
                </div>

                <!-- Main Content -->
                <div style="padding: 10px; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">I.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Title:</span>
                        <span style="margin-left: 5px;">"${sections.title}"</span>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">II.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Date and Venue:</span>
                        <span style="margin-left: 5px;">
                            ${sections.date_venue ? 
                                (sections.date_venue.start_date ? 
                                    (sections.date_venue.start_date === sections.date_venue.end_date || !sections.date_venue.end_date ? 
                                        sections.date_venue.start_date : 
                                        `${sections.date_venue.start_date} to ${sections.date_venue.end_date}`) : 
                                    'Date not specified') : 'Date not specified'}, 
                            ${sections.date_venue.venue}
                        </span>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">III.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Mode of delivery (online/face-to-face):</span>
                        <span style="margin-left: 5px;">${data.mode_of_delivery || 'Face-to-face'}</span>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">IV.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Project Team:</span>
                        
                        <div style="margin-left: 20px; margin-top: 5px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 30%; padding: 5px; vertical-align: top; border: 1px solid #ddd;">Project Leader:</td>
                                    <td style="width: 70%; padding: 5px; border: 1px solid #ddd;">
                                        ${(() => {
                                            // First check direct project_team object
                                            if (data.project_team && data.project_team.project_leaders && data.project_team.project_leaders.length > 0) {
                                                // Process each leader
                                                return data.project_team.project_leaders.map(leader => 
                                                    typeof leader === 'object' ? (leader.name || 'Project Leader') : leader
                                                ).join('<br>');
                                            } 
                                            // Fallback to personnel property
                                            else if (data.personnel && data.personnel.project_leaders && data.personnel.project_leaders.length > 0) {
                                                return data.personnel.project_leaders.map(leader => 
                                                    typeof leader === 'object' ? (leader.name || 'Project Leader') : leader
                                                ).join('<br>');
                                            } 
                                            // Last resort, use specific project_leader if it exists
                                            else if (data.project_leader) {
                                                return data.project_leader;
                                            } 
                                            // Default text if nothing found
                                            else {
                                                return 'Project Leader';
                                            }
                                        })()}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 5px; vertical-align: top; border: 1px solid #ddd;">Assistant Project Leader:</td>
                                    <td style="width: 70%; padding: 5px; border: 1px solid #ddd;">
                                        ${(() => {
                                            // First check direct project_team object
                                            if (data.project_team && data.project_team.assistant_project_leaders && data.project_team.assistant_project_leaders.length > 0) {
                                                // Process each assistant leader
                                                return data.project_team.assistant_project_leaders.map(leader => 
                                                    typeof leader === 'object' ? (leader.name || 'Assistant Project Leader') : leader
                                                ).join('<br>');
                                            } 
                                            // Fallback to personnel property
                                            else if (data.personnel && data.personnel.assistant_project_leaders && data.personnel.assistant_project_leaders.length > 0) {
                                                return data.personnel.assistant_project_leaders.map(leader => 
                                                    typeof leader === 'object' ? (leader.name || 'Assistant Project Leader') : leader
                                                ).join('<br>');
                                            } 
                                            // Last resort, use specific assistant_leader if it exists
                                            else if (data.assistant_leader) {
                                                return data.assistant_leader;
                                            } 
                                            // Default text if nothing found
                                            else {
                                                return 'Assistant Project Leader';
                                            }
                                        })()}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 5px; vertical-align: top; border: 1px solid #ddd;">Project Staff:</td>
                                    <td style="width: 70%; padding: 5px; border: 1px solid #ddd;">
                                        ${(() => {
                                            // First check direct project_team object
                                            if (data.project_team && data.project_team.project_staff && data.project_team.project_staff.length > 0) {
                                                // Process each staff member
                                                return data.project_team.project_staff.map(staff => 
                                                    typeof staff === 'object' ? (staff.name || 'Project Staff Member') : staff
                                                ).join('<br>');
                                            } 
                                            // Fallback to personnel property
                                            else if (data.personnel && data.personnel.project_staff && data.personnel.project_staff.length > 0) {
                                                return data.personnel.project_staff.map(staff => 
                                                    typeof staff === 'object' ? (staff.name || 'Project Staff Member') : staff
                                                ).join('<br>');
                                            } 
                                            // Last resort, use specific project_staff if it exists
                                            else if (data.project_staff) {
                                                return data.project_staff;
                                            } 
                                            // Default text if nothing found
                                            else {
                                                return 'Project Staff Member';
                                            }
                                        })()}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">V.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Partner Office/College/Department:</span>
                        <span style="margin-left: 5px;">
                            ${(() => {
                                // First check if partner_office is directly available
                                if (data.partner_office) {
                                    // Handle array or string
                                    if (Array.isArray(data.partner_office)) {
                                        return data.partner_office.length > 0 ? 
                                            data.partner_office.join(', ') : 'College of Informatics and Computing Sciences';
                                    } else {
                                        // It's a string or other value, just return it
                                        return data.partner_office;
                                    }
                                }
                                // Check for implementing_office as fallback 
                                else if (data.implementing_office) {
                                    if (Array.isArray(data.implementing_office)) {
                                        return data.implementing_office.join(', ');
                                    } else {
                                        return data.implementing_office;
                                    }
                                }
                                // Default value if nothing found
                                else {
                                    return 'College of Informatics and Computing Sciences';
                                }
                            })()}
                        </span>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">VI.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Type of Participants:</span>
                        
                        <div style="margin-left: 20px; margin-top: 5px;">
                            ${data.type_participants ? 
                                `<div>${data.type_participants}</div>` : 
                                `<div>Internal Participants: ${data.internal_type || 'Students, Faculty, University Staff'}</div>
                                 <div>External Participants: ${data.external_type || 'Community members and stakeholders'}</div>`}
                            
                            <div style="margin-top: 10px;">
                                <table style="width: 60%; border-collapse: collapse; text-align: center;">
                                    <tr>
                                        <td style="padding: 5px;"></td>
                                        <td style="padding: 5px; font-weight: bold;">Male</td>
                                        <td style="padding: 5px; font-weight: bold;">Female</td>
                                        <td style="padding: 5px; font-weight: bold;">Total</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px; text-align: left;">BatStateU</td>
                                        <td style="padding: 5px;">${data.beneficiary_distribution?.maleBatStateU || data.internal_male || 0}</td>
                                        <td style="padding: 5px;">${data.beneficiary_distribution?.femaleBatStateU || data.internal_female || 0}</td>
                                        <td style="padding: 5px;">${parseInt(data.beneficiary_distribution?.maleBatStateU || data.internal_male || 0) + parseInt(data.beneficiary_distribution?.femaleBatStateU || data.internal_female || 0)}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px; text-align: left;">External</td>
                                        <td style="padding: 5px;">${data.beneficiary_distribution?.maleOthers || data.external_male || 0}</td>
                                        <td style="padding: 5px;">${data.beneficiary_distribution?.femaleOthers || data.external_female || 0}</td>
                                        <td style="padding: 5px;">${parseInt(data.beneficiary_distribution?.maleOthers || data.external_male || 0) + parseInt(data.beneficiary_distribution?.femaleOthers || data.external_female || 0)}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px; text-align: left; font-weight: bold;">Total</td>
                                        <td style="padding: 5px; font-weight: bold;">${parseInt(data.beneficiary_distribution?.maleBatStateU || data.internal_male || 0) + parseInt(data.beneficiary_distribution?.maleOthers || data.external_male || 0)}</td>
                                        <td style="padding: 5px; font-weight: bold;">${parseInt(data.beneficiary_distribution?.femaleBatStateU || data.internal_female || 0) + parseInt(data.beneficiary_distribution?.femaleOthers || data.external_female || 0)}</td>
                                        <td style="padding: 5px; font-weight: bold;">${parseInt(data.beneficiary_distribution?.maleBatStateU || data.internal_male || 0) + parseInt(data.beneficiary_distribution?.femaleBatStateU || data.internal_female || 0) + parseInt(data.beneficiary_distribution?.maleOthers || data.external_male || 0) + parseInt(data.beneficiary_distribution?.femaleOthers || data.external_female || 0)}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: bold; color: var(--text-primary);">VII.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Objectives:</span>
                        
                        <div style="margin-left: 20px; margin-top: 5px;">
                            <div>
                                <strong>General Objective:</strong>
                                <p>${data.general_objectives || ('To successfully implement ' + (data.activity_title || data.activity || 'the activity'))}</p>
                            </div>
                            
                            <div style="margin-top: 10px;">
                                <strong>Specific Objectives:</strong>
                                <ol style="margin-top: 5px;">
                                    ${data.objectives && Array.isArray(data.objectives) && data.objectives.length > 0 ? 
                                        data.objectives.map(obj => `<li>${obj}</li>`).join('') : 
                                        (data.specific_objectives && Array.isArray(data.specific_objectives) ? 
                                        data.specific_objectives.map(obj => `<li>${obj}</li>`).join('') : 
                                            '<li>To implement the activity successfully</li>')}
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <span style="font-weight: bold; color: var(--text-primary);">VIII.</span>
                        <span style="font-weight: bold; color: var(--text-primary);">Evaluation Results:</span>
                        
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid black;">
                            <tr style="background-color: #f2f2f2;">
                                <th style="padding: 5px; border: 1px solid black; text-align: center;">CRITERIA</th>
                                <th style="padding: 5px; border: 1px solid black; text-align: center;" colspan="2">EXCELLENT</th>
                                <th style="padding: 5px; border: 1px solid black; text-align: center;" colspan="2">VERY SATISFACTORY</th>
                                <th style="padding: 5px; border: 1px solid black; text-align: center;" colspan="2">SATISFACTORY</th>
                                <th style="padding: 5px; border: 1px solid black; text-align: center;" colspan="2">FAIR</th>
                                <th style="padding: 5px; border: 1px solid black; text-align: center;" colspan="2">POOR</th>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">BatStateU</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">Others</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">BatStateU</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">Others</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">BatStateU</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">Others</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">BatStateU</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">Others</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">BatStateU</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">Others</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Activity</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Excellent.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Excellent.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings['Very Satisfactory'].BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings['Very Satisfactory'].Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Satisfactory.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Satisfactory.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Fair.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Fair.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Poor.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.activity_ratings.Poor.Others || 0}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Timeliness</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Excellent.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Excellent.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings['Very Satisfactory'].BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings['Very Satisfactory'].Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Satisfactory.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Satisfactory.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Fair.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Fair.Others || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Poor.BatStateU || 0}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">${data.timeliness_ratings.Poor.Others || 0}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                                <td style="padding: 5px; border: 1px solid black;"></td>
                            </tr>
                        </table>
                        <p style="font-size: 9pt; font-style: italic; text-align: right; margin-top: 5px;">Use BatStateU-ESC-ESO-12: Training-Seminar Evaluation Form</p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div>
                            <span style="font-weight: bold; color: var(--text-primary);">IX.</span>
                            <span style="font-weight: bold; color: var(--text-primary);">Narrative of the Project/Activity:</span>
                        </div>
                        
                        <div style="margin-top: 10px; margin-left: 20px;">
                            <p><strong>A. Background and rationale</strong></p>
                            <p>${sections.narrative_project.background_rationale}</p>
                            
                            <p style="margin-top: 10px;"><strong>B. Brief description of participants</strong></p>
                            <p>${sections.narrative_project.participant_description}</p>
                            
                            <p style="margin-top: 10px;"><strong>C. Narrative of topic/s discussed</strong></p>
                            <p>${sections.narrative_project.topics_discussed}</p>
                            
                            <p style="margin-top: 10px;"><strong>D. Matrix of the results of pre-test and post-tests (if necessary)</strong></p>
                            <p>${sections.narrative_project.pretest_posttest || 'N/A'}</p>
                            
                            <p style="margin-top: 10px;"><strong>E. Expected results, actual outputs, and outcomes (actual accomplishments vis-à-vis targets)</strong></p>
                            <p>${sections.narrative_project.expected_actual_results}</p>
                            
                            <p style="margin-top: 10px;"><strong>F. Lessons learned and insights on the conduct of PPA</strong></p>
                            <p>${sections.narrative_project.lessons_learned}</p>
                            
                            <p style="margin-top: 10px;"><strong>G. What worked and did not work (identify any challenges which may have been encountered in implementing the activity)</strong></p>
                            <p>${sections.narrative_project.what_worked}</p>
                            
                            <p style="margin-top: 10px;"><strong>H. Issues and concerns raised and how addressed</strong></p>
                            <p>${sections.narrative_project.issues_concerns}</p>
                            
                            <p style="margin-top: 10px;"><strong>I. Recommendations</strong></p>
                            <p>${sections.narrative_project.recommendations}</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div>
                            <span style="font-weight: bold; color: var(--text-primary);">X.</span>
                            <span style="font-weight: bold; color: var(--text-primary);">Financial Requirements and Source of Funds:</span>
                        </div>
                        
                        <table style="width: 80%; border-collapse: collapse; margin-top: 10px; margin-left: 20px;">
                            <tr>
                                <td style="padding: 5px; border: 1px solid black; font-weight: bold;"></td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">Amount</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">Budget Source</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Approved Budget as proposed</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php ${(sections.financial_requirements_detail && sections.financial_requirements_detail.budget) ? Number(sections.financial_requirements_detail.budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Actual Budget Utilized</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php 0.00</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border: 1px solid black;">Personal Services (PS) Attribution</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: right;">Php ${(sections.financial_requirements_detail && sections.financial_requirements_detail.ps_attribution) ? Number(sections.financial_requirements_detail.ps_attribution).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</td>
                                <td style="padding: 5px; border: 1px solid black; text-align: center;">
                                    ${sections.financial_requirements_detail && sections.financial_requirements_detail.source_of_fund ? 
                                      (Array.isArray(sections.financial_requirements_detail.source_of_fund) ? 
                                        sections.financial_requirements_detail.source_of_fund.join(', ') : 
                                        sections.financial_requirements_detail.source_of_fund) : 
                                      'GAD'}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div>
                            <span style="font-weight: bold; color: var(--text-primary);">XI.</span>
                            <span style="font-weight: bold; color: var(--text-primary);">Photo Documentation with Caption/s:</span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px; margin-left: 20px; max-width: 95%;">
                            ${sections.activity_images && sections.activity_images.length > 0 ?
                                sections.activity_images.map((img, index) => `
                                    <div style="margin-bottom: 15px;">
                                        <img src="../${img}" alt="Activity Photo ${index+1}" style="width: 100%; border: 1px solid #ddd;">
                                        <div contenteditable="true" style="border: 1px dashed #ccc; padding: 5px; margin-top: 5px; min-height: 30px; font-size: 12px; text-align: center;">
                                            Click to add caption
                                        </div>
                                    </div>
                                `).join('') :
                                `<div class="text-center p-3" style="grid-column: span 3;">No photo documentation available</div>`
                            }
                        </div>
                        <p style="text-align: center; margin-top: 10px; font-style: italic;">Activity Documentation Photos</p>
                    </div>
                </div>

                <!-- Signature Section -->
                <div style="page-break-before: always;"></div>
                <table style="width: 95%; border-collapse: collapse; margin: 20px auto; border: 1px solid black;">
                    <tr>
                        <td style="width: 50%; padding: 5px; border: 1px solid black; text-align: center; vertical-align: top;">Prepared by:</td>
                        <td style="width: 50%; padding: 5px; border: 1px solid black; text-align: center; vertical-align: top;">Reviewed by:</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px 5px; border: 1px solid black; text-align: center; vertical-align: bottom;">
                            <p style="margin-bottom: 5px; font-weight: bold;">${signatories.name1 || 'Ms. RICHELLE M. SULIT'}</p>
                            <p>GAD Head Secretariat</p>
                            <p>Date Signed: _______________</p>
                        </td>
                        <td style="padding: 15px 5px; border: 1px solid black; text-align: center; vertical-align: bottom;">
                            <p style="margin-bottom: 5px; font-weight: bold;">${signatories.name3 || 'Mr. REXON S. HERNANDEZ'}</p>
                            <p>Head, Extension Services</p>
                            <p>Date Signed: _______________</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; border: 1px solid black; text-align: center; vertical-align: top;" colspan="2">Approved:</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px 5px; border: 1px solid black; text-align: center; vertical-align: bottom;" colspan="2">
                            <p style="margin-bottom: 5px; font-weight: bold;">${signatories.name4 || 'Dr. FRANCIS G. BALAZON'}</p>
                            <p>Vice Chancellor for Research, Development and Extension Services</p>
                            <p>Date Signed: _______________</p>
                        </td>
                    </tr>
                </table>
                
                <p style="font-size: 9pt; margin-top: 10px;">Required Attachment: Attendance Sheets</p>
                <p style="font-size: 9pt;">Cc: GAD Central</p>
                
                
                
            </div>
        `;
            
            $('#reportPreview').html(html);
        }

        // Helper functions for formatting the narrative report
        function formatExtensionAgenda(agenda, filterOnlyChecked = false) {
            // Define the labels in order
            const labels = [
                'BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program',
                'Livelihood and other Entrepreneurship related on Agri-Fisheries (LEAF)',
                'Environment and Natural Resources Conservation, Protection and Rehabilitation Program',
                'Smart Analytics and Engineering Innovation',
                'Adopt-a-Municipality/Barangay/School/Social Development Thru BIDANI Implementation',
                'Community Outreach',
                'Technical- Vocational Education and Training (TVET) Program',
                'Technology Transfer and Adoption/Utilization Program',
                'Technical Assistance and Advisory Services Program',
                'Parents Empowerment through Social Development (PESODEV)',
                'Gender and Development',
                'Disaster Risk Reduction and Management and Disaster Preparedness and Response/Climate Change Adaptation (DRRM and DPR/CCA)',
            ];
            
            // Parse the agenda data
            let agendaData = [];
            
            console.log('Received agenda data:', agenda);
            
            // If we received a simple array directly from the PHP selected_extension_agendas field
            if (Array.isArray(agenda) && agenda.length > 0 && typeof agenda[0] === 'string') {
                // Convert text items to binary array where matching labels are set to 1
                agendaData = Array(12).fill(0);
                agenda.forEach(text => {
                    const index = labels.findIndex(label => label === text);
                    if (index !== -1) {
                        agendaData[index] = 1;
                    }
                });
                console.log('Converted text items to binary array:', agendaData);
            } 
            // Check if the agenda is a string that needs parsing
            else if (typeof agenda === 'string') {
                try {
                    agendaData = JSON.parse(agenda);
                    console.log('Parsed JSON string:', agendaData);
                } catch (e) {
                    console.error('Failed to parse agenda data:', e);
                    // Check if it's a comma-separated string of indices
                    if (agenda.includes(',')) {
                        // Parse comma-separated values as indices
                        const indices = agenda.split(',').map(i => parseInt(i.trim())).filter(i => !isNaN(i));
                        // Create an array of 0s and set the selected indices to 1
                        agendaData = Array(12).fill(0);
                        indices.forEach(idx => {
                            if (idx >= 0 && idx < 12) {
                                agendaData[idx] = 1;
                            }
                        });
                        console.log('Parsed comma-separated values:', agendaData);
                    } else {
                        // Default to first item selected if parsing fails
                        agendaData = Array(12).fill(0);
                        agendaData[0] = 1; // Set first item as selected by default
                        console.log('Default to first item:', agendaData);
                    }
                }
            } 
            // Handle array of numbers (binary flags)
            else if (Array.isArray(agenda) && agenda.every(item => typeof item === 'number')) {
                agendaData = [...agenda]; // Make a copy to avoid mutation
                console.log('Using numeric array directly:', agendaData);
            } 
            // Handle other complex object formats
            else if (typeof agenda === 'object' && agenda !== null) {
                // Try to extract from object structure
                if (agenda.selected_extension_agendas && Array.isArray(agenda.selected_extension_agendas)) {
                    // Object has array of selected agenda text
                    agendaData = Array(12).fill(0);
                    agenda.selected_extension_agendas.forEach(text => {
                        const index = labels.findIndex(label => label === text);
                        if (index !== -1) {
                            agendaData[index] = 1;
                        }
                    });
                    console.log('Extracted from selected_extension_agendas:', agendaData);
                } else if (agenda.extension_service_agenda && Array.isArray(agenda.extension_service_agenda)) {
                    // Object has direct extension_service_agenda array
                    agendaData = [...agenda.extension_service_agenda];
                    console.log('Extracted from extension_service_agenda:', agendaData);
                } else {
                    // Default with first item selected
                    agendaData = Array(12).fill(0);
                    agendaData[0] = 1; // Set first item as selected by default
                    console.log('Default to first item for object:', agendaData);
                }
            } else {
                // Default with first item selected
                agendaData = Array(12).fill(0);
                agendaData[0] = 1; // Set first item as selected by default
                console.log('Default to first item for fallback:', agendaData);
            }
            
            // Ensure we have 12 elements
            if (agendaData.length < 12) {
                agendaData = [...agendaData, ...Array(12 - agendaData.length).fill(0)];
                console.log('Extended to 12 elements:', agendaData);
            }
            
            // Check if any items are selected
            const hasSelectedItems = agendaData.some(value => value === 1);
            
            // If no items are selected, select the first one
            if (!hasSelectedItems) {
                agendaData[0] = 1;
                console.log('No items selected, defaulting to first item');
            }
            
            // Generate HTML with no borders - using a simple div with paragraphs
            let html = '<div style="width: 100%;">';
            
            // Display items as a list with no borders, just checkboxes
            for (let i = 0; i < labels.length; i++) {
                const symbol = agendaData[i] === 1 ? '☒' : '☐';
                html += `<div style="margin: 2px 0;">${symbol} ${labels[i]}</div>`;
            }
            
            html += '</div>';
            return html;
        }
        
        function formatDuration(duration) {
            if (!duration) return 'N/A';
            
            let formatted = '';
            if (duration.start_date && duration.end_date) {
                if (duration.start_date === duration.end_date) {
                    formatted += `Date: ${duration.start_date}<br>`;
                } else {
                    formatted += `From: ${duration.start_date} To: ${duration.end_date}<br>`;
                }
            }
            
            if (duration.start_time && duration.end_time) {
                formatted += `Time: ${duration.start_time} - ${duration.end_time}<br>`;
            }
            
         
            
            return formatted || 'N/A';
        }
        
        function formatImplementingOffice(office) {
            if (!office) return 'N/A';
            
            // Remove quotes and square brackets if present
            let formatted = office.replace(/['"[\]]/g, '');
            
            // Replace commas with line breaks for multiple offices
            formatted = formatted.replace(/,\s*/g, '<br>');
            
            return formatted;
        }

        function formatSDGs(sdg) {
            if (!sdg) return 'N/A';
            
            // Try to parse JSON if it's a string
            let sdgArray = sdg;
            if (typeof sdg === 'string') {
                try {
                    sdgArray = JSON.parse(sdg);
                } catch (e) {
                    // If parsing fails, treat as a single item
                    sdgArray = [sdg];
                }
            }
            
            // Ensure sdgArray is truly an array
            if (!Array.isArray(sdgArray)) {
                sdgArray = [sdgArray];
            }
            
            // List of all SDGs in the correct order
            const sdgItems = [
                {id: 'SDG 1 - No Poverty', label: 'No Poverty'},
                {id: 'SDG 2 - Zero Hunger', label: 'Zero Hunger'},
                {id: 'SDG 3 - Good Health and Well-being', label: 'Good Health and Well-Being'},
                {id: 'SDG 4 - Quality Education', label: 'Quality Education'},
                {id: 'SDG 5 - Gender Equality', label: 'Gender Equality'},
                {id: 'SDG 6 - Clean Water and Sanitation', label: 'Clean Water and Sanitation'},
                {id: 'SDG 7 - Affordable and Clean Energy', label: 'Affordable and Clean Energy'},
                {id: 'SDG 8 - Decent Work and Economic Growth', label: 'Decent Work and Economic Growth'},
                {id: 'SDG 9 - Industry, Innovation, and Infrastructure', label: 'Industry, Innovation, and Infrastructure'},
                {id: 'SDG 10 - Reduced Inequalities', label: 'Reduced Inequalities'},
                {id: 'SDG 11 - Sustainable Cities and Communities', label: 'Sustainable Cities and Communities'},
                {id: 'SDG 12 - Responsible Consumption and Production', label: 'Responsible Consumption and Production'},
                {id: 'SDG 13 - Climate Action', label: 'Climate Action'},
                {id: 'SDG 14 - Life Below Water', label: 'Life Below Water'},
                {id: 'SDG 15 - Life on Land', label: 'Life on Land'},
                {id: 'SDG 16 - Peace, Justice, and Strong Institutions', label: 'Peace, Justice and Strong Institutions'},
                {id: 'SDG 17 - Partnerships for the Goals', label: 'Partnership for the Goals'}
            ];
            
            let html = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">';
            sdgItems.forEach(item => {
                const isChecked = sdgArray.some(s => s.includes(item.id)) ? '☒' : '☐';
                html += `<div>${isChecked} ${item.label}</div>`;
            });
            html += '</div>';
            
            return html;
        }

        function formatBeneficiaryData(data) {
            if (!data) return 'N/A';
            
            // Format as a table in the style of the sample image
            const internalTotal = parseInt(data.total_internal_male || 0) + parseInt(data.total_internal_female || 0);
            const externalTotal = parseInt(data.external_male || 0) + parseInt(data.external_female || 0);
            const grandTotal = internalTotal + externalTotal;
            
            // Format the table using the style from the sample
            let html = `
            <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
                <tr>
                    <td style="padding: 5px 10px; border: 1px solid black;">Type of participants:</td>
                    <td style="padding: 5px 10px; border: 1px solid black; text-align: center;" colspan="3">
                        <strong>${data.external_type || 'External'}</strong>
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center; border: 1px solid black;"></th>
                    <th style="text-align: center; border: 1px solid black; padding: 5px;">BatStateU Participants</th>
                    <th style="text-align: center; border: 1px solid black; padding: 5px;">Participants from other Institutions</th>
                    <th style="text-align: center; border: 1px solid black; padding: 5px;">Total</th>
                </tr>
                <tr>
                    <td style="text-align: left; border: 1px solid black; padding: 5px 10px;">Male</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${data.total_internal_male || '0'}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${data.external_male || '0'}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${parseInt(data.total_internal_male || 0) + parseInt(data.external_male || 0)}</td>
                </tr>
                <tr>
                    <td style="text-align: left; border: 1px solid black; padding: 5px 10px;">Female</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${data.total_internal_female || '0'}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${data.external_female || '0'}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${parseInt(data.total_internal_female || 0) + parseInt(data.external_female || 0)}</td>
                </tr>
                <tr>
                    <td style="text-align: right; border: 1px solid black; padding: 5px 10px;"><strong>Grand Total</strong></td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${internalTotal}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">${externalTotal}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;"><strong>${grandTotal}</strong></td>
                </tr>
            </table>
            `;
            
            return html;
        }

        function formatSpecificObjectives(objectives) {
            // Remove all console.log statements
            
            if (!objectives || (Array.isArray(objectives) && objectives.length === 0)) {
                return '<ol><li>To implement the activity successfully</li></ol>';
            }
            
            // *** MATCHING PPAS_PROPOSAL IMPLEMENTATION ***
            // If objectives is a string that looks like JSON, try to parse it
            if (typeof objectives === 'string' && (objectives.startsWith('[') || objectives.startsWith('{'))) {
                try {
                    objectives = JSON.parse(objectives);
                } catch (e) {
                    // If parse fails, continue with string handling
                }
            }
            
            // Handle direct array format like in print_proposal.php
            if (Array.isArray(objectives)) {
                return `<ol>${objectives.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
            }
            
            // Handle object format (from JSON fields)
            if (typeof objectives === 'object' && objectives !== null) {
                try {
                    const objArray = Object.values(objectives);
                    if (objArray.length > 0) {
                        return `<ol>${objArray.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    }
                } catch (e) {
                    // Continue to other methods if this fails
                }
            }
            
            // If it's a string, try to parse it as JSON or split by newlines
            if (typeof objectives === 'string') {
                try {
                    const parsed = JSON.parse(objectives);
                    if (Array.isArray(parsed)) {
                        return `<ol>${parsed.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    } else if (typeof parsed === 'object' && parsed !== null) {
                        // If it's a JSON object, convert to array
                        const objArray = Object.values(parsed);
                        return `<ol>${objArray.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    }
                } catch (e) {
                    // If parsing fails, check if it's a newline-separated string
                    if (objectives.includes('\n')) {
                        const objArray = objectives.split('\n').filter(o => o.trim());
                        return `<ol>${objArray.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    }
                    // Check if it has semicolons as separators
                    if (objectives.includes(';')) {
                        const objArray = objectives.split(';').filter(o => o.trim());
                        return `<ol>${objArray.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    }
                    // Check if it has commas as separators
                    if (objectives.includes(',')) {
                        const objArray = objectives.split(',').filter(o => o.trim());
                        return `<ol>${objArray.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
                    }
                    // Otherwise, just display as is with a single bullet
                    return `<ol><li>${objectives}</li></ol>`;
                }
            }
            
            // For other data types, convert to string
            return `<ol><li>${String(objectives || "To implement the activity successfully")}</li></ol>`;
        }

        function formatSimpleTeamMember(data, personnelData) {
            // If we have personnel data from the API, use that instead
            if (personnelData && Array.isArray(personnelData) && personnelData.length > 0) {
                return personnelData.map(person => person.name).join('<br>');
            }
            
            // Otherwise fall back to the existing data
            if (!data) return 'N/A';
            
            try {
                // If it's a string, try to parse it as JSON
                let members = data;
                if (typeof data === 'string') {
                        try {
                        members = JSON.parse(data);
                        } catch (e) {
                        // If parsing fails, return as is
                        return data;
                    }
                }
                
                // Handle case where members is a simple object with names property
                if (typeof members === 'object' && !Array.isArray(members)) {
                    // Check for names property specifically
                    if (members.names) {
                        if (Array.isArray(members.names)) {
                            return members.names.join('<br>');
                        }
                        return members.names;
                    }
                    
                    // If no names property but has name property
                    if (members.name) {
                        return members.name;
                    }
                    
                    // For other cases, just return a placeholder
                    return members.designation || 'N/A';
                }
                
                // If it's an array, just display the items
                if (Array.isArray(members)) {
                    return members.map(item => {
                        if (typeof item === 'string') return item;
                        if (typeof item === 'object' && item.name) return item.name;
                        return JSON.stringify(item);
                    }).join('<br>');
                }
                
                // Default fallback - return as string
                return typeof members === 'string' ? members : 'N/A';
            } catch (e) {
                console.error('Error formatting team members:', e);
                return 'N/A';
            }
        }

        function formatAssignedTasksTable(leaderTasks, assistantTasks, staffTasks, personnelData) {
            let html = '';
            
            // First, consolidate all task data for better handling
            const formatTaskContent = (task) => {
                if (!task) return "No task assigned";
                
                // If it's a string, try to parse it as JSON
                if (typeof task === 'string') {
                    try {
                        const parsed = JSON.parse(task);
                        if (Array.isArray(parsed)) {
                            return parsed.join('<br>');
                        }
                        return task;
                    } catch (e) {
                        // If not valid JSON, return as is
                        return task;
                    }
                }
                
                // If it's already an array, join with line breaks
                if (Array.isArray(task)) {
                    return task.join('<br>');
                }
                
                // Otherwise return as is
                return task;
            };
            
            // Helper function to convert tasks to array format for easy processing
            const normalizeTasksToArray = (tasks) => {
                if (!tasks) return [];
                
                // If it's a string that looks like JSON, try to parse it
                if (typeof tasks === 'string') {
                    if (tasks.startsWith('[') || tasks.startsWith('{')) {
                        try {
                            const parsed = JSON.parse(tasks);
                            if (Array.isArray(parsed)) {
                                return parsed;
                            }
                            return [tasks];
                        } catch (e) {
                            // If parsing fails, just return as single item
                            return [tasks];
                        }
                    }
                    // If it contains newlines, split by newlines
                    if (tasks.includes('\n')) {
                        return tasks.split('\n').filter(t => t.trim());
                    }
                    // Otherwise single item
                    return [tasks];
                }
                
                // If already an array, return as is
                if (Array.isArray(tasks)) {
                    return tasks;
                }
                
                // Otherwise, convert to a single-item array
                return [tasks];
            };
            
            // Format personnel tasks with 1-to-1 mapping between personnel and tasks
            const formatPersonnelTasks = (personnel, tasks) => {
                if (!personnel || !Array.isArray(personnel) || personnel.length === 0) {
                    return '';
                }
                
                let taskHtml = '';
                const formattedTasks = normalizeTasksToArray(tasks);
                
                // One-to-one mapping: each personnel gets one task in order
                personnel.forEach((person, index) => {
                    const name = person.name || 'Unnamed';
                    let task;
                    
                    // If task exists at this index, use it, otherwise use the first task or default message
                    if (formattedTasks[index]) {
                        task = formatTaskContent(formattedTasks[index]);
                    } else if (formattedTasks.length > 0) {
                        task = formatTaskContent(formattedTasks[0]);
                    } else {
                        task = "No specific task assigned";
                    }
                    
                    taskHtml += `
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">${name}</td>
                            <td style="padding: 5px; border: 1px solid black;">${task}</td>
                        </tr>
                    `;
                });
                
                return taskHtml;
            };
            
            // Default roles to display even if no personnel
            const defaultRoles = {
                'project_leaders': 'Project Leader',
                'assistant_project_leaders': 'Assistant Project Leader',
                'project_staff': 'Project Staff'
            };
            
            // Special parsing for responsibilities format
            const parseResponsibilities = (responsibilitiesData) => {
                if (!responsibilitiesData) return [];
                
                // Try to parse as JSON if it's a string
                if (typeof responsibilitiesData === 'string') {
                    try {
                        const parsed = JSON.parse(responsibilitiesData);
                        if (Array.isArray(parsed)) {
                            return parsed;
                        }
                    } catch (e) {
                        // If not valid JSON, split by newlines if applicable
                        if (responsibilitiesData.includes('\n')) {
                            return responsibilitiesData.split('\n').filter(r => r.trim());
                        }
                        return [responsibilitiesData];
                    }
                }
                
                // If already an array, return as is
                if (Array.isArray(responsibilitiesData)) {
                    return responsibilitiesData;
                }
                
                return [String(responsibilitiesData)];
            };
            
            // Process leader tasks from multiple possible sources
            const processedLeaderTasks = normalizeTasksToArray(leaderTasks);
            
            // Process assistant tasks
            const processedAssistantTasks = normalizeTasksToArray(assistantTasks);
            
            // Process staff tasks
            const processedStaffTasks = normalizeTasksToArray(staffTasks);
            
            // Generate the table content
            if (personnelData) {
                // Handle project leaders
                if (personnelData.project_leaders && personnelData.project_leaders.length > 0) {
                    html += formatPersonnelTasks(personnelData.project_leaders, processedLeaderTasks);
                } else {
                    // No personnel but have tasks
                    html += `
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">${defaultRoles.project_leaders}</td>
                            <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedLeaderTasks)}</td>
                        </tr>
                    `;
                }
                
                // Handle assistant project leaders
                if (personnelData.assistant_project_leaders && personnelData.assistant_project_leaders.length > 0) {
                    html += formatPersonnelTasks(personnelData.assistant_project_leaders, processedAssistantTasks);
                } else {
                    // No personnel but have tasks
                    html += `
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">${defaultRoles.assistant_project_leaders}</td>
                            <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedAssistantTasks)}</td>
                        </tr>
                    `;
                }
                
                // Handle project staff
                if (personnelData.project_staff && personnelData.project_staff.length > 0) {
                    html += formatPersonnelTasks(personnelData.project_staff, processedStaffTasks);
                } else {
                    // No personnel but have tasks
                    html += `
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">${defaultRoles.project_staff}</td>
                            <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedStaffTasks)}</td>
                        </tr>
                    `;
                }
            } else {
                // Fallback if no personnel data at all
                html += `
                    <tr>
                        <td style="padding: 5px; border: 1px solid black;">${defaultRoles.project_leaders}</td>
                        <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedLeaderTasks)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; border: 1px solid black;">${defaultRoles.assistant_project_leaders}</td>
                        <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedAssistantTasks)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; border: 1px solid black;">${defaultRoles.project_staff}</td>
                        <td style="padding: 5px; border: 1px solid black;">${formatTaskContent(processedStaffTasks)}</td>
                    </tr>
                `;
            }
            
            return html;
        }

        function calculateTotalRespondents(ratings, participantType) {
            if (!ratings) return '0';
            
            // Handle JSON string format
            if (typeof ratings === 'string') {
                try {
                    ratings = JSON.parse(ratings);
                } catch (e) {
                    console.error('Error parsing ratings JSON:', e);
                    return '0';
                }
            }
            
            // Map participant types
            const participantMap = {
                'batstateu': 'BatStateU',
                'other': 'Others'
            };
            
            // Get the correct participant key
            const participantKey = participantMap[participantType] || participantType;
            
            console.log('Calculating total respondents for participant type:', participantKey);
            
            // Use the proper rating categories
            const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
            
            let total = 0;
            
            // Sum up the values for this participant type across all rating categories
            ratingCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object' && 
                    ratings[category][participantKey] !== undefined) {
                    const count = parseInt(ratings[category][participantKey] || 0);
                    console.log(`${category} ${participantKey}: ${count}`);
                    total += count;
                }
            });
            
            console.log(`Total ${participantKey} respondents: ${total}`);
            return total.toString();
        }
        
        function calculateTotalParticipants(ratings) {
            if (!ratings) return '0';
            
            // Handle JSON string format
            if (typeof ratings === 'string') {
                try {
                    ratings = JSON.parse(ratings);
                } catch (e) {
                    console.error('Error parsing ratings JSON:', e);
                    return '0';
                }
            }
            
            console.log('Calculating total participants');
            
            // Use the proper rating categories
            const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
            
            // Participant types
            const participantTypes = ['BatStateU', 'Others'];
            
            let total = 0;
            
            // Sum up all values across all rating categories and participant types
            ratingCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object') {
                    participantTypes.forEach(participantType => {
                        if (ratings[category][participantType] !== undefined) {
                            const count = parseInt(ratings[category][participantType] || 0);
                            console.log(`${category} ${participantType}: ${count}`);
                            total += count;
                        }
                    });
                }
            });
            
            console.log(`Total participants: ${total}`);
            return total.toString();
        }

        function displayImages(imagesString) {
            if (!imagesString) return '<p>No images available</p>';
            
            try {
                // If images are stored as a JSON string, parse them
                let images = imagesString;
                if (typeof imagesString === 'string') {
                    try {
                        images = JSON.parse(imagesString);
                    } catch (e) {
                        // If it's not valid JSON, treat it as a single image path
                        images = [imagesString];
                    }
                }
                
                if (!Array.isArray(images) || images.length === 0) {
                    return '<p>No images available</p>';
                }
                
                // Display all images in a grid format with exactly 2 per row
                let imagesHtml = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%;">';
                
                images.forEach(image => {
                    // Fix image path - ensure it starts with correct path
                    let imagePath = image;
                    
                    // If the path doesn't start with http or data:, prepend the correct base path
                    if (!imagePath.startsWith('http') && !imagePath.startsWith('data:')) {
                        // Remove any leading slash
                        if (imagePath.startsWith('/')) {
                            imagePath = imagePath.substring(1);
                        }
                        
                        // Use the correct path where images are actually stored
                        imagePath = '../narrative_images/' + imagePath;
                    }
                    
                    imagesHtml += `
                        <div style="margin-bottom: 8px;">
                            <img src="${imagePath}" style="width: 100%; height: 200px; object-fit: contain; border: 1px solid #ddd;" onerror="this.src='../images/placeholder.png'; this.onerror=null;">
                        </div>
                    `;
                });
                
                imagesHtml += '</div>';
                
                return imagesHtml;
            } catch (e) {
                console.error('Error displaying images:', e);
                return '<p>Error displaying images</p>';
            }
        }

        function displayAdditionalImages(imagesString) {
            if (!imagesString) return '<p>No images available</p>';
            
            try {
                // If images are stored as a JSON string, parse them
                let images = imagesString;
                if (typeof imagesString === 'string') {
                    try {
                        images = JSON.parse(imagesString);
                    } catch (e) {
                        // If it's not valid JSON, treat it as a single image path
                        images = [imagesString];
                    }
                }
                
                if (!Array.isArray(images) || images.length === 0) {
                    return '<p>No images available</p>';
                }
                
                // Display all images in a grid format with exactly 2 per row
                let imagesHtml = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%;">';
                
                images.forEach(image => {
                    // Fix image path - ensure it starts with correct path
                    let imagePath = image;
                    
                    // If the path doesn't start with http or data:, prepend the correct base path
                    if (!imagePath.startsWith('http') && !imagePath.startsWith('data:')) {
                        // Remove any leading slash
                        if (imagePath.startsWith('/')) {
                            imagePath = imagePath.substring(1);
                        }
                        
                        // Use the correct path where images are actually stored
                        imagePath = './narrative_images/' + imagePath;
                    }
                    
                    imagesHtml += `
                        <div style="margin-bottom: 10px;">
                            <img src="${imagePath}" style="width: 350px; height: 250px; object-fit: cover; border: 1px solid #ddd;" onerror="this.src='../images/placeholder.png'; this.onerror=null;">
                        </div>
                    `;
                });
                
                imagesHtml += '</div>';
                
                return imagesHtml;
            } catch (e) {
                console.error('Error displaying additional images:', e);
                return '<p>Error displaying images</p>';
            }
        }
  
         // Generate proposal report
    </script>
    <script>
    function updateNotificationBadge(endpoint, action, badgeId) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    
    const formData = new FormData();
    formData.append('action', action);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error fetching count:', error));
}

// Initial load and periodic updates
document.addEventListener('DOMContentLoaded', function() {
    // For approval badge
    updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
    
    // Set interval for updates (only if not on the page with that badge active)
    const isApprovalPage = document.querySelector('.approval-link.active');
    if (!isApprovalPage) {
        setInterval(() => {
            updateNotificationBadge('../approval/gbp_api.php', 'count_pending', 'approvalBadge');
        }, 30000); // Update every 30 seconds
    }
});

    </script>
  </body>
</html>