<?php
// Immediate Fix for Users Table - Add Missing Columns
// This script will add first_name and last_name columns to the users table

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Fix Users Table - Add Missing Columns</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo "button { background: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }";
echo "button:hover { background: #c82333; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #218838; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Fix Users Table - Add Missing Columns</h1>";
echo "<p>This script will add the missing <strong>first_name</strong> and <strong>last_name</strong> columns to your users table.</p>";

// Check database connection
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

// Check if users table exists
echo "<h2>Step 2: Check Users Table</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Users table exists</div>";
    } else {
        echo "<div class='error'>❌ Users table does not exist</div>";
        echo "<p>Please run the full migration system first.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking users table: " . $e->getMessage() . "</div>";
    exit;
}

// Check current columns
echo "<h2>Step 3: Current Table Structure</h2>";
try {
    $columns = $conn->query("DESCRIBE users");
    $existing_columns = [];
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
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
        exit;
    } else {
        echo "<div class='warning'>⚠️ Missing columns: " . implode(', ', $missing_columns) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking table structure: " . $e->getMessage() . "</div>";
    exit;
}

// Handle form submission
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
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
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
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error fixing table: " . $e->getMessage() . "</div>";
    }
} else {
    // Show the changes that will be made
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
    echo "<button type='submit' name='fix_table' style='background: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>";
    echo "⚠️ Fix Users Table - Add Missing Columns";
    echo "</button>";
    echo "</form>";
    
    echo "<p><a href='admin/users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a></p>";
}

echo "<hr>";
echo "<p><a href='database/migrations.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Full Migration System</a></p>";
echo "<p><a href='fix_production_migrations.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Use Comprehensive Fix Tool</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 