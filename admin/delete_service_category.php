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

// Get category ID
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['error_message'] = 'Invalid category ID';
    header('Location: service_categories.php');
    exit;
}

// Check if category exists
$category_query = "SELECT name FROM service_categories WHERE id = ?";
$stmt = $conn->prepare($category_query);
$stmt->bindValue(1, $category_id, SQLITE3_INTEGER);
$category_result = $stmt->execute();
$category = $category_result->fetchArray(SQLITE3_ASSOC);

if (!$category) {
    $_SESSION['error_message'] = 'Category not found';
    header('Location: service_categories.php');
    exit;
}

// Check if category has any services
$services_query = "SELECT COUNT(*) as count FROM services WHERE category = ?";
$stmt = $conn->prepare($services_query);
$stmt->bindValue(1, $category['name']);
$services_result = $stmt->execute();
$services_count = $services_result->fetchArray(SQLITE3_ASSOC)['count'];

if ($services_count > 0) {
    $_SESSION['error_message'] = 'Cannot delete category that has services. Please move or delete all services in this category first.';
    header('Location: service_categories.php');
    exit;
}

// Delete the category
$delete_query = "DELETE FROM service_categories WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bindValue(1, $category_id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Category "' . htmlspecialchars($category['name']) . '" deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting category: ' . $conn->lastErrorMsg();
}

header('Location: service_categories.php');
exit;
?> 