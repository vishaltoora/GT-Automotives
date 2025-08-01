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
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();

if (!$service) {
    $_SESSION['error_message'] = 'Service not found';
    header('Location: services.php');
    exit;
}

// Check if service is used in any sales
$sales_query = "SELECT COUNT(*) as count FROM sale_items WHERE service_id = ?";
$stmt = $conn->prepare($sales_query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$sales_result = $stmt->get_result();
$sales_count = $sales_result->fetch_assoc()['count'];
$stmt->close();

if ($sales_count > 0) {
    $_SESSION['error_message'] = 'Cannot delete service that has been used in sales. Consider deactivating it instead.';
    header('Location: services.php');
    exit;
}

// Delete the service
$stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Service "' . htmlspecialchars($service['name']) . '" deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting service: ' . $conn->error;
}

$stmt->close();
header('Location: services.php');
exit;
?> 