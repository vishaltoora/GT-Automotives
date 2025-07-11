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
$query = "SELECT * FROM tires WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
$result = $stmt->execute();

if ($result->numColumns() === 0) {
    $_SESSION['error_message'] = 'Product not found';
    header('Location: products.php');
    exit;
}

// Delete the product (cascade will handle related records)
$delete_query = "DELETE FROM tires WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bindValue(1, $product_id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Product deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting product: ' . $conn->lastErrorMsg();
}

header('Location: products.php');
exit;
?> 