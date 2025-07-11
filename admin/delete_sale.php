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

// Get sale details
$sale_query = "SELECT * FROM sales WHERE id = ?";
$sale_stmt = $conn->prepare($sale_query);
$sale_stmt->bindValue(1, $sale_id, SQLITE3_INTEGER);
$sale_result = $sale_stmt->execute();
$sale = $sale_result->fetchArray(SQLITE3_ASSOC);

if (!$sale) {
    $_SESSION['error_message'] = 'Sale not found.';
    header('Location: sales.php');
    exit;
}

// Handle form submission for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->exec('BEGIN TRANSACTION');
        
        // Delete sale items first (due to foreign key constraint)
        $delete_items = $conn->prepare("DELETE FROM sale_items WHERE sale_id = ?");
        $delete_items->bindValue(1, $sale_id, SQLITE3_INTEGER);
        $delete_items->execute();
        
        // Delete the sale
        $delete_sale = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $delete_sale->bindValue(1, $sale_id, SQLITE3_INTEGER);
        $delete_sale->execute();
        
        // Commit transaction
        $conn->exec('COMMIT');
        
        // Set success message and redirect
        $_SESSION['success_message'] = 'Sale deleted successfully!';
        header('Location: sales.php');
        exit;
        
    } catch (Exception $e) {
        $conn->exec('ROLLBACK');
        $_SESSION['error_message'] = 'Error deleting sale: ' . $e->getMessage();
        header('Location: view_sale.php?id=' . $sale_id);
        exit;
    }
}

// Set page title
$page_title = 'Delete Sale - ' . $sale['invoice_number'];

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Delete Sale</h1>
    <div class="admin-actions">
        <a href="view_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sale
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Warning:</strong> You are about to delete the sale "<?php echo htmlspecialchars($sale['invoice_number']); ?>".
</div>

<div class="sale-deletion-details">
    <h3>Sale Information</h3>
    <div class="info-grid">
        <div class="info-item">
            <label>Invoice Number:</label>
            <span><?php echo htmlspecialchars($sale['invoice_number']); ?></span>
        </div>
        <div class="info-item">
            <label>Customer:</label>
            <span><?php echo htmlspecialchars($sale['customer_name']); ?></span>
        </div>
        <div class="info-item">
            <label>Total Amount:</label>
            <span>$<?php echo number_format($sale['total_amount'], 2); ?></span>
        </div>
        <div class="info-item">
            <label>Date Created:</label>
            <span><?php echo date('F j, Y \a\t g:i A', strtotime($sale['created_at'])); ?></span>
        </div>
    </div>
    
    <div class="deletion-effects">
        <h4>What will happen when you delete this sale:</h4>
        <ul>
            <li><i class="fas fa-trash"></i> The sale record will be permanently deleted</li>
            <li><i class="fas fa-exclamation-triangle"></i> This action cannot be undone</li>
            <li><i class="fas fa-file-invoice"></i> The invoice will no longer be accessible</li>
            <li><i class="fas fa-info-circle"></i> Inventory quantities will remain unchanged</li>
        </ul>
    </div>
</div>

<form method="POST" class="deletion-form">
    <div class="form-actions">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete this sale? This action cannot be undone.')">
            <i class="fas fa-trash"></i> Delete Sale Permanently
        </button>
        <a href="view_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</form>

<style>
.sale-deletion-details {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.sale-deletion-details h3 {
    margin: 0 0 1rem 0;
    color: #333;
    border-bottom: 2px solid #dc3545;
    padding-bottom: 0.5rem;
}

.info-grid {
    display: grid;
    gap: 1rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.info-item label {
    font-weight: 600;
    color: #333;
}

.info-item span {
    color: #666;
}

.deletion-effects {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 1rem;
}

.deletion-effects h4 {
    margin: 0 0 1rem 0;
    color: #856404;
    font-size: 1rem;
}

.deletion-effects ul {
    margin: 0;
    padding-left: 1.5rem;
}

.deletion-effects li {
    margin-bottom: 0.5rem;
    color: #856404;
}

.deletion-effects li i {
    width: 16px;
    margin-right: 0.5rem;
    color: #856404;
}

.deletion-form {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 