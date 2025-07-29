<?php
// SQLite Database configuration for local development
$db_path = __DIR__ . '/../database/gt_automotives.db';

// Create database directory if it doesn't exist
$db_dir = dirname($db_path);
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// Create SQLite connection
try {
    $conn = new SQLite3($db_path);
    
    // Enable foreign keys
    $conn->exec('PRAGMA foreign_keys = ON');
    
    // Set busy timeout
    $conn->busyTimeout(5000);
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// SQLite wrapper functions to maintain compatibility with existing code
function sqlite_prepare($conn, $query) {
    return $conn->prepare($query);
}

function sqlite_bind_param($stmt, $types, ...$params) {
    // SQLite3 doesn't use type hints like MySQL, so we'll bind by position
    for ($i = 0; $i < count($params); $i++) {
        $stmt->bindValue($i + 1, $params[$i]);
    }
    return true;
}

function sqlite_execute($stmt) {
    return $stmt->execute();
}

function sqlite_get_result($stmt) {
    return $stmt;
}

function sqlite_fetch_assoc($result) {
    return $result->fetchArray(SQLITE3_ASSOC);
}

function sqlite_num_rows($result) {
    // SQLite3 doesn't have a direct num_rows method
    // We'll need to count manually if needed
    return 0; // Placeholder
}

function sqlite_query($conn, $query) {
    return $conn->query($query);
}

function sqlite_escape_string($conn, $string) {
    return SQLite3::escapeString($string);
}

function sqlite_error($conn) {
    return "SQLite error: " . $conn->lastErrorMsg();
}

// Override MySQLi functions with our wrappers
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
        return "SQLite connection error";
    }
}
?> 