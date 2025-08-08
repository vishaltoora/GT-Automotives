<?php
// Final Test - Verify Production Login is Working
// This script will test if the login issue has been resolved

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🎯 Final Test - Production Login Verification\n";
echo "============================================\n\n";

// Include required files
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Test 1: Check database connection
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

// Test 2: Check user and password
echo "\n2. User and Password Test:\n";
try {
    $user_query = "SELECT id, username, password FROM users WHERE username = 'rohit.toora'";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "✅ User 'rohit.toora' found\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Username: " . $user['username'] . "\n";
        echo "   - Password Hash: " . substr($user['password'], 0, 50) . "...\n";
        
        // Test password verification
        $password_test = password_verify('Maan1234', $user['password']);
        if ($password_test) {
            echo "✅ Password 'Maan1234' is correct!\n";
        } else {
            echo "❌ Password 'Maan1234' is still incorrect!\n";
        }
        
    } else {
        echo "❌ User 'rohit.toora' not found\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Test verifyAdminCredentials function
echo "\n3. verifyAdminCredentials Function Test:\n";
try {
    $auth_result = verifyAdminCredentials('rohit.toora', 'Maan1234', $conn);
    
    if ($auth_result) {
        echo "✅ verifyAdminCredentials function works correctly!\n";
        echo "   - User ID: " . $auth_result['id'] . "\n";
        echo "   - Username: " . $auth_result['username'] . "\n";
        echo "   - Is Admin: " . ($auth_result['is_admin'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ verifyAdminCredentials function still failed!\n";
    }
} catch (Exception $e) {
    echo "❌ Error in verifyAdminCredentials: " . $e->getMessage() . "\n";
}

// Test 4: Test login page
echo "\n4. Login Page Test:\n";
try {
    $login_url = "https://www.gt-automotives.com/admin/login.php";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Final-Test/1.0'
        ]
    ]);
    
    $response = @file_get_contents($login_url, false, $context);
    if ($response !== false) {
        echo "✅ Login page is accessible\n";
        
        if (strpos($response, '<form') !== false && 
            strpos($response, 'name="username"') !== false && 
            strpos($response, 'name="password"') !== false) {
            echo "✅ Login form is properly configured\n";
        } else {
            echo "❌ Login form has issues\n";
        }
    } else {
        echo "❌ Login page is not accessible\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing login page: " . $e->getMessage() . "\n";
}

echo "\n🎯 Final Summary:\n";
echo "===============\n";
echo "1. Database connection: " . ($test_result ? "OK" : "FAILED") . "\n";
echo "2. User exists: " . ($user_result && $user_result->num_rows > 0 ? "YES" : "NO") . "\n";
echo "3. Password verification: " . ($password_test ? "PASSED" : "FAILED") . "\n";
echo "4. verifyAdminCredentials: " . ($auth_result ? "PASSED" : "FAILED") . "\n";
echo "5. Login page accessible: " . ($response !== false ? "YES" : "NO") . "\n\n";

if ($auth_result) {
    echo "🎉 SUCCESS! Login should now work correctly!\n";
    echo "==========================================\n";
    echo "You can now login with:\n";
    echo "   - Username: rohit.toora\n";
    echo "   - Password: Maan1234\n";
    echo "   - URL: https://www.gt-automotives.com/admin/login.php\n\n";
    
    echo "🔒 Security Cleanup:\n";
    echo "==================\n";
    echo "Remember to delete the debug files:\n";
    echo "- debug_production_login.php\n";
    echo "- test_auth_function.php\n";
    echo "- production_password_check.php\n";
    echo "- test_production_login.php\n\n";
    
    echo "Use these Git commands:\n";
    echo "rm debug_production_login.php test_auth_function.php production_password_check.php test_production_login.php\n";
    echo "git add -A\n";
    echo "git commit -m \"Remove debug scripts after fixing login issue\"\n";
    echo "git push\n";
    
} else {
    echo "❌ ISSUE PERSISTS:\n";
    echo "==================\n";
    echo "The login issue is still not resolved.\n";
    echo "Please check:\n";
    echo "1. Database connection settings\n";
    echo "2. PHP error logs\n";
    echo "3. Server configuration\n";
    echo "4. Browser developer console for errors\n";
}

echo "\n🎉 Test complete!\n";
?> 