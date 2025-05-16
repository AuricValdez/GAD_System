<?php
// Script to fix hardcoded values in print_narrative.php

$file = 'print_narrative.php';
$content = file_get_contents($file);

// Replace hardcoded values in the JavaScript functions
$replacements = [
    // Remove hardcoded ratings fallback
    '/\/\/ As a hardcoded fallback[\s\S]*?echo "\}\;\\n"\;/' => 
    "// No hardcoded fallback - use empty object instead\necho \"const emptyRatings = {};\\n\";",
    
    // Update finalActivityRatings and finalTimelinessRatings to use emptyRatings instead of hardcodedRatings
    '/echo "const finalActivityRatings = ppasEntriesActivityRatings \|\| narrativeRatings \|\| dbActivityRatings \|\| hardcodedRatings\\n"\;/' => 
    'echo "const finalActivityRatings = ppasEntriesActivityRatings || narrativeRatings || dbActivityRatings || emptyRatings\\n";',
    
    '/echo "const finalTimelinessRatings = ppasEntriesTimelinessRatings \|\| narrativeTimelinessRatings \|\| dbTimelinessRatings \|\| hardcodedRatings\\n"\;/' => 
    'echo "const finalTimelinessRatings = ppasEntriesTimelinessRatings || narrativeTimelinessRatings || dbTimelinessRatings || emptyRatings\\n";',
    
    // Replace extractRatingValue hardcoded defaults
    '/\/\/ Last resort - return 1 for BatStateU and 2 for Others to provide default values\s*return participantType === \'batstateu\' \? 1 : 2;/' => 
    "// Return 0 if no rating found\nreturn 0;",
    
    '/\/\/ Return default values based on participant type\s*return participantType === \'batstateu\' \? 1 : 2;/' => 
    "// Return 0 instead of hardcoded values\nreturn 0;",
    
    // Replace calculateRatingTotal hardcoded defaults
    '/if \(!ratings\) return 3;/' => 
    'if (!ratings) return 0;',
    
    '/\/\/ Last resort - return 3 as the default total\s*return 3;/' => 
    "// Return 0 if no rating found\nreturn 0;",
    
    '/return 3; \/\/ Default fallback/' => 
    'return 0; // No default fallback',
    
    // Replace calculateTotalRespondents hardcoded defaults
    '/if \(!ratings\) return participantType === \'batstateu\' \? \'5\' : \'10\';/' => 
    'if (!ratings) return \'0\';',
    
    '/return participantType === \'batstateu\' \? \'5\' : \'10\';/' => 
    'return \'0\';',
    
    '/total = participantType === \'batstateu\' \? 5 : 10;/' => 
    'total = 0;',
    
    // Replace calculateTotalParticipants hardcoded defaults
    '/if \(!ratings\) return \'15\';/' => 
    'if (!ratings) return \'0\';',
    
    '/return \'15\';/' => 
    'return \'0\';',
    
    '/total = 15;/' => 
    'total = 0;'
];

foreach ($replacements as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Save the updated content
file_put_contents($file . '.fixed', $content);

echo "Fixed file saved as {$file}.fixed\n";
?> 