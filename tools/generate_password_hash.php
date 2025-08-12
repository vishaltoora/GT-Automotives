<?php
// Generate Password Hash for Production
// This script generates the proper password hash for the new password

$new_password = 'Mann1234';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Generate Password Hash</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; }";
echo "code { background: #f8f9fa; padding: 10px; border-radius: 5px; display: block; margin: 10px 0; font-family: monospace; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔐 Generate Password Hash</h1>";

echo "<div class='success'>";
echo "<h3>Password Hash Generated Successfully!</h3>";
echo "<p><strong>Username:</strong> rohit.toora</p>";
echo "<p><strong>New Password:</strong> Mann1234</p>";
echo "<p><strong>Generated Hash:</strong></p>";
echo "<code>" . $password_hash . "</code>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>SQL Command for Production:</h3>";
echo "<p>Run this command in your MariaDB console:</p>";
echo "<code>";
echo "USE gt_automotives;\n";
echo "UPDATE users \n";
echo "SET password = '" . $password_hash . "' \n";
echo "WHERE username = 'rohit.toora';\n";
echo "SELECT id, username, email, is_admin FROM users WHERE username = 'rohit.toora';";
echo "</code>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Alternative - Direct Command:</h3>";
echo "<p>Copy and paste this single command:</p>";
echo "<code>";
echo "UPDATE users SET password = '" . $password_hash . "' WHERE username = 'rohit.toora';";
echo "</code>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Verification Command:</h3>";
echo "<p>After updating, verify the change:</p>";
echo "<code>";
echo "SELECT id, username, email, is_admin FROM users WHERE username = 'rohit.toora';";
echo "</code>";
echo "</div>";

echo "<hr>";
echo "<p><a href='admin/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 