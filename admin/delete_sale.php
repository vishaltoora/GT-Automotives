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

// Get sale ID from URL
$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$sale_id) {
    header('Location: sales.php');
    exit;
}

// Get sale details first to check if it exists
$sale_query = "SELECT * FROM sales WHERE id = ?";
$sale_stmt = $conn->prepare($sale_query);
$sale_stmt->bind_param("i", $sale_id);
$sale_stmt->execute();
$sale_result = $sale_stmt->get_result();
$sale = $sale_result->fetch_assoc();
$sale_stmt->close();

if (!$sale) {
    $_SESSION['error_message'] = 'Sale not found.';
    header('Location: sales.php');
    exit;
}

try {
    // Start transaction
    $conn->query('START TRANSACTION');
    
    // If sale is not cancelled, restore inventory for products
    if ($sale['payment_status'] !== 'cancelled') {
        // Get sale items to restore inventory (only for products, not services)
        $restore_items_query = "SELECT tire_id, quantity FROM sale_items WHERE sale_id = ? AND tire_id IS NOT NULL";
        $restore_stmt = $conn->prepare($restore_items_query);
        $restore_stmt->bind_param("i", $sale_id);
        $restore_stmt->execute();
        $restore_result = $restore_stmt->get_result();
        
        while ($item = $restore_result->fetch_assoc()) {
            if ($item['tire_id']) {
                $restore_stock = $conn->prepare("
                    UPDATE tires SET stock_quantity = stock_quantity + ? WHERE id = ?
                ");
                $restore_stock->bind_param("ii", $item['quantity'], $item['tire_id']);
                $restore_stock->execute();
                $restore_stock->close();
            }
        }
        $restore_stmt->close();
    }
    
    // Delete sale items first (due to foreign key constraint)
    $delete_items_query = "DELETE FROM sale_items WHERE sale_id = ?";
    $delete_items_stmt = $conn->prepare($delete_items_query);
    $delete_items_stmt->bind_param("i", $sale_id);
    $delete_items_stmt->execute();
    $delete_items_stmt->close();
    
    // Delete the sale record
    $delete_sale_query = "DELETE FROM sales WHERE id = ?";
    $delete_sale_stmt = $conn->prepare($delete_sale_query);
    $delete_sale_stmt->bind_param("i", $sale_id);
    $delete_sale_stmt->execute();
    $delete_sale_stmt->close();
    
    // Commit transaction
    $conn->query('COMMIT');
    
    // Set success message
    $_SESSION['success_message'] = 'Sale deleted successfully!';
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->query('ROLLBACK');
    $_SESSION['error_message'] = 'Error deleting sale: ' . $e->getMessage();
}

// Redirect back to sales list
header('Location: sales.php');
exit;
?> 