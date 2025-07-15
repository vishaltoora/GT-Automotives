<?php
// Centralized error handler for deployment debugging

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    
    // Log error
    error_log($error_message);
    
    // In development, display error
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

// Function to test database connection
function testDatabaseConnection() {
    try {
        $db_path = __DIR__ . '/../database/gt_automotives.db';
        
        if (!file_exists($db_path)) {
            throw new Exception("Database file not found at: $db_path");
        }
        
        if (!is_readable($db_path)) {
            throw new Exception("Database file not readable");
        }
        
        $conn = new SQLite3($db_path);
        $conn->enableExceptions(true);
        
        // Test a simple query
        $result = $conn->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'");
        if (!$result) {
            throw new Exception("Database query failed");
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw $e;
    }
}

// Function to check required PHP extensions
function checkRequiredExtensions() {
    $required = ['sqlite3', 'gd', 'fileinfo'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    return $missing;
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