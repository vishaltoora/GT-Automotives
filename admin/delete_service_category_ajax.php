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

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form data
    $id = intval($_POST['id'] ?? 0);
    
    // Validate input
    if ($id <= 0) {
        throw new Exception('Invalid category ID');
    }
    
    // Check if category has any services
    $check_query = "SELECT COUNT(*) as count FROM services WHERE category = (SELECT name FROM service_categories WHERE id = ?)";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        throw new Exception('Cannot delete category that has services');
    }
    
    // Delete the category
    $stmt = $conn->prepare("DELETE FROM service_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Service category deleted successfully';
    } else {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?> 