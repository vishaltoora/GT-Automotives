<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Test</h1>";

try {
    // Test database connection
    $base_path = dirname(__DIR__);
    require_once $base_path . '/includes/db_connect.php';
    
    if (isset($conn)) {
        echo "✅ Database connected successfully!<br>";
        echo "Server info: " . $conn->server_info . "<br>";
        echo "Host info: " . $conn->host_info . "<br>";
        
        // Test basic queries
        echo "<h2>Basic Queries Test</h2>";
        
        // Test SHOW TABLES
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "✅ SHOW TABLES successful<br>";
            echo "Tables found: " . $result->num_rows . "<br>";
            
            if ($result->num_rows > 0) {
                echo "Table names: ";
                while ($row = $result->fetch_array()) {
                    echo "<strong>" . $row[0] . "</strong>, ";
                }
                echo "<br>";
            }
        } else {
            echo "❌ SHOW TABLES failed: " . $conn->error . "<br>";
        }
        
        // Test specific tables that should exist
        echo "<h2>Required Tables Test</h2>";
        $required_tables = ['users', 'tires', 'brands', 'locations', 'services', 'service_categories', 'sales', 'sale_items'];
        
        foreach ($required_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table '$table' exists<br>";
                
                // Count records
                $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                if ($count_result) {
                    $count = $count_result->fetch_assoc()['count'];
                    echo "   - Records: $count<br>";
                }
            } else {
                echo "❌ Table '$table' missing<br>";
            }
        }
        
        // Test users table specifically
        echo "<h2>Users Table Test</h2>";
        $result = $conn->query("SELECT id, username, is_admin FROM users LIMIT 5");
        if ($result) {
            echo "✅ Users query successful<br>";
            echo "Users found: " . $result->num_rows . "<br>";
            
            if ($result->num_rows > 0) {
                echo "Sample users:<br>";
                while ($row = $result->fetch_assoc()) {
                    echo "   - ID: {$row['id']}, Username: {$row['username']}, Admin: " . ($row['is_admin'] ? 'Yes' : 'No') . "<br>";
                }
            }
        } else {
            echo "❌ Users query failed: " . $conn->error . "<br>";
        }
        
        // Test tires table
        echo "<h2>Tires Table Test</h2>";
        $result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE stock_quantity > 0");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "✅ Tires with stock: $count<br>";
        } else {
            echo "❌ Tires query failed: " . $conn->error . "<br>";
        }
        
        // Test services table
        echo "<h2>Services Table Test</h2>";
        $result = $conn->query("SELECT COUNT(*) as count FROM services WHERE is_active = 1");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "✅ Active services: $count<br>";
        } else {
            echo "❌ Services query failed: " . $conn->error . "<br>";
        }
        
    } else {
        echo "❌ Database connection failed - \$conn not set<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<a href='create_sale.php'>Go to Create Sale</a><br>";
echo "<a href='create_sale.php?debug=1'>Go to Create Sale with Debug</a><br>";
echo "<a href='login.php'>Go to Login</a><br>";
echo "<a href='index.php'>Go to Admin Dashboard</a><br>";
?> 