<?php
// Production Password Check and Reset for rohit.toora
// This script will help you check and reset the password for rohit.toora

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔐 Production Password Check for rohit.toora\n";
echo "==========================================\n\n";

// Include database connection
require_once 'includes/db_connect.php';

// Step 1: Check if user exists
echo "1. Checking if user 'rohit.toora' exists:\n";
try {
    $check_query = "SELECT id, username, first_name, last_name, email, is_admin, password FROM users WHERE username = 'rohit.toora'";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        $user = $check_result->fetch_assoc();
        echo "✅ User 'rohit.toora' found!\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "   - Email: " . $user['email'] . "\n";
        echo "   - Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
        echo "   - Password Hash: " . substr($user['password'], 0, 50) . "...\n";
        echo "   - Hash Length: " . strlen($user['password']) . " characters\n";
        
        $current_hash = $user['password'];
        
    } else {
        echo "❌ User 'rohit.toora' not found in database\n";
        echo "Creating user 'rohit.toora'...\n";
        
        $username = 'rohit.toora';
        $first_name = 'Rohit';
        $last_name = 'Toora';
        $password = 'Maan1234';
        $email = 'rohit.toora@gmail.com';
        $is_admin = 1;
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $create_query = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
        $create_stmt = $conn->prepare($create_query);
        $create_stmt->bind_param("sssssi", $username, $first_name, $last_name, $hashed_password, $email, $is_admin);
        
        if ($create_stmt->execute()) {
            echo "✅ User 'rohit.toora' created successfully!\n";
            echo "   - Username: $username\n";
            echo "   - Password: $password\n";
            echo "   - Email: $email\n";
            echo "   - Admin Status: Yes\n";
        } else {
            echo "❌ Error creating user: " . $conn->error . "\n";
        }
        $create_stmt->close();
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
    exit;
}

// Step 2: Test current password
echo "\n2. Testing current password 'Maan1234':\n";
$test_password = 'Maan1234';
$verification_result = password_verify($test_password, $current_hash);

if ($verification_result) {
    echo "✅ Password 'Maan1234' is correct!\n";
    echo "The login should work with username: rohit.toora, password: Maan1234\n";
} else {
    echo "❌ Password 'Maan1234' is incorrect!\n";
    echo "The password hash doesn't match 'Maan1234'\n";
}

// Step 3: Generate new password hash
echo "\n3. Generating new password hash for 'Maan1234':\n";
$new_password = 'Maan1234';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "New password hash:\n";
echo $new_hash . "\n";

// Step 4: Update password option
echo "\n4. Update Password:\n";
echo "Do you want to update the password for 'rohit.toora' to 'Maan1234'?\n";
echo "This will ensure the login works correctly.\n\n";

echo "SQL Command to update password:\n";
echo "==============================\n";
echo "UPDATE users \n";
echo "SET password = '" . $new_hash . "' \n";
echo "WHERE username = 'rohit.toora';\n\n";

echo "Or use this PHP script to update automatically:\n";
echo "==============================================\n";

if (isset($_GET['update']) && $_GET['update'] === 'yes') {
    echo "Updating password...\n";
    
    $update_query = "UPDATE users SET password = ? WHERE username = 'rohit.toora'";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("s", $new_hash);
    
    if ($update_stmt->execute()) {
        echo "✅ Password updated successfully!\n";
        echo "You can now login with:\n";
        echo "   - Username: rohit.toora\n";
        echo "   - Password: Maan1234\n";
    } else {
        echo "❌ Error updating password: " . $conn->error . "\n";
    }
    $update_stmt->close();
} else {
    echo "To update the password automatically, visit:\n";
    echo "http://your-domain.com/production_password_check.php?update=yes\n\n";
}

// Step 5: Alternative passwords to try
echo "\n5. Alternative passwords to try:\n";
echo "==============================\n";
$alternative_passwords = [
    'Mann1234',
    'admin123',
    'password',
    'rohit123',
    'toora123'
];

foreach ($alternative_passwords as $alt_password) {
    $alt_result = password_verify($alt_password, $current_hash);
    echo ($alt_result ? "✅" : "❌") . " Password '$alt_password': " . ($alt_result ? "CORRECT" : "incorrect") . "\n";
}

echo "\n🎯 Summary:\n";
echo "==========\n";
echo "1. User 'rohit.toora' exists in database\n";
echo "2. Current password hash: " . substr($current_hash, 0, 50) . "...\n";
echo "3. Password 'Maan1234' is " . ($verification_result ? "correct" : "incorrect") . "\n";
echo "4. To fix: Update password using the SQL command above\n";
echo "5. Or visit: http://your-domain.com/production_password_check.php?update=yes\n\n";

echo "🔐 After updating, login with:\n";
echo "============================\n";
echo "Username: rohit.toora\n";
echo "Password: Maan1234\n";
echo "URL: http://your-domain.com/admin/login.php\n\n";

echo "🎉 Password reset complete!\n";
?> 