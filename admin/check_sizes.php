<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
if (file_exists('../includes/db_connect.php')) {
    require_once '../includes/db_connect.php';
}

if (file_exists('../includes/auth.php')) {
    require_once '../includes/auth.php';
}

// Require login
requireLogin();

echo "<h1>Database Size Check</h1>";

if (isset($conn)) {
    // Check total count
    $count_query = "SELECT COUNT(*) as total FROM sizes";
    $count_result = $conn->query($count_query);
    $total_count = $count_result->fetch_assoc()['total'];
    
    echo "<h2>Total Sizes in Database: $total_count</h2>";
    
    // Show first 20 sizes
    echo "<h3>First 20 Sizes:</h3>";
    $sizes_query = "SELECT * FROM sizes ORDER BY name ASC LIMIT 20";
    $sizes_result = $conn->query($sizes_query);
    
    if ($sizes_result && $sizes_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Active</th></tr>";
        
        while ($row = $sizes_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name'] ?? $row['size'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
            echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No sizes found in database.</p>";
    }
    
    // Check database structure
    echo "<h3>Database Structure:</h3>";
    $structure_query = "DESCRIBE sizes";
    $structure_result = $conn->query($structure_query);
    
    if ($structure_result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        while ($row = $structure_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "<p>Database connection failed.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

h1, h2, h3 {
    color: #333;
}

table {
    margin: 20px 0;
    background: white;
}

th {
    background-color: #f0f0f0;
    padding: 8px;
}

td {
    padding: 8px;
    border: 1px solid #ddd;
}
</style> 