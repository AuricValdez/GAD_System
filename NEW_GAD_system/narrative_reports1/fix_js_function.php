<?php
// Fix for getDefaultRatings not defined in JavaScript

// Read the existing file
$file = 'print_narrative.php';
if (!file_exists($file)) {
    echo "Error: File $file not found!\n";
    exit(1);
}

$content = file_get_contents($file);
if ($content === false) {
    echo "Error: Could not read $file!\n";
    exit(1);
}

// Define the JavaScript version of getDefaultRatings to add
$js_function = "
            // JavaScript version of getDefaultRatings
            function getDefaultRatings() {
                return {
                    \"Excellent\": {
                        \"BatStateU\": 50,
                        \"Others\": 60
                    },
                    \"Very Satisfactory\": {
                        \"BatStateU\": 70,
                        \"Others\": 80
                    },
                    \"Satisfactory\": {
                        \"BatStateU\": 90,
                        \"Others\": 100
                    },
                    \"Fair\": {
                        \"BatStateU\": 110,
                        \"Others\": 120
                    },
                    \"Poor\": {
                        \"BatStateU\": 130,
                        \"Others\": 140
                    }
                };
            }
";

// Use a simpler pattern that's more likely to match - right before the transformRatingsToProperFormat function
$pattern = '/function transformRatingsToProperFormat\(ratingsData\) \{/s';
$replacement = "$js_function\n            function transformRatingsToProperFormat(ratingsData) {";

$new_content = preg_replace($pattern, $replacement, $content);

if ($new_content === null) {
    echo "Error: preg_replace failed - pattern not found!\n";
    exit(1);
}

if ($new_content === $content) {
    echo "Error: No changes were made to the file - pattern not found!\n";
    exit(1);
}

// Save the updated file
$result = file_put_contents($file, $new_content);
if ($result === false) {
    echo "Error: Could not write to $file!\n";
    exit(1);
}

echo "Success: Added JavaScript getDefaultRatings function to $file\n";
echo "Added " . strlen($new_content) - strlen($content) . " bytes\n";
?> 