<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login and admin access
requireLogin();
requireAdmin();

echo "<h1>Database Debug Information</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    if ($conn->ping()) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

// Check if users table exists
echo "<h2>Users Table Check</h2>";
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check && $table_check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
        
        // Check table structure
        $structure = $conn->query("DESCRIBE users");
        if ($structure) {
            echo "<h3>Table Structure:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Users table does not exist</p>";
        
        // Create users table if it doesn't exist
        echo "<h3>Creating Users Table...</h3>";
        $create_table = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table)) {
            echo "<p style='color: green;'>✅ Users table created successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating users table: " . $conn->error . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking users table: " . $e->getMessage() . "</p>";
}

// Check for existing users
echo "<h2>Existing Users Check</h2>";
try {
    $users_count = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($users_count) {
        $count = $users_count->fetch_assoc()['count'];
        echo "<p>Total users in database: <strong>" . $count . "</strong></p>";
        
        if ($count > 0) {
            echo "<h3>User List:</h3>";
            $users = $conn->query("SELECT id, username, first_name, last_name, email, is_admin, created_at FROM users ORDER BY created_at DESC");
            if ($users) {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Admin</th><th>Created</th></tr>";
                while ($user = $users->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($user['is_admin'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . $user['created_at'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No users found in database</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error counting users: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking users: " . $e->getMessage() . "</p>";
}

// Test adding a user
echo "<h2>Test User Creation</h2>";
if (isset($_POST['test_add_user'])) {
    try {
        $test_username = 'test_user_' . time();
        $test_password = password_hash('test123', PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        
        if ($stmt) {
            $first_name = 'Test';
            $last_name = 'User';
            $email = 'test@example.com';
            $is_admin = 0;
            
            $stmt->bind_param("sssssi", $test_username, $first_name, $last_name, $test_password, $email, $is_admin);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Test user created successfully</p>";
                echo "<p>Username: <strong>" . $test_username . "</strong></p>";
                echo "<p>Password: <strong>test123</strong></p>";
            } else {
                echo "<p style='color: red;'>❌ Error creating test user: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error preparing insert statement: " . $conn->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error in test user creation: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='POST'>";
echo "<button type='submit' name='test_add_user' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Create Test User</button>";
echo "</form>";

// Check session information
echo "<h2>Session Information</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
echo "<p>Is Admin: " . ($_SESSION['is_admin'] ?? 'Not set') . "</p>";

// Check PHP error log
echo "<h2>PHP Error Log</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = file_get_contents($error_log);
    if ($recent_errors) {
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($recent_errors, -2000)); // Last 2000 characters
        echo "</pre>";
    } else {
        echo "<p>Error log is empty</p>";
    }
} else {
    echo "<p>Error log not found or not accessible</p>";
}

echo "<hr>";
echo "<p><a href='users.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Users Page</a></p>";
echo "<p><a href='fix_users_table.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Users Table Structure</a></p>";
echo "<p><a href='create_admin_user.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👤 Create Admin User</a></p>";
?> 