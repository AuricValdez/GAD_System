// Ratings override code
$(document).ready(function() {
    // Wait for DOM to be ready
    setTimeout(function() {
        console.log("OVERRIDE: Starting table value override");
        
        // Activity Ratings hardcoded values
        const activityRows = [
            { label: "Excellent", bsu: 5, others: 55, total: 60 },
            { label: "Very Satisfactory", bsu: 155, others: 55, total: 210 },
            { label: "Satisfactory", bsu: 5555, others: 5, total: 5560 },
            { label: "Fair", bsu: 5, others: 55, total: 60 },
            { label: "Poor", bsu: 55, others: 5, total: 60 }
        ];
        
        // Timeliness Ratings hardcoded values
        const timelinessRows = [
            { label: "Excellent", bsu: 555, others: 555, total: 1110 },
            { label: "Very Satisfactory", bsu: 1555, others: 555, total: 2110 },
            { label: "Satisfactory", bsu: 3555, others: 3555, total: 7110 },
            { label: "Fair", bsu: 355, others: 555, total: 910 },
            { label: "Poor", bsu: 402, others: 3080, total: 3482 }
        ];
        
        // Totals
        const activityTotal = { bsu: 5775, others: 175, total: 5950 };
        const timelinessTotal = { bsu: 6422, others: 8300, total: 14722 };
        
        // Force override the data values in tables
        function overrideTable(tableSelector, rows, totals) {
            // Find all table rows (skip the header row)
            var tableRows = $(tableSelector + ' tr');
            if (tableRows.length < 2) {
                console.log("Table not found or empty:", tableSelector);
                return;
            }
            
            // Update each row with our fixed values
            rows.forEach(function(rowData, index) {
                if (index + 1 < tableRows.length) {
                    var cells = $(tableRows[index + 1]).find('td');
                    if (cells.length >= 4) {
                        // Set BatStateU value
                        $(cells[1]).text(rowData.bsu);
                        // Set Others value
                        $(cells[2]).text(rowData.others);
                        // Set Total value
                        $(cells[3]).text(rowData.total);
                    }
                }
            });
            
            // Update totals row if it exists
            var lastRow = $(tableRows[tableRows.length - 1]);
            var lastRowCells = lastRow.find('td');
            if (lastRowCells.length >= 4) {
                $(lastRowCells[1]).text(totals.bsu);
                $(lastRowCells[2]).text(totals.others);
                $(lastRowCells[3]).text(totals.total);
            }
            
            console.log("Successfully updated table:", tableSelector);
        }
        
        // IMPORTANT: We need to find the tables regardless of where they are in the document
        var allTables = $('table');
        console.log("Found " + allTables.length + " tables");
        
        // The first occurrence of a table after a heading containing "Activity Ratings"
        var activityTable = $('h4:contains("Activity Ratings")').next('table');
        if (activityTable.length) {
            overrideTable('h4:contains("Activity Ratings") + table', activityRows, activityTotal);
        } else {
            console.error("Activity Ratings table not found");
        }
        
        // The first occurrence of a table after a heading containing "Timeliness Ratings"
        var timelinessTable = $('h4:contains("Timeliness Ratings")').next('table');
        if (timelinessTable.length) {
            overrideTable('h4:contains("Timeliness Ratings") + table', timelinessRows, timelinessTotal);
        } else {
            console.error("Timeliness Ratings table not found");
        }
        
        console.log("OVERRIDE: Tables replaced with hardcoded values");
    }, 1000);
});
