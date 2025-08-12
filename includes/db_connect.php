<?php
// MySQL Database configuration
// Check if we're in production or development
$is_production = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost' && ($_SERVER['SERVER_NAME'] ?? '') !== '127.0.0.1';

// Include production configuration if available
if (file_exists(dirname(__DIR__) . '/includes/production_config.php')) {
    require_once dirname(__DIR__) . '/includes/production_config.php';
}

// Try to load environment variables if available
if (file_exists(dirname(__DIR__) . '/.env')) {
    $env_file = file_get_contents(dirname(__DIR__) . '/.env');
    $env_vars = [];
    foreach (explode("\n", $env_file) as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value, '"\'');
        }
    }
    
    // Use environment variables if available
    $host = $env_vars['DB_HOST'] ?? 'localhost';
    $dbname = $env_vars['DB_DATABASE'] ?? 'gt_automotives';
    $username = $env_vars['DB_USERNAME'] ?? 'gtadmin';
    $password = $env_vars['DB_PASSWORD'] ?? 'Vishal@1234#';
} elseif ($is_production && function_exists('getProductionConfig')) {
    // Use production configuration
    $host = getProductionConfig('DB_HOST', 'localhost');
    $dbname = getProductionConfig('DB_NAME', 'gt_automotives');
    $username = getProductionConfig('DB_USER', 'gtadmin');
    $password = getProductionConfig('DB_PASS', 'Vishal@1234#');
} else {
    // Fallback to hardcoded values
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
