<?php
/**
 * Fix JavaScript Ratings Functions
 * 
 * This script fixes the JavaScript functions that process ratings data
 * to properly handle the JSON structure from narrative_entries.
 */

// Define the file path - use the current directory 
$filename = __DIR__ . '/print_narrative.php';

// Read the current file content
$content = file_get_contents($filename);

// Replace the rest of the transformRatingsToProperFormat function
$searchPattern = "                    if (!ratings || typeof ratings !== 'object') {
                        console.log('No ratings data provided or not an object');
                        return properRatings;
                    }

                    // Check if data is in the narrative_entries format
                    // Look for male_count, female_count, or count properties
                    const isNarrativeEntriesFormat = 
                        ratings.Excellent && 
                        (ratings.Excellent.count !== undefined || 
                         ratings.Excellent.male_count !== undefined);
                    
                    // Check for nested format (activity/timeliness structure)
                    const isNestedFormat = 
                        ratings.activity || ratings.timeliness;
                    
                    if (isNestedFormat) {
                        console.log('Found nested format with activity/timeliness properties');
                        // Use the activity ratings if available
                        if (ratings.activity && typeof ratings.activity === 'object') {
                            ratings = ratings.activity;
                            console.log('Using activity ratings from nested structure');
                        }
                    }
                    
                    if (isNarrativeEntriesFormat) {
                        console.log('Using narrative_entries format for ratings');
                        // Process ratings in narrative_entries format
                        for (const rating in ratings) {
                            if (properRatings[rating]) {
                                const count = parseInt(ratings[rating].count || 0);
                                const maleCount = parseInt(ratings[rating].male_count || 0);
                                const femaleCount = parseInt(ratings[rating].female_count || 0);
                                
                                // Use specific count or sum of male+female if available
                                const totalCount = count || (maleCount + femaleCount);
                                
                                // For narrative_entries, assume all are BatStateU
                                properRatings[rating].BatStateU = totalCount;
                                properRatings[rating].Others = 0;
                            }
                        }
                    } else {
                        console.log('Using original narrative format for ratings');
                        // Directly map the data from the original database structure
                    for (const rating in ratings) {
                        if (properRatings[rating]) {
                                // Handle both capitalized and lowercase property names
                                const batStateUValue = ratings[rating].BatStateU !== undefined ? 
                                    ratings[rating].BatStateU : 
                                    (ratings[rating].batstateu !== undefined ? 
                                        ratings[rating].batstateu : 0);
                                
                                const othersValue = ratings[rating].Others !== undefined ? 
                                    ratings[rating].Others : 
                                    (ratings[rating].others !== undefined ? 
                                        ratings[rating].others : 0);
                                
                                properRatings[rating].BatStateU = parseInt(batStateUValue) || 0;
                                properRatings[rating].Others = parseInt(othersValue) || 0;
                            }
                        }
                        
                        // Check for lowercase keys if no data mapped yet
                        let hasData = false;
                        for (const rating in properRatings) {
                            if (properRatings[rating].BatStateU > 0 || properRatings[rating].Others > 0) {
                                hasData = true;
                                break;
                            }
                        }
                        
                        if (!hasData) {
                            console.log('No data mapped with standard keys, trying lowercase mapping');
                            // Try mapping with lowercase keys
                            const lowercaseMap = {
                                'excellent': 'Excellent',
                                'very_satisfactory': 'Very Satisfactory',
                                'satisfactory': 'Satisfactory',
                                'fair': 'Fair',
                                'poor': 'Poor'
                            };
                            
                            for (const lowKey in lowercaseMap) {
                                const standardKey = lowercaseMap[lowKey];
                                if (ratings[lowKey]) {
                                    const batStateUValue = ratings[lowKey].batstateu || ratings[lowKey].BatStateU || 0;
                                    const othersValue = ratings[lowKey].others || ratings[lowKey].Others || 0;
                                    
                                    properRatings[standardKey].BatStateU = parseInt(batStateUValue) || 0;
                                    properRatings[standardKey].Others = parseInt(othersValue) || 0;
                                }
                            }
                        }
                    }";

$replacement = "                    if (!ratings || typeof ratings !== 'object') {
                        console.log('No ratings data provided or not an object');
                        return properRatings;
                    }

                    // First, try to handle the expected format from narrative_entries
                    // Should be like: {\"Excellent\":{\"BatStateU\":5,\"Others\":2},\"Very Satisfactory\":...}
                    let foundAnyData = false;
                    for (const ratingKey in properRatings) {
                        if (ratings[ratingKey] && typeof ratings[ratingKey] === 'object') {
                            // Try to get BatStateU and Others values
                            if (ratings[ratingKey].BatStateU !== undefined) {
                                properRatings[ratingKey].BatStateU = parseInt(ratings[ratingKey].BatStateU) || 0;
                                foundAnyData = true;
                            }
                            if (ratings[ratingKey].Others !== undefined) {
                                properRatings[ratingKey].Others = parseInt(ratings[ratingKey].Others) || 0;
                                foundAnyData = true;
                            }
                        }
                    }
                    
                    // If we found data in the expected format, return the result
                    if (foundAnyData) {
                        console.log('Found data in expected format');
                        return properRatings;
                    }
                    
                    // Try alternative format with lowercase keys
                    // {\"excellent\":{\"batstateu\":5,\"others\":2},...}
                    const lowercaseMap = {
                        'excellent': 'Excellent',
                        'very_satisfactory': 'Very Satisfactory',
                        'very satisfactory': 'Very Satisfactory',
                        'satisfactory': 'Satisfactory',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };
                    
                    for (const lowKey in lowercaseMap) {
                        const properKey = lowercaseMap[lowKey];
                        if (ratings[lowKey] && typeof ratings[lowKey] === 'object') {
                            // Try batstateu/others keys
                            if (ratings[lowKey].batstateu !== undefined) {
                                properRatings[properKey].BatStateU = parseInt(ratings[lowKey].batstateu) || 0;
                                foundAnyData = true;
                            }
                            if (ratings[lowKey].others !== undefined) {
                                properRatings[properKey].Others = parseInt(ratings[lowKey].others) || 0;
                                foundAnyData = true;
                            }
                            
                            // Also try BatStateU/Others with capital letters
                            if (ratings[lowKey].BatStateU !== undefined) {
                                properRatings[properKey].BatStateU = parseInt(ratings[lowKey].BatStateU) || 0;
                                foundAnyData = true;
                            }
                            if (ratings[lowKey].Others !== undefined) {
                                properRatings[properKey].Others = parseInt(ratings[lowKey].Others) || 0;
                                foundAnyData = true;
                            }
                        }
                    }
                    
                    // Check if data is in another format (e.g., with underscores)
                    if (!foundAnyData) {
                        const underscoreMap = {
                            'excellent': 'Excellent',
                            'very_satisfactory': 'Very Satisfactory',
                            'satisfactory': 'Satisfactory',
                            'fair': 'Fair',
                            'poor': 'Poor'
                        };
                        
                        for (const key in underscoreMap) {
                            const properKey = underscoreMap[key];
                            // Try direct flat structure like excellent_batstateu
                            const batstateuKey = `${key}_batstateu`;
                            const othersKey = `${key}_others`;
                            
                            if (ratings[batstateuKey] !== undefined) {
                                properRatings[properKey].BatStateU = parseInt(ratings[batstateuKey]) || 0;
                                foundAnyData = true;
                            }
                            if (ratings[othersKey] !== undefined) {
                                properRatings[properKey].Others = parseInt(ratings[othersKey]) || 0;
                                foundAnyData = true;
                            }
                        }
                    }";

// Replace the content
$newContent = str_replace($searchPattern, $replacement, $content);

// Now fix the calculateRatingTotal function
$searchPatternTotal = "            function calculateRatingTotal(ratings, ratingType) {
                try {
                    if (!ratings) return 0; // Default total is 3 if no ratings";

$replacementTotal = "            function calculateRatingTotal(ratings, ratingType) {
                try {
                    if (!ratings) return 0; // Default to 0 if no ratings";
                    
// Replace the content
$newContent = str_replace($searchPatternTotal, $replacementTotal, $newContent);

// Apply changes to the file
file_put_contents($filename, $newContent);

echo "Fixed the JavaScript ratings functions in " . $filename; 