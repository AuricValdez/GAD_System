<?php
// Include database configuration
require_once "../config.php";

// Create a table for storing Central notifications about campus replies
$sql = "CREATE TABLE IF NOT EXISTS central_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gbp_id INT NOT NULL,
    campus VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (campus),
    INDEX (gbp_id),
    INDEX (is_read)
)";

if ($conn->query($sql) === TRUE) {
    echo "Central notifications table created successfully";
} else {
    echo "Error creating central notifications table: " . $conn->error;
}

// Close connection
$conn->close();
?> 