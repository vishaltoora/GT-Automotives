<?php
// Production Migration Fix Script
// This script diagnoses and fixes common migration issues on production

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Production Migration Fix</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo "button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }";
echo "button:hover { background: #0056b3; }";
echo ".danger { background: #dc3545; }";
echo ".danger:hover { background: #c82333; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Production Migration Fix Tool</h1>";
echo "<p>This tool will diagnose and fix migration issues on your production server.</p>";

// Step 1: Check database connection
echo "<h2>Step 1: Database Connection Test</h2>";
try {
    // Test database connection by running a simple query
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

// Step 2: Check if migrations table exists
echo "<h2>Step 2: Migration System Check</h2>";
$migrations_table_exists = false;
try {
    $result = $conn->query("SHOW TABLES LIKE 'database_migrations'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Migrations table exists</div>";
        $migrations_table_exists = true;
    } else {
        echo "<div class='warning'>⚠️ Migrations table does not exist - will be created</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking migrations table: " . $e->getMessage() . "</div>";
}

// Step 3: Check current database structure
echo "<h2>Step 3: Database Structure Analysis</h2>";
$tables_to_check = ['users', 'brands', 'tires', 'sizes', 'sales', 'sale_items'];
$missing_tables = [];

foreach ($tables_to_check as $table) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='warning'>⚠️ Table '$table' is missing</div>";
            $missing_tables[] = $table;
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error checking table '$table': " . $e->getMessage() . "</div>";
    }
}

// Step 4: Check users table structure
echo "<h2>Step 4: Users Table Structure</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $columns = $conn->query("DESCRIBE users");
        $existing_columns = [];
        while ($row = $columns->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        $required_columns = ['id', 'username', 'first_name', 'last_name', 'email', 'password', 'is_admin', 'created_at', 'updated_at'];
        $missing_columns = array_diff($required_columns, $existing_columns);
        
        if (empty($missing_columns)) {
            echo "<div class='success'>✅ Users table has all required columns</div>";
        } else {
            echo "<div class='warning'>⚠️ Users table missing columns: " . implode(', ', $missing_columns) . "</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Users table does not exist</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking users table: " . $e->getMessage() . "</div>";
}

// Step 5: Provide fix options
echo "<h2>Step 5: Fix Options</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['run_full_migrations'])) {
        echo "<h3>Running Full Migration System...</h3>";
        
        // Include and run the migration system
        require_once 'database/migrations.php';
        
        $migration = new DatabaseMigration($conn);
        $results = $migration->runMigrations();
        
        if (is_array($results)) {
            echo "<h4>Migration Results:</h4>";
            foreach ($results as $migration_name => $result) {
                $status = $result['success'] ? '✅ Success' : '❌ Failed';
                $color = $result['success'] ? 'success' : 'error';
                echo "<div class='$color'><strong>$migration_name:</strong> $status - {$result['description']}</div>";
            }
        } else {
            echo "<div class='success'>{$results['message']}</div>";
        }
        
    } elseif (isset($_POST['fix_users_table'])) {
        echo "<h3>Fixing Users Table...</h3>";
        
        try {
            // Add missing columns to users table
            $alter_statements = [
                "ADD COLUMN IF NOT EXISTS first_name VARCHAR(255) NOT NULL DEFAULT ''",
                "ADD COLUMN IF NOT EXISTS last_name VARCHAR(255) NOT NULL DEFAULT ''",
                "ADD COLUMN IF NOT EXISTS email VARCHAR(255)",
                "ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0",
                "ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                "ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
            ];
            
            $alter_sql = "ALTER TABLE users " . implode(", ", $alter_statements);
            
            if ($conn->query($alter_sql)) {
                echo "<div class='success'>✅ Users table updated successfully!</div>";
                
                // Create default admin user if none exists
                $user_count = $conn->query("SELECT COUNT(*) as count FROM users");
                $count = $user_count->fetch_assoc()['count'];
                
                if ($count == 0) {
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
            } else {
                echo "<div class='error'>❌ Error updating users table: " . $conn->error . "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error fixing users table: " . $e->getMessage() . "</div>";
        }
        
    } elseif (isset($_POST['create_missing_tables'])) {
        echo "<h3>Creating Missing Tables...</h3>";
        
        $tables_sql = [
            'brands' => "CREATE TABLE IF NOT EXISTS brands (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                logo_url TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'sizes' => "CREATE TABLE IF NOT EXISTS sizes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                size VARCHAR(50) NOT NULL UNIQUE,
                width INT,
                aspect_ratio INT,
                diameter INT,
                load_index VARCHAR(10),
                speed_rating VARCHAR(5),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'tires' => "CREATE TABLE IF NOT EXISTS tires (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                brand_id INT,
                size VARCHAR(100),
                price DECIMAL(10,2) NOT NULL,
                stock_quantity INT DEFAULT 0,
                image_url TEXT,
                condition ENUM('new', 'used') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
            )"
        ];
        
        foreach ($tables_sql as $table_name => $sql) {
            try {
                if ($conn->query($sql)) {
                    echo "<div class='success'>✅ Table '$table_name' created successfully</div>";
                } else {
                    echo "<div class='error'>❌ Error creating table '$table_name': " . $conn->error . "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error creating table '$table_name': " . $e->getMessage() . "</div>";
            }
        }
    }
} else {
    echo "<div class='info'>Choose an action to fix your migration issues:</div>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='run_full_migrations'>🔄 Run Full Migration System</button>";
    echo "<button type='submit' name='fix_users_table'>👥 Fix Users Table Only</button>";
    echo "<button type='submit' name='create_missing_tables'>📋 Create Missing Tables</button>";
    echo "</form>";
}

// Step 6: Verification links
echo "<h2>Step 6: Verification</h2>";
echo "<div class='info'>After running migrations, verify your setup:</div>";
echo "<ul>";
echo "<li><a href='admin/users.php' target='_blank'>Check Users Management</a></li>";
echo "<li><a href='admin/products.php' target='_blank'>Check Products Management</a></li>";
echo "<li><a href='database/migrations.php' target='_blank'>View Migration Status</a></li>";
echo "<li><a href='admin/login.php' target='_blank'>Test Admin Login</a></li>";
echo "</ul>";

echo "<h2>Step 7: Manual Migration (if needed)</h2>";
echo "<div class='warning'>If the automatic fixes don't work, you can run migrations manually:</div>";
echo "<ol>";
echo "<li>Visit: <a href='database/migrations.php' target='_blank'>database/migrations.php</a></li>";
echo "<li>Click '🔄 Run Pending Migrations'</li>";
echo "<li>Review the results</li>";
echo "</ol>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 