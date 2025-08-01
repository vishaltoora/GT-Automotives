<?php
// Include database connection
require_once 'includes/db_connect.php';

// Admin user details
$username = 'rohit.toora';
$first_name = 'Rohit';
$last_name = 'Toora';
$password = 'Maan1234';
$email = 'rohit.toora@gmail.com';
$is_admin = 1;

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$check_query = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "User 'rohit.toora' already exists!\n";
    echo "Updating user information...\n";
    
    // Update existing user's information
    $update_query = "UPDATE users SET password = ?, email = ?, is_admin = ?, first_name = ?, last_name = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssiss", $hashed_password, $email, $is_admin, $first_name, $last_name, $username);
    
    if ($update_stmt->execute()) {
        echo "User 'rohit.toora' updated successfully!\n";
        echo "Username: $username\n";
        echo "Name: $first_name $last_name\n";
        echo "Password: $password\n";
        echo "Email: $email\n";
        echo "Admin Status: Yes\n";
    } else {
        echo "Error updating user: " . $conn->error . "\n";
    }
} else {
    // Insert new admin user
    $insert_query = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sssssi", $username, $first_name, $last_name, $hashed_password, $email, $is_admin);
    
    if ($insert_stmt->execute()) {
        echo "Admin user 'rohit.toora' created successfully!\n";
        echo "Username: $username\n";
        echo "Name: $first_name $last_name\n";
        echo "Password: $password\n";
        echo "Email: $email\n";
        echo "Admin Status: Yes\n";
    } else {
        echo "Error creating admin user: " . $conn->error . "\n";
    }
}

// Verify the user was created/updated
$verify_query = "SELECT id, username, first_name, last_name, email, is_admin FROM users WHERE username = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("s", $username);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$user = $verify_result->fetch_assoc();

if ($user) {
    echo "\nVerification:\n";
    echo "User ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Name: " . ($user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : 'Not set') . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
} else {
    echo "Error: Could not verify user creation.\n";
}

$conn->close();
echo "\nScript completed!\n";
?> 