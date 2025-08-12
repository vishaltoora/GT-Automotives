<?php
// Force Production Migration - Add Missing Columns to Users Table
// This script will force the migration to run on production

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Force Production Migration</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
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
echo ".sql-box { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; margin: 15px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🚨 Force Production Migration</h1>";
echo "<p>This script will force the migration to run on production and add the missing <strong>first_name</strong> and <strong>last_name</strong> columns to your users table.</p>";

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

// Step 2: Check current users table structure
echo "<h2>Step 2: Current Users Table Structure</h2>";
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
    
    echo "<div class='info'>";
    echo "<strong>Current columns:</strong> " . implode(', ', $existing_columns);
    echo "</div>";
    
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

// Step 3: Show current users data
echo "<h2>Step 3: Current Users Data</h2>";
try {
    $users = $conn->query("SELECT id, username, email, is_admin, created_at FROM users");
    if ($users && $users->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Is Admin</th><th>Created At</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . ($user['is_admin'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No users found in the database.</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error fetching users: " . $e->getMessage() . "</div>";
}

// Step 4: Handle form submission to force the migration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_migration'])) {
    echo "<h2>Step 4: Forcing Migration - Adding Missing Columns</h2>";
    
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
            
            echo "<div class='sql-box'>";
            echo "<strong>Executing SQL:</strong><br>";
            echo "<code>" . htmlspecialchars($alter_sql) . "</code>";
            echo "</div>";
            
            if ($conn->query($alter_sql)) {
                echo "<div class='success'>✅ Users table updated successfully!</div>";
                
                // Update existing users with default values
                echo "<h3>Updating Existing Users...</h3>";
                $update_sql = "UPDATE users SET first_name = 'Admin', last_name = 'User' WHERE first_name = '' OR first_name IS NULL";
                echo "<div class='sql-box'>";
                echo "<strong>Executing SQL:</strong><br>";
                echo "<code>" . htmlspecialchars($update_sql) . "</code>";
                echo "</div>";
                
                if ($conn->query($update_sql)) {
                    echo "<div class='success'>✅ Existing users updated with default values!</div>";
                } else {
                    echo "<div class='warning'>⚠️ Warning: Could not update existing users: " . $conn->error . "</div>";
                }
                
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
                
                // Show updated users data
                echo "<h3>Updated Users Data:</h3>";
                $updated_users = $conn->query("SELECT id, username, first_name, last_name, email, is_admin FROM users");
                if ($updated_users && $updated_users->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Username</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Is Admin</th></tr>";
                    while ($user = $updated_users->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . $user['username'] . "</td>";
                        echo "<td>" . $user['first_name'] . "</td>";
                        echo "<td>" . $user['last_name'] . "</td>";
                        echo "<td>" . $user['email'] . "</td>";
                        echo "<td>" . ($user['is_admin'] ? 'Yes' : 'No') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                echo "<div class='success'>";
                echo "<h3>🎉 Force Migration Completed Successfully!</h3>";
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
        echo "<div class='error'>❌ Error forcing migration: " . $e->getMessage() . "</div>";
    }
} else {
    // Show the changes that will be made
    if (!empty($missing_columns)) {
        echo "<h2>Step 4: Force Migration - Changes to be Applied</h2>";
        echo "<div class='warning'>";
        echo "<h3>⚠️ Warning</h3>";
        echo "<p>This will force the migration to run and modify the users table structure by adding the missing columns.</p>";
        echo "<p>Make sure you have a backup of your database before proceeding.</p>";
        echo "</div>";
        
        echo "<h3>SQL Statements to be Executed:</h3>";
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
        echo "<div class='sql-box'>";
        echo "<strong>1. Add missing columns:</strong><br>";
        echo "<code>" . htmlspecialchars($alter_sql) . "</code>";
        echo "</div>";
        
        echo "<div class='sql-box'>";
        echo "<strong>2. Update existing users:</strong><br>";
        echo "<code>UPDATE users SET first_name = 'Admin', last_name = 'User' WHERE first_name = '' OR first_name IS NULL;</code>";
        echo "</div>";
        
        echo "<form method='POST' style='margin: 20px 0;'>";
        echo "<button type='submit' name='force_migration'>🚨 Force Migration - Add Missing Columns</button>";
        echo "</form>";
    } else {
        echo "<div class='success'>✅ Users table is already properly configured!</div>";
    }
}

// Step 5: Manual SQL option
echo "<h2>Step 5: Manual SQL (if needed)</h2>";
echo "<div class='info'>";
echo "<p>If the automatic fix doesn't work, you can run these SQL commands manually in your database:</p>";
echo "<div class='sql-box'>";
echo "<strong>1. Add missing columns:</strong><br>";
echo "<code>ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '';</code><br>";
echo "<code>ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';</code>";
echo "</div>";
echo "<div class='sql-box'>";
echo "<strong>2. Update existing users:</strong><br>";
echo "<code>UPDATE users SET first_name = 'Admin', last_name = 'User' WHERE first_name = '' OR first_name IS NULL;</code>";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><a href='admin/users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Back to Users Page</a></p>";
echo "<p><a href='database/migrations.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Full Migration System</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 