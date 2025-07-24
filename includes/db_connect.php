<?php
// Database configuration for SQLite
$db_path = __DIR__ . '/../database/gt_automotives';

// Create database directory if it doesn't exist
if (!file_exists(dirname($db_path))) {
    mkdir(dirname($db_path), 0777, true);
}

// Create SQLite connection
$conn = new SQLite3($db_path);

// Enable exceptions for errors
$conn->enableExceptions(true);

// SQLite wrapper functions (using different names to avoid conflicts)
function sqlite_prepare($conn, $query) {
    return $conn->prepare($query);
}

function sqlite_bind_param($stmt, $types, ...$params) {
    $i = 1; // SQLite parameters are 1-indexed
    foreach ($params as $param) {
        if ($types[$i-1] === 'i') {
            $stmt->bindValue($i, $param, SQLITE3_INTEGER);
        } elseif ($types[$i-1] === 'd') {
            $stmt->bindValue($i, $param, SQLITE3_FLOAT);
        } else {
            $stmt->bindValue($i, $param, SQLITE3_TEXT);
        }
        $i++;
    }
    return true;
}

function sqlite_execute($stmt) {
    return $stmt->execute();
}

function sqlite_get_result($stmt) {
    $result = $stmt->execute();
    return $result;
}

function sqlite_fetch_assoc($result) {
    return $result->fetchArray(SQLITE3_ASSOC);
}

function sqlite_num_rows($result) {
    $count = 0;
    $result->reset();
    while ($result->fetchArray()) {
        $count++;
    }
    $result->reset();
    return $count;
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

// Override MySQLi functions with our SQLite wrappers only if they don't exist
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
