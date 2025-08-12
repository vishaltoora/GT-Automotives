<?php
// MySQL Database configuration
$host = 'localhost';
$dbname = 'gt_automotives';
$username = 'gtadmin';
$password = 'Vishal@1234#'; // Change this to your actual password

// Create MySQL connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// No wrapper functions needed - use native MySQL functions directly
?> 