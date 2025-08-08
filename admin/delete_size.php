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
$size_stmt = $conn->prepare("SELECT * FROM sizes WHERE id = ?");
$size_stmt->bind_param("i", $size_id);
$size_result = $size_stmt->get_result();
$size = $size_result->fetch_assoc();

if (!$size) {
    $_SESSION['error_message'] = 'Size not found';
    header('Location: sizes.php');
    exit;
}

// Check if size is being used by any products
$usage_stmt = $conn->prepare("SELECT COUNT(*) as count FROM tires WHERE size = ?");
$usage_stmt->bind_param("s", $size['name']);
$usage_result = $usage_stmt->get_result();
$usage_count = $usage_result->fetch_assoc()['count'];

if ($usage_count > 0) {
    $_SESSION['error_message'] = "Cannot delete size '{$size['name']}' because it is being used by $usage_count product(s). Please update or remove those products first.";
    header('Location: sizes.php');
    exit;
}

// Delete the size
$delete_stmt = $conn->prepare("DELETE FROM sizes WHERE id = ?");
$delete_stmt->bind_param("i", $size_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "Size '{$size['name']}' deleted successfully";
} else {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
}

header('Location: sizes.php');
exit;
?> 