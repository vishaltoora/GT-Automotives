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

// Get sale details with creator info
$sale_query = "SELECT s.*, u.username as created_by_name FROM sales s 
               LEFT JOIN users u ON s.created_by = u.id 
               WHERE s.id = ?";
$sale_stmt = $conn->prepare($sale_query);
$sale_stmt->bindValue(1, $sale_id, SQLITE3_INTEGER);
$sale_result = $sale_stmt->execute();
$sale = $sale_result->fetchArray(SQLITE3_ASSOC);

if (!$sale) {
    header('Location: sales.php');
    exit;
}

// Get sale items with product and service details
$items_query = "SELECT si.*, 
                       t.name as tire_name, t.size, b.name as brand_name,
                       s.name as service_name, sc.name as service_category
                FROM sale_items si 
                LEFT JOIN tires t ON si.tire_id = t.id 
                LEFT JOIN brands b ON t.brand_id = b.id 
                LEFT JOIN services s ON si.service_id = s.id 
                LEFT JOIN service_categories sc ON s.category = sc.name 
                WHERE si.sale_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bindValue(1, $sale_id, SQLITE3_INTEGER);
$items_result = $items_stmt->execute();

$sale_items = [];
while ($row = $items_result->fetchArray(SQLITE3_ASSOC)) {
    $sale_items[] = $row;
}

// Set page title
$page_title = 'View Sale - ' . $sale['invoice_number'];

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Sale Details</h1>
    <div class="admin-actions">
        <a href="edit_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Sale
        </a>
        <a href="generate_invoice.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">
            <i class="fas fa-file-invoice"></i> Generate Invoice
        </a>
        <a href="delete_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-danger delete-confirm" 
           onclick="return confirm('Are you sure you want to delete this sale? This action cannot be undone.')">
            <i class="fas fa-trash"></i> Delete Sale
        </a>
        <a href="sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>
</div>

<?php if (isset($_GET['created']) && $_GET['created'] == '1'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Sale created successfully! Invoice number: <strong><?php echo htmlspecialchars($sale['invoice_number']); ?></strong>
    </div>
<?php endif; ?>

<div class="sale-details-grid">
    <!-- Sale Information -->
    <div class="sale-section">
        <h3><i class="fas fa-receipt"></i> Sale Information</h3>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Invoice Number:</label>
                <span class="invoice-number"><?php echo htmlspecialchars($sale['invoice_number']); ?></span>
            </div>
            
            <div class="info-item">
                <label>Date:</label>
                <span><?php echo date('F j, Y \a\t g:i A', strtotime($sale['created_at'])); ?></span>
            </div>
            
            <div class="info-item">
                <label>Payment Status:</label>
                <span class="status-badge status-<?php echo $sale['payment_status']; ?>">
                    <?php 
                    $status_icon = '';
                    switch($sale['payment_status']) {
                        case 'pending':
                            $status_icon = '⏳ ';
                            break;
                        case 'paid':
                            $status_icon = '✅ ';
                            break;
                        case 'cancelled':
                            $status_icon = '❌ ';
                            break;
                    }
                    echo $status_icon . ucfirst($sale['payment_status']); 
                    ?>
                </span>
            </div>
            
            <div class="info-item">
                <label>Payment Method:</label>
                <span><?php echo ucfirst(str_replace('_', ' ', $sale['payment_method'])); ?></span>
            </div>
            
            <div class="info-item">
                <label>Created By:</label>
                <span><?php echo htmlspecialchars($sale['created_by_name']); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Customer Information -->
    <div class="sale-section">
        <h3><i class="fas fa-user"></i> Customer Information</h3>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Name:</label>
                <span><?php echo htmlspecialchars($sale['customer_name']); ?></span>
            </div>
            
            <?php if ($sale['customer_business_name']): ?>
                <div class="info-item">
                    <label>Business/Company:</label>
                    <span><?php echo htmlspecialchars($sale['customer_business_name']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($sale['customer_email']): ?>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($sale['customer_email']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($sale['customer_phone']): ?>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($sale['customer_phone']); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="info-item">
                <label>Address:</label>
                <span><?php echo $sale['customer_address'] ? nl2br(htmlspecialchars($sale['customer_address'])) : 'Prince George, BC'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Sale Items -->
<div class="sale-section">
    <h3><i class="fas fa-shopping-cart"></i> Sale Items</h3>
    
    <?php if (count($sale_items) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item Type</th>
                    <th>Item Details</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale_items as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['tire_id']): ?>
                                <span class="badge badge-primary">Product</span>
                            <?php elseif ($item['service_id']): ?>
                                <span class="badge badge-success">Service</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Unknown</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="item-info">
                                <?php if ($item['tire_id']): ?>
                                    <strong><?php echo htmlspecialchars($item['brand_name'] . ' - ' . $item['tire_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                <?php elseif ($item['service_id']): ?>
                                    <strong><?php echo htmlspecialchars($item['service_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">Category: <?php echo htmlspecialchars($item['service_category']); ?></small>
                                <?php else: ?>
                                    <em>Unknown item</em>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td><strong>$<?php echo number_format($item['total_price'], 2); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No items found for this sale.</p>
    <?php endif; ?>
</div>

<!-- Totals -->
<div class="sale-section">
    <h3><i class="fas fa-calculator"></i> Totals</h3>
    
    <div class="totals-grid">
        <div class="total-item">
            <span>Subtotal:</span>
            <span>$<?php echo number_format($sale['subtotal'], 2); ?></span>
        </div>
        
        <div class="total-item">
            <span>GST (<?php echo ($sale['gst_rate'] * 100); ?>%):</span>
            <span>$<?php echo number_format($sale['gst_amount'], 2); ?></span>
        </div>
        
        <?php if ($sale['pst_rate'] > 0): ?>
        <div class="total-item">
            <span>PST (<?php echo ($sale['pst_rate'] * 100); ?>%):</span>
            <span>$<?php echo number_format($sale['pst_amount'], 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="total-item total-grand">
            <span>Total Amount:</span>
            <span>$<?php echo number_format($sale['total_amount'], 2); ?></span>
        </div>
    </div>
</div>

<?php if ($sale['notes']): ?>
    <div class="sale-section">
        <h3><i class="fas fa-sticky-note"></i> Notes</h3>
        <div class="notes-content">
            <?php echo nl2br(htmlspecialchars($sale['notes'])); ?>
        </div>
    </div>
<?php endif; ?>

<style>
.sale-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.sale-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.sale-section h3 {
    margin: 0 0 1.5rem 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-grid {
    display: grid;
    gap: 1rem;
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
    min-width: 120px;
}

.info-item span {
    text-align: right;
    color: #666;
}

.invoice-number {
    font-weight: bold;
    color: #007bff !important;
    font-size: 1.1rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-paid {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.product-info {
    display: flex;
    flex-direction: column;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

.item-info {
    display: flex;
    flex-direction: column;
}

.item-info strong {
    color: #333;
}

.item-info small {
    color: #666;
    font-size: 0.85rem;
}

.totals-grid {
    display: grid;
    gap: 1rem;
}

.total-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 1.1rem;
}

.total-grand {
    background: #e3f2fd;
    font-weight: bold;
    font-size: 1.3rem;
    color: #0d47a1;
}

.notes-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    line-height: 1.6;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .sale-details-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .info-item span {
        text-align: left;
    }
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 