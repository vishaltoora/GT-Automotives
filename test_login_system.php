<?php
// Test Login System
// This script will test the login system and password verification

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Login System</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo "button { background: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 10px; }";
echo "button:hover { background: #c82333; }";
echo "table { border-collapse: collapse; width: 100%; margin: 15px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background-color: #f2f2f2; }";
echo "code { background: #f8f9fa; padding: 10px; border-radius: 5px; display: block; margin: 10px 0; font-family: monospace; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔐 Test Login System</h1>";
echo "<p>This script will test the login system and password verification for user 'rohit.toora'.</p>";

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

// Step 2: Get current user data
echo "<h2>Step 2: Current User Data</h2>";
try {
    $user_query = "SELECT id, username, password, email, is_admin FROM users WHERE username = 'rohit.toora'";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>" . $user['id'] . "</td></tr>";
        echo "<tr><td>Username</td><td>" . $user['username'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $user['email'] . "</td></tr>";
        echo "<tr><td>Is Admin</td><td>" . ($user['is_admin'] ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>Password Hash</td><td><code>" . $user['password'] . "</code></td></tr>";
        echo "</table>";
        
        $current_hash = $user['password'];
        
    } else {
        echo "<div class='error'>❌ User 'rohit.toora' not found in database</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error fetching user data: " . $e->getMessage() . "</div>";
    exit;
}

// Step 3: Test password verification
echo "<h2>Step 3: Test Password Verification</h2>";
$test_password = 'Mann1234';

echo "<div class='info'>";
echo "<p><strong>Testing password:</strong> $test_password</p>";
echo "<p><strong>Current hash:</strong> $current_hash</p>";
echo "</div>";

$verification_result = password_verify($test_password, $current_hash);
echo "<div class='" . ($verification_result ? 'success' : 'error') . "'>";
echo ($verification_result ? "✅" : "❌") . " Password verification: " . ($verification_result ? 'PASSED' : 'FAILED');
echo "</div>";

// Step 4: Generate a known working password
echo "<h2>Step 4: Generate Working Password</h2>";
$working_password = 'admin123'; // Simple password that should work
$working_hash = password_hash($working_password, PASSWORD_DEFAULT);

echo "<div class='info'>";
echo "<p><strong>Working Password:</strong> $working_password</p>";
echo "<p><strong>Working Hash:</strong></p>";
echo "<code>$working_hash</code>";
echo "</div>";

// Step 5: Handle form submission to update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    echo "<h2>Step 5: Updating Password</h2>";
    
    try {
        $update_sql = "UPDATE users SET password = ? WHERE username = 'rohit.toora'";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $working_hash);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Password updated successfully!</div>";
            
            // Test the new password
            $test_verification = password_verify($working_password, $working_hash);
            echo "<div class='" . ($test_verification ? 'success' : 'error') . "'>";
            echo ($test_verification ? "✅" : "❌") . " New password verification: " . ($test_verification ? 'PASSED' : 'FAILED');
            echo "</div>";
            
            echo "<div class='success'>";
            echo "<h3>🎉 Password Update Complete!</h3>";
            echo "<p><strong>Username:</strong> rohit.toora</p>";
            echo "<p><strong>New Password:</strong> $working_password</p>";
            echo "<p>You can now login with these credentials.</p>";
            echo "</div>";
            
            echo "<div style='margin: 20px 0;'>";
            echo "<a href='admin/login.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>🔐 Test Login</a>";
            echo "</div>";
            
        } else {
            echo "<div class='error'>❌ Error updating password: " . $stmt->error . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error updating password: " . $e->getMessage() . "</div>";
    }
} else {
    // Show the update option
    echo "<h2>Step 5: Update Password</h2>";
    echo "<div class='warning'>";
    echo "<h3>⚠️ Warning</h3>";
    echo "<p>This will update the password for user 'rohit.toora' to '$working_password'.</p>";
    echo "<p>This is a simple password that should definitely work.</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>SQL Command (if you prefer manual update):</h3>";
    echo "<code>";
    echo "USE gt_automotives;\n";
    echo "UPDATE users \n";
    echo "SET password = '$working_hash' \n";
    echo "WHERE username = 'rohit.toora';";
    echo "</code>";
    echo "</div>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<button type='submit' name='update_password'>🔐 Update Password to $working_password</button>";
    echo "</form>";
}

// Step 6: Manual SQL option
echo "<h2>Step 6: Manual SQL (if needed)</h2>";
echo "<div class='info'>";
echo "<p>If the automatic update doesn't work, run this SQL manually in your database:</p>";
echo "<code>";
echo "USE gt_automotives;\n";
echo "UPDATE users \n";
echo "SET password = '$working_hash' \n";
echo "WHERE username = 'rohit.toora';\n";
echo "SELECT id, username, email, is_admin FROM users WHERE username = 'rohit.toora';";
echo "</code>";
echo "</div>";

echo "<hr>";
echo "<p><a href='admin/login.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Back to Login</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 