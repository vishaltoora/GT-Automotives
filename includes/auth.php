<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Function to redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Function to require admin access
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php");
        exit;
    }
}

// Function to verify admin credentials
function verifyAdminCredentials($username, $password, $conn) {
    // Check if user exists and password is correct
    $escaped_username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM users WHERE username = '$escaped_username'";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            return $row;
        }
    }
    
    return false;
}

// Function to logout admin
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: login.php");
    exit;
}
?> 