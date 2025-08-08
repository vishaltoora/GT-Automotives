<?php
// Run Migrations Now Script
// This script will immediately run all pending database migrations

require_once 'includes/db_connect.php';

echo "<h1>Running Database Migrations</h1>";

// Include the migration system
require_once 'database/migrations.php';

// Create migration instance
$migration = new DatabaseMigration($conn);

// Run migrations
echo "<h2>Executing Migrations...</h2>";
$results = $migration->runMigrations();

if (is_array($results)) {
    echo "<h3>Migration Results:</h3>";
    $success_count = 0;
    $failed_count = 0;
    
    foreach ($results as $migration_name => $result) {
        $status = $result['success'] ? '✅ Success' : '❌ Failed';
        $color = $result['success'] ? 'green' : 'red';
        echo "<p style='color: {$color};'><strong>{$migration_name}:</strong> {$status} - {$result['description']}</p>";
        
        if ($result['success']) {
            $success_count++;
        } else {
            $failed_count++;
        }
    }
    
    echo "<h3>Summary:</h3>";
    echo "<p><strong>Successful:</strong> {$success_count}</p>";
    echo "<p><strong>Failed:</strong> {$failed_count}</p>";
    
    if ($failed_count > 0) {
        echo "<h3>⚠️ Some migrations failed. Check the errors above.</h3>";
    } else {
        echo "<h3>✅ All migrations completed successfully!</h3>";
    }
} else {
    echo "<p style='color: green;'>{$results['message']}</p>";
}

// Check current table status
echo "<h2>Current Database Status</h2>";
$result = $conn->query("SHOW TABLES");
$tables = [];
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

echo "<p><strong>Total Tables:</strong> " . count($tables) . "</p>";
echo "<h3>Available Tables:</h3>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>✅ $table</li>";
}
echo "</ul>";

// Check if critical tables exist
$critical_tables = ['tires', 'brands', 'users'];
$missing_critical = [];
foreach ($critical_tables as $table) {
    if (!in_array($table, $tables)) {
        $missing_critical[] = $table;
    }
}

if (!empty($missing_critical)) {
    echo "<h3>⚠️ Missing Critical Tables:</h3>";
    echo "<ul>";
    foreach ($missing_critical as $table) {
        echo "<li>❌ $table</li>";
    }
    echo "</ul>";
    echo "<p>These tables are essential for the application to work. Please check the migration errors above.</p>";
} else {
    echo "<h3>✅ All critical tables are present!</h3>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Test your products page: <a href='products.php' target='_blank'>products.php</a></li>";
echo "<li>Test admin panel: <a href='admin/products.php' target='_blank'>admin/products.php</a></li>";
echo "<li>Check database status: <a href='check_missing_tables.php' target='_blank'>check_missing_tables.php</a></li>";
echo "</ol>";

echo "<h2>Quick Test</h2>";
echo "<p>Try accessing these URLs to verify the fix:</p>";
echo "<ul>";
echo "<li><a href='products.php' target='_blank'>Customer Products Page</a></li>";
echo "<li><a href='admin/products.php' target='_blank'>Admin Products Management</a></li>";
echo "<li><a href='admin/index.php' target='_blank'>Admin Dashboard</a></li>";
echo "</ul>";

if (empty($missing_critical)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>🎉 Migration Complete!</h3>";
    echo "<p>Your database should now have all the required tables. The products page should work correctly.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>⚠️ Migration Issues Detected</h3>";
    echo "<p>Some critical tables are still missing. Please check the migration errors and try again.</p>";
    echo "</div>";
}
?> 