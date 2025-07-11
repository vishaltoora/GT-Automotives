<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['admin_id'] ?? null;
}

// Function to redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Function to verify admin credentials
function verifyAdminCredentials($username, $password, $conn) {
    $escaped_username = SQLite3::escapeString($username);
    $query = "SELECT id, username, password FROM users WHERE username = '$escaped_username' AND is_admin = 1 LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetchArray(SQLITE3_ASSOC)) {
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