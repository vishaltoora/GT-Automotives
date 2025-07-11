<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get size ID from URL
$size_id = intval($_GET['id'] ?? 0);

if ($size_id <= 0) {
    $_SESSION['error_message'] = 'Invalid size ID';
    header('Location: sizes.php');
    exit;
}

// Get size data
$size_query = "SELECT * FROM sizes WHERE id = ?";
$size_stmt = $conn->prepare($size_query);
$size_stmt->bindValue(1, $size_id, SQLITE3_INTEGER);
$size_result = $size_stmt->execute();
$size = $size_result->fetchArray(SQLITE3_ASSOC);

if (!$size) {
    $_SESSION['error_message'] = 'Size not found';
    header('Location: sizes.php');
    exit;
}

// Check if size is being used by any products
$usage_query = "SELECT COUNT(*) as count FROM tires WHERE size = ?";
$usage_stmt = $conn->prepare($usage_query);
$usage_stmt->bindValue(1, $size['name'], SQLITE3_TEXT);
$usage_result = $usage_stmt->execute();
$usage_count = $usage_result->fetchArray(SQLITE3_ASSOC)['count'];

if ($usage_count > 0) {
    $_SESSION['error_message'] = "Cannot delete size '{$size['name']}' because it is being used by $usage_count product(s). Please update or remove those products first.";
    header('Location: sizes.php');
    exit;
}

// Delete the size
$delete_query = "DELETE FROM sizes WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bindValue(1, $size_id, SQLITE3_INTEGER);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "Size '{$size['name']}' deleted successfully";
} else {
    $_SESSION['error_message'] = 'Database error: ' . $conn->lastErrorMsg();
}

header('Location: sizes.php');
exit;
?> 