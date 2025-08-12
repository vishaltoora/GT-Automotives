<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>GT Automotives - Connection Test</h1>";

// Test session
echo "<h2>Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: " . session_id() . "<br>";
echo "Session Data: " . print_r($_SESSION, true) . "<br>";

// Test database connection
echo "<h2>Database Test</h2>";
try {
    $base_path = dirname(__DIR__);
    require_once $base_path . '/includes/db_connect.php';
    
    if (isset($conn)) {
        echo "Database connected successfully!<br>";
        
        // Test a simple query
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "Tables found: " . $result->num_rows . "<br>";
            echo "Tables: ";
            while ($row = $result->fetch_array()) {
                echo $row[0] . ", ";
            }
            echo "<br>";
        }
    } else {
        echo "Database connection failed - \$conn not set<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Test file includes
echo "<h2>File Include Test</h2>";
$base_path = dirname(__DIR__);
echo "Base path: " . $base_path . "<br>";
echo "DB connect file exists: " . (file_exists($base_path . '/includes/db_connect.php') ? 'Yes' : 'No') . "<br>";
echo "Auth file exists: " . (file_exists($base_path . '/includes/auth.php') ? 'Yes' : 'No') . "<br>";

// Test server info
echo "<h2>Server Info</h2>";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "<br>";

// Test if we can access the create_sale.php file
echo "<h2>File Access Test</h2>";
$create_sale_path = __DIR__ . '/create_sale.php';
echo "Create sale file exists: " . (file_exists($create_sale_path) ? 'Yes' : 'No') . "<br>";
echo "Create sale file path: " . $create_sale_path . "<br>";
echo "Create sale file readable: " . (is_readable($create_sale_path) ? 'Yes' : 'No') . "<br>";

echo "<hr>";
echo "<a href='create_sale.php'>Go to Create Sale Page</a><br>";
echo "<a href='create_sale.php?debug=1'>Go to Create Sale Page with Debug</a><br>";
echo "<a href='login.php'>Go to Login Page</a><br>";
echo "<a href='index.php'>Go to Admin Dashboard</a><br>";
?> 