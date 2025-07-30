<?php
// Debug database data to identify why brand names are empty
echo "<h1>Database Data Debug</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

try {
    $host = 'localhost';
    $dbname = 'gt_automotives';
    $username = 'gtadmin';
    $password = 'Vishal@1234#';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
        exit;
    }
    
    echo "<h2>1. Check Brands Table</h2>";
    $result = $conn->query("SELECT * FROM brands");
    if ($result) {
        echo "<p>Found " . $result->num_rows . " brands:</p>";
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ID:</strong> " . $row['id'] . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>Logo URL:</strong> " . htmlspecialchars($row['logo_url']) . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>Error querying brands: " . $conn->error . "</p>";
    }
    
    echo "<h2>2. Check Tires Table</h2>";
    $result = $conn->query("SELECT * FROM tires");
    if ($result) {
        echo "<p>Found " . $result->num_rows . " tires:</p>";
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ID:</strong> " . $row['id'] . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>Brand ID:</strong> " . $row['brand_id'] . "<br>";
            echo "<strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>";
            echo "<strong>Price:</strong> $" . number_format($row['price'], 2) . "<br>";
            echo "<strong>Stock:</strong> " . $row['stock_quantity'] . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>Error querying tires: " . $conn->error . "</p>";
    }
    
    echo "<h2>3. Test the JOIN Query</h2>";
    $query = "SELECT t.*, b.name as brand, b.logo_url FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY b.name, t.name";
    $result = $conn->query($query);
    
    if ($result) {
        echo "<p>JOIN query found " . $result->num_rows . " results:</p>";
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ID:</strong> " . $row['id'] . "<br>";
            echo "<strong>Brand:</strong> " . htmlspecialchars($row['brand']) . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>";
            echo "<strong>Price:</strong> $" . number_format($row['price'], 2) . "<br>";
            echo "<strong>Stock:</strong> " . $row['stock_quantity'] . "<br>";
            echo "<strong>Brand ID:</strong> " . $row['brand_id'] . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>Error with JOIN query: " . $conn->error . "</p>";
    }
    
    echo "<h2>4. Check for NULL brand_id values</h2>";
    $result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE brand_id IS NULL");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Tires with NULL brand_id: " . $row['count'] . "</p>";
    }
    
    echo "<h2>5. Check for invalid brand_id references</h2>";
    $result = $conn->query("SELECT t.id, t.name, t.brand_id FROM tires t LEFT JOIN brands b ON t.brand_id = b.id WHERE b.id IS NULL AND t.brand_id IS NOT NULL");
    if ($result) {
        echo "<p>Tires with invalid brand_id references: " . $result->num_rows . "</p>";
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ff0000; padding: 10px; margin: 10px 0; background: #ffe6e6;'>";
            echo "<strong>ID:</strong> " . $row['id'] . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>Brand ID:</strong> " . $row['brand_id'] . " (INVALID)<br>";
            echo "</div>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?> 