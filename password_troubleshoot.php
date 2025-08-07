<?php
// Password Troubleshooting Script
// This script will help diagnose and fix password issues

require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Password Troubleshooting</title>";
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
echo "<h1>🔐 Password Troubleshooting</h1>";
echo "<p>This script will help diagnose and fix password issues for user 'rohit.toora'.</p>";

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

// Step 2: Check current user data
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
        echo "<tr><td>Current Password Hash</td><td><code>" . substr($user['password'], 0, 50) . "...</code></td></tr>";
        echo "</table>";
        
        echo "<div class='info'>";
        echo "<strong>Current password hash length:</strong> " . strlen($user['password']) . " characters";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ User 'rohit.toora' not found in database</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error fetching user data: " . $e->getMessage() . "</div>";
    exit;
}

// Step 3: Generate new password hash
echo "<h2>Step 3: Generate New Password Hash</h2>";
$new_password = 'Mann1234';
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<div class='info'>";
echo "<p><strong>New Password:</strong> $new_password</p>";
echo "<p><strong>New Password Hash:</strong></p>";
echo "<code>$new_password_hash</code>";
echo "</div>";

// Step 4: Test password verification
echo "<h2>Step 4: Test Password Verification</h2>";
$test_verification = password_verify($new_password, $new_password_hash);
echo "<div class='" . ($test_verification ? 'success' : 'error') . "'>";
echo ($test_verification ? "✅" : "❌") . " Password verification test: " . ($test_verification ? 'PASSED' : 'FAILED');
echo "</div>";

// Step 5: Handle form submission to update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    echo "<h2>Step 5: Updating Password</h2>";
    
    try {
        $update_sql = "UPDATE users SET password = ? WHERE username = 'rohit.toora'";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $new_password_hash);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Password updated successfully!</div>";
            
            // Verify the update
            $verify_query = "SELECT id, username, email, is_admin FROM users WHERE username = 'rohit.toora'";
            $verify_result = $conn->query($verify_query);
            if ($verify_result && $verify_result->num_rows > 0) {
                $updated_user = $verify_result->fetch_assoc();
                echo "<div class='info'>";
                echo "<h3>Updated User Data:</h3>";
                echo "<p><strong>Username:</strong> " . $updated_user['username'] . "</p>";
                echo "<p><strong>Email:</strong> " . $updated_user['email'] . "</p>";
                echo "<p><strong>Is Admin:</strong> " . ($updated_user['is_admin'] ? 'Yes' : 'No') . "</p>";
                echo "</div>";
            }
            
            echo "<div class='success'>";
            echo "<h3>🎉 Password Update Complete!</h3>";
            echo "<p><strong>Username:</strong> rohit.toora</p>";
            echo "<p><strong>New Password:</strong> Mann1234</p>";
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
    echo "<p>This will update the password for user 'rohit.toora' to 'Mann1234'.</p>";
    echo "<p>Make sure you want to proceed with this change.</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>SQL Command (if you prefer manual update):</h3>";
    echo "<code>";
    echo "USE gt_automotives;\n";
    echo "UPDATE users \n";
    echo "SET password = '$new_password_hash' \n";
    echo "WHERE username = 'rohit.toora';";
    echo "</code>";
    echo "</div>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<button type='submit' name='update_password'>🔐 Update Password to Mann1234</button>";
    echo "</form>";
}

// Step 6: Manual SQL option
echo "<h2>Step 6: Manual SQL (if needed)</h2>";
echo "<div class='info'>";
echo "<p>If the automatic update doesn't work, run this SQL manually in your database:</p>";
echo "<code>";
echo "USE gt_automotives;\n";
echo "UPDATE users \n";
echo "SET password = '$new_password_hash' \n";
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