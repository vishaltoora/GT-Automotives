<?php
// Admin Products Debug Script
echo "<h1>Admin Products Debug</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

// 1. Check if admin session is working
echo "<h2>1. Admin Session Check</h2>";
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    echo "<p style='color: green;'>✅ Admin session active - User ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No admin session found</p>";
    echo "<p><a href='login.php'>Login to Admin</a></p>";
}

// 2. Check file existence
echo "<h2>2. File Existence Check</h2>";
$files_to_check = [
    'admin/products.php',
    'admin/includes/header.php',
    'admin/includes/db_connect.php',
    'includes/db_connect.php',
    'css/style.css'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file) ? "✅ Exists" : "❌ Missing";
    echo "<p><strong>$file:</strong> $exists</p>";
}

// 3. Test database connection
echo "<h2>3. Database Connection Test</h2>";
try {
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Database connected successfully</p>";
        
        // Check admin tables
        $tables = ['users', 'brands', 'tires'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $result->num_rows > 0 ? "✅ Exists" : "❌ Missing";
            echo "<p><strong>Table '$table':</strong> $exists</p>";
        }
        
        // Check admin user
        $result = $conn->query("SELECT id, username, is_admin FROM users WHERE is_admin = 1");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Admin users found: " . $result->num_rows . "</p>";
            while ($row = $result->fetch_assoc()) {
                echo "<p>Admin User: " . htmlspecialchars($row['username']) . " (ID: " . $row['id'] . ")</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No admin users found</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 4. Test admin products query
echo "<h2>4. Admin Products Query Test</h2>";
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $query = "SELECT t.*, b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY t.id DESC";
        $result = $conn->query($query);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Admin products query successful</p>";
            echo "<p>Found " . $result->num_rows . " products</p>";
            
            if ($result->num_rows > 0) {
                echo "<h3>Sample Products for Admin:</h3>";
                $count = 0;
                while ($row = $result->fetch_assoc() && $count < 3) {
                    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
                    echo "<strong>ID:</strong> " . $row['id'] . "<br>";
                    echo "<strong>Brand:</strong> " . htmlspecialchars($row['brand'] ?? 'No Brand') . "<br>";
                    echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
                    echo "<strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>";
                    echo "<strong>Price:</strong> $" . number_format($row['price'], 2) . "<br>";
                    echo "<strong>Stock:</strong> " . $row['stock_quantity'] . "<br>";
                    echo "</div>";
                    $count++;
                }
            } else {
                echo "<p style='color: orange;'>⚠️ No products found in database</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Admin products query failed: " . $conn->error . "</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. Check admin permissions
echo "<h2>5. Admin Permissions Check</h2>";
$admin_dir = 'admin';
if (is_dir($admin_dir)) {
    echo "<p>✅ Admin directory exists</p>";
    if (is_readable($admin_dir)) {
        echo "<p>✅ Admin directory is readable</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin directory is not readable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin directory does not exist</p>";
}

// 6. Test admin includes
echo "<h2>6. Admin Include Files Test</h2>";
try {
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
        echo "<p style='color: green;'>✅ db_connect.php loaded successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ db_connect.php not found</p>";
    }
    
    if (file_exists('includes/error_handler.php')) {
        require_once 'includes/error_handler.php';
        echo "<p style='color: green;'>✅ error_handler.php loaded successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ error_handler.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Include error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 7. Check for any PHP errors
echo "<h2>7. PHP Error Check</h2>";
echo "<p><strong>display_errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p><strong>error_reporting:</strong> " . ini_get('error_reporting') . "</p>";

$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = file_get_contents($error_log);
    if (!empty($recent_errors)) {
        echo "<h3>Recent PHP Errors:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($recent_errors, -1000));
        echo "</pre>";
    } else {
        echo "<p>No recent errors found in log.</p>";
    }
} else {
    echo "<p>Error log not accessible or not configured.</p>";
}

echo "</div>";
?> 