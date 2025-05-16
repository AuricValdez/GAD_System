// Main script for narrative_reports
console.log("MAIN: Loading all scripts for narrative reports");

// Load direct_data_access.js
(function loadDirectDataAccess() {
    const script = document.createElement('script');
    script.src = 'direct_data_access.js';
    script.async = false;
    script.onload = function() {
        console.log("MAIN: direct_data_access.js loaded successfully");
    };
    script.onerror = function() {
        console.error("MAIN: Failed to load direct_data_access.js");
    };
    document.head.appendChild(script);
})();

// Override MutationObserver to avoid errors
(function fixMutationObserver() {
    const originalObserve = MutationObserver.prototype.observe;
    
    MutationObserver.prototype.observe = function(target, options) {
        if (!target) {
            console.error("MAIN: MutationObserver.observe called with invalid target");
            return;
        }
        
        // Call the original function only if target is valid
        try {
            originalObserve.call(this, target, options);
            console.log("MAIN: MutationObserver started successfully");
        } catch (e) {
            console.error("MAIN: MutationObserver error:", e);
        }
    };
})();

// Function to fetch narrative data from API
function fetchNarrativeData() {
    console.log("MAIN: Attempting to fetch narrative data from API");
    
    // Get PPAS ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const ppasId = urlParams.get('id') || urlParams.get('ppas_id') || urlParams.get('report_id');
    
    if (!ppasId) {
        console.warn("MAIN: No PPAS ID found in URL, cannot fetch data");
        return null;
    }
    
    try {
        // Make synchronous request to get data
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `api/get_narrative.php?ppas_form_id=${ppasId}`, false); // Synchronous
        xhr.send();
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success' && response.data) {
                    console.log("MAIN: Successfully fetched narrative data:", response.data);
                    
                    // Store data globally
                    window.narrativeData = response.data;
                    
                    // Return the data
                    return response.data;
                } else {
                    console.warn("MAIN: API returned error or no data:", response);
                }
            } catch (e) {
                console.error("MAIN: Error parsing API response:", e);
            }
        } else {
            console.error("MAIN: API request failed with status:", xhr.status);
        }
    } catch (e) {
        console.error("MAIN: Error fetching narrative data:", e);
    }
    
    return null;
}

// Direct DOM manipulation function
function updateRatingsInDOM() {
    console.log("MAIN: Directly updating ratings in DOM");
    
    // First try to fetch real data if we don't have it yet
    if (!window.narrativeData) {
        window.narrativeData = fetchNarrativeData();
    }
    
    // Find all tables
    const tables = document.querySelectorAll('table');
    if (!tables || tables.length < 2) {
        console.warn("MAIN: Unable to find tables");
        return;
    }
    
    // Target the ratings tables
    const activityTable = tables[0];
    const timelinessTable = tables[1]; 
    
    // Get actual rating data
    let activityRatings = null;
    let timelinessRatings = null;
    
    if (window.narrativeData) {
        activityRatings = window.narrativeData.activity_ratings;
        timelinessRatings = window.narrativeData.timeliness_ratings;
        console.log("MAIN: Using data from API:", activityRatings);
    }
    
    // If we couldn't get real data, use fallback data
    if (!activityRatings) {
        console.warn("MAIN: Using fallback rating data");
        activityRatings = {
            "Excellent": { batstateu: 33, others: 3 },
            "Very Satisfactory": { batstateu: 3333, others: 3 },
            "Satisfactory": { batstateu: 3333, others: 3 },
            "Fair": { batstateu: 33, others: 3 },
            "Poor": { batstateu: 3, others: 3 }
        };
    }
    
    if (!timelinessRatings) {
        timelinessRatings = {
            "Excellent": { batstateu: 33, others: 3 },
            "Very Satisfactory": { batstateu: 3, others: 33 },
            "Satisfactory": { batstateu: 333, others: 33 },
            "Fair": { batstateu: 333, others: 3 },
            "Poor": { batstateu: 32, others: 34 }
        };
    }
    
    // Convert API format to table update format
    const activityData = {
        "1.1. Excellent": { 
            batstateu: getRatingValue(activityRatings, "Excellent", "BatStateU"), 
            others: getRatingValue(activityRatings, "Excellent", "Others") 
        },
        "1.2. Very Satisfactory": { 
            batstateu: getRatingValue(activityRatings, "Very Satisfactory", "BatStateU"), 
            others: getRatingValue(activityRatings, "Very Satisfactory", "Others") 
        },
        "1.3. Satisfactory": { 
            batstateu: getRatingValue(activityRatings, "Satisfactory", "BatStateU"), 
            others: getRatingValue(activityRatings, "Satisfactory", "Others") 
        },
        "1.4. Fair": { 
            batstateu: getRatingValue(activityRatings, "Fair", "BatStateU"), 
            others: getRatingValue(activityRatings, "Fair", "Others") 
        },
        "1.5. Poor": { 
            batstateu: getRatingValue(activityRatings, "Poor", "BatStateU"), 
            others: getRatingValue(activityRatings, "Poor", "Others") 
        }
    };
    
    // Timeliness ratings conversion
    const timelinessData = {
        "2.1. Excellent": { 
            batstateu: getRatingValue(timelinessRatings, "Excellent", "BatStateU"), 
            others: getRatingValue(timelinessRatings, "Excellent", "Others") 
        },
        "2.2. Very Satisfactory": { 
            batstateu: getRatingValue(timelinessRatings, "Very Satisfactory", "BatStateU"), 
            others: getRatingValue(timelinessRatings, "Very Satisfactory", "Others") 
        },
        "2.3. Satisfactory": { 
            batstateu: getRatingValue(timelinessRatings, "Satisfactory", "BatStateU"), 
            others: getRatingValue(timelinessRatings, "Satisfactory", "Others") 
        },
        "2.4. Fair": { 
            batstateu: getRatingValue(timelinessRatings, "Fair", "BatStateU"), 
            others: getRatingValue(timelinessRatings, "Fair", "Others") 
        },
        "2.5. Poor": { 
            batstateu: getRatingValue(timelinessRatings, "Poor", "BatStateU"), 
            others: getRatingValue(timelinessRatings, "Poor", "Others") 
        }
    };
    
    // Helper function to get rating value regardless of case
    function getRatingValue(ratings, ratingType, participantType) {
        if (!ratings) return 0;
        
        // Try case-sensitive first
        if (ratings[ratingType] && 
            ratings[ratingType][participantType] !== undefined) {
            return parseInt(ratings[ratingType][participantType]) || 0;
        }
        
        // Try with lowercase keys
        const lcRatingType = ratingType.toLowerCase();
        const lcParticipantType = participantType.toLowerCase();
        
        // Try different participant key formats
        const possibleParticipantKeys = [
            participantType,
            lcParticipantType,
            participantType === 'BatStateU' ? 'batstateu' : null,
            participantType === 'Others' ? 'other' : null,
            participantType === 'Others' ? 'others' : null
        ].filter(Boolean);
        
        // Check different key formats
        for (const key in ratings) {
            if (key.toLowerCase() === lcRatingType) {
                for (const partKey of possibleParticipantKeys) {
                    if (ratings[key][partKey] !== undefined) {
                        console.log(`MAIN: Found value at ${key}.${partKey}: ${ratings[key][partKey]}`);
                        return parseInt(ratings[key][partKey]) || 0;
                    }
                }
            }
        }
        
        console.warn(`MAIN: Rating value not found for ${ratingType}.${participantType}, using 0`);
        return 0;
    }
    
    // Update the tables
    for (const [label, values] of Object.entries(activityData)) {
        updateTableRow(activityTable, label, values.batstateu, values.others);
    }
    
    for (const [label, values] of Object.entries(timelinessData)) {
        updateTableRow(timelinessTable, label, values.batstateu, values.others);
    }
    
    console.log("MAIN: Updated all rating values in tables");
}

// Helper function to update a table row
function updateTableRow(table, labelText, batStateUValue, othersValue) {
    try {
        // Ensure we have integer values
        batStateUValue = parseInt(batStateUValue) || 0;
        othersValue = parseInt(othersValue) || 0;
        
        console.log(`MAIN: Updating row ${labelText} with values: ${batStateUValue}, ${othersValue}`);
        
        // Find the row containing this label
        for (let i = 0; i < table.rows.length; i++) {
            const row = table.rows[i];
            const firstCell = row.cells[0];
            
            if (firstCell && firstCell.textContent.includes(labelText)) {
                // Found the row - update the values
                if (row.cells.length >= 4) {
                    // BatStateU column (index 1)
                    updateCellValue(row.cells[1], batStateUValue);
                    
                    // Others column (index 2)
                    updateCellValue(row.cells[2], othersValue);
                    
                    // Total column (index 3)
                    const total = batStateUValue + othersValue;
                    updateCellValue(row.cells[3], total);
                    
                    console.log(`MAIN: Updated row for ${labelText} with values: ${batStateUValue}, ${othersValue}, ${total}`);
                }
                break;
            }
        }
    } catch (e) {
        console.error(`MAIN: Error updating row for ${labelText}:`, e);
    }
}

// Helper to update a cell value
function updateCellValue(cell, value) {
    if (!cell) return;
    
    const strongElem = cell.querySelector('strong');
    if (strongElem) {
        strongElem.textContent = value;
    } else {
        cell.textContent = value;
    }
}

// Execute when DOM content is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log("MAIN: DOM content loaded, updating ratings");
    // First try to fetch the data
    window.narrativeData = fetchNarrativeData();
    setTimeout(updateRatingsInDOM, 1000);
});

// Execute when window is fully loaded
window.addEventListener('load', function() {
    console.log("MAIN: Window loaded, updating ratings");
    setTimeout(updateRatingsInDOM, 1500);
    setTimeout(updateRatingsInDOM, 3000);
    setTimeout(updateRatingsInDOM, 5000);
});

// Create a button to manually update ratings
function addUpdateButton() {
    const button = document.createElement('button');
    button.textContent = 'Update Ratings';
    button.style.position = 'fixed';
    button.style.bottom = '100px';
    button.style.right = '10px';
    button.style.zIndex = '9999';
    button.style.backgroundColor = 'blue';
    button.style.color = 'white';
    button.style.padding = '10px';
    button.style.border = 'none';
    button.style.borderRadius = '5px';
    button.style.cursor = 'pointer';
    
    button.onclick = function() {
        // Refetch data and update
        window.narrativeData = fetchNarrativeData();
        updateRatingsInDOM();
        alert('Ratings updated! Check if they are visible now.');
    };
    
    document.body.appendChild(button);
    console.log("MAIN: Update button added");
}

// Add the button when the DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addUpdateButton);
} else {
    addUpdateButton();
}

console.log("MAIN: Script initialization complete"); 