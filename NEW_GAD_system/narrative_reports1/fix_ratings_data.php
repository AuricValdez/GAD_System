<?php
/**
 * Fix Ratings Data
 * 
 * This script fixes the issue with narrative_entries ratings data not being properly
 * processed in print_narrative.php.
 */

// Define the file path - use the current directory 
$filename = __DIR__ . '/print_narrative.php';

// Read the current file content
$content = file_get_contents($filename);

// Find the position after the ratings_data query and before the ppas_entries check
$searchPattern = "                }
            }
        }
                
                // Also check ppas_entries table for ratings data";

$replacement = "                }
            }
        }
                
                // Process the narrative_entries data if found
                if (\$ratings_data) {
                    error_log(\"Found matching narrative_entries record with ID: \" . \$ratings_data['id']);
                    // Since these are JSON fields, we need to handle them correctly
                    \$activity_ratings_json = !empty(\$ratings_data['activity_ratings']) ? \$ratings_data['activity_ratings'] : 'null';
                    \$timeliness_ratings_json = !empty(\$ratings_data['timeliness_ratings']) ? \$ratings_data['timeliness_ratings'] : 'null';
                    \$activity_images_json = !empty(\$ratings_data['activity_images']) ? \$ratings_data['activity_images'] : 'null';
                    
                    // Make sure we're outputting valid JSON
                    if (\$activity_ratings_json !== 'null') {
                        // Validate it's proper JSON
                        \$decoded = json_decode(\$activity_ratings_json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // If not valid JSON, try to fix it or use null
                            error_log(\"Invalid JSON in activity_ratings: \" . json_last_error_msg());
                            \$activity_ratings_json = 'null';
                        }
                    }
                    
                    if (\$timeliness_ratings_json !== 'null') {
                        // Validate it's proper JSON
                        \$decoded = json_decode(\$timeliness_ratings_json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // If not valid JSON, try to fix it or use null
                            error_log(\"Invalid JSON in timeliness_ratings: \" . json_last_error_msg());
                            \$timeliness_ratings_json = 'null';
                        }
                    }
                    
                    if (\$activity_images_json !== 'null') {
                        // Validate it's proper JSON
                        \$decoded = json_decode(\$activity_images_json);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // If not valid JSON, try to fix it or use null
                            error_log(\"Invalid JSON in activity_images: \" . json_last_error_msg());
                            \$activity_images_json = 'null';
                        }
                    }
                    
                    echo \"const dbActivityRatings = \" . \$activity_ratings_json . \";\\n\";
                    echo \"const dbTimelinessRatings = \" . \$timeliness_ratings_json . \";\\n\";
                    echo \"const dbActivityImages = \" . \$activity_images_json . \";\\n\";
                    
                    // Debug output
                    error_log(\"Activity Ratings from narrative_entries: \" . \$activity_ratings_json);
                    error_log(\"Timeliness Ratings from narrative_entries: \" . \$timeliness_ratings_json);
                    error_log(\"Activity Images from narrative_entries: \" . \$activity_images_json);
                } else {
                    error_log(\"No narrative_entries ratings data found for ppas_form_id: \$ppas_form_id\");
                    echo \"const dbActivityRatings = null;\\n\";
                    echo \"const dbTimelinessRatings = null;\\n\";
                    echo \"const dbActivityImages = null;\\n\";
                }
                
                // Also check ppas_entries table for ratings data";

// Replace the content
$newContent = str_replace($searchPattern, $replacement, $content);

// Fix the transformRatingsToProperFormat function
$searchPatternFunction = "            function transformRatingsToProperFormat(ratingsData) {
                console.log('Transforming ratings data:', ratingsData);
                
                // Initialize proper ratings structure with zeros
                const properRatings = {
                    \"Excellent\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Very Satisfactory\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Satisfactory\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Fair\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Poor\": { \"BatStateU\": 0, \"Others\": 0 }
                };

                try {
                    // If ratingsData is a string, try to parse it
                    let ratings = ratingsData;
                    if (typeof ratingsData === 'string') {
                        try {
                            ratings = JSON.parse(ratingsData);
                            console.log('Successfully parsed ratings from string');
                        } catch (e) {
                            console.error('Failed to parse ratings JSON:', e);
                            return properRatings;
                        }
                    }
                    
                    if (!ratings) {
                        console.log('No ratings data provided');
                        return properRatings;
                    }";

$replacementFunction = "            function transformRatingsToProperFormat(ratingsData) {
                console.log('Transforming ratings data:', ratingsData);
                
                // Initialize proper ratings structure with zeros
                const properRatings = {
                    \"Excellent\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Very Satisfactory\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Satisfactory\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Fair\": { \"BatStateU\": 0, \"Others\": 0 },
                    \"Poor\": { \"BatStateU\": 0, \"Others\": 0 }
                };

                try {
                    // If ratingsData is a string, try to parse it
                    let ratings = ratingsData;
                    if (typeof ratingsData === 'string') {
                        try {
                            ratings = JSON.parse(ratingsData);
                            console.log('Successfully parsed ratings from string');
                        } catch (e) {
                            console.error('Failed to parse ratings JSON:', e);
                            return properRatings;
                        }
                    }
                    
                    if (!ratings || typeof ratings !== 'object') {
                        console.log('No ratings data provided or not an object');
                        return properRatings;
                    }";

// Replace the function definition
$newContent = str_replace($searchPatternFunction, $replacementFunction, $newContent);

// Apply changes to the file
file_put_contents($filename, $newContent);

echo "Fixed the ratings data handling in " . $filename; 