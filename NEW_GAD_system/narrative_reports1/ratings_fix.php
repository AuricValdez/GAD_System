<?php
// Script to update the getDefaultRatings function in print_narrative.php

$file = 'print_narrative.php';
$content = file_get_contents($file);

// Updated getDefaultRatings function with higher values
$newFunction = <<<'EOD'
function getDefaultRatings() {
    return [
        'Excellent' => [
            'BatStateU' => 50,
            'Others' => 60
        ],
        'Very Satisfactory' => [
            'BatStateU' => 70,
            'Others' => 80
        ],
        'Satisfactory' => [
            'BatStateU' => 90,
            'Others' => 100
        ],
        'Fair' => [
            'BatStateU' => 110,
            'Others' => 120
        ],
        'Poor' => [
            'BatStateU' => 130,
            'Others' => 140
        ]
    ];
}
EOD;

// Pattern to find the existing function
$pattern = '/function getDefaultRatings\(\) \{.*?return \[.*?\];.*?\}/s';

// Replace the function
$content = preg_replace($pattern, $newFunction, $content);

// Add console.log of data.activity_ratings so we can see what's happening
$jsLogPattern = '/data\.activity_ratings = transformedActivityRatings;/';
$jsLogReplacement = 'data.activity_ratings = transformedActivityRatings; console.log("FINAL ACTIVITY RATINGS:", data.activity_ratings);';
$content = preg_replace($jsLogPattern, $jsLogReplacement, $content);

// Also modify the transformRatingsToProperFormat function to always use default ratings
$transformPattern = '/function transformRatingsToProperFormat\(ratingsData\) \{.*?return properRatings;.*?\}/s';
$transformReplacement = <<<'EOD'
function transformRatingsToProperFormat(ratingsData) {
    console.log('Using default ratings instead of parsing');
    return getDefaultRatings();
}
EOD;
$content = preg_replace($transformPattern, $transformReplacement, $content);

// Save the updated content
file_put_contents($file, $content);

echo "Updated getDefaultRatings function in $file with higher values\n";
echo "Also modified transformRatingsToProperFormat to always use default ratings\n";
?> 