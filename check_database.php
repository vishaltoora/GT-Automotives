<?php
// Include database connection
require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Database Diagnostic - GT Automotives</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }";
echo ".success { background: #e8f5e9; border-color: #4caf50; }";
echo ".error { background: #ffebee; border-color: #f44336; }";
echo ".warning { background: #fff3cd; border-color: #ffc107; }";
echo ".info { background: #e3f2fd; border-color: #2196f3; }";
echo "table { width: 100%; border-collapse: collapse; margin-top: 10px; }";
echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #f8f9fa; font-weight: bold; }";
echo ".btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Database Diagnostic Tool</h1>";
echo "<p>This tool will help identify why products aren't showing in production.</p>";

// Test 1: Database Connection
echo "<div class='section info'>";
echo "<h2>1. Database Connection Test</h2>";
try {
    if ($conn->ping()) {
        echo "<p class='success'>✅ Database connection successful</p>";
        echo "<p><strong>Host:</strong> " . $conn->host_info . "</p>";
        echo "<p><strong>Server Version:</strong> " . $conn->server_info . "</p>";
    } else {
        echo "<p class='error'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 2: Check if tables exist
echo "<div class='section info'>";
echo "<h2>2. Database Tables Check</h2>";
$tables_query = "SHOW TABLES";
$tables_result = $conn->query($tables_query);

if ($tables_result) {
    echo "<p class='success'>✅ Database tables found:</p>";
    echo "<table>";
    echo "<tr><th>Table Name</th></tr>";
    while ($row = $tables_result->fetch_array()) {
        echo "<tr><td>" . $row[0] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Error checking tables: " . $conn->error . "</p>";
}
echo "</div>";

// Test 3: Check tires table structure
echo "<div class='section info'>";
echo "<h2>3. Tires Table Structure</h2>";
$structure_query = "DESCRIBE tires";
$structure_result = $conn->query($structure_query);

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Error checking tires table structure: " . $conn->error . "</p>";
}
echo "</div>";

// Test 4: Check brands table structure
echo "<div class='section info'>";
echo "<h2>4. Brands Table Structure</h2>";
$brands_structure_query = "DESCRIBE brands";
$brands_structure_result = $conn->query($brands_structure_query);

if ($brands_structure_result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $brands_structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Error checking brands table structure: " . $conn->error . "</p>";
}
echo "</div>";

// Test 5: Count records in tables
echo "<div class='section info'>";
echo "<h2>5. Record Counts</h2>";
$count_queries = [
    "tires" => "SELECT COUNT(*) as count FROM tires",
    "brands" => "SELECT COUNT(*) as count FROM brands",
    "users" => "SELECT COUNT(*) as count FROM users"
];

foreach ($count_queries as $table => $query) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p><strong>$table table:</strong> " . $row['count'] . " records</p>";
    } else {
        echo "<p class='error'>❌ Error counting $table: " . $conn->error . "</p>";
    }
}
echo "</div>";

// Test 6: Sample data from tires table
echo "<div class='section info'>";
echo "<h2>6. Sample Data from Tires Table</h2>";
$sample_query = "SELECT t.*, b.name as brand_name FROM tires t LEFT JOIN brands b ON t.brand_id = b.id LIMIT 5";
$sample_result = $conn->query($sample_query);

if ($sample_result && $sample_result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Brand</th><th>Size</th><th>Price</th><th>Stock</th></tr>";
    while ($row = $sample_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['brand_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['size']) . "</td>";
        echo "<td>$" . number_format($row['price'], 2) . "</td>";
        echo "<td>" . $row['stock_quantity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No data found in tires table or error occurred</p>";
    if ($sample_result) {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}
echo "</div>";

// Test 7: Check for common issues
echo "<div class='section info'>";
echo "<h2>7. Common Issues Check</h2>";

// Check if tires table exists
$tires_exists = $conn->query("SHOW TABLES LIKE 'tires'");
if ($tires_exists && $tires_exists->num_rows > 0) {
    echo "<p class='success'>✅ Tires table exists</p>";
} else {
    echo "<p class='error'>❌ Tires table does not exist</p>";
}

// Check if brands table exists
$brands_exists = $conn->query("SHOW TABLES LIKE 'brands'");
if ($brands_exists && $brands_exists->num_rows > 0) {
    echo "<p class='success'>✅ Brands table exists</p>";
} else {
    echo "<p class='error'>❌ Brands table does not exist</p>";
}

// Check for foreign key relationships
$fk_query = "SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tires'";
$fk_result = $conn->query($fk_query);

if ($fk_result && $fk_result->num_rows > 0) {
    echo "<p class='success'>✅ Foreign key relationships found</p>";
    while ($row = $fk_result->fetch_assoc()) {
        echo "<p>Foreign Key: " . $row['COLUMN_NAME'] . " → " . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "</p>";
    }
} else {
    echo "<p class='warning'>⚠️ No foreign key relationships found</p>";
}
echo "</div>";

// Test 8: Database permissions
echo "<div class='section info'>";
echo "<h2>8. Database Permissions Check</h2>";
$permissions_query = "SHOW GRANTS FOR CURRENT_USER()";
$permissions_result = $conn->query($permissions_query);

if ($permissions_result) {
    echo "<p class='success'>✅ Current user permissions:</p>";
    while ($row = $permissions_result->fetch_array()) {
        echo "<p>" . htmlspecialchars($row[0]) . "</p>";
    }
} else {
    echo "<p class='error'>❌ Error checking permissions: " . $conn->error . "</p>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🔧 Quick Fixes</h2>";
echo "<p>If you're having issues, try these solutions:</p>";
echo "<ul>";
echo "<li><a href='?action=create_tables' class='btn'>Create Missing Tables</a></li>";
echo "<li><a href='?action=insert_sample_data' class='btn'>Insert Sample Data</a></li>";
echo "<li><a href='products.php?debug=1' class='btn'>Debug Products Page</a></li>";
echo "<li><a href='admin/index.php' class='btn'>Go to Admin Panel</a></li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?> 