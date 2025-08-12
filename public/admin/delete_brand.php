<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No brand ID provided';
    header('Location: brands.php');
    exit;
}

$brand_id = intval($_GET['id']);

// Check if brand exists
$check_stmt = $conn->prepare("SELECT * FROM brands WHERE id = ?");
$check_stmt->bind_param("i", $brand_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$brand = $result->fetch_assoc();
$check_stmt->close(); // Close the first statement

if (!$brand) {
    $_SESSION['error_message'] = 'Brand not found';
    header('Location: brands.php');
    exit;
}

// Delete the brand
$delete_stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
$delete_stmt->bind_param("i", $brand_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = 'Brand deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting brand: ' . $conn->error;
}

$delete_stmt->close(); // Close the second statement
header('Location: brands.php');
exit; 