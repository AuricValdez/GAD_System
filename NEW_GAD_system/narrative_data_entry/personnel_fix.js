// Function to load personnel data from narrative
function loadPersonnelFromNarrative(narrative) {
    if (!narrative || !narrative.other_internal_personnel) {
        console.log('No personnel data found in narrative');
        return;
    }
    
    console.log('Raw other_internal_personnel data:', narrative.other_internal_personnel);
    
    try {
        let personnel = [];
        
        // Check if it's already an array (non-stringified)
        if (Array.isArray(narrative.other_internal_personnel)) {
            console.log('Personnel data is already an array');
            personnel = narrative.other_internal_personnel;
        } else {
            // Try to parse JSON string
            personnel = JSON.parse(narrative.other_internal_personnel);
        }
        
        if (Array.isArray(personnel)) {
            console.log('Found personnel data:', personnel);
            
            // Clear existing personnel array
            window.selectedPersonnel = [];
            
            // Process and deduplicate personnel
            const uniquePersonnel = new Map();
            
            personnel.forEach(person => {
                // Handle both object format and string format
                if (typeof person === 'object' && person.name) {
                    const key = person.name;
                    if (!uniquePersonnel.has(key)) {
                        uniquePersonnel.set(key, {
                            id: person.id || person.name,
                            name: person.name,
                            rank: person.rank || '',
                            hourlyRate: person.hourlyRate || 0,
                            duration: person.duration || 0
                        });
                    }
                } else if (typeof person === 'string') {
                    const key = person;
                    if (!uniquePersonnel.has(key)) {
                        uniquePersonnel.set(key, {
                            id: person,
                            name: person,
                            rank: '',
                            hourlyRate: 0,
                            duration: 0
                        });
                    }
                }
            });
            
            // Get the current duration from the totalDuration field
            const currentDuration = parseFloat($('#totalDuration').val()) || 0;
            console.log('Current duration for personnel:', currentDuration);
            
            // Convert Map back to array
            window.selectedPersonnel = Array.from(uniquePersonnel.values());
            
            // Apply current duration to all personnel
            window.selectedPersonnel.forEach(person => {
                person.duration = currentDuration;
            });
            
            console.log('Processed personnel data with updated durations:', window.selectedPersonnel);
            
            // Get personnel details for each person
            const promises = window.selectedPersonnel.map(person => {
                // Only fetch details if we have a numeric ID
                if (person.id && !isNaN(person.id)) {
                    return $.ajax({
                        url: 'get_personnel_details.php',
                        type: 'GET',
                        data: { personnel_id: person.id },
                        dataType: 'json'
                    }).then(data => {
                        if (data.status === 'success') {
                            person.rank = data.data.academic_rank || person.rank;
                            person.hourlyRate = parseFloat(data.data.hourly_rate) || person.hourlyRate;
                            console.log('Updated personnel with fetched details:', person);
                        }
                        return person;
                    }).catch(error => {
                        console.error('Error fetching personnel details:', error);
                        return person;
                    });
                }
                return Promise.resolve(person);
            });
            
            // Wait for all personnel details to be fetched
            Promise.all(promises).then(() => {
                // Update the UI
                if (typeof window.updatePersonnelList === 'function') {
                    window.updatePersonnelList();
                    console.log('Personnel list updated');
                } else {
                    console.error('updatePersonnelList function not found');
                }
                
                // Calculate total PS
                if (typeof window.calculateTotalPS === 'function') {
                    window.calculateTotalPS();
                } else {
                    console.error('calculateTotalPS function not found');
                }
            });
        }
    } catch (e) {
        console.error('Error parsing personnel data:', e);
    }
}

// Function to fetch duration and PS attribution from PPAS form
async function loadDurationAndPS(ppasFormId, title) {
    console.log('Loading duration and PS for PPAS form ID:', ppasFormId);
    
    try {
        let duration = 0;
        let ppasPS = 0;
        
        // If we have a PPAS form ID, use it
        if (ppasFormId) {
            console.log('Using PPAS form ID for duration fetch:', ppasFormId);
            const response = await $.ajax({
                url: 'get_ppas_duration.php',
                type: 'GET',
                data: { activity_id: ppasFormId },
                dataType: 'json'
            });
            
            console.log('PPAS duration response:', response);
            
            if (response && response.status === 'success') {
                // Set the duration field
                duration = parseFloat(response.total_duration) || 0;
                console.log('Setting duration to:', duration);
                $('#totalDuration').val(duration.toFixed(2));
                
                // Set window.ppasPS which is used in calculateTotalPS
                window.ppasPS = parseFloat(response.ps_attribution) || 0;
                ppasPS = window.ppasPS;
                console.log('Set window.ppasPS to:', window.ppasPS);
                
                // Update duration for all personnel
                if (window.selectedPersonnel && window.selectedPersonnel.length > 0) {
                    console.log('Updating duration for', window.selectedPersonnel.length, 'personnel');
                    for (const person of window.selectedPersonnel) {
                        person.duration = duration;
                        console.log('Updated duration for', person.name, 'to', duration);
                    }
                    
                    // Update the UI after changing durations
                    if (typeof window.updatePersonnelList === 'function') {
                        window.updatePersonnelList();
                    }
                    
                    // Re-calculate total PS
                    if (typeof window.calculateTotalPS === 'function') {
                        window.calculateTotalPS();
                    }
                }
                
                return { duration, ppasPS };
            }
        }
        
        // If we're still here, try looking up by title
        if (title) {
            console.log('Looking up PPAS by title:', title);
            const year = $('#year').val();
            
            if (year) {
                // Get details by activity name and year
                const detailsResponse = await $.ajax({
                    url: 'narrative_handler.php',
                    type: 'POST',
                    data: { 
                        action: 'get_activity_details',
                        activity: title,
                        year: year
                    },
                    dataType: 'json'
                });
                
                console.log('Activity details response:', detailsResponse);
                
                if (detailsResponse && detailsResponse.success) {
                    const data = detailsResponse.data;
                    
                    // If we got a PPAS form ID, fetch duration using that
                    if (data.ppas_form_id) {
                        return loadDurationAndPS(data.ppas_form_id, title);
                    }
                    
                    // Otherwise use the PS attribution directly
                    if (data.ps_attribution) {
                        console.log('Setting PS attribution from activity details:', data.ps_attribution);
                        $('#psAttribution').val(data.ps_attribution);
                        window.ppasPS = parseFloat(data.ps_attribution) || 0;
                        ppasPS = window.ppasPS;
                        console.log('Set window.ppasPS to:', window.ppasPS);
                        
                        // Set duration if available
                        if (data.total_duration || data.total_duration_hours) {
                            duration = parseFloat(data.total_duration || data.total_duration_hours) || 0;
                            console.log('Setting duration to:', duration);
                            $('#totalDuration').val(duration.toFixed(2));
                            
                            // Update duration for all personnel
                            if (window.selectedPersonnel && window.selectedPersonnel.length > 0) {
                                console.log('Updating duration for', window.selectedPersonnel.length, 'personnel from details');
                                for (const person of window.selectedPersonnel) {
                                    person.duration = duration;
                                }
                                
                                // Update the UI
                                if (typeof window.updatePersonnelList === 'function') {
                                    window.updatePersonnelList();
                                }
                                
                                // Re-calculate total PS
                                if (typeof window.calculateTotalPS === 'function') {
                                    window.calculateTotalPS();
                                }
                            }
                        }
                        
                        return { duration, ppasPS };
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error fetching duration and PS:', error);
    }
    
    return { duration: 0, ppasPS: 0 };
}

// Add event listener to run after page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ppasPS if not already set
    window.ppasPS = window.ppasPS || 0;
    
    // Add helper function to check for duplicate personnel
    window.isPersonnelAlreadySelected = function(id) {
        return window.selectedPersonnel.some(p => p.id == id);
    };
    
    // Ensure personnel data is correctly added to form submission
    const originalFormSubmitHandler = window.handleFormSubmit || function() {};
    window.handleFormSubmit = function(event) {
        console.log('Enhanced form submission started');
        
        // Ensure we have a form reference
        const form = event.target || document.querySelector('form');
        if (!form) {
            console.error('Form not found');
            return originalFormSubmitHandler.apply(this, arguments);
        }
        
        // Log personnel data
        console.log('Selected personnel before form submission:', window.selectedPersonnel);
        
        // Make sure we have FormData
        let formData = new FormData(form);
        
        // Remove any existing personnel fields (prevent duplicates)
        const existingInputs = form.querySelectorAll('input[name="selected_personnel"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected personnel as hidden input
        if (window.selectedPersonnel && window.selectedPersonnel.length > 0) {
            console.log('Adding personnel data to form:', window.selectedPersonnel);
            
            // Add as JSON string
            const personnelData = JSON.stringify(window.selectedPersonnel);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_personnel';
            hiddenInput.value = personnelData;
            form.appendChild(hiddenInput);
            
            console.log('Personnel data added to form as hidden input:', personnelData);
        } else {
            console.log('No personnel data to add to form');
        }
        
        // Add title to form if it's missing
        const titleElement = document.getElementById('title');
        if (titleElement && titleElement.value && !formData.has('title')) {
            const titleInput = document.createElement('input');
            titleInput.type = 'hidden';
            titleInput.name = 'title';
            titleInput.value = titleElement.value;
            form.appendChild(titleInput);
            console.log('Added title to form:', titleElement.value);
        }
        
        // Ensure ppas_form_id is included if available
        if (titleElement && titleElement.tagName === 'SELECT' && titleElement.value) {
            // For select elements, the value is likely the ppas_form_id
            const ppasFormId = titleElement.value;
            console.log('Found ppas_form_id from title dropdown:', ppasFormId);
            
            // Add it as a hidden input if not already in the form
            if (!formData.has('ppas_form_id')) {
                const ppasInput = document.createElement('input');
                ppasInput.type = 'hidden';
                ppasInput.name = 'ppas_form_id';
                ppasInput.value = ppasFormId;
                form.appendChild(ppasInput);
                console.log('Added ppas_form_id to form:', ppasFormId);
            }
        } else if (window.narrativeData && window.narrativeData.ppas_form_id) {
            // If we have the narrative data with ppas_form_id, use that
            const ppasFormId = window.narrativeData.ppas_form_id;
            console.log('Using ppas_form_id from narrative data:', ppasFormId);
            
            if (!formData.has('ppas_form_id')) {
                const ppasInput = document.createElement('input');
                ppasInput.type = 'hidden';
                ppasInput.name = 'ppas_form_id';
                ppasInput.value = ppasFormId;
                form.appendChild(ppasInput);
                console.log('Added ppas_form_id to form from narrative data:', ppasFormId);
            }
        }
        
        // Continue with original handler
        return originalFormSubmitHandler.apply(this, arguments);
    };
    
    // Attach our handler to all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't stop propagation, just run our enhanced handler
            window.handleFormSubmit(e);
        });
    });
    
    // Find the original loadNarrativeForEdit function
    if (typeof loadNarrativeForEdit === 'function') {
        // Store the original function
        const originalLoadNarrativeForEdit = loadNarrativeForEdit;
        
        // Override the function with our enhanced version
        loadNarrativeForEdit = function(narrativeId) {
            console.log('Enhanced loadNarrativeForEdit called with ID:', narrativeId);
            
            // Call the original function
            originalLoadNarrativeForEdit(narrativeId);
            
            // Add our additional functionality
            setTimeout(async function() {
                console.log('Attempting to load personnel data for narrative ID:', narrativeId);
                // Try to get the narrative data from the global variable
                if (window.currentNarrativeId && window.currentNarrativeId === narrativeId) {
                    console.log('Current narrative ID matches requested ID');
                    // Make an AJAX call to get the narrative data
                    $.ajax({
                        url: 'narrative_handler.php',
                        type: 'POST',
                        data: { 
                            action: 'get_single',
                            id: narrativeId
                        },
                        dataType: 'json',
                        success: async function(response) {
                            console.log('Received response for narrative data:', response);
                            if (response.success) {
                                console.log('Narrative data retrieved successfully');
                                const narrativeData = response.data;
                                
                                // Store narrative data in global variable for later use
                                window.narrativeData = narrativeData;
                                console.log('Stored narrative data in window.narrativeData:', window.narrativeData);
                                
                                // Set title if it's missing
                                if (narrativeData.title && ($('#title').val() === '' || !$('#title').val())) {
                                    console.log('Setting title to:', narrativeData.title);
                                    $('#title').val(narrativeData.title);
                                    
                                    // Create hidden input for title just in case
                                    if ($('input[name="title"]').length === 0) {
                                        $('<input>').attr({
                                            type: 'hidden',
                                            name: 'title',
                                            value: narrativeData.title
                                        }).appendTo('form');
                                        console.log('Added hidden title input with value:', narrativeData.title);
                                    }
                                    
                                    // Also need to select the right option if it's a dropdown
                                    const titleSelect = document.getElementById('title');
                                    if (titleSelect && titleSelect.tagName === 'SELECT') {
                                        // Try to find option by value or text content
                                        let found = false;
                                        for (let i = 0; i < titleSelect.options.length; i++) {
                                            const option = titleSelect.options[i];
                                            if (option.value == narrativeData.ppas_form_id || 
                                                option.textContent === narrativeData.title) {
                                                titleSelect.selectedIndex = i;
                                                found = true;
                                                break;
                                            }
                                        }
                                        
                                        // If not found, we might need to add it
                                        if (!found) {
                                            const newOption = document.createElement('option');
                                            newOption.value = narrativeData.ppas_form_id || '';
                                            newOption.textContent = narrativeData.title;
                                            newOption.selected = true;
                                            titleSelect.appendChild(newOption);
                                        }
                                    }
                                }
                                
                                // Add ppas_form_id as hidden input if available
                                if (narrativeData.ppas_form_id) {
                                    console.log('Adding ppas_form_id hidden input:', narrativeData.ppas_form_id);
                                    // Remove existing if any
                                    $('input[name="ppas_form_id"]').remove();
                                    
                                    $('<input>').attr({
                                        type: 'hidden',
                                        name: 'ppas_form_id',
                                        value: narrativeData.ppas_form_id
                                    }).appendTo('form');
                                }
                                
                                // Get the duration and PS attribution first, so we have the values ready for personnel
                                if (narrativeData.ppas_form_id) {
                                    await loadDurationAndPS(narrativeData.ppas_form_id, narrativeData.title);
                                } else {
                                    // Try by title
                                    await loadDurationAndPS(null, narrativeData.title);
                                }
                                
                                // Set PS attribution directly if available
                                if (narrativeData.ps_attribution) {
                                    console.log('Setting PS attribution from narrative:', narrativeData.ps_attribution);
                                    $('#psAttribution').val(narrativeData.ps_attribution);
                                    window.ppasPS = parseFloat(narrativeData.ps_attribution) || 0;
                                    console.log('Set window.ppasPS to:', window.ppasPS);
                                }
                                
                                // Load personnel data after all other data is set
                                loadPersonnelFromNarrative(narrativeData);
                                
                                // Force calculate total PS after everything has loaded
                                setTimeout(() => {
                                    if (typeof window.calculateTotalPS === 'function') {
                                        window.calculateTotalPS();
                                    }
                                    
                                    // Also create a hidden input for personnel data
                                    if (window.selectedPersonnel && window.selectedPersonnel.length > 0) {
                                        // Remove existing hidden input if any
                                        $('input[name="selected_personnel"]').remove();
                                        
                                        $('<input>').attr({
                                            type: 'hidden',
                                            name: 'selected_personnel',
                                            value: JSON.stringify(window.selectedPersonnel)
                                        }).appendTo('form');
                                        
                                        console.log('Added hidden personnel input with', window.selectedPersonnel.length, 'items');
                                    }
                                }, 1000);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching narrative data:', error);
                        }
                    });
                } else {
                    console.log('Current narrative ID does not match or is not set');
                }
            }, 1000); // Wait 1 second to ensure the original function has completed
        };
    } else {
        console.error('loadNarrativeForEdit function not found');
    }
}); 