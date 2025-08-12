<?php
// MySQL Database configuration
// Check if we're in production or development
$is_production = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost' && ($_SERVER['SERVER_NAME'] ?? '') !== '127.0.0.1';

if ($is_production) {
    // Production database settings
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#'; // Change this to your actual production password
} else {
    // Development database settings
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#';
}

// Create MySQL connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
    // Test connection with a simple query
    if (!$conn->query("SELECT 1")) {
        throw new Exception("Database query test failed");
    }
    
} catch (Exception $e) {
    // Log error for production
    if ($is_production) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please check the error logs.");
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// No wrapper functions needed - use native MySQL functions directly
?>
