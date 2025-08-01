<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';

echo "<h1>Fix Users Table Structure</h1>";

// Check current table structure
echo "<h2>Current Table Structure</h2>";
try {
    $structure = $conn->query("DESCRIBE users");
    if ($structure) {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        $existing_columns = [];
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
            $existing_columns[] = $row['Field'];
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking table structure: " . $e->getMessage() . "</p>";
    exit;
}

// Define expected columns
$expected_columns = [
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'username' => 'VARCHAR(255) UNIQUE NOT NULL',
    'first_name' => 'VARCHAR(255) NOT NULL',
    'last_name' => 'VARCHAR(255) NOT NULL',
    'email' => 'VARCHAR(255)',
    'password' => 'VARCHAR(255) NOT NULL',
    'is_admin' => 'TINYINT(1) DEFAULT 0',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

echo "<h2>Required Changes</h2>";
$changes_needed = [];

foreach ($expected_columns as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        $changes_needed[] = "ADD COLUMN `$column` $definition";
        echo "<p style='color: orange;'>⚠️ Missing column: <strong>$column</strong></p>";
    }
}

if (empty($changes_needed)) {
    echo "<p style='color: green;'>✅ Table structure is correct!</p>";
    echo "<p><a href='users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Users Page</a></p>";
    exit;
}

// Handle form submission to fix the table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_table'])) {
    echo "<h2>Fixing Table Structure...</h2>";
    
    try {
        // Build ALTER TABLE statement
        $alter_sql = "ALTER TABLE users " . implode(", ", $changes_needed);
        
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Table structure updated successfully!</p>";
            
            // Show new structure
            echo "<h3>Updated Table Structure:</h3>";
            $new_structure = $conn->query("DESCRIBE users");
            if ($new_structure) {
                echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                while ($row = $new_structure->fetch_assoc()) {
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
            }
            
            // Check if we need to create a default admin user
            $user_count = $conn->query("SELECT COUNT(*) as count FROM users");
            $count = $user_count->fetch_assoc()['count'];
            
            if ($count == 0) {
                echo "<h3>Creating Default Admin User...</h3>";
                $default_password = password_hash('admin123', PASSWORD_DEFAULT);
                $insert_admin = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES ('admin', 'Admin', 'User', ?, 'admin@gtautomotives.com', 1)";
                $stmt = $conn->prepare($insert_admin);
                $stmt->bind_param("s", $default_password);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>✅ Default admin user created!</p>";
                    echo "<p><strong>Username:</strong> admin</p>";
                    echo "<p><strong>Password:</strong> admin123</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error creating admin user: " . $stmt->error . "</p>";
                }
            }
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>✅ Table Fixed Successfully!</h3>";
            echo "<p>The users table has been updated with the correct structure.</p>";
            echo "</div>";
            
            echo "<p><a href='users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Users Page</a></p>";
            echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
            
        } else {
            echo "<p style='color: red;'>❌ Error updating table: " . $conn->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error fixing table: " . $e->getMessage() . "</p>";
    }
} else {
    // Show the changes that will be made
    echo "<h2>Changes to be Applied</h2>";
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Warning</h3>";
    echo "<p>This will modify the users table structure. Make sure you have a backup of your database.</p>";
    echo "</div>";
    
    echo "<h3>SQL Statement:</h3>";
    $alter_sql = "ALTER TABLE users " . implode(", ", $changes_needed);
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($alter_sql);
    echo "</pre>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<button type='submit' name='fix_table' style='background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
    echo "⚠️ Fix Table Structure";
    echo "</button>";
    echo "</form>";
    
    echo "<p><a href='users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a></p>";
}

echo "<hr>";
echo "<p><a href='debug_users.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Debug Script</a></p>";
?> 