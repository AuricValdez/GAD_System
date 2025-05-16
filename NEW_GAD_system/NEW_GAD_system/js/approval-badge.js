/**
 * Fetches the count of pending GBP entries and updates the approval badge
 */
function updateApprovalBadge() {
    // Skip if not logged in as Central (badge is only visible to Central)
    if (!document.querySelector('.approval-link')) {
        return;
    }

    // If we're on the approval page (link is active), we don't need to update the badge with intervals
    const isApprovalPage = document.querySelector('.approval-link.active');
    
    const formData = new FormData();
    formData.append('action', 'count_pending');
    
    fetch('../approval/gbp_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('approvalBadge');
            
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                }
            } else {
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error fetching pending count:', error);
    });
}

// Update approval badge when the page loads
document.addEventListener('DOMContentLoaded', function() {
    updateApprovalBadge();
    
    // If we're not on the approval page, set up interval updates
    const isApprovalPage = document.querySelector('.approval-link.active');
    if (!isApprovalPage) {
        // Update badge every 30 seconds
        setInterval(updateApprovalBadge, 30000);
    }
}); 