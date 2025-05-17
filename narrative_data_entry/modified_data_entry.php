// Helper function to load activities with a callback
function loadActivitiesForCampusAndYear(callback) {
    const campus = document.getElementById('campus').value;
    const year = document.getElementById('year').value;
    
    // Only proceed if both campus and year are selected
    if (!campus || !year) {
        if (typeof callback === 'function') callback();
        return;
    }
    
    // Load activities filtered by campus and year
    $.ajax({
        url: 'narrative_handler.php',
        type: 'POST',
        data: { 
            action: 'get_titles_from_ppas',
            campus: campus,
            year: year
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const titleSelect = document.getElementById('title');
                
                // Remove existing event listener first to avoid duplicates
                titleSelect.removeEventListener('change', loadActivityDetails);
                
                // Clear existing options except the first one
                while (titleSelect.options.length > 1) {
                    titleSelect.remove(1);
                }
                
                // Add new options
                response.data.forEach(activity => {
                    const option = document.createElement('option');
                    option.value = activity.title;
                    option.textContent = activity.title;
                    
                    // Add red color to activities that already have narratives
                    if (activity.has_narrative) {
                        option.style.color = 'red';
                        option.setAttribute('data-has-narrative', 'true');
                    }
                    
                    titleSelect.appendChild(option);
                });
                
                // Add change event listener to title dropdown
                titleSelect.addEventListener('change', loadActivityDetails);
                
                if (typeof callback === 'function') callback();
            } else {
                console.error("Error loading activities: " + response.message);
                if (typeof callback === 'function') callback();
            }
        },
        error: function(xhr) {
            console.error("AJAX Error:", xhr.responseText);
            if (typeof callback === 'function') callback();
        }
    });
}

// Function to load activities based on selected campus and year
function loadActivitiesForCampusAndYear() {
    const campus = document.getElementById('campus').value;
    const year = document.getElementById('year').value;
    
    // Only proceed if both campus and year are selected
    if (!campus || !year) {
        return;
    }
    
    // Load activities filtered by campus and year
    $.ajax({
        url: 'narrative_handler.php',
        type: 'POST',
        data: { 
            action: 'get_titles_from_ppas',
            campus: campus,
            year: year
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const titleSelect = document.getElementById('title');
                
                // Remove existing event listener first to avoid duplicates
                titleSelect.removeEventListener('change', loadActivityDetails);
                
                // Clear existing options except the first one
                while (titleSelect.options.length > 1) {
                    titleSelect.remove(1);
                }
                
                // Add new options
                response.data.forEach(activity => {
                    const option = document.createElement('option');
                    option.value = activity.title;
                    option.textContent = activity.title;
                    
                    // Add red color to activities that already have narratives
                    if (activity.has_narrative) {
                        option.style.color = 'red';
                        option.setAttribute('data-has-narrative', 'true');
                    }
                    
                    titleSelect.appendChild(option);
                });
                
                // Display message if no activities found
                if (response.data.length === 0) {
                    console.log("No activities found for the selected campus and year");
                    // Reset title dropdown
                    titleSelect.selectedIndex = 0;
                }
                
                // Add change event listener to title dropdown
                titleSelect.addEventListener('change', loadActivityDetails);
            } else {
                console.error("Error loading activities: " + response.message);
                // Show error in a small notification
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Could not load activities for the selected campus and year',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error (activities): " + status + " - " + error);
            console.log("Response Text: " + xhr.responseText);
        }
    });
} 