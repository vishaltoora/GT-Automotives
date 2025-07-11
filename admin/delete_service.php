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

// Get service ID
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id <= 0) {
    $_SESSION['error_message'] = 'Invalid service ID';
    header('Location: services.php');
    exit;
}

// Check if service exists
$service_query = "SELECT name FROM services WHERE id = ?";
$stmt = $conn->prepare($service_query);
$stmt->bindValue(1, $service_id, SQLITE3_INTEGER);
$service_result = $stmt->execute();
$service = $service_result->fetchArray(SQLITE3_ASSOC);

if (!$service) {
    $_SESSION['error_message'] = 'Service not found';
    header('Location: services.php');
    exit;
}

// Check if service is used in any sales
$sales_query = "SELECT COUNT(*) as count FROM sale_items WHERE service_id = ?";
$stmt = $conn->prepare($sales_query);
$stmt->bindValue(1, $service_id, SQLITE3_INTEGER);
$sales_result = $stmt->execute();
$sales_count = $sales_result->fetchArray(SQLITE3_ASSOC)['count'];

if ($sales_count > 0) {
    $_SESSION['error_message'] = 'Cannot delete service that has been used in sales. Consider deactivating it instead.';
    header('Location: services.php');
    exit;
}

// Delete the service
$delete_query = "DELETE FROM services WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bindValue(1, $service_id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Service "' . htmlspecialchars($service['name']) . '" deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting service: ' . $conn->lastErrorMsg();
}

header('Location: services.php');
exit;
?> 