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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-tools');
    
    // Validate input
    $errors = [];
    
    if ($id <= 0) {
        $errors[] = 'Invalid category ID';
    }
    
    if (empty($name)) {
        $errors[] = 'Category name is required';
    }
    
    if (empty($icon)) {
        $errors[] = 'Icon is required';
    }
    
    // Check if category name already exists (excluding current category)
    $check_query = "SELECT COUNT(*) as count FROM service_categories WHERE name = ? AND id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        $errors[] = 'Category name already exists';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        echo json_encode($response);
        exit;
    }
    
    // Update the category
    $stmt = $conn->prepare("UPDATE service_categories SET name = ?, description = ?, icon = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $icon, $id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Service category updated successfully';
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