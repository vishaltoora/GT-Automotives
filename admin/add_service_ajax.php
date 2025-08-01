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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $duration_minutes = intval($_POST['duration_minutes'] ?? 60);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Service name is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if ($duration_minutes <= 0) {
        $errors[] = 'Duration must be greater than zero';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        echo json_encode($response);
        exit;
    }
    
    // Insert the service
    $stmt = $conn->prepare("INSERT INTO services (name, description, price, category, duration_minutes, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsii", $name, $description, $price, $category, $duration_minutes, $is_active);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Service added successfully';
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