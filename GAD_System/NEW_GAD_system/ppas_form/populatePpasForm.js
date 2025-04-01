// Gender Issue Debug Helper
console.log('Debugging gender issue ID loading in PPAS form');

// Function to be called from browser console
function debugGenderIssue(elementId = 'gender_issue') {
    const element = document.getElementById(elementId);
    
    if (!element) {
        console.error(`Element with ID ${elementId} not found`);
        return;
    }
    
    console.log('Gender issue element:', element);
    console.log('Current value:', element.value);
    console.log('Options available:');
    
    Array.from(element.options).forEach((option, index) => {
        console.log(`  Option ${index}: value=${option.value}, text=${option.text}`);
    });
    
    // Check if the dropdown is disabled
    if (element.disabled) {
        console.warn('Gender issue dropdown is DISABLED');
        enableGenderIssue();
    } else {
        console.log('Gender issue dropdown is ENABLED');
    }
    
    return 'Debug complete. Check console for results.';
}

// Function to set gender issue value directly
function setGenderIssue(id) {
    const element = document.getElementById('gender_issue');
    if (!element) {
        console.error('Gender issue element not found');
        return false;
    }
    
    element.value = id;
    console.log(`Set gender_issue to ${id}, current value is now:`, element.value);
    
    // Trigger change event
    const event = new Event('change');
    element.dispatchEvent(event);
    
    return true;
}

// Function to enable the gender issue dropdown
function enableGenderIssue() {
    const element = document.getElementById('gender_issue');
    if (!element) {
        console.error('Gender issue element not found');
        return false;
    }
    
    // Enable the dropdown
    element.disabled = false;
    
    // Remove disabled class from parent
    const formGroup = element.closest('.form-group');
    if (formGroup) {
        formGroup.classList.remove('field-disabled');
        delete formGroup.dataset.hint;
    }
    
    console.log('Gender issue dropdown has been enabled');
    return true;
}

// Function to fetch gender issues from server and check if our ID is valid
function checkGenderIssues(currentId) {
    console.log(`Checking if gender issue ID ${currentId} exists in the database`);
    
    fetch('check_gender_issues.php')
        .then(response => response.json())
        .then(data => {
            console.log('Gender issues from server:', data);
            
            if (data.success) {
                const issues = data.issues;
                const matchingIssue = issues.find(issue => issue.id == currentId);
                
                if (matchingIssue) {
                    console.log('Found matching gender issue:', matchingIssue);
                    
                    // Check if this ID exists in the dropdown
                    const dropdown = document.getElementById('gender_issue');
                    if (dropdown) {
                        const optionExists = Array.from(dropdown.options).some(option => option.value == currentId);
                        
                        if (!optionExists) {
                            console.warn(`Gender issue ID ${currentId} exists in database but not in dropdown`);
                            
                            // Add this option to the dropdown
                            const newOption = document.createElement('option');
                            newOption.value = matchingIssue.id;
                            newOption.text = matchingIssue.gender_issue;
                            dropdown.appendChild(newOption);
                            
                            console.log(`Added option for ${matchingIssue.gender_issue} to dropdown`);
                            
                            // Now set the value
                            dropdown.value = currentId;
                            console.log(`Updated dropdown value to ${currentId}`);
                            
                            // Trigger change event
                            const event = new Event('change');
                            dropdown.dispatchEvent(event);
                        } else {
                            console.log(`Gender issue ID ${currentId} exists in dropdown - selecting it`);
                            dropdown.value = currentId;
                            
                            // Trigger change event
                            const event = new Event('change');
                            dropdown.dispatchEvent(event);
                        }
                    }
                } else {
                    console.warn(`Gender issue ID ${currentId} not found in database`);
                }
            } else {
                console.error('Failed to get gender issues:', data.message);
            }
        })
        .catch(error => {
            console.error('Error checking gender issues:', error);
        });
}

// Execute debug checks immediately
setTimeout(() => {
    console.log('Automatic debug checks running...');
    debugGenderIssue();
    
    // Get gender issue ID from global variable
    const genderIssueId = window.currentGenderIssueId;
    if (genderIssueId) {
        console.log(`Found gender_issue_id in global variable: ${genderIssueId}`);
        
        // Enable the dropdown if needed
        enableGenderIssue();
        
        // Check if this ID exists in the available gender issues
        checkGenderIssues(genderIssueId);
    } else {
        // Fallback to getting the entry ID
        const addBtn = document.getElementById('addBtn');
        if (addBtn && addBtn.hasAttribute('data-entry-id')) {
            const entryId = addBtn.getAttribute('data-entry-id');
            console.log(`Current entry ID: ${entryId}, but no global gender_issue_id found`);
            
            // Check if gender_issue_id exists in server data
            fetch(`get_ppas_entry.php?id=${entryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.entry && data.entry.gender_issue_id) {
                        console.log(`Entry has gender_issue_id: ${data.entry.gender_issue_id}`);
                        
                        // Enable the dropdown if needed
                        enableGenderIssue();
                        
                        // Check if this ID exists in the available gender issues
                        checkGenderIssues(data.entry.gender_issue_id);
                    } else {
                        console.warn('Entry has no gender_issue_id');
                    }
                })
                .catch(error => {
                    console.error('Error fetching entry:', error);
                });
        }
    }
}, 1000); 