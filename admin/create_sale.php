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

// Set page title
$page_title = 'Create New Sale';

// Get all products with brand names
$products_query = "SELECT t.id, t.name, t.size, t.price, t.stock_quantity, t.`condition`, b.name as brand_name, l.name as location_name 
    FROM tires t 
    LEFT JOIN brands b ON t.brand_id = b.id 
    LEFT JOIN locations l ON t.location_id = l.id 
    WHERE t.stock_quantity > 0 
    ORDER BY t.`condition` DESC, b.name, t.name";
$products_result = $conn->query($products_query);

$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Get all active services
$services_query = "SELECT s.*, sc.name as category_name FROM services s 
                  LEFT JOIN service_categories sc ON s.category = sc.name 
                  WHERE s.is_active = 1 
                  ORDER BY s.category, s.name";
$services_result = $conn->query($services_query);

$services = [];
while ($row = $services_result->fetch_assoc()) {
    $services[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['customer_name'])) {
            throw new Exception('Customer name is required');
        }
        
        if (empty($_POST['items']) || !is_array($_POST['items'])) {
            throw new Exception('At least one item is required');
        }
        
        // Generate invoice number
        $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Calculate totals
        $subtotal = 0;
        $items_data = [];
        
        // Validate inventory before processing
        $inventory_errors = [];
        
        foreach ($_POST['items'] as $item) {
            if (empty($item['item_type']) || empty($item['quantity']) || empty($item['unit_price'])) {
                continue;
            }
            
            $item_type = $item['item_type'];
            $quantity = (int)$item['quantity'];
            $unit_price = (float)$item['unit_price'];
            $total_price = $quantity * $unit_price;
            
            if ($item_type === 'product') {
                $tire_id = (int)$item['tire_id'];
                
                // Check inventory availability for products
                $stock_query = "SELECT stock_quantity, name FROM tires WHERE id = ?";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bind_param("i", $tire_id);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();
                $tire_info = $stock_result->fetch_assoc();
                $stock_stmt->close();
                
                if (!$tire_info) {
                    $inventory_errors[] = "Product not found.";
                    continue;
                }
                
                if ($tire_info['stock_quantity'] < $quantity) {
                    $inventory_errors[] = "Insufficient stock for " . $tire_info['name'] . ". Available: " . $tire_info['stock_quantity'] . ", Requested: " . $quantity;
                }
                
                $items_data[] = [
                    'item_type' => 'product',
                    'tire_id' => $tire_id,
                    'service_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'total_price' => $total_price
                ];
            } elseif ($item_type === 'service') {
                $service_id = (int)$item['service_id'];
                
                // Check if service exists and is active
                $service_query = "SELECT name FROM services WHERE id = ? AND is_active = 1";
                $service_stmt = $conn->prepare($service_query);
                $service_stmt->bind_param("i", $service_id);
                $service_stmt->execute();
                $service_result = $service_stmt->get_result();
                $service_info = $service_result->fetch_assoc();
                $service_stmt->close();
                
                if (!$service_info) {
                    $inventory_errors[] = "Service not found or inactive.";
                    continue;
                }
                
                $items_data[] = [
                    'item_type' => 'service',
                    'tire_id' => null,
                    'service_id' => $service_id,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'total_price' => $total_price
                ];
            }
            
            $subtotal += $total_price;
        }
        
        // If there are inventory errors, throw exception
        if (!empty($inventory_errors)) {
            throw new Exception('Inventory errors: ' . implode(' ', $inventory_errors));
        }
        
        if ($subtotal <= 0) {
            throw new Exception('Invalid sale amount');
        }
        
        // Get tax rates from form or use defaults (BC Canada rates)
        $gst_rate = isset($_POST['gst_rate']) ? (float)$_POST['gst_rate'] / 100 : 0.05; // Convert percentage to decimal
        $pst_rate = isset($_POST['pst_rate']) ? (float)$_POST['pst_rate'] / 100 : 0.07; // Convert percentage to decimal
        
        // Check if this is a cash without invoice transaction
        $payment_method = $_POST['payment_method'] ?? 'cash_with_invoice';
        $is_cash_without_invoice = ($payment_method === 'cash_without_invoice');
        
        if ($is_cash_without_invoice) {
            // For cash without invoice, no taxes applied
            $gst_amount = 0;
            $pst_amount = 0;
            $total_amount = $subtotal;
        } else {
            // Regular sale with taxes
            $gst_amount = $subtotal * $gst_rate;
            $pst_amount = $subtotal * $pst_rate;
            $total_amount = $subtotal + $gst_amount + $pst_amount;
        }
        
        // If a specific total was set, adjust unit prices instead of tax rates
        if (isset($_POST['set_total']) && !empty($_POST['set_total'])) {
            $set_total = (float)$_POST['set_total'];
            if ($set_total > 0) {
                $total_amount = $set_total;
                
                if ($is_cash_without_invoice) {
                    // For cash without invoice, adjust unit prices to match set total directly
                    $required_subtotal = $set_total;
                } else {
                    // Calculate the required subtotal to achieve the set total with taxes
                    $required_subtotal = $set_total / (1 + $gst_rate + $pst_rate);
                }
                
                // Calculate total quantity for adjustment
                $total_quantity = 0;
                foreach ($items_data as $item) {
                    $total_quantity += $item['quantity'];
                }
                
                if ($total_quantity > 0) {
                    // Calculate adjustment per unit
                    $adjustment_per_unit = ($required_subtotal - $subtotal) / $total_quantity;
                    
                    // Adjust unit prices in items_data
                    foreach ($items_data as &$item) {
                        $new_unit_price = max(0, $item['unit_price'] + $adjustment_per_unit);
                        $item['unit_price'] = $new_unit_price;
                        $item['total_price'] = $item['quantity'] * $new_unit_price;
                    }
                    
                    // Recalculate subtotal and tax amounts
                    $subtotal = 0;
                    foreach ($items_data as $item) {
                        $subtotal += $item['total_price'];
                    }
                    
                    if ($is_cash_without_invoice) {
                        $gst_amount = 0;
                        $pst_amount = 0;
                        $total_amount = $subtotal;
                    } else {
                        $gst_amount = $subtotal * $gst_rate;
                        $pst_amount = $subtotal * $pst_rate;
                        $total_amount = $subtotal + $gst_amount + $pst_amount;
                    }
                }
            }
        }
        
        // Get current user ID
        $user_id = getCurrentUserId();
        
        if (!$user_id) {
            throw new Exception('User session not found. Please log in again.');
        }
        
        // Check if this is a cash without invoice transaction
        $payment_method = $_POST['payment_method'] ?? 'cash_with_invoice';
        $is_cash_without_invoice = ($payment_method === 'cash_without_invoice');
        
        if ($is_cash_without_invoice) {
            // For cash without invoice, only update inventory, don't create sale record
            $conn->exec('BEGIN TRANSACTION');
            
            // Update inventory for each product
            foreach ($items_data as $item) {
                if ($item['item_type'] === 'product' && $item['tire_id']) {
                    $update_stock = $conn->prepare("
                        UPDATE tires SET stock_quantity = stock_quantity - ? WHERE id = ?
                    ");
                    $update_stock->bind_param("ii", $item['quantity'], $item['tire_id']);
                    $update_stock->execute();
                }
            }
            
            // Commit transaction
            $conn->exec('COMMIT');
            
            // Set success message and redirect
            $_SESSION['success_message'] = 'Cash transaction completed successfully! Inventory has been updated.';
            header("Location: sales.php");
            exit;
        } else {
            // Regular sale with invoice - create sale record
            // Begin transaction
            $conn->exec('BEGIN TRANSACTION');
            
            // Insert sale record
            $sale_stmt = $conn->prepare("
                INSERT INTO sales (
                    invoice_number, customer_name, customer_business_name, customer_email, customer_phone, customer_address,
                    subtotal, gst_rate, gst_amount, pst_rate, pst_amount, total_amount, payment_method, payment_status, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $sale_stmt->bind_param("ssssssddddsssi", $invoice_number, $_POST['customer_name'], $_POST['customer_business_name'] ?? '', $_POST['customer_email'] ?? '', $_POST['customer_phone'] ?? '', $_POST['customer_address'] ?? '', $subtotal, $gst_rate, $gst_amount, $pst_rate, $pst_amount, $total_amount, $_POST['payment_method'] ?? 'cash_with_invoice', $_POST['payment_status'] ?? 'pending', $_POST['notes'] ?? '', $user_id);
            
            $sale_stmt->execute();
            $sale_id = $conn->insert_id();
            
            // Insert sale items
            $item_stmt = $conn->prepare("
                INSERT INTO sale_items (sale_id, tire_id, service_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($items_data as $item) {
                $item_stmt->bind_param("iiiddd", $sale_id, $item['tire_id'], $item['service_id'], $item['quantity'], $item['unit_price'], $item['total_price']);
                $item_stmt->execute();
                
                // Update stock quantity for products only
                if ($item['item_type'] === 'product' && $item['tire_id']) {
                    $update_stock = $conn->prepare("
                        UPDATE tires SET stock_quantity = stock_quantity - ? WHERE id = ?
                    ");
                    $update_stock->bind_param("ii", $item['quantity'], $item['tire_id']);
                    $update_stock->execute();
                }
            }
            
            // Commit transaction
            $conn->exec('COMMIT');
            
            // Redirect to view sale page
            header("Location: view_sale.php?id=" . $sale_id . "&created=1");
            exit;
        }
        
    } catch (Exception $e) {
        $conn->exec('ROLLBACK');
        $error_message = $e->getMessage();
    }
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Create New Sale</h1>
    <div class="admin-actions">
        <a href="sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<form method="POST" id="saleForm">
    <div class="form-grid">
        <!-- Customer Information -->
        <div class="form-section">
            <h3><i class="fas fa-user"></i> Customer Information</h3>
            
            <div class="form-group">
                <label for="customer_name">Customer Name *</label>
                <input type="text" id="customer_name" name="customer_name" required 
                       value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="customer_business_name">Business/Company Name</label>
                <input type="text" id="customer_business_name" name="customer_business_name" 
                       value="<?php echo htmlspecialchars($_POST['customer_business_name'] ?? ''); ?>" 
                       placeholder="Optional business or company name">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" 
                           value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_phone">Phone</label>
                    <input type="tel" id="customer_phone" name="customer_phone" 
                           value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="customer_address">Address</label>
                <textarea id="customer_address" name="customer_address" rows="3" placeholder="Prince George, BC"><?php echo htmlspecialchars($_POST['customer_address'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="form-section">
            <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method">
                        <option value="cash_with_invoice" <?php echo ($_POST['payment_method'] ?? 'cash_with_invoice') === 'cash_with_invoice' ? 'selected' : ''; ?>>Cash with Invoice</option>
                        <option value="cash_without_invoice" <?php echo ($_POST['payment_method'] ?? '') === 'cash_without_invoice' ? 'selected' : ''; ?>>Cash without Invoice</option>
                        <option value="credit_card" <?php echo ($_POST['payment_method'] ?? '') === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="debit_card" <?php echo ($_POST['payment_method'] ?? '') === 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                        <option value="check" <?php echo ($_POST['payment_method'] ?? '') === 'check' ? 'selected' : ''; ?>>Check</option>
                        <option value="bank_transfer" <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="payment_status">Payment Status</label>
                    <select id="payment_status" name="payment_status">
                        <option value="pending" <?php echo ($_POST['payment_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                        <option value="paid" <?php echo ($_POST['payment_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>✅ Paid</option>
                        <option value="cancelled" <?php echo ($_POST['payment_status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div id="cash-without-invoice-info" class="alert alert-info" style="display: none;">
                <i class="fas fa-info-circle"></i>
                <strong>Cash without Invoice:</strong> This transaction will update inventory but will not create an invoice record. 
                No sale record will be saved in the database.
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- Items Section -->
    <div class="form-section">
        <h3><i class="fas fa-shopping-cart"></i> Sale Items</h3>
        
        <div id="items-container">
            <div class="item-row" data-item-id="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Item Type</label>
                        <select name="items[0][item_type]" class="item-type-select" required onchange="updateItemOptions(this)">
                            <option value="">Select item type</option>
                            <option value="product">Product</option>
                            <option value="service">Service</option>
                        </select>
                    </div>
                    
                    <div class="form-group product-select-group" style="display: none;">
                        <label>Product</label>
                        <select name="items[0][tire_id]" class="product-select">
                            <option value="">Select a product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" 
                                        data-price="<?php echo $product['price']; ?>"
                                        data-stock="<?php echo $product['stock_quantity']; ?>">
                                    <?php echo htmlspecialchars($product['brand_name'] . ' - ' . $product['name'] . ' (' . $product['size'] . ') - ' . ucfirst($product['condition'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group service-select-group" style="display: none;">
                        <label>Service</label>
                        <select name="items[0][service_id]" class="service-select">
                            <option value="">Select a service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                        data-price="<?php echo $service['price']; ?>"
                                        data-duration="<?php echo $service['duration_minutes']; ?>">
                                    <?php echo htmlspecialchars($service['name'] . ' (' . $service['category_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="items[0][quantity]" class="quantity-input" 
                               min="1" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Unit Price</label>
                        <input type="number" name="items[0][unit_price]" class="unit-price-input" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Total</label>
                        <input type="number" class="item-total" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <button type="button" id="add-item" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Add Item
        </button>
    </div>
    
    <!-- Totals Section -->
    <div class="totals-section">
        <div class="tax-rates-section">
            <h4>Tax Rates</h4>
            <div class="tax-inputs">
                <div class="tax-input">
                    <label for="gst_rate">GST Rate (%)</label>
                    <input type="number" id="gst_rate" name="gst_rate" value="5" min="0" max="100" step="0.01" onchange="calculateTotals()">
                </div>
                <div class="tax-input">
                    <label for="pst_rate">PST Rate (%)</label>
                    <input type="number" id="pst_rate" name="pst_rate" value="7" min="0" max="100" step="0.01" onchange="calculateTotals()">
                </div>
            </div>
        </div>
        
        <div class="totals-grid">
            <div class="total-item">
                <span>Subtotal:</span>
                <span id="subtotal">$0.00</span>
            </div>
            <div class="total-item">
                <span>GST (<span id="gst-rate-display">5</span>%):</span>
                <span id="gst-amount">$0.00</span>
            </div>
            <div class="total-item">
                <span>PST (<span id="pst-rate-display">7</span>%):</span>
                <span id="pst-amount">$0.00</span>
            </div>
            <div class="total-item total-grand">
                <span>Total:</span>
                <span id="grand-total">$0.00</span>
            </div>
            <div class="total-item total-input">
                <span>Set Total Amount:</span>
                <input type="number" id="set-total" step="0.01" min="0" placeholder="Enter total amount" onchange="recalculateFromTotal()">
                <input type="hidden" id="set-total-hidden" name="set_total">
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submit-btn">
            <i class="fas fa-save"></i> <span id="submit-text">Create Sale</span>
        </button>
        <a href="sales.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.form-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section h3 {
    margin: 0 0 1.5rem 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.item-row {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.totals-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.totals-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    gap: 1rem;
}

.tax-rates-section {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.tax-rates-section h4 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1rem;
}

.tax-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.tax-input {
    display: flex;
    flex-direction: column;
}

.tax-input label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.tax-input input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.total-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.total-grand {
    font-weight: bold;
    font-size: 1.2rem;
    color: #333;
    border-bottom: none;
}

.total-input {
    border-top: 2px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 0.5rem;
}

.total-input input {
    width: 120px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    text-align: right;
}

.total-input input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<script>
let itemCounter = 1;

// Function to update item options based on item type selection
function updateItemOptions(selectElement) {
    const itemRow = selectElement.closest('.item-row');
    const itemType = selectElement.value;
    const productGroup = itemRow.querySelector('.product-select-group');
    const serviceGroup = itemRow.querySelector('.service-select-group');
    const productSelect = itemRow.querySelector('.product-select');
    const serviceSelect = itemRow.querySelector('.service-select');
    
    // Hide both groups initially
    productGroup.style.display = 'none';
    serviceGroup.style.display = 'none';
    
    // Clear both selects
    productSelect.value = '';
    serviceSelect.value = '';
    
    // Show appropriate group based on selection
    if (itemType === 'product') {
        productGroup.style.display = 'block';
        productSelect.required = true;
        serviceSelect.required = false;
    } else if (itemType === 'service') {
        serviceGroup.style.display = 'block';
        serviceSelect.required = true;
        productSelect.required = false;
    } else {
        productSelect.required = false;
        serviceSelect.required = false;
    }
    
    // Clear unit price and recalculate
    itemRow.querySelector('.unit-price-input').value = '';
    calculateTotals();
}

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const newItem = container.querySelector('.item-row').cloneNode(true);
    
    // Update the item ID
    newItem.dataset.itemId = itemCounter;
    
    // Clear the values
    newItem.querySelector('.item-type-select').value = '';
    newItem.querySelector('.product-select').value = '';
    newItem.querySelector('.service-select').value = '';
    newItem.querySelector('.quantity-input').value = '1';
    newItem.querySelector('.unit-price-input').value = '';
    newItem.querySelector('.item-total').value = '';
    
    // Hide both select groups
    newItem.querySelector('.product-select-group').style.display = 'none';
    newItem.querySelector('.service-select-group').style.display = 'none';
    
    // Update the name attributes
    newItem.querySelector('.item-type-select').name = `items[${itemCounter}][item_type]`;
    newItem.querySelector('.product-select').name = `items[${itemCounter}][tire_id]`;
    newItem.querySelector('.service-select').name = `items[${itemCounter}][service_id]`;
    newItem.querySelector('.quantity-input').name = `items[${itemCounter}][quantity]`;
    newItem.querySelector('.unit-price-input').name = `items[${itemCounter}][unit_price]`;
    
    container.appendChild(newItem);
    itemCounter++;
    
    // Reattach event listeners
    attachItemEventListeners(newItem);
});

// Remove item functionality
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        const itemRow = e.target.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            itemRow.remove();
            calculateTotals();
        }
    }
});

// Calculate item total
function calculateItemTotal(itemRow) {
    const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
    const unitPrice = parseFloat(itemRow.querySelector('.unit-price-input').value) || 0;
    const total = quantity * unitPrice;
    itemRow.querySelector('.item-total').value = total.toFixed(2);
    
    // Check stock availability for products only
    const itemType = itemRow.querySelector('.item-type-select').value;
    if (itemType === 'product') {
        checkStockAvailability(itemRow);
    }
    
    return total;
}

// Check stock availability for an item (products only)
function checkStockAvailability(itemRow) {
    const productSelect = itemRow.querySelector('.product-select');
    const quantityInput = itemRow.querySelector('.quantity-input');
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.dataset.stock) {
        return;
    }
    
    const stockQuantity = parseInt(selectedOption.dataset.stock) || 0;
    const requestedQuantity = parseInt(quantityInput.value) || 0;
    
    // Remove existing warnings
    const existingWarning = itemRow.querySelector('.stock-warning');
    if (existingWarning) {
        existingWarning.remove();
    }
    
    // Add warning if quantity exceeds stock
    if (requestedQuantity > stockQuantity) {
        const warning = document.createElement('div');
        warning.className = 'stock-warning';
        warning.style.cssText = 'color: #721c24; background: #f8d7da; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem; font-size: 0.9rem;';
        warning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Insufficient stock: ${stockQuantity} available, ${requestedQuantity} requested`;
        itemRow.appendChild(warning);
    } else if (stockQuantity <= 5 && stockQuantity > 0 && requestedQuantity > 0) {
        const warning = document.createElement('div');
        warning.className = 'stock-warning';
        warning.style.cssText = 'color: #856404; background: #fff3cd; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem; font-size: 0.9rem;';
        warning.innerHTML = `<i class="fas fa-info-circle"></i> Low stock: ${stockQuantity} units available`;
        itemRow.appendChild(warning);
    }
}

// Calculate grand total
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(itemRow => {
        subtotal += calculateItemTotal(itemRow);
    });
    
    // Get tax rates from input fields
    const gstRate = parseFloat(document.getElementById('gst_rate').value) / 100;
    const pstRate = parseFloat(document.getElementById('pst_rate').value) / 100;
    
    // Check if this is a cash without invoice transaction
    const paymentMethod = document.getElementById('payment_method').value;
    const isCashWithoutInvoice = (paymentMethod === 'cash_without_invoice');

    let gstAmount = 0;
    let pstAmount = 0;
    let grandTotal = 0;

    if (isCashWithoutInvoice) {
        gstAmount = 0;
        pstAmount = 0;
        grandTotal = subtotal;
    } else {
        gstAmount = subtotal * gstRate;
        pstAmount = subtotal * pstRate;
        grandTotal = subtotal + gstAmount + pstAmount;
    }
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('gst-amount').textContent = '$' + gstAmount.toFixed(2);
    document.getElementById('pst-amount').textContent = '$' + pstAmount.toFixed(2);
    document.getElementById('grand-total').textContent = '$' + grandTotal.toFixed(2);
    
    // Update rate displays
    document.getElementById('gst-rate-display').textContent = (gstRate * 100).toFixed(2);
    document.getElementById('pst-rate-display').textContent = (pstRate * 100).toFixed(2);
}

// Attach event listeners to item row
function attachItemEventListeners(itemRow) {
    const quantityInput = itemRow.querySelector('.quantity-input');
    const unitPriceInput = itemRow.querySelector('.unit-price-input');
    const productSelect = itemRow.querySelector('.product-select');
    const serviceSelect = itemRow.querySelector('.service-select');
    const itemTypeSelect = itemRow.querySelector('.item-type-select');
    
    quantityInput.addEventListener('input', calculateTotals);
    unitPriceInput.addEventListener('input', calculateTotals);
    
    // Product select change handler
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.price) {
            unitPriceInput.value = selectedOption.dataset.price;
            calculateTotals();
        }
        
        // Show low stock warning
        const stockQuantity = parseInt(selectedOption.dataset.stock) || 0;
        const quantityInput = itemRow.querySelector('.quantity-input');
        const currentQuantity = parseInt(quantityInput.value) || 0;
        
        // Remove existing warnings
        const existingWarning = itemRow.querySelector('.stock-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        
        // Add warning if stock is low
        if (stockQuantity <= 5 && stockQuantity > 0) {
            const warning = document.createElement('div');
            warning.className = 'stock-warning';
            warning.style.cssText = 'color: #856404; background: #fff3cd; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem; font-size: 0.9rem;';
            warning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Low stock: ${stockQuantity} units available`;
            itemRow.appendChild(warning);
        } else if (stockQuantity === 0) {
            const warning = document.createElement('div');
            warning.className = 'stock-warning';
            warning.style.cssText = 'color: #721c24; background: #f8d7da; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem; font-size: 0.9rem;';
            warning.innerHTML = `<i class="fas fa-times-circle"></i> Out of stock`;
            itemRow.appendChild(warning);
        }
    });
    
    // Service select change handler
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.price) {
            unitPriceInput.value = selectedOption.dataset.price;
            calculateTotals();
        }
    });
    
    // Item type select change handler
    itemTypeSelect.addEventListener('change', function() {
        updateItemOptions(this);
    });
}

// Initialize event listeners for the first item
document.addEventListener('DOMContentLoaded', function() {
    attachItemEventListeners(document.querySelector('.item-row'));
    calculateTotals();
});

function recalculateFromTotal() {
    const setTotal = parseFloat(document.getElementById('set-total').value) || 0;
    if (setTotal <= 0) {
        document.getElementById('set-total-hidden').value = '';
        calculateTotals();
        return;
    }
    
    // Calculate current subtotal from items
    let subtotal = 0;
    let totalQuantity = 0;
    document.querySelectorAll('.item-row').forEach(itemRow => {
        const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(itemRow.querySelector('.unit-price-input').value) || 0;
        subtotal += quantity * unitPrice;
        totalQuantity += quantity;
    });
    
    if (subtotal <= 0 || totalQuantity <= 0) {
        alert('Please add items with quantities to the sale first.');
        document.getElementById('set-total').value = '';
        document.getElementById('set-total-hidden').value = '';
        return;
    }
    
    // Set the hidden field
    document.getElementById('set-total-hidden').value = setTotal;
    
    // Calculate tax rates
    const gstRate = parseFloat(document.getElementById('gst_rate').value) / 100;
    const pstRate = parseFloat(document.getElementById('pst_rate').value) / 100;
    
    // Check if this is a cash without invoice transaction
    const paymentMethod = document.getElementById('payment_method').value;
    const isCashWithoutInvoice = (paymentMethod === 'cash_without_invoice');

    let requiredSubtotal = 0;
    if (isCashWithoutInvoice) {
        requiredSubtotal = setTotal;
    } else {
        requiredSubtotal = setTotal / (1 + gstRate + pstRate);
    }
    
    // Calculate the adjustment needed per unit
    const currentSubtotal = subtotal;
    const adjustmentPerUnit = (requiredSubtotal - currentSubtotal) / totalQuantity;
    
    // Adjust unit prices proportionally
    document.querySelectorAll('.item-row').forEach(itemRow => {
        const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
        if (quantity > 0) {
            const currentUnitPrice = parseFloat(itemRow.querySelector('.unit-price-input').value) || 0;
            const newUnitPrice = Math.max(0, currentUnitPrice + adjustmentPerUnit);
            itemRow.querySelector('.unit-price-input').value = newUnitPrice.toFixed(2);
        }
    });
    
    // Recalculate totals with new unit prices
    calculateTotals();
}

// Update submit button text based on payment method
function updateSubmitButton() {
    const paymentMethod = document.getElementById('payment_method').value;
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const infoDiv = document.getElementById('cash-without-invoice-info');
    const taxSection = document.querySelector('.tax-rates-section');
    const gstRow = document.querySelector('.total-item:nth-child(2)');
    const pstRow = document.querySelector('.total-item:nth-child(3)');
    
    if (paymentMethod === 'cash_without_invoice') {
        submitText.textContent = 'Complete Cash Transaction';
        submitBtn.className = 'btn btn-success';
        infoDiv.style.display = 'block';
        taxSection.style.display = 'none';
        gstRow.style.display = 'none';
        pstRow.style.display = 'none';
    } else {
        submitText.textContent = 'Create Sale';
        submitBtn.className = 'btn btn-primary';
        infoDiv.style.display = 'none';
        taxSection.style.display = 'block';
        gstRow.style.display = 'flex';
        pstRow.style.display = 'flex';
    }
    
    // Recalculate totals after changing payment method
    calculateTotals();
}

// Add event listener to payment method select
document.getElementById('payment_method').addEventListener('change', updateSubmitButton);

// Initialize button text on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSubmitButton();
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 