<?php
// Script to fix file permissions for deployment
echo "<h1>Fixing File Permissions</h1>";

// Create necessary directories if they don't exist
$directories = [
    'database',
    'uploads'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✅ Created directory: $dir</p>";
        } else {
            echo "<p>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>✅ Directory exists: $dir</p>";
    }
}

// Set proper permissions for database file
if (file_exists('database/gt_automotives.db')) {
    if (chmod('database/gt_automotives.db', 0644)) {
        echo "<p>✅ Set database file permissions</p>";
    } else {
        echo "<p>❌ Failed to set database file permissions</p>";
    }
} else {
    echo "<p>❌ Database file not found</p>";
}

// Set permissions for uploads directory
if (is_dir('uploads')) {
    if (chmod('uploads', 0755)) {
        echo "<p>✅ Set uploads directory permissions</p>";
    } else {
        echo "<p>❌ Failed to set uploads directory permissions</p>";
    }
}

echo "<h2>Current Permissions</h2>";
$files_to_check = [
    'database/gt_automotives.db',
    'uploads',
    'css/style.css',
    'admin/index.php',
    'products.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? "✅" : "❌";
        $writable = is_writable($file) ? "✅" : "❌";
        echo "<p>$readable $writable $file (perms: " . substr(sprintf('%o', $perms), -4) . ")</p>";
    } else {
        echo "<p>❌ ❌ $file (not found)</p>";
    }
}

echo "<h2>Next Steps</h2>";
echo "<p>1. Run this script on your deployed server</p>";
echo "<p>2. Check the debug.php page for detailed information</p>";
echo "<p>3. Ensure your web server has write permissions to the uploads directory</p>";
?> 