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

    // Get product ID
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    // Get product data
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT * FROM tires WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $product = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'brand_id' => $product['brand_id'],
            'location_id' => $product['location_id'],
            'name' => $product['name'],
            'size' => $product['size'],
            'price' => $product['price'],
            'stock_quantity' => $product['stock_quantity'],
            'condition' => $product['condition'],
            'description' => $product['description']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in get_product_data.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}
?> 