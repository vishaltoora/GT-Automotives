<?php
/**
 * Migration Script: SQLite to MySQL
 * Run this script on your production server to migrate from SQLite to MySQL
 */

echo "<h1>GT Automotives - SQLite to MySQL Migration</h1>";

// Step 1: Check if we're on the production server
$is_production = (strpos($_SERVER['DOCUMENT_ROOT'], '/opt/bitnami') !== false);
echo "<h2>Step 1: Environment Check</h2>";
echo "<p><strong>Server:</strong> " . ($is_production ? "Production (Bitnami)" : "Development") . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Step 2: Check current database connection
echo "<h2>Step 2: Current Database Status</h2>";
$sqlite_db_path = __DIR__ . '/database/gt_automotives.db';
if (file_exists($sqlite_db_path)) {
    echo "<p>✅ SQLite database found at: " . $sqlite_db_path . "</p>";
    echo "<p><strong>Size:</strong> " . number_format(filesize($sqlite_db_path)) . " bytes</p>";
} else {
    echo "<p>❌ SQLite database not found</p>";
}

// Step 3: Check MySQL availability
echo "<h2>Step 3: MySQL Availability</h2>";
if (extension_loaded('mysqli')) {
    echo "<p>✅ MySQLi extension is available</p>";
} else {
    echo "<p>❌ MySQLi extension not available</p>";
    echo "<p>Please install MySQL/MariaDB and the mysqli extension</p>";
}

// Step 4: Test MySQL connection
echo "<h2>Step 4: MySQL Connection Test</h2>";
$mysql_config = [
    'host' => 'localhost',
    'dbname' => 'gt_automotives',
    'username' => 'gtadmin',
    'password' => 'Vishal@1234#' // Change this to your actual password
];

try {
    $test_conn = new mysqli($mysql_config['host'], $mysql_config['username'], $mysql_config['password']);
    if ($test_conn->connect_error) {
        throw new Exception("Connection failed: " . $test_conn->connect_error);
    }
    echo "<p>✅ MySQL connection successful</p>";
    
    // Check if database exists
    $result = $test_conn->query("SHOW DATABASES LIKE 'gt_automotives'");
    if ($result->num_rows > 0) {
        echo "<p>✅ Database 'gt_automotives' exists</p>";
    } else {
        echo "<p>⚠️ Database 'gt_automotives' does not exist</p>";
        echo "<p>You need to create the database first:</p>";
        echo "<pre>mysql -u root -p -e \"CREATE DATABASE gt_automotives;\"</pre>";
    }
    
    $test_conn->close();
} catch (Exception $e) {
    echo "<p>❌ MySQL connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL credentials and ensure MySQL is running</p>";
}

// Step 5: Migration Actions
echo "<h2>Step 5: Migration Actions</h2>";

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'backup_sqlite':
            echo "<h3>Backing up SQLite database...</h3>";
            if (file_exists($sqlite_db_path)) {
                $backup_path = $sqlite_db_path . '.backup.' . date('Y-m-d-H-i-s');
                if (copy($sqlite_db_path, $backup_path)) {
                    echo "<p>✅ SQLite database backed up to: " . $backup_path . "</p>";
                } else {
                    echo "<p>❌ Failed to backup SQLite database</p>";
                }
            } else {
                echo "<p>⚠️ No SQLite database to backup</p>";
            }
            break;
            
        case 'switch_to_mysql':
            echo "<h3>Switching to MySQL connection...</h3>";
            $sqlite_backup = __DIR__ . '/includes/db_connect.php.sqlite.backup';
            $mysql_file = __DIR__ . '/includes/db_connect_mysql.php';
            $target_file = __DIR__ . '/includes/db_connect.php';
            
            // Backup current SQLite connection file
            if (copy($target_file, $sqlite_backup)) {
                echo "<p>✅ Current connection file backed up</p>";
            }
            
            // Copy MySQL connection file
            if (copy($mysql_file, $target_file)) {
                echo "<p>✅ Switched to MySQL connection</p>";
            } else {
                echo "<p>❌ Failed to switch to MySQL connection</p>";
            }
            break;
            
        case 'import_schema':
            echo "<h3>Importing MySQL schema...</h3>";
            $schema_file = __DIR__ . '/database/schema_mysql.sql';
            if (file_exists($schema_file)) {
                echo "<p>✅ Schema file found</p>";
                echo "<p>Please run this command on your server:</p>";
                echo "<pre>mysql -u gtadmin -p gt_automotives < " . $schema_file . "</pre>";
            } else {
                echo "<p>❌ Schema file not found</p>";
            }
            break;
    }
}

// Step 6: Manual Instructions
echo "<h2>Step 6: Manual Migration Steps</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "<h3>For Production Server:</h3>";
echo "<ol>";
echo "<li><strong>Install MySQL/MariaDB:</strong><br>";
echo "<pre>sudo apt-get update<br>sudo apt-get install mysql-server</pre></li>";

echo "<li><strong>Create Database and User:</strong><br>";
echo "<pre>sudo mysql -u root -p<br>";
echo "CREATE DATABASE gt_automotives;<br>";
echo "CREATE USER 'gtadmin'@'localhost' IDENTIFIED BY 'your_secure_password';<br>";
echo "GRANT ALL PRIVILEGES ON gt_automotives.* TO 'gtadmin'@'localhost';<br>";
echo "FLUSH PRIVILEGES;<br>";
echo "EXIT;</pre></li>";

echo "<li><strong>Import Schema:</strong><br>";
echo "<pre>mysql -u gtadmin -p gt_automotives < /opt/bitnami/apache/htdocs/database/schema_mysql.sql</pre></li>";

echo "<li><strong>Switch Connection:</strong><br>";
echo "<pre>cd /opt/bitnami/apache/htdocs<br>";
echo "cp includes/db_connect.php includes/db_connect.php.sqlite.backup<br>";
echo "cp includes/db_connect_mysql.php includes/db_connect.php</pre></li>";

echo "<li><strong>Update Password:</strong><br>";
echo "Edit <code>includes/db_connect.php</code> and update the password to match your MySQL user password.</li>";

echo "<li><strong>Test the Application:</strong><br>";
echo "Visit your website and check if the database connection works.</li>";
echo "</ol>";
echo "</div>";

// Step 7: Quick Actions
echo "<h2>Step 7: Quick Actions</h2>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='backup_sqlite' style='margin: 5px; padding: 10px; background: #007cba; color: white; border: none; border-radius: 3px;'>Backup SQLite Database</button>";
echo "<button type='submit' name='action' value='switch_to_mysql' style='margin: 5px; padding: 10px; background: #28a745; color: white; border: none; border-radius: 3px;'>Switch to MySQL Connection</button>";
echo "<button type='submit' name='action' value='import_schema' style='margin: 5px; padding: 10px; background: #ffc107; color: black; border: none; border-radius: 3px;'>Show Schema Import Command</button>";
echo "</form>";

echo "<h2>Step 8: Verification</h2>";
echo "<p><a href='test.php'>Test Basic Functionality</a></p>";
echo "<p><a href='debug.php'>Detailed Debug Information</a></p>";
echo "<p><a href='products.php'>Test Products Page</a></p>";
echo "<p><a href='admin/login.php'>Test Admin Login</a></p>";

echo "<hr>";
echo "<p><strong>Note:</strong> After migration, make sure to update the MySQL password in <code>includes/db_connect.php</code> to match your actual MySQL user password.</p>";
?> 