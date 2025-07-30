<?php
// Simplified products.php for debugging
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Products</title></head><body>";
echo "<h1>Products.php Test</h1>";

// Step 1: Basic PHP test
echo "<h2>Step 1: Basic PHP</h2>";
echo "<p>✅ PHP is working</p>";

// Step 2: Test includes
echo "<h2>Step 2: Testing Includes</h2>";
try {
    if (file_exists('includes/error_handler.php')) {
        require_once 'includes/error_handler.php';
        echo "<p>✅ error_handler.php loaded</p>";
    } else {
        echo "<p>❌ error_handler.php not found</p>";
    }
    
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
        echo "<p>✅ db_connect.php loaded</p>";
    } else {
        echo "<p>❌ db_connect.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Include error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: Test database connection
echo "<h2>Step 3: Database Connection</h2>";
try {
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<p>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p>✅ Database connected successfully</p>";
        
        // Test basic query
        $result = $conn->query("SELECT COUNT(*) as count FROM tires");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>✅ Found " . $row['count'] . " tires in database</p>";
        } else {
            echo "<p>❌ Query failed: " . $conn->error . "</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 4: Test the actual products query
echo "<h2>Step 4: Products Query Test</h2>";
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $query = "SELECT t.*, b.name as brand, b.logo_url FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY b.name, t.name LIMIT 5";
        $result = $conn->query($query);
        
        if ($result) {
            echo "<p>✅ Products query successful</p>";
            echo "<p>Found " . $result->num_rows . " products</p>";
            
            if ($result->num_rows > 0) {
                echo "<h3>Sample Products:</h3>";
                while ($row = $result->fetch_assoc()) {
                    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                    echo "<strong>Brand:</strong> " . htmlspecialchars($row['brand']) . "<br>";
                    echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
                    echo "<strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>";
                    echo "<strong>Price:</strong> $" . number_format($row['price'], 2) . "<br>";
                    echo "</div>";
                }
            } else {
                echo "<p>❌ No products found in database</p>";
            }
        } else {
            echo "<p>❌ Products query failed: " . $conn->error . "</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 5: Test CSS file
echo "<h2>Step 5: CSS File Test</h2>";
if (file_exists('css/style.css')) {
    echo "<p>✅ CSS file exists</p>";
    $css_size = filesize('css/style.css');
    echo "<p>CSS file size: " . $css_size . " bytes</p>";
} else {
    echo "<p>❌ CSS file not found</p>";
}

echo "</body></html>";
?> 