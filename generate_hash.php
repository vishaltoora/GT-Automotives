<?php
$username = 'rohit.toora';
$password = 'Maan1234';
$email = 'rohit.toora@gmail.com';

// Generate the hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "=== Password Hash Generator ===\n\n";
echo "Username: $username\n";
echo "Password: $password\n";
echo "Email: $email\n";
echo "Hashed Password: $hashed_password\n\n";

echo "=== MySQL Commands ===\n";
echo "-- Check if user exists:\n";
echo "SELECT id, username, email, is_admin FROM users WHERE username = '$username';\n\n";

echo "-- Create new admin user:\n";
echo "INSERT INTO users (username, password, email, is_admin) VALUES ('$username', '$hashed_password', '$email', 1);\n\n";

echo "-- Verify the user was created:\n";
echo "SELECT id, username, email, is_admin FROM users WHERE username = '$username';\n\n";

echo "-- List all admin users:\n";
echo "SELECT id, username, email, is_admin FROM users WHERE is_admin = 1;\n";
?> 