<?php
// Simple script to run migrations on production
// This will fix the users table structure

require_once 'includes/db_connect.php';

echo "<h1>Running Production Migrations</h1>";
echo "<p>This script will fix the users table structure by adding missing columns.</p>";

// Test database connection
try {
    if ($conn->ping()) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
    exit;
}

// Check current users table structure
echo "<h2>Current Users Table Structure</h2>";
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

// Check what columns are missing
$required_columns = ['first_name', 'last_name', 'email', 'is_admin', 'created_at', 'updated_at'];
$missing_columns = array_diff($required_columns, $existing_columns);

if (empty($missing_columns)) {
    echo "<p style='color: green;'>✅ Users table has all required columns!</p>";
    echo "<p><a href='admin/users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Users Page</a></p>";
    exit;
}

echo "<h2>Missing Columns</h2>";
echo "<p>The following columns are missing from the users table:</p>";
echo "<ul>";
foreach ($missing_columns as $column) {
    echo "<li style='color: orange;'>⚠️ $column</li>";
}
echo "</ul>";

// Handle form submission to fix the table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_table'])) {
    echo "<h2>Fixing Users Table...</h2>";
    
    try {
        // Build ALTER TABLE statement for missing columns
        $alter_statements = [];
        
        if (in_array('first_name', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT ''";
        }
        if (in_array('last_name', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT ''";
        }
        if (in_array('email', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN email VARCHAR(255)";
        }
        if (in_array('is_admin', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
        }
        if (in_array('created_at', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        }
        if (in_array('updated_at', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        }
        
        if (!empty($alter_statements)) {
            $alter_sql = "ALTER TABLE users " . implode(", ", $alter_statements);
            
            if ($conn->query($alter_sql)) {
                echo "<p style='color: green;'>✅ Users table updated successfully!</p>";
                
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
                echo "<h3>✅ Users Table Fixed Successfully!</h3>";
                echo "<p>The users table has been updated with the correct structure.</p>";
                echo "</div>";
                
                echo "<p><a href='admin/users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Users Page</a></p>";
                echo "<p><a href='admin/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
                
            } else {
                echo "<p style='color: red;'>❌ Error updating table: " . $conn->error . "</p>";
            }
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
    $alter_statements = [];
    foreach ($missing_columns as $column) {
        switch ($column) {
            case 'first_name':
                $alter_statements[] = "ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT ''";
                break;
            case 'last_name':
                $alter_statements[] = "ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT ''";
                break;
            case 'email':
                $alter_statements[] = "ADD COLUMN email VARCHAR(255)";
                break;
            case 'is_admin':
                $alter_statements[] = "ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
                break;
            case 'created_at':
                $alter_statements[] = "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                break;
            case 'updated_at':
                $alter_statements[] = "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                break;
        }
    }
    
    $alter_sql = "ALTER TABLE users " . implode(", ", $alter_statements);
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($alter_sql);
    echo "</pre>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<button type='submit' name='fix_table' style='background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
    echo "⚠️ Fix Users Table Structure";
    echo "</button>";
    echo "</form>";
    
    echo "<p><a href='admin/users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a></p>";
}

echo "<hr>";
echo "<p><a href='database/migrations.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Full Migration System</a></p>";
echo "<p><a href='admin/debug_users.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Debug Users</a></p>";
?> 