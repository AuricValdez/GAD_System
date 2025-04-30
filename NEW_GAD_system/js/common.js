function handleLogout(e) {
    e.preventDefault();
    window.location.href = getBasePath() + 'loading_screen.php?to=' + getBasePath() + 'index.php';
}

function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/academic_rank/') || 
        path.includes('/gbp_forms/') || 
        path.includes('/personnel_list/') || 
        path.includes('/ppas_forms/') || 
        path.includes('/target_forms/')) {
        return '../';
    }
    return '';
}
