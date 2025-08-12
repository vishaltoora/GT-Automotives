<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include production configuration if available
if (file_exists(dirname(__DIR__) . '/includes/production_config.php')) {
    require_once dirname(__DIR__) . '/includes/production_config.php';
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
        // Determine the correct path to login.php
        $current_path = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($current_path, '/admin/') !== false) {
            // We're in admin directory, redirect to admin login
            header("Location: login.php");
        } else {
            // We're in another directory, redirect to admin login
            header("Location: admin/login.php");
        }
        exit;
    }
}

// Function to require admin access
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        // Determine the correct path to index.php
        $current_path = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($current_path, '/admin/') !== false) {
            // We're in admin directory, redirect to admin index
            header("Location: index.php");
        } else {
            // We're in another directory, redirect to admin index
            header("Location: admin/index.php");
        }
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
    
    // Redirect to home page
    header("Location: ../index.php");
    exit;
}

// Production session security enhancements
if (function_exists('isProduction') && isProduction()) {
    // Set secure session parameters for production
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?> 