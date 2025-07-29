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
    $stmt = $conn->prepare("SELECT * FROM service_categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $category_result = $stmt->get_result();
    $category = $category_result->fetch_assoc();

if (!$category) {
    $_SESSION['error_message'] = 'Category not found';
    header('Location: service_categories.php');
    exit;
}

// Check if category has any services
    $services_stmt = $conn->prepare("SELECT COUNT(*) as count FROM services WHERE category_id = ?");
    $services_stmt->bind_param("i", $category['name']);
    $services_result = $services_stmt->get_result();
    $services_count = $services_result->fetch_assoc()['count'];

if ($services_count > 0) {
    $_SESSION['error_message'] = 'Cannot delete category that has services. Please move or delete all services in this category first.';
    header('Location: service_categories.php');
    exit;
}

// Delete the category
    $stmt = $conn->prepare("DELETE FROM service_categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Category "' . htmlspecialchars($category['name']) . '" deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting category: ' . $conn->error();
}

header('Location: service_categories.php');
exit;
?> 