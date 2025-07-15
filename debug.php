<?php
// Debug script to identify deployment issues
echo "<h1>GT Automotives Debug Information</h1>";

// Check PHP version
echo "<h2>PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check required extensions
echo "<h2>Required Extensions</h2>";
$required_extensions = ['sqlite3', 'gd', 'fileinfo', 'zip'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Loaded" : "❌ Not Loaded";
    echo "<p><strong>$ext:</strong> $status</p>";
}

// Check file permissions
echo "<h2>File Permissions</h2>";
$paths_to_check = [
    'database' => 'Database directory',
    'database/gt_automotives.db' => 'Database file',
    'uploads' => 'Uploads directory',
    'css' => 'CSS directory',
    'admin' => 'Admin directory'
];

foreach ($paths_to_check as $path => $description) {
    if (file_exists($path)) {
        $writable = is_writable($path) ? "✅ Writable" : "❌ Not Writable";
        $readable = is_readable($path) ? "✅ Readable" : "❌ Not Readable";
        echo "<p><strong>$description ($path):</strong> $readable, $writable</p>";
    } else {
        echo "<p><strong>$description ($path):</strong> ❌ Does not exist</p>";
    }
}

// Test database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'includes/db_connect.php';
    echo "<p>✅ Database connection successful</p>";
    
    // Test a simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'");
    if ($result) {
        $row = $result->fetchArray();
        echo "<p>✅ Database has " . $row['count'] . " tables</p>";
    } else {
        echo "<p>❌ Database query failed</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check Composer autoloader
echo "<h2>Composer Autoloader</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p>✅ Composer autoloader exists</p>";
    try {
        require_once 'vendor/autoload.php';
        echo "<p>✅ Composer autoloader loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p>❌ Composer autoloader failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>❌ Composer autoloader not found</p>";
}

// Check for any PHP errors
echo "<h2>PHP Error Log</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = file_get_contents($error_log);
    if ($recent_errors) {
        echo "<pre>" . htmlspecialchars(substr($recent_errors, -1000)) . "</pre>";
    } else {
        echo "<p>No recent errors in log</p>";
    }
} else {
    echo "<p>Error log not found or not configured</p>";
}

// Test image compression functionality
echo "<h2>Image Compression Test</h2>";
if (class_exists('GTAutomotives\Utils\ImageCompressor')) {
    echo "<p>✅ ImageCompressor class exists</p>";
} else {
    echo "<p>❌ ImageCompressor class not found</p>";
}

// Check if uploads directory exists and is writable
if (!is_dir('uploads')) {
    echo "<p>Creating uploads directory...</p>";
    if (mkdir('uploads', 0755, true)) {
        echo "<p>✅ Uploads directory created successfully</p>";
    } else {
        echo "<p>❌ Failed to create uploads directory</p>";
    }
} else {
    echo "<p>✅ Uploads directory exists</p>";
}

echo "<h2>Environment Variables</h2>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>Current Working Directory:</strong> " . getcwd() . "</p>";
?> 