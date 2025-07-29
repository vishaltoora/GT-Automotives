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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No product ID provided';
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Check if product exists
$stmt = $conn->prepare("SELECT * FROM tires WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Product not found';
    header('Location: products.php');
    exit;
}

// Close the first statement
$stmt->close();

// Delete the product (cascade will handle related records)
$delete_stmt = $conn->prepare("DELETE FROM tires WHERE id = ?");
$delete_stmt->bind_param("i", $product_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = 'Product deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting product: ' . $conn->error();
}

$delete_stmt->close();
header('Location: products.php');
exit;
?> 