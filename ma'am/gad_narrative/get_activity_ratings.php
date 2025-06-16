<?php
/**
 * Function to get activity and timeliness ratings from narrative_entries table
 * 
 * @param int $ppas_form_id The ID of the PPAS form to get ratings data for
 * @param bool $debug Whether to output debug information
 * @return array The ratings data including activity_ratings and timeliness_ratings
 */
if (!function_exists('getActivityRatings')) {
    function getActivityRatings($ppas_form_id, $debug = false) {
        try {
            // Get database connection
            $pdo = new PDO(
                "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            if ($debug) {
                echo "<div style='background-color: #f5f5f5; padding: 15px; margin: 10px 0; border: 1px solid #ddd;'>";
                echo "<h3>Ratings Data Debug Information</h3>";
                echo "<p>Database connection successful</p>";
            }
            
            // Default empty ratings structure
            $default_ratings = [
                'Excellent' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0],
                'Very Satisfactory' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0],
                'Satisfactory' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0],
                'Fair' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0],
                'Poor' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0],
                'Total' => ['BatStateU' => 0, 'Others' => 0, 'Total' => 0]
            ];
            
            // Initialize result with default structure
            $result = [
                'activity_ratings' => $default_ratings,
                'timeliness_ratings' => $default_ratings
            ];
            
            // Try direct match by ppas_form_id first
            $direct_sql = "SELECT activity_ratings, timeliness_ratings FROM narrative_entries WHERE ppas_form_id = :ppas_form_id LIMIT 1";
            $stmt = $pdo->prepare($direct_sql);
            $stmt->execute([':ppas_form_id' => $ppas_form_id]);
            $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($debug) {
                echo "<p>Direct match query: " . $direct_sql . " with ppas_form_id = " . $ppas_form_id . "</p>";
                if ($narrative_entry_data) {
                    echo "<p style='color:green;'>Found direct match by ppas_form_id</p>";
                } else {
                    echo "<p>No direct match found by ppas_form_id, trying alternative methods...</p>";
                    
                    // Get PPAS form details to try alternative matching
                    $ppas_sql = "SELECT activity, campus, year FROM ppas_forms WHERE id = :id";
                    $ppas_stmt = $pdo->prepare($ppas_sql);
                    $ppas_stmt->bindParam(':id', $ppas_form_id);
                    $ppas_stmt->execute();
                    $ppas_data = $ppas_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ppas_data) {
                        echo "<p>PPAS form found: " . htmlspecialchars($ppas_data['activity']) . " (" . $ppas_data['year'] . ")</p>";
                        
                        // Try matching by title and year
                        $title_sql = "SELECT activity_ratings, timeliness_ratings FROM narrative_entries WHERE title = :title AND year = :year LIMIT 1";
                        $stmt = $pdo->prepare($title_sql);
                        $stmt->bindParam(':title', $ppas_data['activity'], PDO::PARAM_STR);
                        $stmt->bindParam(':year', $ppas_data['year'], PDO::PARAM_STR);
                        $stmt->execute();
                        $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($narrative_entry_data) {
                            echo "<p style='color:green;'>Found match by title and year</p>";
                        } else {
                            echo "<p>No match by title and year either</p>";
                        }
                    } else {
                        echo "<p style='color:red;'>Could not find PPAS form with ID " . $ppas_form_id . "</p>";
                    }
                }
            }
            
            // If we found data, process the ratings
            if ($narrative_entry_data) {
                // Process activity ratings
                if (!empty($narrative_entry_data['activity_ratings'])) {
                    $ratings_data = $narrative_entry_data['activity_ratings'];
                    
                    // Check if it's already a JSON string or an array
                    if (is_string($ratings_data)) {
                        $activity_ratings = json_decode($ratings_data, true);
                        if ($debug) {
                            echo "<p>Parsed activity_ratings from JSON string</p>";
                        }
                    } else {
                        $activity_ratings = $ratings_data;
                        if ($debug) {
                            echo "<p>Activity ratings data was already an array</p>";
                        }
                    }
                    
                    // Process the ratings into our standard format if valid
                    if (is_array($activity_ratings)) {
                        if ($debug) {
                            echo "<p>Raw activity ratings data: " . (is_string($ratings_data) ? $ratings_data : json_encode($ratings_data)) . "</p>";
                        }
                        
                        // Process based on detected structure
                        if (isset($activity_ratings['Excellent'])) {
                            // Structure: {'Excellent': {'BatStateU': X, 'Others': Y}, ...}
                            foreach ($result['activity_ratings'] as $rating => $counts) {
                                if ($rating != 'Total' && isset($activity_ratings[$rating])) {
                                    $result['activity_ratings'][$rating]['BatStateU'] = intval($activity_ratings[$rating]['BatStateU'] ?? 0);
                                    $result['activity_ratings'][$rating]['Others'] = intval($activity_ratings[$rating]['Others'] ?? 0);
                                    $result['activity_ratings'][$rating]['Total'] = 
                                        $result['activity_ratings'][$rating]['BatStateU'] + 
                                        $result['activity_ratings'][$rating]['Others'];
                                    
                                    // Add to totals
                                    $result['activity_ratings']['Total']['BatStateU'] += $result['activity_ratings'][$rating]['BatStateU'];
                                    $result['activity_ratings']['Total']['Others'] += $result['activity_ratings'][$rating]['Others'];
                                    $result['activity_ratings']['Total']['Total'] += $result['activity_ratings'][$rating]['Total'];
                                }
                            }
                        } else {
                            if ($debug) {
                                echo "<p style='color:orange;'>Activity ratings not in expected format</p>";
                            }
                        }
                    } else {
                        if ($debug) {
                            echo "<p style='color:red;'>Failed to decode activity ratings JSON: " . json_last_error_msg() . "</p>";
                        }
                    }
                } else if ($debug) {
                    echo "<p style='color:orange;'>No activity_ratings data found</p>";
                }
                
                // Process timeliness ratings
                if (!empty($narrative_entry_data['timeliness_ratings'])) {
                    $ratings_data = $narrative_entry_data['timeliness_ratings'];
                    
                    // Check if it's already a JSON string or an array
                    if (is_string($ratings_data)) {
                        $timeliness_ratings = json_decode($ratings_data, true);
                        if ($debug) {
                            echo "<p>Parsed timeliness_ratings from JSON string</p>";
                        }
                    } else {
                        $timeliness_ratings = $ratings_data;
                        if ($debug) {
                            echo "<p>Timeliness ratings data was already an array</p>";
                        }
                    }
                    
                    // Process the ratings into our standard format if valid
                    if (is_array($timeliness_ratings)) {
                        if ($debug) {
                            echo "<p>Raw timeliness ratings data: " . (is_string($ratings_data) ? $ratings_data : json_encode($ratings_data)) . "</p>";
                        }
                        
                        // Process based on detected structure
                        if (isset($timeliness_ratings['Excellent'])) {
                            // Structure: {'Excellent': {'BatStateU': X, 'Others': Y}, ...}
                            foreach ($result['timeliness_ratings'] as $rating => $counts) {
                                if ($rating != 'Total' && isset($timeliness_ratings[$rating])) {
                                    $result['timeliness_ratings'][$rating]['BatStateU'] = intval($timeliness_ratings[$rating]['BatStateU'] ?? 0);
                                    $result['timeliness_ratings'][$rating]['Others'] = intval($timeliness_ratings[$rating]['Others'] ?? 0);
                                    $result['timeliness_ratings'][$rating]['Total'] = 
                                        $result['timeliness_ratings'][$rating]['BatStateU'] + 
                                        $result['timeliness_ratings'][$rating]['Others'];
                                    
                                    // Add to totals
                                    $result['timeliness_ratings']['Total']['BatStateU'] += $result['timeliness_ratings'][$rating]['BatStateU'];
                                    $result['timeliness_ratings']['Total']['Others'] += $result['timeliness_ratings'][$rating]['Others'];
                                    $result['timeliness_ratings']['Total']['Total'] += $result['timeliness_ratings'][$rating]['Total'];
                                }
                            }
                        } else {
                            if ($debug) {
                                echo "<p style='color:orange;'>Timeliness ratings not in expected format</p>";
                            }
                        }
                    } else {
                        if ($debug) {
                            echo "<p style='color:red;'>Failed to decode timeliness ratings JSON: " . json_last_error_msg() . "</p>";
                        }
                    }
                } else if ($debug) {
                    echo "<p style='color:orange;'>No timeliness_ratings data found</p>";
                }
            } else if ($debug) {
                echo "<p style='color:red;'>No narrative entry found for this PPAS form ID</p>";
            }
            
            if ($debug) {
                echo "<h4>Processed Activity Ratings:</h4>";
                echo "<pre>" . print_r($result['activity_ratings'], true) . "</pre>";
                
                echo "<h4>Processed Timeliness Ratings:</h4>";
                echo "<pre>" . print_r($result['timeliness_ratings'], true) . "</pre>";
                
                echo "</div>";
            }
            
            return $result;
        } catch (PDOException $e) {
            if ($debug) {
                echo "<p style='color:red;'>Error in getActivityRatings: " . $e->getMessage() . "</p>";
                if (isset($pdo)) {
                    echo "</div>";
                }
            }
            error_log("Error in getActivityRatings: " . $e->getMessage());
            return [
                'activity_ratings' => $default_ratings ?? [],
                'timeliness_ratings' => $default_ratings ?? []
            ];
        }
    }
}

/**
 * Function to display ratings in an HTML table
 * 
 * @param array $ratings The ratings data to display
 * @param string $title The title for the ratings table
 * @return string HTML table displaying the ratings
 */
if (!function_exists('displayRatingsTable')) {
    function displayRatingsTable($ratings, $title) {
        $html = '<div class="ratings-table-container">';
        $html .= '<h4>' . htmlspecialchars($title) . '</h4>';
        $html .= '<table class="table table-bordered table-sm">';
        
        // Header row
        $html .= '<thead class="thead-light">';
        $html .= '<tr>';
        $html .= '<th>Rating</th>';
        $html .= '<th>BatStateU participants</th>';
        $html .= '<th>Participants from other Institutions</th>';
        $html .= '<th>Total</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Body rows
        $html .= '<tbody>';
        
        // Rating categories (excluding Total which we'll add last)
        $rating_categories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
        
        foreach ($rating_categories as $rating) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($rating) . '</td>';
            $html .= '<td>' . (isset($ratings[$rating]['BatStateU']) ? $ratings[$rating]['BatStateU'] : 0) . '</td>';
            $html .= '<td>' . (isset($ratings[$rating]['Others']) ? $ratings[$rating]['Others'] : 0) . '</td>';
            $html .= '<td>' . (isset($ratings[$rating]['Total']) ? $ratings[$rating]['Total'] : 0) . '</td>';
            $html .= '</tr>';
        }
        
        // Total row
        $html .= '<tr class="font-weight-bold">';
        $html .= '<td>Total Respondents:</td>';
        $html .= '<td>' . (isset($ratings['Total']['BatStateU']) ? $ratings['Total']['BatStateU'] : 0) . '</td>';
        $html .= '<td>' . (isset($ratings['Total']['Others']) ? $ratings['Total']['Others'] : 0) . '</td>';
        $html .= '<td>' . (isset($ratings['Total']['Total']) ? $ratings['Total']['Total'] : 0) . '</td>';
        $html .= '</tr>';
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
}

// Example usage:
// $ppas_form_id = 1;
// $ratings_data = getActivityRatings($ppas_form_id);
// echo displayRatingsTable($ratings_data['activity_ratings'], 'Number of beneficiaries/participants who rated the activity as:');
// echo displayRatingsTable($ratings_data['timeliness_ratings'], 'Number of beneficiaries/participants who rated the timeliness of the activity as:');
?> 