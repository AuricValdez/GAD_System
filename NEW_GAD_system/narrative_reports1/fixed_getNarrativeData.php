<?php
/**
 * Fixed getNarrativeData function - Modified to only use activity_ratings and timeliness_ratings
 * from narrative_entries table directly, without using evaluation field
 */
function getNarrativeData($ppas_form_id) {
    try {
        $pdo = getConnection();

        // Get the PPAS form info first to use for lookup
        $title_sql = "SELECT activity_title, campus, fiscal_year FROM ppas_forms WHERE id = :id";
        $title_stmt = $pdo->prepare($title_sql);
        $title_stmt->bindParam(':id', $ppas_form_id);
        $title_stmt->execute();
        $title_data = $title_stmt->fetch(PDO::FETCH_ASSOC);
        
        $activity_title = $title_data['activity_title'] ?? '';
        $campus = $title_data['campus'] ?? '';
        $year = $title_data['fiscal_year'] ?? date('Y');
        
        error_log("Looking for narrative_entries matching ppas_form_id: '$ppas_form_id', title: '$activity_title', campus: '$campus', year: '$year'");

        // First try to get narrative by ppas_form_id
        $stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE ppas_form_id = :ppas_form_id");
        $stmt->execute([':ppas_form_id' => $ppas_form_id]);
        $narrative_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found by ppas_form_id, try by title
        if (!$narrative_entry) {
            error_log("No narrative found by ppas_form_id, trying by title: $activity_title");
            
            // Try exact title match
            $title_stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE title = :title LIMIT 1");
            $title_stmt->execute([':title' => $activity_title]);
            $narrative_entry = $title_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Still not found, try with LIKE for partial match
            if (!$narrative_entry) {
                error_log("No exact title match, trying with LIKE");
                $like_stmt = $pdo->prepare("SELECT * FROM narrative_entries WHERE title LIKE :title_pattern LIMIT 1");
                $like_stmt->execute([':title_pattern' => '%' . $activity_title . '%']);
                $narrative_entry = $like_stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        if (!$narrative_entry) {
            error_log("ERROR: Failed to find narrative entry by any method");
            return ['error' => 'Narrative not found'];
        }

        // Debug - Log what we found in the database
        error_log("FOUND ENTRY: Narrative Entry ID: " . $narrative_entry['id']);
        error_log("FOUND ENTRY: Activity Ratings: " . (isset($narrative_entry['activity_ratings']) ? substr($narrative_entry['activity_ratings'], 0, 100) . '...' : 'NULL'));
        error_log("FOUND ENTRY: Timeliness Ratings: " . (isset($narrative_entry['timeliness_ratings']) ? substr($narrative_entry['timeliness_ratings'], 0, 100) . '...' : 'NULL'));

        // Process activity_ratings
        $activityRatings = null;
        if (!empty($narrative_entry['activity_ratings'])) {
            $activityRatings = $narrative_entry['activity_ratings'];
            error_log("Using activity_ratings: " . substr($activityRatings, 0, 200));
        } else {
            error_log("WARNING: No activity_ratings found in narrative_entry");
        }

        // Process timeliness_ratings
        $timelinessRatings = null;
        if (!empty($narrative_entry['timeliness_ratings'])) {
            $timelinessRatings = $narrative_entry['timeliness_ratings'];
            error_log("Using timeliness_ratings: " . substr($timelinessRatings, 0, 200));
        } else {
            error_log("WARNING: No timeliness_ratings found in narrative_entry");
        }

        // Set in the return data
        $narrative_entry['activityRatings'] = $activityRatings;
        $narrative_entry['timelinessRatings'] = $timelinessRatings;

        // Debug what we found
        if (!empty($narrative_entry['id'])) {
            error_log("Processing narrative_entry with ID: " . $narrative_entry['id']);
            
            // Activity ratings
            if (!empty($narrative_entry['activity_ratings'])) {
                $activity_ratings_str = $narrative_entry['activity_ratings'];
                error_log("Raw activity_ratings data: " . substr($activity_ratings_str, 0, 200));
                
                // Try to decode and re-encode to ensure valid JSON
                $decoded = json_decode($activity_ratings_str, true);
                if ($decoded !== null) {
                    echo "const dbActivityRatings = " . json_encode($decoded) . ";\n";
                    error_log("Successfully decoded and re-encoded activity_ratings");
                } else {
                    echo "const dbActivityRatings = " . json_encode($activity_ratings_str) . ";\n";
                    error_log("Failed to decode JSON, outputting as string: " . json_last_error_msg());
                }
            } else {
                echo "const dbActivityRatings = null;\n";
                error_log("No activity_ratings found in narrative_entry");
            }
            
            // Timeliness ratings
            if (!empty($narrative_entry['timeliness_ratings'])) {
                $timeliness_ratings_str = $narrative_entry['timeliness_ratings'];
                error_log("Raw timeliness_ratings data: " . substr($timeliness_ratings_str, 0, 200));
                
                // Try to decode and re-encode to ensure valid JSON
                $decoded = json_decode($timeliness_ratings_str, true);
                if ($decoded !== null) {
                    echo "const dbTimelinessRatings = " . json_encode($decoded) . ";\n";
                    error_log("Successfully decoded and re-encoded timeliness_ratings");
                } else {
                    echo "const dbTimelinessRatings = " . json_encode($timeliness_ratings_str) . ";\n";
                    error_log("Failed to decode JSON, outputting as string: " . json_last_error_msg());
                }
            } else {
                echo "const dbTimelinessRatings = null;\n";
                error_log("No timeliness_ratings found in narrative_entry");
            }
            
            // Handle activity images
            if (!empty($narrative_entry['photo_paths'])) {
                $photo_paths_str = $narrative_entry['photo_paths'];
                error_log("Raw photo_paths data: " . substr($photo_paths_str, 0, 200));
                
                // Try to decode and re-encode to ensure valid JSON
                $decoded = json_decode($photo_paths_str, true);
                if ($decoded !== null && is_array($decoded)) {
                    echo "const dbActivityImages = " . json_encode($decoded) . ";\n";
                    error_log("Successfully decoded and re-encoded photo_paths");
                } else if (!empty($narrative_entry['photo_path'])) {
                    echo "const dbActivityImages = " . json_encode([$narrative_entry['photo_path']]) . ";\n";
                    error_log("Using photo_path as fallback");
                } else {
                    echo "const dbActivityImages = [];\n";
                    error_log("No valid photo data found");
                }
            } else if (!empty($narrative_entry['photo_path'])) {
                echo "const dbActivityImages = " . json_encode([$narrative_entry['photo_path']]) . ";\n";
                error_log("Using photo_path as fallback");
            } else {
                echo "const dbActivityImages = [];\n";
                error_log("No photo_paths or photo_path found");
            }
        }
        
        return $narrative_entry;

    } catch (PDOException $e) {
        error_log("Error in getNarrativeData: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}
?> 