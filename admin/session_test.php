<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Test</h1>";

// Test session start
if (session_status() === PHP_SESSION_NONE) {
    echo "Starting session...<br>";
    session_start();
    echo "Session started successfully<br>";
} else {
    echo "Session already active<br>";
}

echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

// Test session data
if (isset($_GET['set'])) {
    $_SESSION['test_data'] = 'Test value set at ' . date('Y-m-d H:i:s');
    echo "Session data set<br>";
}

if (isset($_SESSION['test_data'])) {
    echo "Session data: " . htmlspecialchars($_SESSION['test_data']) . "<br>";
} else {
    echo "No session data found<br>";
}

// Test session directory
echo "Session save path: " . session_save_path() . "<br>";
echo "Session save path writable: " . (is_writable(session_save_path()) ? 'Yes' : 'No') . "<br>";

// Test session cookie
echo "Session cookie params: " . print_r(session_get_cookie_params(), true) . "<br>";

// Test session name
echo "Session name: " . session_name() . "<br>";

// Test session ID regeneration
if (isset($_GET['regenerate'])) {
    echo "Regenerating session ID...<br>";
    session_regenerate_id(true);
    echo "New session ID: " . session_id() . "<br>";
}

echo "<hr>";
echo "<a href='?set=1'>Set Session Data</a><br>";
echo "<a href='?regenerate=1'>Regenerate Session ID</a><br>";
echo "<a href='?'>Refresh</a><br>";
echo "<a href='create_sale.php'>Go to Create Sale</a><br>";
echo "<a href='login.php'>Go to Login</a><br>";
?> 