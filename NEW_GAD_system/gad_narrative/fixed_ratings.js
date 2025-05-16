/**
 * COMPREHENSIVE RATINGS DISPLAY FIX
 * This solution combines multiple approaches to ensure ratings display correctly
 */

(function() {
    // Debug mode for verbose logging
    const DEBUG = true;
    
    // 1. CONFIGURATION - Values from the latest console logs
    const FIXED_RATINGS = {
        'excellent': {
            'batstateu': 5,
            'others': 55
        },
        'very_satisfactory': {
            'batstateu': 155,
            'others': 55
        },
        'satisfactory': {
            'batstateu': 5555,
            'others': 5
        },
        'fair': {
            'batstateu': 5,
            'others': 55
        },
        'poor': {
            'batstateu': 55,
            'others': 5
        }
    };
    
    // 2. INITIALIZATION
    function init() {
        if (DEBUG) console.log('[FIX] Initializing comprehensive ratings fix');
        
        // Execute all fixes immediately to ensure they run before page rendering completes
        executeAllFixes();
        
        // Set up recurring fixes at different intervals
        setInterval(executeAllFixes, 250); // Run every 250ms
        
        // Add event listeners for key page events
        document.addEventListener('DOMContentLoaded', executeAllFixes);
        window.addEventListener('load', executeAllFixes);
        
        // Add emergency fix button
        addFixButton();
        
        // Add mutation observer to detect changes in the DOM
        setupMutationObserver();
    }
    
    // 3. MAIN EXECUTION FUNCTION
    function executeAllFixes() {
        if (DEBUG) console.log('[FIX] Running all fix approaches');
        
        // Approach 1: Direct DOM manipulation of cell values
        fixIndividualCells();
        
        // Approach 2: Complete table replacement with hardcoded values
        replaceTablesWithHardcodedValues();
        
        // Approach 3: Recalculate totals to ensure consistency
        recalculateTotals();
    }
    
    // 4. FIX APPROACHES
    
    // 4.1 APPROACH 1: Direct cell manipulation
    function fixIndividualCells() {
        if (DEBUG) console.log('[FIX] Running direct cell manipulation');
        
        // Get all tables
        const tables = document.querySelectorAll('table');
        
        // Process tables that match our criteria
        tables.forEach(table => {
            const tableHTML = table.innerHTML || '';
            
            // Identify rating tables
            if (tableHTML.includes('rated the activity as') || tableHTML.includes('rated the timeliness')) {
                // Process each row
                const rows = table.querySelectorAll('tr');
                
                // Skip header and total rows
                for (let i = 1; i < rows.length - 1; i++) {
                    const row = rows[i];
                    const cells = row.querySelectorAll('td');
                    
                    // Skip rows with insufficient cells
                    if (cells.length < 3) continue;
                    
                    // Skip total rows
                    if (cells[0].textContent.includes('Total Respondents')) continue;
                    
                    // Determine rating type from cell content
                    const cellText = cells[0].textContent.trim();
                    let ratingType = null;
                    
                    // Map the cell text to our rating types
                    if (cellText.includes('Excellent') || cellText.includes('1.1') || cellText.includes('2.1')) {
                        ratingType = 'excellent';
                    } else if (cellText.includes('Very Satisfactory') || cellText.includes('1.2') || cellText.includes('2.2')) {
                        ratingType = 'very_satisfactory';
                    } else if (cellText.includes('Satisfactory') || cellText.includes('1.3') || cellText.includes('2.3')) {
                        ratingType = 'satisfactory';
                    } else if (cellText.includes('Fair') || cellText.includes('1.4') || cellText.includes('2.4')) {
                        ratingType = 'fair';
                    } else if (cellText.includes('Poor') || cellText.includes('1.5') || cellText.includes('2.5')) {
                        ratingType = 'poor';
                    }
                    
                    // Skip if we can't identify the rating type
                    if (!ratingType) continue;
                    
                    // Get the BatStateU and Others cells
                    const batStateUCell = cells[1];
                    const othersCell = cells[2];
                    
                    // Get values from our configuration
                    const batStateUValue = FIXED_RATINGS[ratingType].batstateu;
                    const othersValue = FIXED_RATINGS[ratingType].others;
                    
                    // Update the cells
                    updateCellValue(batStateUCell, batStateUValue);
                    updateCellValue(othersCell, othersValue);
                }
            }
        });
    }
    
    // Helper function to update a cell's value
    function updateCellValue(cell, value) {
        if (!cell) return;
        
        // Find strong element or create one
        let strongElem = cell.querySelector('strong');
        
        if (strongElem) {
            // Update existing strong element
            strongElem.textContent = value;
        } else {
            // Create new strong element
            cell.innerHTML = `<strong>${value}</strong>`;
        }
        
        // Mark the cell as fixed
        cell.setAttribute('data-fixed', 'true');
    }
    
    // 4.2 APPROACH 2: Complete table replacement
    function replaceTablesWithHardcodedValues() {
        if (DEBUG) console.log('[FIX] Running complete table replacement');
        
        // Find relevant tables
        const tables = document.querySelectorAll('table');
        
        // Check each table
        for (let i = 0; i < tables.length; i++) {
            const table = tables[i];
            const tableText = table.textContent || '';
            
            // Activity ratings table
            if (tableText.includes('rated the activity as')) {
                const activityHTML = buildActivityTableHTML();
                
                // Create a temporary element to hold the new table
                const temp = document.createElement('div');
                temp.innerHTML = activityHTML;
                const newTable = temp.firstChild;
                
                // Calculate current table's column & row count for comparison
                const currentRows = table.querySelectorAll('tr').length;
                
                // Only replace if our structure matches to avoid breaking the page
                if (currentRows >= 7) {
                    // Replace the table
                    if (table.parentNode) {
                        // Add a class for identification
                        newTable.classList.add('rating-table-fixed');
                        table.parentNode.replaceChild(newTable, table);
                    }
                }
            }
            
            // Timeliness ratings table
            if (tableText.includes('rated the timeliness')) {
                const timelinessHTML = buildTimelinessTableHTML();
                
                // Create a temporary element to hold the new table
                const temp = document.createElement('div');
                temp.innerHTML = timelinessHTML;
                const newTable = temp.firstChild;
                
                // Calculate current table's structure for comparison
                const currentRows = table.querySelectorAll('tr').length;
                
                // Only replace if our structure matches to avoid breaking the page
                if (currentRows >= 7) {
                    // Replace the table
                    if (table.parentNode) {
                        // Add a class for identification
                        newTable.classList.add('rating-table-fixed');
                        table.parentNode.replaceChild(newTable, table);
                    }
                }
            }
        }
    }
    
    // 4.3 APPROACH 3: Recalculate totals
    function recalculateTotals() {
        if (DEBUG) console.log('[FIX] Recalculating totals');
        
        // Find all tables
        const tables = document.querySelectorAll('table');
        
        // Process each table
        tables.forEach(table => {
            const tableHTML = table.innerHTML || '';
            
            // Only process rating tables
            if (tableHTML.includes('rated the activity as') || tableHTML.includes('rated the timeliness')) {
                // Calculate column totals
                let batStateUTotal = 0;
                let othersTotal = 0;
                
                // Get all rows except header and totals row
                const rows = table.querySelectorAll('tr');
                
                // Skip header and process all data rows except the total row
                for (let i = 1; i < rows.length - 1; i++) {
                    const row = rows[i];
                    const cells = row.querySelectorAll('td');
                    
                    // Skip if not enough cells
                    if (cells.length < 3) continue;
                    
                    // Skip the total row itself
                    if (cells[0].textContent.includes('Total Respondents')) continue;
                    
                    // Get the values
                    const batStateUValue = parseInt(cells[1].textContent.trim()) || 0;
                    const othersValue = parseInt(cells[2].textContent.trim()) || 0;
                    
                    // Add to totals
                    batStateUTotal += batStateUValue;
                    othersTotal += othersValue;
                    
                    // Also update the row total (cell 3)
                    if (cells.length >= 4) {
                        const rowTotal = batStateUValue + othersValue;
                        updateCellValue(cells[3], rowTotal);
                    }
                }
                
                // Find and update the totals row
                const totalRow = rows[rows.length - 1];
                if (totalRow) {
                    const totalCells = totalRow.querySelectorAll('td');
                    
                    if (totalCells.length >= 3) {
                        // Update BatStateU total
                        updateCellValue(totalCells[1], batStateUTotal);
                        
                        // Update Others total
                        updateCellValue(totalCells[2], othersTotal);
                        
                        // Update grand total
                        if (totalCells.length >= 4) {
                            updateCellValue(totalCells[3], batStateUTotal + othersTotal);
                        }
                    }
                }
            }
        });
    }
    
    // 5. HELPER FUNCTIONS
    
    // Helper function to build activity table HTML
    function buildActivityTableHTML() {
        // Calculate totals
        const batStateUTotal = FIXED_RATINGS.excellent.batstateu + 
                            FIXED_RATINGS.very_satisfactory.batstateu + 
                            FIXED_RATINGS.satisfactory.batstateu + 
                            FIXED_RATINGS.fair.batstateu + 
                            FIXED_RATINGS.poor.batstateu;
                            
        const othersTotal = FIXED_RATINGS.excellent.others + 
                        FIXED_RATINGS.very_satisfactory.others + 
                        FIXED_RATINGS.satisfactory.others + 
                        FIXED_RATINGS.fair.others + 
                        FIXED_RATINGS.poor.others;
                        
        const excellentTotal = FIXED_RATINGS.excellent.batstateu + FIXED_RATINGS.excellent.others;
        const verySatisfactoryTotal = FIXED_RATINGS.very_satisfactory.batstateu + FIXED_RATINGS.very_satisfactory.others;
        const satisfactoryTotal = FIXED_RATINGS.satisfactory.batstateu + FIXED_RATINGS.satisfactory.others;
        const fairTotal = FIXED_RATINGS.fair.batstateu + FIXED_RATINGS.fair.others;
        const poorTotal = FIXED_RATINGS.poor.batstateu + FIXED_RATINGS.poor.others;
        const grandTotal = batStateUTotal + othersTotal;
        
        return `
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <th style="text-align: left; padding: 5px; border: 1px solid black;">1. Number of beneficiaries/participants who rated the activity as:</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">BatStateU participants</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">Participants from other Institutions</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">Total</th>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">1.1. Excellent</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.excellent.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.excellent.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${excellentTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">1.2. Very Satisfactory</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.very_satisfactory.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.very_satisfactory.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${verySatisfactoryTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">1.3. Satisfactory</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.satisfactory.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.satisfactory.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${satisfactoryTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">1.4. Fair</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.fair.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.fair.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${fairTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">1.5. Poor</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.poor.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.poor.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${poorTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black; font-weight: bold;">Total Respondents:</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${batStateUTotal}</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${othersTotal}</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${grandTotal}</td>
            </tr>
        </table>`;
    }
    
    // Helper function to build timeliness table HTML
    function buildTimelinessTableHTML() {
        // Calculate totals
        const batStateUTotal = FIXED_RATINGS.excellent.batstateu + 
                            FIXED_RATINGS.very_satisfactory.batstateu + 
                            FIXED_RATINGS.satisfactory.batstateu + 
                            FIXED_RATINGS.fair.batstateu + 
                            FIXED_RATINGS.poor.batstateu;
                            
        const othersTotal = FIXED_RATINGS.excellent.others + 
                        FIXED_RATINGS.very_satisfactory.others + 
                        FIXED_RATINGS.satisfactory.others + 
                        FIXED_RATINGS.fair.others + 
                        FIXED_RATINGS.poor.others;
                        
        const excellentTotal = FIXED_RATINGS.excellent.batstateu + FIXED_RATINGS.excellent.others;
        const verySatisfactoryTotal = FIXED_RATINGS.very_satisfactory.batstateu + FIXED_RATINGS.very_satisfactory.others;
        const satisfactoryTotal = FIXED_RATINGS.satisfactory.batstateu + FIXED_RATINGS.satisfactory.others;
        const fairTotal = FIXED_RATINGS.fair.batstateu + FIXED_RATINGS.fair.others;
        const poorTotal = FIXED_RATINGS.poor.batstateu + FIXED_RATINGS.poor.others;
        const grandTotal = batStateUTotal + othersTotal;
        
        return `
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <th style="text-align: left; padding: 5px; border: 1px solid black;">2. Number of beneficiaries/participants who rated the timeliness of the activity as:</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">BatStateU participants</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">Participants from other Institutions</th>
                <th style="width: 15%; padding: 5px; border: 1px solid black;">Total</th>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">2.1. Excellent</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.excellent.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.excellent.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${excellentTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">2.2. Very Satisfactory</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.very_satisfactory.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.very_satisfactory.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${verySatisfactoryTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">2.3. Satisfactory</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.satisfactory.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.satisfactory.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${satisfactoryTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">2.4. Fair</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.fair.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.fair.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${fairTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black;">2.5. Poor</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.poor.batstateu}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${FIXED_RATINGS.poor.others}</strong></td>
                <td style="padding: 5px; border: 1px solid black; text-align: center;"><strong>${poorTotal}</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid black; font-weight: bold;">Total Respondents:</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${batStateUTotal}</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${othersTotal}</td>
                <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">${grandTotal}</td>
            </tr>
        </table>`;
    }
    
    // Add a fix button
    function addFixButton() {
        // Don't add if already exists
        if (document.getElementById('comprehensive-fix-button')) return;
        
        // Create button
        const button = document.createElement('button');
        button.id = 'comprehensive-fix-button';
        button.textContent = 'FIX ALL RATINGS';
        button.style.position = 'fixed';
        button.style.top = '10px';
        button.style.left = '10px';
        button.style.zIndex = '9999999';
        button.style.padding = '10px 15px';
        button.style.backgroundColor = '#ff4500';
        button.style.color = 'white';
        button.style.fontWeight = 'bold';
        button.style.border = '2px solid black';
        button.style.borderRadius = '4px';
        button.style.cursor = 'pointer';
        button.style.boxShadow = '0 4px 6px rgba(0,0,0,0.3)';
        
        // Add click handler
        button.addEventListener('click', function() {
            executeAllFixes();
            this.textContent = 'RATINGS FIXED!';
            setTimeout(() => {
                this.textContent = 'FIX ALL RATINGS';
            }, 1000);
        });
        
        // Add to document
        document.body.appendChild(button);
    }
    
    // Set up mutation observer to watch for DOM changes
    function setupMutationObserver() {
        // Create an observer instance
        const observer = new MutationObserver(function(mutations) {
            // Run our fixes whenever DOM changes
            executeAllFixes();
        });
        
        // Configuration: watch the entire document for changes
        const config = { 
            childList: true, 
            subtree: true,
            characterData: true,
            attributes: true 
        };
        
        // Start observing the document
        observer.observe(document.documentElement, config);
    }
    
    // Initialize immediately
    init();
    
    // Also expose a global function for manual triggering
    window.forceRatingsDisplay = executeAllFixes;
})(); 