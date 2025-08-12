<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';

echo "<h1>Create Admin User</h1>";

// Check if users table exists, create if not
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$table_check || $table_check->num_rows == 0) {
        echo "<h2>Creating Users Table...</h2>";
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
            exit;
        }
    } else {
        echo "<p style='color: green;'>✅ Users table already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking/creating users table: " . $e->getMessage() . "</p>";
    exit;
}

// Check if admin user already exists
$admin_check = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
$admin_count = $admin_check->fetch_assoc()['count'];

if ($admin_count > 0) {
    echo "<p style='color: orange;'>⚠️ Admin user already exists</p>";
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate input
    if (empty($username)) $errors[] = "Username is required";
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check if username already exists
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already exists";
        }
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sssss", $username, $first_name, $last_name, $hashed_password, $email);
            
            if ($stmt->execute()) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>✅ Admin User Created Successfully!</h3>";
                echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($first_name . ' ' . $last_name) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($email ?: 'Not provided') . "</p>";
                echo "<p><strong>Role:</strong> Administrator</p>";
                echo "</div>";
                echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
                exit;
            } else {
                $errors[] = "Error creating user: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ Errors:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

// Show form
?>
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Create Initial Admin User</h2>
    <p>This will create the first administrator user for your system.</p>
    
    <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
        <div style="margin-bottom: 15px;">
            <label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username *</label>
            <input type="text" id="username" name="username" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Enter username">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="first_name" style="display: block; margin-bottom: 5px; font-weight: bold;">First Name *</label>
            <input type="text" id="first_name" name="first_name" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Enter first name">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="last_name" style="display: block; margin-bottom: 5px; font-weight: bold;">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Enter last name">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
            <input type="email" id="email" name="email" 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Enter email address (optional)">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password *</label>
            <input type="password" id="password" name="password" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Enter password (minimum 6 characters)" minlength="6">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   placeholder="Confirm password">
        </div>
        
        <button type="submit" style="background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Create Admin User
        </button>
    </form>
</div> 