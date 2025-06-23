<?php
// Database connection helper
function getConnection() {
    $server = "localhost";
    $username = "root";
    $password = "";
    $database = "gad_db";
    
    try {
        $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw $e;
    }
} 