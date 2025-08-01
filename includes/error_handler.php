<?php
// Centralized error handler for deployment debugging

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    
    // Log error
    error_log($error_message);
    
    // Only display errors in development
    if (isset($_GET['debug']) || $_SERVER['SERVER_NAME'] === 'localhost') {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Error:</strong> " . htmlspecialchars($error_message);
        echo "</div>";
    }
    
    return true;
}

// Set custom error handler
set_error_handler("customErrorHandler");

// Function to safely include files
function safeInclude($file) {
    if (file_exists($file)) {
        try {
            require_once $file;
            return true;
        } catch (Exception $e) {
            error_log("Failed to include $file: " . $e->getMessage());
            return false;
        }
    } else {
        error_log("File not found: $file");
        return false;
    }
}

// Function to check required PHP extensions
function checkRequiredExtensions() {
    $required = ['mysqli', 'gd', 'fileinfo', 'zip'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    return $missing;
}

// Function to test database connection
function testDatabaseConnection() {
    try {
        // Create a new connection for testing
        $host = 'localhost';
        $dbname = 'gt_automotives';
        $username = 'gtadmin';
        $password = 'Vishal@1234#';
        
        $test_conn = new mysqli($host, $username, $password, $dbname);
        
        if ($test_conn->connect_error) {
            return "Connection failed: " . $test_conn->connect_error;
        }
        
        $result = $test_conn->query("SHOW TABLES");
        if ($result) {
            $table_count = $result->num_rows;
            $test_conn->close();
            return "Connected ($table_count tables)";
        } else {
            $test_conn->close();
            return "Query failed";
        }
    } catch (Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
}

// Function to create uploads directory if needed
function ensureUploadsDirectory() {
    $uploads_dir = __DIR__ . '/../uploads';
    $compressed_dir = $uploads_dir . '/compressed';
    
    if (!is_dir($uploads_dir)) {
        if (!mkdir($uploads_dir, 0755, true)) {
            throw new Exception("Failed to create uploads directory");
        }
    }
    
    if (!is_dir($compressed_dir)) {
        if (!mkdir($compressed_dir, 0755, true)) {
            throw new Exception("Failed to create compressed directory");
        }
    }
    
    if (!is_writable($uploads_dir)) {
        throw new Exception("Uploads directory not writable");
    }
}
?> 