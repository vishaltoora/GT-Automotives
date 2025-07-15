<?php
// Simple test script for basic functionality
echo "<h1>GT Automotives Basic Functionality Test</h1>";

// Test 1: Basic PHP functionality
echo "<h2>Test 1: Basic PHP</h2>";
echo "<p>✅ PHP is working</p>";

// Test 2: Database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    require_once 'includes/db_connect.php';
    echo "<p>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Check if products page loads
echo "<h2>Test 3: Products Page</h2>";
if (file_exists('products.php')) {
    echo "<p>✅ products.php exists</p>";
} else {
    echo "<p>❌ products.php not found</p>";
}

// Test 4: Check if admin page loads
echo "<h2>Test 4: Admin Page</h2>";
if (file_exists('admin/index.php')) {
    echo "<p>✅ admin/index.php exists</p>";
} else {
    echo "<p>❌ admin/index.php not found</p>";
}

// Test 5: Check CSS file
echo "<h2>Test 5: CSS File</h2>";
if (file_exists('css/style.css')) {
    echo "<p>✅ css/style.css exists</p>";
} else {
    echo "<p>❌ css/style.css not found</p>";
}

// Test 6: Check if we can read from database
echo "<h2>Test 6: Database Read Test</h2>";
try {
    $result = $conn->query("SELECT name FROM sqlite_master WHERE type='table'");
    if ($result) {
        echo "<p>✅ Database tables found:</p><ul>";
        while ($row = $result->fetchArray()) {
            echo "<li>" . htmlspecialchars($row['name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ Could not query database</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Quick Links</h2>";
echo "<p><a href='index.php'>Home Page</a></p>";
echo "<p><a href='products.php'>Products Page</a></p>";
echo "<p><a href='admin/login.php'>Admin Login</a></p>";
echo "<p><a href='debug.php'>Detailed Debug Info</a></p>";
?> 