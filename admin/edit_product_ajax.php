<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include database connection
    if (file_exists('../includes/db_connect.php')) {
        require_once '../includes/db_connect.php';
    }

    if (file_exists('../includes/auth.php')) {
        require_once '../includes/auth.php';
    }

    // Require login
    requireLogin();

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get form data
    $product_id = intval($_POST['product_id'] ?? 0);
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $location_id = intval($_POST['location_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $condition = $_POST['condition'] ?? 'new';
    
    // Validate input
    $errors = [];
    
    if ($product_id <= 0) {
        $errors[] = 'Invalid product ID';
    }
    
    if ($brand_id <= 0) {
        $errors[] = 'Brand is required';
    }
    
    if ($location_id <= 0) {
        $errors[] = 'Location is required';
    }
    
    if (empty($name)) {
        $errors[] = 'Product type is required';
    }
    
    if (empty($size)) {
        $errors[] = 'Size is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if ($stock_quantity < 0) {
        $errors[] = 'Stock quantity cannot be negative';
    }
    
    if (!in_array($condition, ['new', 'used'])) {
        $errors[] = 'Invalid condition selected';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // If no errors, update the product
    if (isset($conn)) {
        $update_query = "UPDATE tires SET brand_id = ?, name = ?, size = ?, price = ?, description = ?, stock_quantity = ?, `condition` = ?, location_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        
        if ($stmt) {
            $stmt->bind_param("issdsisi", $brand_id, $name, $size, $price, $description, $stock_quantity, $condition, $location_id, $product_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Product updated successfully!',
                    'product_id' => $product_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $stmt->error]);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in edit_product_ajax.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}
?> 