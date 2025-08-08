<?php
// Debug Production Login Issue
// This script will help identify why login is not working

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Debug Production Login Issue\n";
echo "==============================\n\n";

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Step 1: Check database connection
echo "1. Database Connection Test:\n";
try {
    $test_result = $conn->query("SELECT 1");
    if ($test_result) {
        echo "✅ Database connection successful\n";
    } else {
        echo "❌ Database connection failed\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Step 2: Check user details
echo "\n2. User Details Check:\n";
try {
    $user_query = "SELECT id, username, first_name, last_name, email, is_admin, password FROM users WHERE username = 'rohit.toora'";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "✅ User 'rohit.toora' found:\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Username: " . $user['username'] . "\n";
        echo "   - Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "   - Email: " . $user['email'] . "\n";
        echo "   - Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
        echo "   - Password Hash: " . substr($user['password'], 0, 50) . "...\n";
        
        $current_hash = $user['password'];
        
    } else {
        echo "❌ User 'rohit.toora' not found!\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
    exit;
}

// Step 3: Test password verification
echo "\n3. Password Verification Test:\n";
$test_password = 'Maan1234';
$verification_result = password_verify($test_password, $current_hash);

if ($verification_result) {
    echo "✅ Password 'Maan1234' is correct!\n";
} else {
    echo "❌ Password 'Maan1234' is incorrect!\n";
    echo "This means the hash doesn't match the password.\n";
}

// Step 4: Test verifyAdminCredentials function
echo "\n4. verifyAdminCredentials Function Test:\n";
try {
    $auth_result = verifyAdminCredentials('rohit.toora', 'Maan1234', $conn);
    if ($auth_result) {
        echo "✅ verifyAdminCredentials function works correctly!\n";
        echo "   - User ID: " . $auth_result['id'] . "\n";
        echo "   - Username: " . $auth_result['username'] . "\n";
        echo "   - Is Admin: " . ($auth_result['is_admin'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ verifyAdminCredentials function failed!\n";
        echo "This is why login is not working.\n";
    }
} catch (Exception $e) {
    echo "❌ Error in verifyAdminCredentials: " . $e->getMessage() . "\n";
}

// Step 5: Check for case sensitivity issues
echo "\n5. Case Sensitivity Test:\n";
$case_variations = [
    'rohit.toora',
    'ROHIT.TOORA',
    'Rohit.Toora',
    'rohit_toora',
    'rohittoora'
];

foreach ($case_variations as $username_variant) {
    $case_query = "SELECT username FROM users WHERE username = ?";
    $case_stmt = $conn->prepare($case_query);
    $case_stmt->bind_param("s", $username_variant);
    $case_stmt->execute();
    $case_result = $case_stmt->get_result();
    
    if ($case_result->num_rows > 0) {
        echo "✅ Username '$username_variant' found in database\n";
    } else {
        echo "❌ Username '$username_variant' NOT found in database\n";
    }
    $case_stmt->close();
}

// Step 6: Test login page accessibility
echo "\n6. Login Page Test:\n";
try {
    $login_url = "http://" . $_SERVER['HTTP_HOST'] . "/admin/login.php";
    echo "Testing login page: $login_url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Debug-Test/1.0'
        ]
    ]);
    
    $response = @file_get_contents($login_url, false, $context);
    if ($response !== false) {
        echo "✅ Login page is accessible\n";
        
        // Check for form elements
        if (strpos($response, '<form') !== false) {
            echo "✅ Login form is present\n";
        } else {
            echo "❌ Login form not found\n";
        }
        
        if (strpos($response, 'name="username"') !== false && strpos($response, 'name="password"') !== false) {
            echo "✅ Username and password fields are present\n";
        } else {
            echo "❌ Username or password fields missing\n";
        }
        
    } else {
        echo "❌ Login page is not accessible\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing login page: " . $e->getMessage() . "\n";
}

// Step 7: Check for whitespace or hidden characters
echo "\n7. Username/Password Cleanliness Test:\n";
$clean_username = trim('rohit.toora');
$clean_password = trim('Maan1234');

echo "Original username: 'rohit.toora'\n";
echo "Cleaned username: '$clean_username'\n";
echo "Username length: " . strlen($clean_username) . "\n";
echo "Username bytes: " . strlen(utf8_encode($clean_username)) . "\n";

echo "Original password: 'Maan1234'\n";
echo "Cleaned password: '$clean_password'\n";
echo "Password length: " . strlen($clean_password) . "\n";
echo "Password bytes: " . strlen(utf8_encode($clean_password)) . "\n";

// Step 8: Generate a fresh password hash
echo "\n8. Fresh Password Hash Test:\n";
$fresh_hash = password_hash('Maan1234', PASSWORD_DEFAULT);
$fresh_verification = password_verify('Maan1234', $fresh_hash);

echo "Fresh hash: " . $fresh_hash . "\n";
echo "Fresh verification: " . ($fresh_verification ? 'PASSED' : 'FAILED') . "\n";

// Step 9: Update with fresh hash
echo "\n9. Update with Fresh Hash:\n";
if (isset($_GET['update']) && $_GET['update'] === 'yes') {
    echo "Updating password with fresh hash...\n";
    
    $update_query = "UPDATE users SET password = ? WHERE username = 'rohit.toora'";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("s", $fresh_hash);
    
    if ($update_stmt->execute()) {
        echo "✅ Password updated with fresh hash!\n";
        echo "Try logging in again with:\n";
        echo "   - Username: rohit.toora\n";
        echo "   - Password: Maan1234\n";
    } else {
        echo "❌ Error updating password: " . $conn->error . "\n";
    }
    $update_stmt->close();
} else {
    echo "To update with fresh hash, visit:\n";
    echo "http://" . $_SERVER['HTTP_HOST'] . "/debug_production_login.php?update=yes\n\n";
}

echo "\n🎯 Summary:\n";
echo "==========\n";
echo "1. Database connection: " . ($test_result ? "OK" : "FAILED") . "\n";
echo "2. User exists: " . ($user_result && $user_result->num_rows > 0 ? "YES" : "NO") . "\n";
echo "3. Password verification: " . ($verification_result ? "PASSED" : "FAILED") . "\n";
echo "4. verifyAdminCredentials: " . ($auth_result ? "PASSED" : "FAILED") . "\n";
echo "5. Login page accessible: " . ($response !== false ? "YES" : "NO") . "\n\n";

if (!$auth_result) {
    echo "🔧 RECOMMENDED FIX:\n";
    echo "==================\n";
    echo "1. Visit: http://" . $_SERVER['HTTP_HOST'] . "/debug_production_login.php?update=yes\n";
    echo "2. Try logging in again\n";
    echo "3. If still not working, check your browser's developer console for errors\n";
}

echo "\n🎉 Debug complete!\n";
?> 