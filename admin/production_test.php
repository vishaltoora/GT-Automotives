<?php
// Production Test Script for Create Sale Page
// This script helps diagnose issues in production environment

echo "<h1>Production Environment Test</h1>";
echo "<p>This script tests various components needed for the create sale page to work.</p>";

// Test 1: Server Information
echo "<h2>1. Server Information</h2>";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "Script Path: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "<br>";

// Test 2: Environment Detection
echo "<h2>2. Environment Detection</h2>";
$is_production = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost' && ($_SERVER['SERVER_NAME'] ?? '') !== '127.0.0.1';
echo "Environment: " . ($is_production ? 'Production' : 'Development') . "<br>";
echo "Localhost Check: " . (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) ? 'Yes' : 'No') . "<br>";

// Test 3: File System Access
echo "<h2>3. File System Access</h2>";
$base_path = dirname(__DIR__);
echo "Base Path: " . $base_path . "<br>";
echo "Current Directory: " . getcwd() . "<br>";

$files_to_check = [
    'includes/db_connect.php' => $base_path . '/includes/db_connect.php',
    'includes/auth.php' => $base_path . '/includes/auth.php',
    'admin/includes/header.php' => $base_path . '/admin/includes/header.php',
    'admin/includes/footer.php' => $base_path . '/admin/includes/footer.php'
];

foreach ($files_to_check as $file => $full_path) {
    echo "File: " . $file . " - " . (file_exists($full_path) ? 'Exists' : 'Missing') . " (" . $full_path . ")<br>";
}

// Test 4: Database Connection
echo "<h2>4. Database Connection</h2>";
try {
    require_once $base_path . '/includes/db_connect.php';
    if (isset($conn) && $conn instanceof mysqli) {
        echo "Database connection: Success<br>";
        echo "Database host: " . $conn->host_info . "<br>";
        echo "Database server: " . $conn->server_info . "<br>";
        
        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Database query test: Success (Result: " . $row['test'] . ")<br>";
        } else {
            echo "Database query test: Failed<br>";
        }
    } else {
        echo "Database connection: Failed - \$conn not set or not mysqli<br>";
    }
} catch (Exception $e) {
    echo "Database connection: Exception - " . $e->getMessage() . "<br>";
}

// Test 5: Session Handling
echo "<h2>5. Session Handling</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session writable: " . (is_writable(session_save_path()) ? 'Yes' : 'No') . "<br>";

// Test 6: Authentication
echo "<h2>6. Authentication</h2>";
try {
    require_once $base_path . '/includes/auth.php';
    echo "Auth file loaded: Success<br>";
    
    // Test auth functions
    echo "isLoggedIn(): " . (isLoggedIn() ? 'True' : 'False') . "<br>";
    echo "getCurrentUserId(): " . (getCurrentUserId() ?? 'null') . "<br>";
    echo "isAdmin(): " . (isAdmin() ? 'True' : 'False') . "<br>";
    
} catch (Exception $e) {
    echo "Auth file load: Exception - " . $e->getMessage() . "<br>";
}

// Test 7: Database Tables
echo "<h2>7. Database Tables Check</h2>";
if (isset($conn) && $conn instanceof mysqli) {
    $required_tables = ['users', 'tires', 'brands', 'locations', 'services', 'service_categories', 'sales', 'sale_items'];
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "Table '$table': Exists<br>";
            
            // Count records
            $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            if ($count_result) {
                $count_row = $count_result->fetch_assoc();
                echo "  - Records: " . $count_row['count'] . "<br>";
            }
        } else {
            echo "Table '$table': Missing<br>";
        }
    }
}

// Test 8: Error Reporting
echo "<h2>8. Error Reporting</h2>";
echo "Error reporting: " . (error_reporting() ? 'Enabled' : 'Disabled') . "<br>";
echo "Display errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "<br>";
echo "Log errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "<br>";
echo "Error log: " . (ini_get('error_log') ?: 'Default') . "<br>";

// Test 9: Permissions
echo "<h2>9. File Permissions</h2>";
$dirs_to_check = [
    'includes' => $base_path . '/includes',
    'uploads' => $base_path . '/uploads',
    'storage' => $base_path . '/storage',
    'admin/includes' => $base_path . '/admin/includes'
];

foreach ($dirs_to_check as $dir => $full_path) {
    if (is_dir($full_path)) {
        echo "Directory '$dir': " . (is_readable($full_path) ? 'Readable' : 'Not readable') . 
             ", " . (is_writable($full_path) ? 'Writable' : 'Not writable') . "<br>";
    } else {
        echo "Directory '$dir': Does not exist<br>";
    }
}

echo "<hr>";
echo "<p><strong>Test completed.</strong> If you see any failures above, they may be causing the create sale page issues.</p>";
echo "<p>To test the create sale page with debug info, visit: <a href='create_sale.php?debug=1'>create_sale.php?debug=1</a></p>";
?> 