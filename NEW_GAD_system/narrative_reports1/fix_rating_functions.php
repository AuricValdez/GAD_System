<?php
/**
 * Fix Rating Functions
 * 
 * This script fixes the JavaScript rating functions in print_narrative.php
 * to ensure they handle the data from narrative_entries correctly.
 */

// Define the file path - use the current directory 
$filename = __DIR__ . '/print_narrative.php';

// Read the current file content
$content = file_get_contents($filename);

// Fix the extractRatingValue function
$searchPattern = "function extractRatingValue(ratings, ratingType, participantType) {
                try {
                    if (!ratings) return 0;
                    
                    // Log for debugging
                    console.log(`Extracting rating: ${ratingType}, participant: ${participantType}`, ratings);
                    
                    // Convert rating type to proper case format
                    const ratingMap = {
                        'excellent': 'Excellent',
                        'very_satisfactory': 'Very Satisfactory',
                        'satisfactory': 'Satisfactory',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };

                    const properRatingType = ratingMap[ratingType] || ratingType;
                    const properParticipantType = participantType === 'batstateu' ? 'BatStateU' : 'Others';
                    
                    // First try standard format
                    if (ratings[properRatingType] && ratings[properRatingType][properParticipantType] !== undefined) {
                        return parseInt(ratings[properRatingType][properParticipantType]) || 0;
                    }
                    
                    // Try lowercase keys
                    if (ratings[ratingType] && ratings[ratingType][participantType] !== undefined) {
                        return parseInt(ratings[ratingType][participantType]) || 0;
                    }
                    
                    // Try direct access (flat structure)
                    const flatKey = `${ratingType}_${participantType}`;
                    if (ratings[flatKey] !== undefined) {
                        return parseInt(ratings[flatKey]) || 0;
                    }
                    
                    // Check if ratings has lowercase version of properRatingType
                    const lowerCaseRatingType = properRatingType.toLowerCase();
                    if (ratings[lowerCaseRatingType] && ratings[lowerCaseRatingType][participantType] !== undefined) {
                        return parseInt(ratings[lowerCaseRatingType][participantType]) || 0;
                    }
                    
                    // Replace spaces with underscores for alternative format
                    const underscoreRatingType = ratingType.replace(/ /g, '_').toLowerCase();
                    if (ratings[underscoreRatingType] && ratings[underscoreRatingType][participantType] !== undefined) {
                        return parseInt(ratings[underscoreRatingType][participantType]) || 0;
                    }

                    // Return 0 if no rating found
return 0;
                } catch (e) {
                    console.error('Error extracting rating value:', e);
                    // Return 0 instead of hardcoded values
return 0;
                }
            }";

$replacement = "function extractRatingValue(ratings, ratingType, participantType) {
                try {
                    if (!ratings) return 0;
                    
                    // Log for debugging
                    console.log(`Extracting rating: ${ratingType}, participant: ${participantType}`, ratings);
                    
                    // Convert rating type to proper case format
                    const ratingMap = {
                        'excellent': 'Excellent',
                        'very_satisfactory': 'Very Satisfactory',
                        'satisfactory': 'Satisfactory',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };

                    const properRatingType = ratingMap[ratingType] || ratingType;
                    const properParticipantType = participantType === 'batstateu' ? 'BatStateU' : 'Others';
                    
                    // First try standard format
                    if (ratings[properRatingType] && ratings[properRatingType][properParticipantType] !== undefined) {
                        return parseInt(ratings[properRatingType][properParticipantType]) || 0;
                    }
                    
                    // Try lowercase keys
                    if (ratings[ratingType] && ratings[ratingType][participantType] !== undefined) {
                        return parseInt(ratings[ratingType][participantType]) || 0;
                    }
                    
                    // Try direct access (flat structure)
                    const flatKey = `${ratingType}_${participantType}`;
                    if (ratings[flatKey] !== undefined) {
                        return parseInt(ratings[flatKey]) || 0;
                    }
                    
                    // Check if ratings has lowercase version of properRatingType
                    const lowerCaseRatingType = properRatingType.toLowerCase();
                    if (ratings[lowerCaseRatingType] && ratings[lowerCaseRatingType][participantType] !== undefined) {
                        return parseInt(ratings[lowerCaseRatingType][participantType]) || 0;
                    }
                    
                    // Replace spaces with underscores for alternative format
                    const underscoreRatingType = ratingType.replace(/ /g, '_').toLowerCase();
                    if (ratings[underscoreRatingType] && ratings[underscoreRatingType][participantType] !== undefined) {
                        return parseInt(ratings[underscoreRatingType][participantType]) || 0;
                    }

                    // Also try handling 'very satisfactory' vs 'very_satisfactory'
                    if (ratingType === 'very_satisfactory') {
                        const altFormat = 'very satisfactory';
                        if (ratings[altFormat] && ratings[altFormat][participantType] !== undefined) {
                            return parseInt(ratings[altFormat][participantType]) || 0;
                        }
                    }

                    // Return 0 if no rating found
                    return 0;
                } catch (e) {
                    console.error('Error extracting rating value:', e);
                    // Return 0 instead of hardcoded values
                    return 0;
                }
            }";

// Replace the content
$newContent = str_replace($searchPattern, $replacement, $content);

// Fix the calculateRatingTotal function
$searchPatternTotal = "function calculateRatingTotal(ratings, ratingType) {
                try {
                    if (!ratings) return 0; // Default to 0 if no ratings
                    
                    // Convert rating type to proper case format
                    const ratingMap = {
                        'excellent': 'Excellent',
                        'very_satisfactory': 'Very Satisfactory',
                        'satisfactory': 'Satisfactory',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };

                    const properRatingType = ratingMap[ratingType] || ratingType;
                    
                    // Check if the ratings object has the expected structure
                    if (ratings[properRatingType]) {
                        const batStateU = parseInt(ratings[properRatingType].BatStateU) || 0;
                        const others = parseInt(ratings[properRatingType].Others) || 0;
                        return batStateU + others;
                    }

                    // Try lowercase keys
                    if (ratings[ratingType]) {
                        const batStateU = parseInt(ratings[ratingType].batstateu) || 0;
                        const others = parseInt(ratings[ratingType].other) || 0;
                        return batStateU + others;
                    }
                    
                    // Try flat structure
                    const batStateUKey = `${ratingType}_batstateu`;
                    const othersKey = `${ratingType}_other`;
                    if (ratings[batStateUKey] !== undefined && ratings[othersKey] !== undefined) {
                        return (parseInt(ratings[batStateUKey]) || 0) + (parseInt(ratings[othersKey]) || 0);
                    }
                    
                    // Check if ratings has lowercase version of properRatingType
                    const lowerCaseRatingType = properRatingType.toLowerCase();
                    if (ratings[lowerCaseRatingType]) {
                        const batStateU = parseInt(ratings[lowerCaseRatingType].batstateu) || 0;
                        const others = parseInt(ratings[lowerCaseRatingType].other) || 0;
                        return batStateU + others;
                    }

                    // Return 0 if no rating found
return 0;";

$replacementTotal = "function calculateRatingTotal(ratings, ratingType) {
                try {
                    if (!ratings) return 0; // Default to 0 if no ratings
                    
                    // Convert rating type to proper case format
                    const ratingMap = {
                        'excellent': 'Excellent',
                        'very_satisfactory': 'Very Satisfactory',
                        'satisfactory': 'Satisfactory',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };

                    const properRatingType = ratingMap[ratingType] || ratingType;
                    
                    // Check if the ratings object has the expected structure
                    if (ratings[properRatingType]) {
                        const batStateU = parseInt(ratings[properRatingType].BatStateU) || 0;
                        const others = parseInt(ratings[properRatingType].Others) || 0;
                        return batStateU + others;
                    }

                    // Try lowercase keys
                    if (ratings[ratingType]) {
                        const batStateU = parseInt(ratings[ratingType].batstateu) || 0;
                        const others = parseInt(ratings[ratingType].other) || 0;
                        return batStateU + others;
                    }
                    
                    // Try flat structure
                    const batStateUKey = `${ratingType}_batstateu`;
                    const othersKey = `${ratingType}_other`;
                    if (ratings[batStateUKey] !== undefined && ratings[othersKey] !== undefined) {
                        return (parseInt(ratings[batStateUKey]) || 0) + (parseInt(ratings[othersKey]) || 0);
                    }
                    
                    // Check if ratings has lowercase version of properRatingType
                    const lowerCaseRatingType = properRatingType.toLowerCase();
                    if (ratings[lowerCaseRatingType]) {
                        const batStateU = parseInt(ratings[lowerCaseRatingType].batstateu) || 0;
                        const others = parseInt(ratings[lowerCaseRatingType].other) || 0;
                        return batStateU + others;
                    }
                    
                    // For 'very_satisfactory', try 'very satisfactory' format
                    if (ratingType === 'very_satisfactory') {
                        const altFormat = 'very satisfactory';
                        if (ratings[altFormat]) {
                            const batStateU = parseInt(ratings[altFormat].batstateu || ratings[altFormat].BatStateU) || 0;
                            const others = parseInt(ratings[altFormat].others || ratings[altFormat].Others) || 0;
                            return batStateU + others;
                        }
                    }

                    // Return 0 if no rating found
                    return 0;";
                    
// Replace the calculateRatingTotal function
$newContent = str_replace($searchPatternTotal, $replacementTotal, $newContent);

// Apply changes to the file
file_put_contents($filename, $newContent);

echo "Fixed the rating functions (extractRatingValue and calculateRatingTotal) in " . $filename; 