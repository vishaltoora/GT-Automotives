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

// Get location ID
$location_id = intval($_GET['id'] ?? 0);

if ($location_id <= 0) {
    $_SESSION['error_message'] = 'Invalid location ID';
    header('Location: locations.php');
    exit;
}

// Check if location exists
    $stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
    $stmt->bind_param("i", $location_id);
    $result = $stmt->get_result();
    $location = $result->fetch_assoc();

if (!$location) {
    $_SESSION['error_message'] = 'Location not found';
    header('Location: locations.php');
    exit;
}

// Check if location is being used by any products or services
$tires_count_query = "SELECT COUNT(*) as count FROM tires WHERE location_id = ?";
$tires_stmt = $conn->prepare($tires_count_query);
$tires_stmt->bind_param("i", $location_id);
$tires_result = $tires_stmt->get_result();
$tires_count = $tires_result->fetch_assoc()['count'];

$services_count_query = "SELECT COUNT(*) as count FROM services WHERE location_id = ?";
$services_stmt = $conn->prepare($services_count_query);
$services_stmt->bind_param("i", $location_id);
$services_result = $services_stmt->get_result();
$services_count = $services_result->fetch_assoc()['count'];

$total_items = $tires_count + $services_count;

if ($total_items > 0) {
    $_SESSION['error_message'] = "Cannot delete location '{$location['name']}' because it contains {$total_items} item(s). Please move or delete these items first.";
    header('Location: locations.php');
    exit;
}

// Delete the location
    $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->bind_param("i", $location_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Location "' . htmlspecialchars($location['name']) . '" deleted successfully';
} else {
    $_SESSION['error_message'] = 'Error deleting location: ' . $conn->error;
}

header('Location: locations.php');
exit;
?> 