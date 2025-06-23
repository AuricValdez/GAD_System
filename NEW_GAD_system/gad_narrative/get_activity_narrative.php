<?php
/**
 * Get activity narrative fields from narrative_entries table
 * 
 * @param int $ppas_form_id The ID of the PPAS form to get narrative data for
 * @param bool $debug Whether to output debug information
 * @return array The narrative data including results, lessons, what_worked, issues, and recommendations
 */
if (!function_exists('getActivityNarrativeFields')) {
    function getActivityNarrativeFields($ppas_form_id, $debug = false) {
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
                echo "<h3>Narrative Fields Debug Information</h3>";
                echo "<p>Database connection successful</p>";
            }
            
            // Get the PPAS form data first to get title, campus, and year
            $ppas_sql = "SELECT * FROM ppas_forms WHERE id = :id";
            $ppas_stmt = $pdo->prepare($ppas_sql);
            $ppas_stmt->bindParam(':id', $ppas_form_id);
            $ppas_stmt->execute();
            $ppas_data = $ppas_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($debug) {
                echo "<p>PPAS Form Query: " . $ppas_sql . " with id = " . $ppas_form_id . "</p>";
                if ($ppas_data) {
                    echo "<p style='color:green;'>Found PPAS form data: " . $ppas_data['activity'] . " (" . $ppas_data['year'] . ")</p>";
                } else {
                    echo "<p style='color:red;'>No PPAS form data found for ID " . $ppas_form_id . "</p>";
                }
            }
            
            $activity_title = $ppas_data['activity'] ?? '';
            $campus = $ppas_data['campus'] ?? '';
            $year = $ppas_data['year'] ?? date('Y');
            
            if ($debug) {
                echo "<p>Title: " . htmlspecialchars($activity_title) . "</p>";
                echo "<p>Campus: " . htmlspecialchars($campus) . "</p>";
                echo "<p>Year: " . $year . "</p>";
            }
            
            // Initialize empty result
            $result = [
                'results' => '',
                'lessons' => '',
                'what_worked' => '',
                'issues' => '',
                'recommendations' => ''
            ];
            
            // Check if narrative_entries table exists
            $table_check = $pdo->query("SHOW TABLES LIKE 'narrative_entries'");
            if ($table_check->rowCount() === 0) {
                if ($debug) {
                    echo "<p style='color:red;'>Error: The 'narrative_entries' table does not exist!</p>";
                    echo "</div>";
                }
                return $result;
            }
            
            // Get narrative_entries table structure for debugging
            if ($debug) {
                $columns_query = $pdo->query("DESCRIBE narrative_entries");
                $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
                echo "<p>narrative_entries table columns:</p>";
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
                }
                echo "</ul>";
                
                // Output count of entries in the table
                $count_query = $pdo->query("SELECT COUNT(*) as count FROM narrative_entries");
                $count_result = $count_query->fetch(PDO::FETCH_ASSOC);
                echo "<p>Total entries in narrative_entries table: <strong>" . $count_result['count'] . "</strong></p>";
            }
            
            // Define field mappings (standard field => possible alternative names)
            $field_mappings = [
                'results' => ['expected_results', 'results'],
                'lessons' => ['lessons_learned', 'lessons'],
                'what_worked' => ['what_worked'],
                'issues' => ['issues_concerns', 'issues'],
                'recommendations' => ['recommendations']
            ];
            
            // Try direct match by ppas_form_id first
            $direct_sql = "SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id LIMIT 1";
            $stmt = $pdo->prepare($direct_sql);
            $stmt->execute([':ppas_form_id' => $ppas_form_id]);
            $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($debug) {
                echo "<p>Direct match query: " . $direct_sql . " with ppas_form_id = " . $ppas_form_id . "</p>";
                if ($narrative_entry_data) {
                    echo "<p style='color:green;'>Found direct match by ppas_form_id</p>";
                } else {
                    echo "<p>No direct match found by ppas_form_id, trying title and year...</p>";
                }
            }
            
            // If no direct match, try by title and year
            if (!$narrative_entry_data) {
                $title_sql = "SELECT * FROM narrative_entries WHERE title = :title AND year = :year LIMIT 1";
                $stmt = $pdo->prepare($title_sql);
                $stmt->bindParam(':title', $activity_title, PDO::PARAM_STR);
                $stmt->bindParam(':year', $year, PDO::PARAM_STR);
                $stmt->execute();
                $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($debug) {
                    echo "<p>Title match query: " . $title_sql . " with title = '" . htmlspecialchars($activity_title) . "' and year = " . $year . "</p>";
                    if ($narrative_entry_data) {
                        echo "<p style='color:green;'>Found match by exact title and year</p>";
                    } else {
                        echo "<p>No match by exact title and year, trying fuzzy title match...</p>";
                    }
                }
                
                // If still no match, try fuzzy title match
                if (!$narrative_entry_data) {
                    $fuzzy_title = "%" . substr($activity_title, 0, 15) . "%";
                    $fuzzy_sql = "SELECT * FROM narrative_entries WHERE title LIKE :fuzzy_title AND year = :year LIMIT 1";
                    $stmt = $pdo->prepare($fuzzy_sql);
                    $stmt->bindParam(':fuzzy_title', $fuzzy_title, PDO::PARAM_STR);
                    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
                    $stmt->execute();
                    $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($debug) {
                        echo "<p>Fuzzy match query: " . $fuzzy_sql . " with fuzzy_title = '" . htmlspecialchars($fuzzy_title) . "' and year = " . $year . "</p>";
                        if ($narrative_entry_data) {
                            echo "<p style='color:green;'>Found match by fuzzy title and year</p>";
                        } else {
                            echo "<p>No match by fuzzy title and year, trying campus and year...</p>";
                        }
                    }
                    
                    // Final fallback: try by campus and year
                    if (!$narrative_entry_data) {
                        $campus_sql = "SELECT * FROM narrative_entries WHERE campus = :campus AND year = :year LIMIT 1";
                        $stmt = $pdo->prepare($campus_sql);
                        $stmt->execute([':campus' => $campus, ':year' => $year]);
                        $narrative_entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($debug) {
                            echo "<p>Campus match query: " . $campus_sql . " with campus = '" . htmlspecialchars($campus) . "' and year = " . $year . "</p>";
                            if ($narrative_entry_data) {
                                echo "<p style='color:green;'>Found match by campus and year</p>";
                            } else {
                                echo "<p style='color:red;'>No matching narrative_entries found with any method</p>";
                                // Show a list of all entries in the table for debugging
                                $all_entries = $pdo->query("SELECT id, title, campus, year FROM narrative_entries LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                                if (count($all_entries) > 0) {
                                    echo "<p>First 10 entries in narrative_entries table:</p>";
                                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                                    echo "<tr><th>ID</th><th>Title</th><th>Campus</th><th>Year</th></tr>";
                                    foreach ($all_entries as $entry) {
                                        echo "<tr>";
                                        echo "<td>" . $entry['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($entry['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($entry['campus']) . "</td>";
                                        echo "<td>" . $entry['year'] . "</td>";
                                        echo "</tr>";
                                    }
                                    echo "</table>";
                                } else {
                                    echo "<p>No entries found in narrative_entries table.</p>";
                                }
                            }
                        }
                    }
                }
            }
            
            // If we found data, extract the requested fields
            if ($narrative_entry_data) {
                if ($debug) {
                    echo "<p style='color:green;'>Found narrative entry with ID: " . $narrative_entry_data['id'] . "</p>";
                    echo "<p>Entry details: Title = '" . htmlspecialchars($narrative_entry_data['title']) . "', Campus = '" . 
                         htmlspecialchars($narrative_entry_data['campus']) . "', Year = '" . $narrative_entry_data['year'] . "'</p>";
                }
                
                // Check for each field using the field mappings
                foreach ($field_mappings as $standard_field => $alternate_fields) {
                    $result[$standard_field] = '';
                    
                    // Try each possible field name
                    foreach ($alternate_fields as $field_name) {
                        if (isset($narrative_entry_data[$field_name]) && !empty($narrative_entry_data[$field_name])) {
                            $result[$standard_field] = $narrative_entry_data[$field_name];
                            if ($debug) {
                                echo "<p>Found value for <strong>$standard_field</strong> using field <strong>$field_name</strong></p>";
                            }
                            break;
                        }
                    }
                    
                    if ($debug && empty($result[$standard_field])) {
                        echo "<p style='color:orange;'>No value found for <strong>$standard_field</strong> (tried: " . implode(", ", $alternate_fields) . ")</p>";
                    }
                }
                
                if ($debug) {
                    echo "<p>Extracted fields:</p>";
                    echo "<ul>";
                    foreach ($result as $key => $value) {
                        echo "<li>$key: " . (empty($value) ? "<span style='color:red;'>EMPTY</span>" : "<span style='color:green;'>FOUND (" . strlen($value) . " chars)</span>") . "</li>";
                    }
                    echo "</ul>";
                }
            } else if ($debug) {
                echo "<p style='color:red;'>No matching narrative entry found in narrative_entries table.</p>";
            }
            
            if ($debug) {
                echo "</div>";
            }
            
            return $result;
        } catch (PDOException $e) {
            if ($debug) {
                echo "<p style='color:red;'>Error in getActivityNarrativeFields: " . $e->getMessage() . "</p>";
                if (isset($pdo)) {
                    echo "</div>";
                }
            }
            error_log("Error in getActivityNarrativeFields: " . $e->getMessage());
            return [
                'results' => '',
                'lessons' => '',
                'what_worked' => '',
                'issues' => '',
                'recommendations' => ''
            ];
        }
    }
}

// Example usage:
// $narrative_fields = getActivityNarrativeFields($ppas_form_id);
// echo "Results: " . $narrative_fields['results'];
// echo "Lessons: " . $narrative_fields['lessons'];
// echo "What worked: " . $narrative_fields['what_worked'];
// echo "Issues: " . $narrative_fields['issues'];
// echo "Recommendations: " . $narrative_fields['recommendations'];
?> 