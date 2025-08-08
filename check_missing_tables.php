<?php
// Check Missing Tables Script
// This script will identify which tables are missing in production

require_once 'includes/db_connect.php';

echo "<h1>Database Table Check</h1>";

// Define all expected tables
$expected_tables = [
    'users',
    'brands', 
    'sizes',
    'tires',
    'used_tire_photos',
    'inquiries',
    'sales',
    'sale_items',
    'service_categories',
    'services',
    'locations',
    'database_migrations'
];

// Check which tables exist
$existing_tables = [];
$missing_tables = [];

$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $existing_tables[] = $row[0];
    }
}

foreach ($expected_tables as $table) {
    if (!in_array($table, $existing_tables)) {
        $missing_tables[] = $table;
    }
}

echo "<h2>Current Database Status</h2>";
echo "<p><strong>Total Expected Tables:</strong> " . count($expected_tables) . "</p>";
echo "<p><strong>Existing Tables:</strong> " . count($existing_tables) . "</p>";
echo "<p><strong>Missing Tables:</strong> " . count($missing_tables) . "</p>";

echo "<h3>Existing Tables:</h3>";
echo "<ul>";
foreach ($existing_tables as $table) {
    echo "<li>✅ $table</li>";
}
echo "</ul>";

if (!empty($missing_tables)) {
    echo "<h3>Missing Tables:</h3>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li>❌ $table</li>";
    }
    echo "</ul>";
    
    echo "<h2>Fix Options</h2>";
    echo "<p>The following tables are missing from your production database:</p>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";
    
    echo "<h3>Recommended Actions:</h3>";
    echo "<ol>";
    echo "<li><strong>Run Database Migrations:</strong> Visit <a href='database/migrations.php' target='_blank'>database/migrations.php</a> to run pending migrations</li>";
    echo "<li><strong>Manual Schema Import:</strong> Import the complete schema from <a href='database/schema.sql' target='_blank'>database/schema.sql</a></li>";
    echo "<li><strong>Check Migration Status:</strong> The missing tables suggest migrations haven't been run properly</li>";
    echo "</ol>";
    
    echo "<h3>Quick Fix Commands:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace;'>";
    echo "<p><strong>Option 1 - Run Migrations:</strong></p>";
    echo "<code>curl -X POST https://your-domain.com/database/migrations.php -d 'run_migrations=1'</code><br><br>";
    
    echo "<p><strong>Option 2 - Import Schema:</strong></p>";
    echo "<code>mysql -u username -p database_name < database/schema.sql</code><br><br>";
    
    echo "<p><strong>Option 3 - Check Migration Status:</strong></p>";
    echo "<code>Visit: https://your-domain.com/database/migrations.php</code>";
    echo "</div>";
} else {
    echo "<h3>✅ All Tables Present</h3>";
    echo "<p>All expected tables are present in your database. If you're still having issues, check:</p>";
    echo "<ul>";
    echo "<li>Table permissions</li>";
    echo "<li>Data in the tables</li>";
    echo "<li>Application configuration</li>";
    echo "</ul>";
}

// Check if database_migrations table exists and show migration status
if (in_array('database_migrations', $existing_tables)) {
    echo "<h2>Migration Status</h2>";
    $migrations_result = $conn->query("SELECT * FROM database_migrations ORDER BY executed_at");
    if ($migrations_result && $migrations_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Migration</th><th>Status</th><th>Executed At</th><th>Error</th></tr>";
        while ($row = $migrations_result->fetch_assoc()) {
            $status_color = $row['status'] === 'executed' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$row['migration_name']}</td>";
            echo "<td style='color: $status_color;'>{$row['status']}</td>";
            echo "<td>{$row['executed_at']}</td>";
            echo "<td>{$row['error_message']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No migrations have been recorded yet.</p>";
    }
} else {
    echo "<h2>Migration Status</h2>";
    echo "<p>❌ The database_migrations table is missing. This suggests migrations haven't been run.</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Visit <a href='database/migrations.php' target='_blank'>database/migrations.php</a> to run migrations</li>";
echo "<li>Check the migration results</li>";
echo "<li>Test your products page after migrations complete</li>";
echo "</ol>";

echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='products.php' target='_blank'>Test Products Page</a></li>";
echo "<li><a href='admin/products.php' target='_blank'>Test Admin Products</a></li>";
echo "<li><a href='database/migrations.php' target='_blank'>Run Database Migrations</a></li>";
echo "</ul>";
?> 