<?php
/**
 * This is a partial fix to replace the JavaScript section that handles ratings data.
 * It prioritizes using actual database values over fallback data.
 */
?>

// Process ratings data to extract nested objects if necessary
let processedActivityRatings = dbActivityRatings;
let processedTimelinessRatings = dbTimelinessRatings;

// Print detailed debug info to help troubleshoot
console.log('DEBUG DATA SOURCES:');
console.log('dbActivityRatings:', dbActivityRatings);
console.log('dbTimelinessRatings:', dbTimelinessRatings);
console.log('narrativeRatings:', typeof narrativeRatings !== 'undefined' ? narrativeRatings : 'undefined');
console.log('narrativeTimelinessRatings:', typeof narrativeTimelinessRatings !== 'undefined' ? narrativeTimelinessRatings : 'undefined');
console.log('ppasEntriesActivityRatings:', typeof ppasEntriesActivityRatings !== 'undefined' ? ppasEntriesActivityRatings : 'undefined');
console.log('ppasEntriesTimelinessRatings:', typeof ppasEntriesTimelinessRatings !== 'undefined' ? ppasEntriesTimelinessRatings : 'undefined');

// Handle nested structure in dbActivityRatings
if (dbActivityRatings && typeof dbActivityRatings === 'object') {
    console.log('Found object structure in dbActivityRatings');
    if (dbActivityRatings.activity) {
        console.log('Found nested activity property in dbActivityRatings');
        processedActivityRatings = dbActivityRatings.activity;
    } else if (dbActivityRatings.ratings) {
        console.log('Found nested ratings property in dbActivityRatings');
        processedActivityRatings = dbActivityRatings.ratings;
    }
}

// Handle nested structure in dbTimelinessRatings
if (dbTimelinessRatings && typeof dbTimelinessRatings === 'object') {
    console.log('Found object structure in dbTimelinessRatings');
    if (dbTimelinessRatings.timeliness) {
        console.log('Found nested timeliness property in dbTimelinessRatings');
        processedTimelinessRatings = dbTimelinessRatings.timeliness;
    }
}

// Fallback data only used as last resort
const activityFallback = {
    "Excellent": {
        "BatStateU": 1,  // Changed to minimal values to encourage fixing the real data
        "Others": 1
    },
    "Very Satisfactory": {
        "BatStateU": 1,
        "Others": 1
    },
    "Satisfactory": {
        "BatStateU": 1,
        "Others": 1
    },
    "Fair": {
        "BatStateU": 1,
        "Others": 1
    },
    "Poor": {
        "BatStateU": 1,
        "Others": 1
    }
};

const timelinessFallback = {
    "Excellent": {
        "BatStateU": 1,
        "Others": 1
    },
    "Very Satisfactory": {
        "BatStateU": 1,
        "Others": 1
    },
    "Satisfactory": {
        "BatStateU": 1,
        "Others": 1
    },
    "Fair": {
        "BatStateU": 1,
        "Others": 1
    },
    "Poor": {
        "BatStateU": 1,
        "Others": 1
    }
};

// PRIORITY: Use database data first, fallback last
const finalActivityRatings = dbActivityRatings ? processedActivityRatings : 
                            (narrativeRatings || ppasEntriesActivityRatings || activityFallback);
const finalTimelinessRatings = dbTimelinessRatings ? processedTimelinessRatings : 
                              (narrativeTimelinessRatings || ppasEntriesTimelinessRatings || timelinessFallback);

console.log('FINAL ACTIVITY RATINGS:', finalActivityRatings);
console.log('FINAL TIMELINESS RATINGS:', finalTimelinessRatings); 