// Script to add event handler to clear quarter dropdown when year changes
document.addEventListener('DOMContentLoaded', function() {
    // Get the year and quarter dropdown elements
    const yearSelect = document.getElementById('year');
    const quarterSelect = document.getElementById('quarter');
    
    // If both elements exist, add our custom handler
    if (yearSelect && quarterSelect) {
        // Add event listener to year dropdown
        yearSelect.addEventListener('change', function() {
            // Clear quarter dropdown value when year changes
            quarterSelect.value = '';
            
            // The original event handlers will still execute as normal
            // and handle enabling/disabling the dropdown
        });
    }
}); 