<?php
// MySQL Database configuration
$host = 'localhost';
$dbname = 'gt_automotives';
$username = 'gtadmin';
$password = 'Vishal@1234#'; // Change this to your actual password

// Create MySQL connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// MySQL wrapper functions to maintain compatibility with existing code
function sqlite_prepare($conn, $query) {
    return $conn->prepare($query);
}

function sqlite_bind_param($stmt, $types, ...$params) {
    return $stmt->bind_param($types, ...$params);
}

function sqlite_execute($stmt) {
    return $stmt->execute();
}

function sqlite_get_result($stmt) {
    return $stmt->get_result();
}

function sqlite_fetch_assoc($result) {
    return $result->fetch_assoc();
}

function sqlite_num_rows($result) {
    return $result->num_rows;
}

function sqlite_query($conn, $query) {
    return $conn->query($query);
}

function sqlite_escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}

function sqlite_error($conn) {
    return "MySQL error: " . $conn->error;
}

// Override MySQLi functions with our wrappers only if they don't exist
if (!function_exists('mysqli_prepare')) {
    function mysqli_prepare($conn, $query) {
        return sqlite_prepare($conn, $query);
    }
}

if (!function_exists('mysqli_stmt_bind_param')) {
    function mysqli_stmt_bind_param($stmt, $types, ...$params) {
        return sqlite_bind_param($stmt, $types, ...$params);
    }
}

if (!function_exists('mysqli_stmt_execute')) {
    function mysqli_stmt_execute($stmt) {
        return sqlite_execute($stmt);
    }
}

if (!function_exists('mysqli_stmt_get_result')) {
    function mysqli_stmt_get_result($stmt) {
        return sqlite_get_result($stmt);
    }
}

if (!function_exists('mysqli_fetch_assoc')) {
    function mysqli_fetch_assoc($result) {
        return sqlite_fetch_assoc($result);
    }
}

if (!function_exists('mysqli_num_rows')) {
    function mysqli_num_rows($result) {
        return sqlite_num_rows($result);
    }
}

if (!function_exists('mysqli_query')) {
    function mysqli_query($conn, $query) {
        return sqlite_query($conn, $query);
    }
}

if (!function_exists('mysqli_real_escape_string')) {
    function mysqli_real_escape_string($conn, $string) {
        return sqlite_escape_string($conn, $string);
    }
}

if (!function_exists('mysqli_connect_error')) {
    function mysqli_connect_error() {
        return "MySQL connection error";
    }
}

?>
