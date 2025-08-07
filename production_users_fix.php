<?php
// Production Users Table Fix
// This script specifically fixes the users table on production by adding missing columns

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Production Users Table Fix</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo "button { background: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 10px; }";
echo "button:hover { background: #c82333; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #218838; }";
echo "table { border-collapse: collapse; width: 100%; margin: 15px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Production Users Table Fix</h1>";
echo "<p>This script will fix the users table on production by adding the missing <strong>first_name</strong> and <strong>last_name</strong> columns.</p>";

// Step 1: Check database connection
echo "<h2>Step 1: Database Connection</h2>";
try {
    $test_result = $conn->query("SELECT 1");
    if ($test_result) {
        echo "<div class='success'>✅ Database connection successful</div>";
    } else {
        echo "<div class='error'>❌ Database connection failed</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection error: " . $e->getMessage() . "</div>";
    exit;
}

// Step 2: Check if users table exists
echo "<h2>Step 2: Check Users Table</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Users table exists</div>";
    } else {
        echo "<div class='error'>❌ Users table does not exist</div>";
        echo "<p>Creating users table...</p>";
        
        $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            first_name VARCHAR(255) NOT NULL DEFAULT '',
            last_name VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255),
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table_sql)) {
            echo "<div class='success'>✅ Users table created successfully</div>";
        } else {
            echo "<div class='error'>❌ Error creating users table: " . $conn->error . "</div>";
            exit;
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking users table: " . $e->getMessage() . "</div>";
    exit;
}

// Step 3: Check current table structure
echo "<h2>Step 3: Current Table Structure</h2>";
try {
    $columns = $conn->query("DESCRIBE users");
    $existing_columns = [];
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $columns->fetch_assoc()) {
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
    
    $required_columns = ['first_name', 'last_name'];
    $missing_columns = array_diff($required_columns, $existing_columns);
    
    if (empty($missing_columns)) {
        echo "<div class='success'>✅ Users table already has first_name and last_name columns!</div>";
        echo "<p><a href='admin/users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Users Page</a></p>";
    } else {
        echo "<div class='warning'>⚠️ Missing columns: " . implode(', ', $missing_columns) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking table structure: " . $e->getMessage() . "</div>";
    exit;
}

// Step 4: Handle form submission to fix the table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_table'])) {
    echo "<h2>Step 4: Adding Missing Columns</h2>";
    
    try {
        // Build ALTER TABLE statement for missing columns
        $alter_statements = [];
        
        if (in_array('first_name', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT ''";
        }
        if (in_array('last_name', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT ''";
        }
        
        if (!empty($alter_statements)) {
            $alter_sql = "ALTER TABLE users " . implode(", ", $alter_statements);
            
            echo "<div class='info'>Executing SQL: <code>" . htmlspecialchars($alter_sql) . "</code></div>";
            
            if ($conn->query($alter_sql)) {
                echo "<div class='success'>✅ Users table updated successfully!</div>";
                
                // Show new structure
                echo "<h3>Updated Table Structure:</h3>";
                $new_columns = $conn->query("DESCRIBE users");
                echo "<table>";
                echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                while ($row = $new_columns->fetch_assoc()) {
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
                        echo "<div class='success'>✅ Default admin user created!</div>";
                        echo "<p><strong>Username:</strong> admin</p>";
                        echo "<p><strong>Password:</strong> admin123</p>";
                    } else {
                        echo "<div class='error'>❌ Error creating admin user: " . $stmt->error . "</div>";
                    }
                }
                
                echo "<div class='success'>";
                echo "<h3>🎉 Users Table Fixed Successfully!</h3>";
                echo "<p>The users table now has the required first_name and last_name columns.</p>";
                echo "<p>You can now add new users through the admin panel.</p>";
                echo "</div>";
                
                echo "<div style='margin: 20px 0;'>";
                echo "<a href='admin/users.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>👥 Go to Users Page</a>";
                echo "<a href='admin/login.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🔐 Go to Login</a>";
                echo "</div>";
                
            } else {
                echo "<div class='error'>❌ Error updating table: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='success'>✅ No changes needed - table already has required columns</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error fixing table: " . $e->getMessage() . "</div>";
    }
} else {
    // Show the changes that will be made
    if (!empty($missing_columns)) {
        echo "<h2>Step 4: Changes to be Applied</h2>";
        echo "<div class='warning'>";
        echo "<h3>⚠️ Warning</h3>";
        echo "<p>This will modify the users table structure by adding the missing columns.</p>";
        echo "<p>Make sure you have a backup of your database before proceeding.</p>";
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
            }
        }
        
        $alter_sql = "ALTER TABLE users " . implode(", ", $alter_statements);
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        echo htmlspecialchars($alter_sql);
        echo "</pre>";
        
        echo "<form method='POST' style='margin: 20px 0;'>";
        echo "<button type='submit' name='fix_table'>⚠️ Fix Users Table - Add Missing Columns</button>";
        echo "</form>";
    } else {
        echo "<div class='success'>✅ Users table is already properly configured!</div>";
    }
}

// Step 5: Manual SQL option
echo "<h2>Step 5: Manual SQL (if needed)</h2>";
echo "<div class='info'>";
echo "<p>If the automatic fix doesn't work, you can run this SQL manually in your database:</p>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo "-- Add missing columns to users table\n";
echo "ALTER TABLE users \n";
echo "ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '',\n";
echo "ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';\n\n";
echo "-- Update existing users with default values (if any)\n";
echo "UPDATE users SET first_name = 'Admin', last_name = 'User' \n";
echo "WHERE first_name = '' OR first_name IS NULL;\n\n";
echo "-- Create admin user if none exists\n";
echo "INSERT INTO users (username, first_name, last_name, password, email, is_admin) \n";
echo "SELECT 'admin', 'Admin', 'User', '\$2y\$10\$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1 \n";
echo "WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');";
echo "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><a href='admin/users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Back to Users Page</a></p>";
echo "<p><a href='database/migrations.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Full Migration System</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 