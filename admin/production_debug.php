<?php
// Production Debug Script for GT Automotives
// This script helps identify production issues

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set base path
$base_path = dirname(__DIR__);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Production Debug - GT Automotives</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background-color: #d4edda; border-color: #c3e6cb; }";
echo ".error { background-color: #f8d7da; border-color: #f5c6cb; }";
echo ".warning { background-color: #fff3cd; border-color: #ffeaa7; }";
echo ".info { background-color: #d1ecf1; border-color: #bee5eb; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h1>Production Debug Report</h1>";

// 1. Server Information
echo "<div class='section info'>";
echo "<h2>1. Server Information</h2>";
echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";
echo "<p><strong>Script Path:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</p>";
echo "<p><strong>Current Working Directory:</strong> " . getcwd() . "</p>";
echo "</div>";

// 2. File System Check
echo "<div class='section info'>";
echo "<h2>2. File System Check</h2>";

$files_to_check = [
    'includes/db_connect.php',
    'includes/auth.php',
    'admin/create_sale.php',
    '.env',
    '.env.production'
];

foreach ($files_to_check as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p class='success'>✓ {$file} exists</p>";
        echo "<p>Full path: {$full_path}</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($full_path)), -4) . "</p>";
    } else {
        echo "<p class='error'>✗ {$file} NOT FOUND</p>";
        echo "<p>Full path: {$full_path}</p>";
    }
}
echo "</div>";

// 3. Database Connection Test
echo "<div class='section info'>";
echo "<h2>3. Database Connection Test</h2>";

try {
    // Try to include database connection
    if (file_exists($base_path . '/includes/db_connect.php')) {
        require_once $base_path . '/includes/db_connect.php';
        
        if (isset($conn) && $conn instanceof mysqli) {
            if ($conn->ping()) {
                echo "<p class='success'>✓ Database connection successful</p>";
                echo "<p><strong>Host:</strong> " . $conn->host_info . "</p>";
                
                // Test a simple query
                $result = $conn->query("SELECT COUNT(*) as count FROM tires");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<p class='success'>✓ Database query successful - Found " . $row['count'] . " tires</p>";
                } else {
                    echo "<p class='error'>✗ Database query failed: " . $conn->error . "</p>";
                }
            } else {
                echo "<p class='error'>✗ Database connection failed: " . $conn->connect_error . "</p>";
            }
        } else {
            echo "<p class='error'>✗ Database connection variable not set</p>";
        }
    } else {
        echo "<p class='error'>✗ Database connection file not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection exception: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Session Test
echo "<div class='section info'>";
echo "<h2>4. Session Test</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

// Test session write
$_SESSION['debug_test'] = 'test_value_' . time();
if (isset($_SESSION['debug_test'])) {
    echo "<p class='success'>✓ Session write successful</p>";
} else {
    echo "<p class='error'>✗ Session write failed</p>";
}
echo "</div>";

// 5. Authentication Test
echo "<div class='section info'>";
echo "<h2>5. Authentication Test</h2>";

if (file_exists($base_path . '/includes/auth.php')) {
    require_once $base_path . '/includes/auth.php';
    
    if (function_exists('isLoggedIn')) {
        $is_logged_in = isLoggedIn();
        echo "<p><strong>Is Logged In:</strong> " . ($is_logged_in ? 'Yes' : 'No') . "</p>";
        
        if ($is_logged_in) {
            echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
            echo "<p><strong>Username:</strong> " . ($_SESSION['username'] ?? 'Not set') . "</p>";
            echo "<p><strong>Is Admin:</strong> " . ($_SESSION['is_admin'] ?? 'Not set') . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Authentication functions not found</p>";
    }
} else {
    echo "<p class='error'>✗ Authentication file not found</p>";
}
echo "</div>";

// 6. Environment Variables
echo "<div class='section info'>";
echo "<h2>6. Environment Variables</h2>";

// Check .env file
$env_file = $base_path . '/.env';
if (file_exists($env_file)) {
    echo "<p class='success'>✓ .env file exists</p>";
    $env_content = file_get_contents($env_file);
    $env_lines = explode("\n", $env_content);
    
    echo "<h3>Environment Variables:</h3>";
    echo "<ul>";
    foreach ($env_lines as $line) {
        $line = trim($line);
        if (!empty($line) && !str_starts_with($line, '#')) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');
                
                // Mask sensitive values
                if (in_array($key, ['DB_PASSWORD', 'APP_KEY'])) {
                    $value = str_repeat('*', strlen($value));
                }
                
                echo "<li><strong>{$key}:</strong> {$value}</li>";
            }
        }
    }
    echo "</ul>";
} else {
    echo "<p class='error'>✗ .env file not found</p>";
}
echo "</div>";

// 7. PHP Configuration
echo "<div class='section info'>";
echo "<h2>7. PHP Configuration</h2>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>Error Reporting:</strong> " . ini_get('error_reporting') . "</p>";
echo "<p><strong>Display Errors:</strong> " . ini_get('display_errors') . "</p>";
echo "<p><strong>Log Errors:</strong> " . ini_get('log_errors') . "</p>";
echo "<p><strong>Error Log:</strong> " . ini_get('error_log') . "</p>";
echo "</div>";

// 8. Recommendations
echo "<div class='section warning'>";
echo "<h2>8. Recommendations</h2>";
echo "<ul>";
echo "<li>Check production database credentials in .env file</li>";
echo "<li>Verify file permissions for includes directory</li>";
echo "<li>Check server error logs for specific error messages</li>";
echo "<li>Ensure session directory is writable</li>";
echo "<li>Test direct access to /admin/create_sale.php</li>";
echo "<li>Check if mod_rewrite is enabled for Laravel routes</li>";
echo "</ul>";
echo "</div>";

echo "</body>";
echo "</html>";
?> 