<?php
// Diagnostic script for products.php issues
echo "<h1>Products.php Diagnostic Report</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

// 1. Check PHP version and extensions
echo "<h2>1. PHP Environment</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

$required_extensions = ['mysqli', 'gd', 'fileinfo'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Loaded" : "❌ Missing";
    echo "<p><strong>$ext:</strong> $status</p>";
}

// 2. Check file existence
echo "<h2>2. File Existence</h2>";
$files_to_check = [
    'includes/error_handler.php',
    'includes/db_connect.php',
    'css/style.css'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file) ? "✅ Exists" : "❌ Missing";
    echo "<p><strong>$file:</strong> $exists</p>";
}

// 3. Test database connection
echo "<h2>3. Database Connection</h2>";
try {
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'><strong>Database Connection Failed:</strong> " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'><strong>Database Connection:</strong> ✅ Successful</p>";
        
        // Check if tables exist
        $tables = ['brands', 'tires'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $result->num_rows > 0 ? "✅ Exists" : "❌ Missing";
            echo "<p><strong>Table '$table':</strong> $exists</p>";
        }
        
        // Check tire count
        $result = $conn->query("SELECT COUNT(*) as count FROM tires");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p><strong>Tires in database:</strong> " . $row['count'] . "</p>";
        }
        
        // Test the actual query from products.php
        $query = "SELECT t.*, b.name as brand, b.logo_url FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY b.name, t.name";
        $result = $conn->query($query);
        
        if ($result) {
            echo "<p style='color: green;'><strong>Products Query:</strong> ✅ Successful (" . $result->num_rows . " results)</p>";
            
            // Show first few products
            echo "<h3>Sample Products:</h3>";
            $count = 0;
            while ($row = $result->fetch_assoc() && $count < 3) {
                echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
                echo "<strong>Brand:</strong> " . htmlspecialchars($row['brand']) . "<br>";
                echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
                echo "<strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>";
                echo "<strong>Price:</strong> $" . number_format($row['price'], 2) . "<br>";
                echo "<strong>Stock:</strong> " . $row['stock_quantity'] . "<br>";
                echo "</div>";
                $count++;
            }
        } else {
            echo "<p style='color: red;'><strong>Products Query Failed:</strong> " . $conn->error . "</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 4. Check error reporting
echo "<h2>4. Error Reporting</h2>";
echo "<p><strong>display_errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p><strong>error_reporting:</strong> " . ini_get('error_reporting') . "</p>";
echo "<p><strong>log_errors:</strong> " . (ini_get('log_errors') ? 'On' : 'Off') . "</p>";

// 5. Test includes
echo "<h2>5. Include Files Test</h2>";
try {
    if (file_exists('includes/error_handler.php')) {
        require_once 'includes/error_handler.php';
        echo "<p style='color: green;'>✅ error_handler.php loaded successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ error_handler.php not found</p>";
    }
    
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
        echo "<p style='color: green;'>✅ db_connect.php loaded successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ db_connect.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Include Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 6. Check for any PHP errors
echo "<h2>6. Recent PHP Errors</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = file_get_contents($error_log);
    if (!empty($recent_errors)) {
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($recent_errors, -1000)); // Last 1000 characters
        echo "</pre>";
    } else {
        echo "<p>No recent errors found in log.</p>";
    }
} else {
    echo "<p>Error log not accessible or not configured.</p>";
}

echo "</div>";
?> 