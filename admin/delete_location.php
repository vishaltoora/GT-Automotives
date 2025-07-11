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
$location_query = "SELECT * FROM locations WHERE id = ?";
$location_stmt = $conn->prepare($location_query);
$location_stmt->bindValue(1, $location_id, SQLITE3_INTEGER);
$location_result = $location_stmt->execute();
$location = $location_result->fetchArray(SQLITE3_ASSOC);

if (!$location) {
    $_SESSION['error_message'] = 'Location not found';
    header('Location: locations.php');
    exit;
}

// Check if location is being used by any products or services
$tires_count_query = "SELECT COUNT(*) as count FROM tires WHERE location_id = ?";
$tires_stmt = $conn->prepare($tires_count_query);
$tires_stmt->bindValue(1, $location_id, SQLITE3_INTEGER);
$tires_result = $tires_stmt->execute();
$tires_count = $tires_result->fetchArray(SQLITE3_ASSOC)['count'];

$services_count_query = "SELECT COUNT(*) as count FROM services WHERE location_id = ?";
$services_stmt = $conn->prepare($services_count_query);
$services_stmt->bindValue(1, $location_id, SQLITE3_INTEGER);
$services_result = $services_stmt->execute();
$services_count = $services_result->fetchArray(SQLITE3_ASSOC)['count'];

$total_items = $tires_count + $services_count;

if ($total_items > 0) {
    $_SESSION['error_message'] = "Cannot delete location '{$location['name']}' because it contains {$total_items} item(s). Please move or delete these items first.";
    header('Location: locations.php');
    exit;
}

// Delete the location
$delete_query = "DELETE FROM locations WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bindValue(1, $location_id, SQLITE3_INTEGER);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "Location '{$location['name']}' deleted successfully";
} else {
    $_SESSION['error_message'] = 'Error deleting location: ' . $conn->lastErrorMsg();
}

header('Location: locations.php');
exit;
?> 