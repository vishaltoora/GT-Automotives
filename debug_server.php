<?php
// Simple server debug script
// This helps identify what's causing the 500 error

echo "=== GT Automotives Server Debug ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n\n";

echo "=== Testing Basic PHP ===\n";
echo "PHP Info: " . (phpinfo() ? 'Working' : 'Failed') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n\n";

echo "=== Testing File System ===\n";
$base_path = __DIR__;
echo "Base Path: $base_path\n";
echo "Directory Readable: " . (is_readable($base_path) ? 'Yes' : 'No') . "\n";
echo "Directory Writable: " . (is_writable($base_path) ? 'Yes' : 'No') . "\n\n";

echo "=== Testing Includes ===\n";
$files_to_test = [
    'includes/production_config.php',
    'includes/db_connect.php',
    'includes/auth.php'
];

foreach ($files_to_test as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "✓ $file exists\n";
        echo "  Readable: " . (is_readable($full_path) ? 'Yes' : 'No') . "\n";
        echo "  Size: " . filesize($full_path) . " bytes\n";
        
        // Test if we can include it
        try {
            ob_start();
            include_once $full_path;
            ob_end_clean();
            echo "  Include: Success\n";
        } catch (Exception $e) {
            echo "  Include: Failed - " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  Include: Failed - " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ $file NOT FOUND\n";
    }
    echo "\n";
}

echo "=== Testing .htaccess ===\n";
$htaccess_path = $base_path . '/.htaccess';
if (file_exists($htaccess_path)) {
    echo "✓ .htaccess exists\n";
    echo "  Size: " . filesize($htaccess_path) . " bytes\n";
    echo "  Readable: " . (is_readable($htaccess_path) ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ .htaccess NOT FOUND\n";
}

echo "\n=== Debug Complete ===\n";
echo "If you see this message, basic PHP is working.\n";
echo "Check your server error logs for more details.\n";
?> 