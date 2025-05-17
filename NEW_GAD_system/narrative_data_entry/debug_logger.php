<?php
// Debug logger for narrative_data_entry
function debug_to_file($message, $data = null) {
    $log_file = __DIR__ . '/debug_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_message = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_message .= "\n" . print_r($data, true);
        } else {
            $log_message .= "\n" . $data;
        }
    }
    
    $log_message .= "\n" . str_repeat('-', 80) . "\n";
    
    // Append to log file
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Helper function to get the current form data
function get_form_data() {
    $data = $_POST;
    
    // Mask any sensitive information if needed
    // $data['password'] = '******';
    
    return $data;
}
?> 