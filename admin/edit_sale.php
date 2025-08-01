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
$stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result = $stmt->get_result();
$sale = $result->fetch_assoc();
$stmt->close();

if (!$sale) {
    header('Location: sales.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['customer_name'])) {
            throw new Exception('Customer name is required');
        }
        
        // Validate date and time
        if (empty($_POST['created_date'])) {
            throw new Exception('Invoice date is required');
        }
        
        if (empty($_POST['created_time'])) {
            throw new Exception('Invoice time is required');
        }
        
        // Validate date format
        $date_obj = DateTime::createFromFormat('Y-m-d', $_POST['created_date']);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $_POST['created_date']) {
            throw new Exception('Invalid date format');
        }
        
        // Validate time format
        $time_obj = DateTime::createFromFormat('H:i', $_POST['created_time']);
        if (!$time_obj || $time_obj->format('H:i') !== $_POST['created_time']) {
            throw new Exception('Invalid time format');
        }
        
        // Get tax rates from form (convert percentage to decimal)
        $gst_rate = isset($_POST['gst_rate']) ? (float)$_POST['gst_rate'] / 100 : $sale['gst_rate'];
        $pst_rate = isset($_POST['pst_rate']) ? (float)$_POST['pst_rate'] / 100 : $sale['pst_rate'];
        
        // Recalculate tax amounts and total
        $subtotal = $sale['subtotal']; // Keep original subtotal
        $gst_amount = $subtotal * $gst_rate;
        $pst_amount = $subtotal * $pst_rate;
        $total_amount = $subtotal + $gst_amount + $pst_amount;
        
        // Handle inventory changes if payment status changes
        $old_payment_status = $sale['payment_status'];
        $new_payment_status = $_POST['payment_status'] ?? 'pending';
        
        // If sale is being cancelled, restore inventory
        if ($old_payment_status !== 'cancelled' && $new_payment_status === 'cancelled') {
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
        
        // If sale is being reactivated from cancelled, deduct inventory again
        if ($old_payment_status === 'cancelled' && $new_payment_status !== 'cancelled') {
            // Get sale items to deduct inventory (only for products, not services)
            $deduct_items_query = "SELECT tire_id, quantity FROM sale_items WHERE sale_id = ? AND tire_id IS NOT NULL";
            $deduct_stmt = $conn->prepare($deduct_items_query);
            $deduct_stmt->bind_param("i", $sale_id);
            $deduct_stmt->execute();
            $deduct_result = $deduct_stmt->get_result();
            
            while ($item = $deduct_result->fetch_assoc()) {
                if ($item['tire_id']) {
                    $deduct_stock = $conn->prepare("
                        UPDATE tires SET stock_quantity = stock_quantity - ? WHERE id = ?
                    ");
                    $deduct_stock->bind_param("ii", $item['quantity'], $item['tire_id']);
                    $deduct_stock->execute();
                    $deduct_stock->close();
                }
            }
            $deduct_stmt->close();
        }
        
        // Update sale record
        $update_stmt = $conn->prepare("
            UPDATE sales SET 
                customer_name = ?, 
                customer_business_name = ?,
                customer_email = ?, 
                customer_phone = ?, 
                customer_address = ?,
                gst_rate = ?,
                gst_amount = ?,
                pst_rate = ?,
                pst_amount = ?,
                total_amount = ?,
                payment_method = ?,
                payment_status = ?,
                notes = ?,
                created_at = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        // Format the date and time for database
        $created_at = $_POST['created_date'] . ' ' . $_POST['created_time'] . ':00';
        
        // Store all values in variables for bind_param
        $customer_name = $_POST['customer_name'];
        $customer_business_name = $_POST['customer_business_name'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $customer_phone = $_POST['customer_phone'] ?? '';
        $customer_address = $_POST['customer_address'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'cash_with_invoice';
        $payment_status = $_POST['payment_status'] ?? 'pending';
        $notes = $_POST['notes'] ?? '';
        
        $update_stmt->bind_param("sssssssssssssss",
            $customer_name,
            $customer_business_name,
            $customer_email,
            $customer_phone,
            $customer_address,
            $gst_rate,
            $gst_amount,
            $pst_rate,
            $pst_amount,
            $total_amount,
            $payment_method,
            $payment_status,
            $notes,
            $created_at,
            $sale_id
        );
        
        $update_stmt->execute();
        
        // Set success message and redirect
        $_SESSION['success_message'] = 'Sale updated successfully!';
        header("Location: view_sale.php?id=" . $sale_id);
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Set page title
$page_title = 'Edit Sale - ' . $sale['invoice_number'];

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Edit Sale</h1>
    <div class="admin-actions">
        <a href="view_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sale
        </a>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<form method="POST" class="admin-form">
    <div class="form-row">
        <div class="form-group">
            <label for="invoice_number">Invoice Number</label>
            <input type="text" id="invoice_number" value="<?php echo htmlspecialchars($sale['invoice_number']); ?>" readonly>
            <small>Invoice number cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label for="customer_name">Customer Name *</label>
            <input type="text" id="customer_name" name="customer_name" required 
                   value="<?php echo htmlspecialchars($sale['customer_name']); ?>">
        </div>
    </div>
    
    <div class="date-time-group">
        <h4><i class="fas fa-calendar-alt"></i> Invoice Date & Time</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="created_date">Invoice Date</label>
                <input type="date" id="created_date" name="created_date" 
                       value="<?php echo date('Y-m-d', strtotime($sale['created_at'])); ?>" required>
                <small>Date when the invoice was created</small>
            </div>
            
            <div class="form-group">
                <label for="created_time">Invoice Time</label>
                <input type="time" id="created_time" name="created_time" 
                       value="<?php echo date('H:i', strtotime($sale['created_at'])); ?>" required>
                <small>Time when the invoice was created</small>
            </div>
        </div>
        <div class="alert alert-info" style="margin-top: 1rem; padding: 0.75rem; font-size: 0.9rem;">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> You can modify the invoice date and time. This will update when the invoice appears to have been created.
        </div>
    </div>
    
    <div class="form-group">
        <label for="customer_business_name">Business/Company Name</label>
        <input type="text" id="customer_business_name" name="customer_business_name" 
               value="<?php echo htmlspecialchars($sale['customer_business_name'] ?? ''); ?>" 
               placeholder="Optional business or company name">
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="customer_email">Email</label>
            <input type="email" id="customer_email" name="customer_email" 
                   value="<?php echo htmlspecialchars($sale['customer_email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="customer_phone">Phone</label>
            <input type="tel" id="customer_phone" name="customer_phone" 
                   value="<?php echo htmlspecialchars($sale['customer_phone'] ?? ''); ?>">
        </div>
    </div>
    
    <div class="form-group">
        <label for="customer_address">Address</label>
        <textarea id="customer_address" name="customer_address" rows="3" placeholder="Prince George, BC"><?php echo htmlspecialchars($sale['customer_address'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method">
                <option value="cash_with_invoice" <?php echo $sale['payment_method'] === 'cash_with_invoice' ? 'selected' : ''; ?>>Cash with Invoice</option>
                <option value="cash_without_invoice" <?php echo $sale['payment_method'] === 'cash_without_invoice' ? 'selected' : ''; ?>>Cash without Invoice</option>
                <option value="credit_card" <?php echo $sale['payment_method'] === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                <option value="debit_card" <?php echo $sale['payment_method'] === 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                <option value="check" <?php echo $sale['payment_method'] === 'check' ? 'selected' : ''; ?>>Check</option>
                <option value="bank_transfer" <?php echo $sale['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="payment_status">Payment Status</label>
            <select id="payment_status" name="payment_status" data-status="<?php echo $sale['payment_status']; ?>">
                <option value="pending" <?php echo $sale['payment_status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                <option value="paid" <?php echo $sale['payment_status'] === 'paid' ? 'selected' : ''; ?>>✅ Paid</option>
                <option value="cancelled" <?php echo $sale['payment_status'] === 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($sale['notes'] ?? ''); ?></textarea>
    </div>
    
    <!-- Tax Rates Section -->
    <div class="tax-rates-section">
        <h3>Tax Rates</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="gst_rate">GST Rate (%)</label>
                <input type="number" id="gst_rate" name="gst_rate" 
                       value="<?php echo ($sale['gst_rate'] * 100); ?>" 
                       min="0" max="100" step="0.01" onchange="calculateTotals()">
            </div>
            <div class="form-group">
                <label for="pst_rate">PST Rate (%)</label>
                <input type="number" id="pst_rate" name="pst_rate" 
                       value="<?php echo ($sale['pst_rate'] * 100); ?>" 
                       min="0" max="100" step="0.01" onchange="calculateTotals()">
            </div>
        </div>
    </div>
    
    <!-- Sale Summary (Dynamic) -->
    <div class="sale-summary">
        <h3>Sale Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <label>Subtotal:</label>
                <span>$<?php echo number_format($sale['subtotal'], 2); ?></span>
            </div>
            <div class="summary-item">
                <label>GST (<span id="gst-rate-display"><?php echo ($sale['gst_rate'] * 100); ?></span>%):</label>
                <span id="gst-amount">$<?php echo number_format($sale['gst_amount'], 2); ?></span>
            </div>
            <div class="summary-item">
                <label>PST (<span id="pst-rate-display"><?php echo ($sale['pst_rate'] * 100); ?></span>%):</label>
                <span id="pst-amount">$<?php echo number_format($sale['pst_amount'], 2); ?></span>
            </div>
            <div class="summary-item total">
                <label>Total Amount:</label>
                <span id="total-amount">$<?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
        </div>
        <small>Tax rates can be modified. Totals will update automatically.</small>
    </div>
    
    <div class="form-submit">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Sale
        </button>
        <a href="view_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.tax-rates-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
}

.tax-rates-section h3 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1.2rem;
}

.sale-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
}

.sale-summary h3 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1.2rem;
}

.summary-grid {
    display: grid;
    gap: 0.75rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.summary-item.total {
    font-weight: bold;
    font-size: 1.1rem;
    color: #243c55;
    border-bottom: none;
    border-top: 2px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 0.5rem;
}

.summary-item label {
    font-weight: 600;
    color: #333;
}

.summary-item span {
    color: #666;
}

.summary-item.total span {
    color: #243c55;
    font-weight: bold;
}

/* Enhanced form styling for select elements */
.admin-form select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    background-color: white;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    height: auto;
    min-height: 2.75rem;
    line-height: 1.5;
}

.admin-form select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.admin-form select:hover {
    border-color: #007bff;
}

/* Payment status option colors */
.admin-form select#payment_status option[value="paid"] {
    background-color: #d4edda;
    color: #155724;
    font-weight: 600;
}

.admin-form select#payment_status option[value="pending"] {
    background-color: #fff3cd;
    color: #856404;
    font-weight: 600;
}

.admin-form select#payment_status option[value="cancelled"] {
    background-color: #f8d7da;
    color: #721c24;
    font-weight: 600;
}

/* Payment status dropdown background colors based on selected value */
.admin-form select#payment_status[data-status="paid"] {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.admin-form select#payment_status[data-status="pending"] {
    background-color: #fff3cd;
    color: #856404;
    border-color: #ffeaa7;
}

.admin-form select#payment_status[data-status="cancelled"] {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

/* Date and time input styling */
.admin-form input[type="date"],
.admin-form input[type="time"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    background-color: white;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    height: auto;
    min-height: 2.75rem;
    line-height: 1.5;
}

.admin-form input[type="date"]:focus,
.admin-form input[type="time"]:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.admin-form input[type="date"]:hover,
.admin-form input[type="time"]:hover {
    border-color: #007bff;
}

/* Date and time field group styling */
.date-time-group {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #007bff;
    margin-bottom: 1rem;
}

.date-time-group h4 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
}

.date-time-group h4 i {
    color: #007bff;
    margin-right: 0.5rem;
}

.date-time-group .form-group small {
    color: #666;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

/* Ensure consistent sizing with other form elements */
.admin-form input,
.admin-form select,
.admin-form textarea {
    font-family: inherit;
    font-size: 1rem;
    line-height: 1.5;
}

/* Make sure form groups have consistent spacing */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}
</style>

<script>
function calculateTotals() {
    const subtotal = <?php echo $sale['subtotal']; ?>;
    const gstRate = parseFloat(document.getElementById('gst_rate').value) / 100;
    const pstRate = parseFloat(document.getElementById('pst_rate').value) / 100;
    
    const gstAmount = subtotal * gstRate;
    const pstAmount = subtotal * pstRate;
    const totalAmount = subtotal + gstAmount + pstAmount;
    
    // Update displays
    document.getElementById('gst-rate-display').textContent = (gstRate * 100).toFixed(2);
    document.getElementById('pst-rate-display').textContent = (pstRate * 100).toFixed(2);
    document.getElementById('gst-amount').textContent = '$' + gstAmount.toFixed(2);
    document.getElementById('pst-amount').textContent = '$' + pstAmount.toFixed(2);
    document.getElementById('total-amount').textContent = '$' + totalAmount.toFixed(2);
}

// Function to update payment status dropdown color
function updatePaymentStatusColor() {
    const paymentStatusSelect = document.getElementById('payment_status');
    const selectedValue = paymentStatusSelect.value;
    
    // Remove all status classes
    paymentStatusSelect.removeAttribute('data-status');
    
    // Add the appropriate status class
    if (selectedValue === 'paid' || selectedValue === 'pending' || selectedValue === 'cancelled') {
        paymentStatusSelect.setAttribute('data-status', selectedValue);
        paymentStatusSelect.classList.add('status-' + selectedValue);
        
        // Update chip text
        // The status chip is removed, so this part is no longer needed.
    }
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
    
    // Set initial payment status color
    updatePaymentStatusColor();
    
    // Add event listener for payment status changes
    document.getElementById('payment_status').addEventListener('change', updatePaymentStatusColor);
    
    // Position the status chip inside the select box
    // The status chip is removed, so this part is no longer needed.
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 