<?php
// Test the verifyAdminCredentials function
// This will help identify if the issue is in the authentication function

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔐 Testing verifyAdminCredentials Function\n";
echo "========================================\n\n";

// Include required files
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Test 1: Check the function exists
echo "1. Function Check:\n";
if (function_exists('verifyAdminCredentials')) {
    echo "✅ verifyAdminCredentials function exists\n";
} else {
    echo "❌ verifyAdminCredentials function not found\n";
    exit;
}

// Test 2: Check database connection
echo "\n2. Database Connection:\n";
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

// Test 3: Check user exists
echo "\n3. User Check:\n";
try {
    $user_query = "SELECT id, username, password FROM users WHERE username = 'rohit.toora'";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "✅ User 'rohit.toora' found\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Username: " . $user['username'] . "\n";
        echo "   - Password Hash: " . substr($user['password'], 0, 50) . "...\n";
    } else {
        echo "❌ User 'rohit.toora' not found\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Test password verification directly
echo "\n4. Direct Password Verification:\n";
$test_password = 'Maan1234';
$verification_result = password_verify($test_password, $user['password']);

if ($verification_result) {
    echo "✅ Password 'Maan1234' is correct!\n";
} else {
    echo "❌ Password 'Maan1234' is incorrect!\n";
}

// Test 5: Test verifyAdminCredentials function
echo "\n5. verifyAdminCredentials Function Test:\n";
try {
    $auth_result = verifyAdminCredentials('rohit.toora', 'Maan1234', $conn);
    
    if ($auth_result) {
        echo "✅ verifyAdminCredentials returned user data:\n";
        echo "   - ID: " . $auth_result['id'] . "\n";
        echo "   - Username: " . $auth_result['username'] . "\n";
        echo "   - Is Admin: " . ($auth_result['is_admin'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ verifyAdminCredentials returned false\n";
        echo "This means the function is not finding the user or password is wrong.\n";
    }
} catch (Exception $e) {
    echo "❌ Error in verifyAdminCredentials: " . $e->getMessage() . "\n";
}

// Test 6: Check the function code
echo "\n6. Function Code Analysis:\n";
echo "The verifyAdminCredentials function does the following:\n";
echo "1. Escapes the username using mysqli_real_escape_string\n";
echo "2. Queries the database for the user\n";
echo "3. Uses password_verify to check the password\n";
echo "4. Returns the user data if successful, false otherwise\n\n";

// Test 7: Manual query test
echo "\n7. Manual Query Test:\n";
try {
    $escaped_username = mysqli_real_escape_string($conn, 'rohit.toora');
    $manual_query = "SELECT * FROM users WHERE username = '$escaped_username'";
    $manual_result = $conn->query($manual_query);
    
    if ($manual_result && $manual_result->num_rows > 0) {
        $manual_user = $manual_result->fetch_assoc();
        echo "✅ Manual query found user:\n";
        echo "   - ID: " . $manual_user['id'] . "\n";
        echo "   - Username: " . $manual_user['username'] . "\n";
        
        // Test password verification
        $manual_password_check = password_verify('Maan1234', $manual_user['password']);
        echo "   - Password verification: " . ($manual_password_check ? 'PASSED' : 'FAILED') . "\n";
        
    } else {
        echo "❌ Manual query did not find user\n";
    }
} catch (Exception $e) {
    echo "❌ Error in manual query: " . $e->getMessage() . "\n";
}

// Test 8: Alternative password test
echo "\n8. Alternative Password Test:\n";
$alternative_passwords = [
    'Mann1234',
    'admin123',
    'password',
    'rohit123',
    'toora123'
];

foreach ($alternative_passwords as $alt_password) {
    $alt_result = password_verify($alt_password, $user['password']);
    echo ($alt_result ? "✅" : "❌") . " Password '$alt_password': " . ($alt_result ? "CORRECT" : "incorrect") . "\n";
}

echo "\n🎯 Summary:\n";
echo "==========\n";
echo "1. Function exists: " . (function_exists('verifyAdminCredentials') ? "YES" : "NO") . "\n";
echo "2. Database connection: " . ($test_result ? "OK" : "FAILED") . "\n";
echo "3. User exists: " . ($user_result && $user_result->num_rows > 0 ? "YES" : "NO") . "\n";
echo "4. Password verification: " . ($verification_result ? "PASSED" : "FAILED") . "\n";
echo "5. verifyAdminCredentials: " . ($auth_result ? "PASSED" : "FAILED") . "\n\n";

if (!$auth_result) {
    echo "🔧 POSSIBLE ISSUES:\n";
    echo "==================\n";
    echo "1. The password hash might be corrupted\n";
    echo "2. There might be whitespace in the username/password\n";
    echo "3. The database connection might be different in production\n";
    echo "4. The function might have an error\n\n";
    
    echo "🔧 RECOMMENDED FIXES:\n";
    echo "=====================\n";
    echo "1. Try the debug script: debug_production_login.php\n";
    echo "2. Update password with fresh hash\n";
    echo "3. Check for whitespace in username/password\n";
    echo "4. Verify database connection settings\n";
}

echo "\n🎉 Test complete!\n";
?> 