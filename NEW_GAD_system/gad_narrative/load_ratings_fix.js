/**
 * LOADER SCRIPT FOR RATINGS FIX
 * This script will load the comprehensive fix and ensure it runs properly
 */

(function() {
    console.log('[LOADER] Initializing ratings fix loader');
    
    // Create a script element to load the fix
    const script = document.createElement('script');
    script.src = 'fixed_ratings.js?v=' + new Date().getTime(); // Add timestamp to prevent caching
    script.async = false; // Load synchronously to ensure it runs immediately
    
    // Add success handler
    script.onload = function() {
        console.log('[LOADER] Successfully loaded fixed_ratings.js');
        
        // Check if the fix loaded a global function
        if (typeof window.forceRatingsDisplay === 'function') {
            console.log('[LOADER] Calling forceRatingsDisplay function');
            window.forceRatingsDisplay();
        } else {
            console.warn('[LOADER] Global forceRatingsDisplay function not found');
        }
    };
    
    // Add error handler
    script.onerror = function() {
        console.error('[LOADER] Failed to load fixed_ratings.js');
        
        // Fallback: try to load from alternate location
        const fallbackScript = document.createElement('script');
        fallbackScript.src = '../narrative_reports/fixed_ratings.js?v=' + new Date().getTime();
        fallbackScript.async = false;
        
        fallbackScript.onload = function() {
            console.log('[LOADER] Successfully loaded fix from fallback location');
        };
        
        fallbackScript.onerror = function() {
            console.error('[LOADER] Complete failure to load fix script');
            alert('Failed to load ratings fix script. Please contact support.');
        };
        
        document.head.appendChild(fallbackScript);
    };
    
    // Add to document
    document.head.appendChild(script);
    
    // Create a "safety net" setTimeout
    setTimeout(function() {
        if (typeof window.forceRatingsDisplay === 'function') {
            console.log('[LOADER] Executing forceRatingsDisplay from safety net');
            window.forceRatingsDisplay();
        }
    }, 2000); // Wait 2 seconds
})(); 