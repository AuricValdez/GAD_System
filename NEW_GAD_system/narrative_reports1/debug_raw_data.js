// Debug script for narrative_reports
console.log('Loading debug script for raw data extraction');

// Function to extract individual ratings
function extractRawRatingValue(ratings, ratingType, participantType) {
    try {
        console.log(`DEBUG: Extracting ${ratingType} for ${participantType} from:`, ratings);
        
        // First check if ratings is valid
        if (!ratings) {
            console.warn('DEBUG: Ratings is null or undefined');
            return 0;
        }
        
        // Try to parse if it's a string
        if (typeof ratings === 'string') {
            try {
                ratings = JSON.parse(ratings);
                console.log('DEBUG: Parsed ratings from string:', ratings);
            } catch (e) {
                console.error('DEBUG: Failed to parse ratings string:', e);
                return 0;
            }
        }
        
        // Standard format mappings
        const ratingMap = {
            'excellent': ['Excellent', 'excellent', 'EXCELLENT'],
            'very_satisfactory': ['Very Satisfactory', 'very_satisfactory', 'verySatisfactory', 'very satisfactory', 'VERY SATISFACTORY'],
            'satisfactory': ['Satisfactory', 'satisfactory', 'SATISFACTORY'],
            'fair': ['Fair', 'fair', 'FAIR'],
            'poor': ['Poor', 'poor', 'POOR']
        };
        
        const participantMap = {
            'batstateu': ['BatStateU', 'batstateu', 'batStateU', 'BATSTATEU'],
            'other': ['Others', 'other', 'Other', 'others', 'OTHER']
        };
        
        // Find the actual key in use
        const possibleRatingKeys = ratingMap[ratingType] || [ratingType];
        const possibleParticipantKeys = participantMap[participantType] || [participantType];
        
        console.log('DEBUG: Possible rating keys:', possibleRatingKeys);
        console.log('DEBUG: Possible participant keys:', possibleParticipantKeys);
        
        // Try all combinations of rating and participant keys
        for (const rKey of possibleRatingKeys) {
            if (ratings[rKey] && typeof ratings[rKey] === 'object') {
                for (const pKey of possibleParticipantKeys) {
                    if (ratings[rKey][pKey] !== undefined) {
                        console.log(`DEBUG: FOUND VALUE at ${rKey}.${pKey}:`, ratings[rKey][pKey]);
                        return ratings[rKey][pKey];
                    }
                }
            }
        }
        
        // Dump all keys for debugging
        console.log('DEBUG: All keys in ratings:', Object.keys(ratings));
        for (const key of Object.keys(ratings)) {
            if (typeof ratings[key] === 'object') {
                console.log(`DEBUG: Keys in ratings.${key}:`, Object.keys(ratings[key]));
            }
        }
        
        // Last resort - try every key that might contain our rating type
        for (const key of Object.keys(ratings)) {
            const keyLower = key.toLowerCase();
            if (possibleRatingKeys.some(r => keyLower.includes(r.toLowerCase()))) {
                if (typeof ratings[key] === 'object') {
                    for (const pKey of Object.keys(ratings[key])) {
                        const pKeyLower = pKey.toLowerCase();
                        if (possibleParticipantKeys.some(p => pKeyLower.includes(p.toLowerCase()))) {
                            console.log(`DEBUG: FUZZY MATCH at ${key}.${pKey}:`, ratings[key][pKey]);
                            return ratings[key][pKey];
                        }
                    }
                }
            }
        }
        
        console.warn(`DEBUG: No matching value found for ${ratingType}.${participantType}`);
        return 0;
    } catch (e) {
        console.error('DEBUG: Error extracting raw rating:', e);
        return 0;
    }
}

// Function to override the existing rating functions
function installDebugFunctions() {
    console.log('DEBUG: Installing debug functions');
    
    // Store original functions
    const originalShowRawRatingValue = window.showRawRatingValue;
    const originalExtractRatingValue = window.extractRatingValue;
    
    // Override with debug versions
    window.showRawRatingValue = function(ratings, ratingType, participantType) {
        console.log('DEBUG: showRawRatingValue called with:', {ratings, ratingType, participantType});
        return extractRawRatingValue(ratings, ratingType, participantType);
    };
    
    window.extractRatingValue = function(ratings, ratingType, participantType) {
        console.log('DEBUG: extractRatingValue called with:', {ratings, ratingType, participantType});
        return extractRawRatingValue(ratings, ratingType, participantType);
    };
    
    console.log('DEBUG: Debug functions installed');
}

// Function to directly extract from the last fetched data
function debugLastFetchedData() {
    try {
        console.log('DEBUG: Analyzing last fetched data');
        
        // Find the raw data variables
        const rawVars = [
            'rawActivityRatings', 
            'data.activity_ratings', 
            'transformedActivityRatings',
            'dbActivityRatings'
        ];
        
        // Try to access each variable
        for (const varName of rawVars) {
            try {
                let value;
                if (varName.includes('.')) {
                    const parts = varName.split('.');
                    value = window[parts[0]];
                    for (let i = 1; i < parts.length; i++) {
                        if (value) value = value[parts[i]];
                    }
                } else {
                    value = window[varName];
                }
                
                console.log(`DEBUG: Variable ${varName}:`, value);
                
                if (value) {
                    // Try to extract some values
                    console.log(`DEBUG: Sample extraction from ${varName}:`);
                    console.log('- excellent.batstateu:', extractRawRatingValue(value, 'excellent', 'batstateu'));
                    console.log('- very_satisfactory.batstateu:', extractRawRatingValue(value, 'very_satisfactory', 'batstateu'));
                    console.log('- satisfactory.batstateu:', extractRawRatingValue(value, 'satisfactory', 'batstateu'));
                    console.log('- fair.batstateu:', extractRawRatingValue(value, 'fair', 'batstateu'));
                    console.log('- poor.batstateu:', extractRawRatingValue(value, 'poor', 'batstateu'));
                }
            } catch (e) {
                console.warn(`DEBUG: Error accessing ${varName}:`, e);
            }
        }
    } catch (e) {
        console.error('DEBUG: Error analyzing last fetched data:', e);
    }
}

// Install the debug functions when this script loads
window.addEventListener('load', function() {
    console.log('DEBUG: Installing event handlers');
    
    // Wait for document to be ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(installDebugFunctions, 500);
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(installDebugFunctions, 500);
        });
    }
    
    // Add button to manually trigger debug
    const debugBtn = document.createElement('button');
    debugBtn.textContent = 'Debug Ratings Data';
    debugBtn.style.position = 'fixed';
    debugBtn.style.bottom = '10px';
    debugBtn.style.right = '10px';
    debugBtn.style.zIndex = '9999';
    debugBtn.style.padding = '5px 10px';
    debugBtn.style.backgroundColor = 'red';
    debugBtn.style.color = 'white';
    debugBtn.onclick = debugLastFetchedData;
    
    document.body.appendChild(debugBtn);
    console.log('DEBUG: Debug button added');
});

console.log('DEBUG: Script loaded successfully'); 