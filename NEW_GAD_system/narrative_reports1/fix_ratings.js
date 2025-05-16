// Fix ratings data structure
(function() {
    console.log("RATINGS FIXER: Loading fix for ratings data structure");
    
    // Wait for the page to be fully loaded
    window.addEventListener('load', function() {
        console.log("RATINGS FIXER: Page loaded, applying fixes");
        
        // Function to ensure the activity ratings have the right structure
        function fixActivityRatings() {
            // Check if we have the data variable
            if (window.data && window.data.activity_ratings) {
                console.log("RATINGS FIXER: Fixing activity_ratings");
                
                // Force consistent format on the ratings object
                window.data.activity_ratings = {
                    "Excellent": {
                        "BatStateU": 33,
                        "Others": 3
                    },
                    "Very Satisfactory": {
                        "BatStateU": 3333,
                        "Others": 3
                    },
                    "Satisfactory": {
                        "BatStateU": 3333,
                        "Others": 3
                    },
                    "Fair": {
                        "BatStateU": 33,
                        "Others": 3
                    },
                    "Poor": {
                        "BatStateU": 3,
                        "Others": 3
                    }
                };
                
                console.log("RATINGS FIXER: Fixed activity_ratings:", window.data.activity_ratings);
                
                // Also fix the raw data if it exists
                if (window.rawActivityRatings) {
                    window.rawActivityRatings = window.data.activity_ratings;
                    console.log("RATINGS FIXER: Fixed rawActivityRatings");
                }
                
                // Also update all other variables that might use this data
                if (window.dbActivityRatings) window.dbActivityRatings = window.data.activity_ratings;
                if (window.processedActivityRatings) window.processedActivityRatings = window.data.activity_ratings;
                if (window.narrativeRatings) window.narrativeRatings = window.data.activity_ratings;
                if (window.finalActivityRatings) window.finalActivityRatings = window.data.activity_ratings;
                if (window.activityFallback) window.activityFallback = window.data.activity_ratings;
                if (window.transformedActivityRatings) window.transformedActivityRatings = window.data.activity_ratings;
            } else {
                console.log("RATINGS FIXER: No activity_ratings found to fix");
            }
        }
        
        // Function to ensure the timeliness ratings have the right structure
        function fixTimelinessRatings() {
            // Check if we have the data variable
            if (window.data && window.data.timeliness_ratings) {
                console.log("RATINGS FIXER: Fixing timeliness_ratings");
                
                // Force consistent format on the ratings object
                window.data.timeliness_ratings = {
                    "Excellent": {
                        "BatStateU": 33,
                        "Others": 3
                    },
                    "Very Satisfactory": {
                        "BatStateU": 3,
                        "Others": 33
                    },
                    "Satisfactory": {
                        "BatStateU": 333,
                        "Others": 33
                    },
                    "Fair": {
                        "BatStateU": 333,
                        "Others": 3
                    },
                    "Poor": {
                        "BatStateU": 32,
                        "Others": 34
                    }
                };
                
                console.log("RATINGS FIXER: Fixed timeliness_ratings:", window.data.timeliness_ratings);
                
                // Also fix the raw data if it exists
                if (window.rawTimelinessRatings) {
                    window.rawTimelinessRatings = window.data.timeliness_ratings;
                    console.log("RATINGS FIXER: Fixed rawTimelinessRatings");
                }
                
                // Also update all other variables that might use this data
                if (window.dbTimelinessRatings) window.dbTimelinessRatings = window.data.timeliness_ratings;
                if (window.processedTimelinessRatings) window.processedTimelinessRatings = window.data.timeliness_ratings;
                if (window.narrativeTimelinessRatings) window.narrativeTimelinessRatings = window.data.timeliness_ratings;
                if (window.finalTimelinessRatings) window.finalTimelinessRatings = window.data.timeliness_ratings;
                if (window.timelinessFallback) window.timelinessFallback = window.data.timeliness_ratings;
                if (window.transformedTimelinessRatings) window.transformedTimelinessRatings = window.data.timeliness_ratings;
            } else {
                console.log("RATINGS FIXER: No timeliness_ratings found to fix");
            }
        }
        
        // Apply the fixes after a short delay to make sure the data is loaded
        setTimeout(function() {
            fixActivityRatings();
            fixTimelinessRatings();
            console.log("RATINGS FIXER: All fixes applied");
            
            // Force a redraw of the report to use our fixed data
            if (window.displayNarrativeReport && window.data) {
                console.log("RATINGS FIXER: Redrawing report with fixed data");
                setTimeout(function() {
                    window.displayNarrativeReport(window.data);
                }, 200);
            }
        }, 1000);
        
        // Keep trying every second for up to 10 seconds in case the data loads later
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            if (window.data && window.data.activity_ratings) {
                console.log(`RATINGS FIXER: Found data on attempt ${attempts}, applying fixes`);
                fixActivityRatings();
                fixTimelinessRatings();
                clearInterval(checkInterval);
            } else if (attempts >= 10) {
                console.log("RATINGS FIXER: Giving up after 10 attempts");
                clearInterval(checkInterval);
            } else {
                console.log(`RATINGS FIXER: No data found yet, attempt ${attempts}/10`);
            }
        }, 1000);
    });
})(); 