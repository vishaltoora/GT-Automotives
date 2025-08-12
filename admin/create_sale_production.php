<?php
// Production-ready Create Sale Page for GT Automotives
// This version has enhanced error handling and debugging

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Production debugging - enable error logging but not display
$is_production = ($_SERVER['SERVER_NAME'] ?? '') !== 'localhost' && ($_SERVER['SERVER_NAME'] ?? '') !== '127.0.0.1';

if ($is_production) {
    // In production, log errors but don't display them
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/php_errors.log');
} else {
    // In development, show errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set base path for includes
$base_path = dirname(__DIR__);

// Log function for production debugging
function logError($message) {
    global $is_production;
    if ($is_production) {
        error_log("[GT Automotives] " . $message);
    }
}

// Try to include required files with error handling
try {
    // Check if database connection file exists
    $db_file = $base_path . '/includes/db_connect.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database connection file not found: {$db_file}");
    }
    
    // Check if auth file exists
    $auth_file = $base_path . '/includes/auth.php';
    if (!file_exists($auth_file)) {
        throw new Exception("Authentication file not found: {$auth_file}");
    }
    
    // Include files
    require_once $db_file;
    require_once $auth_file;
    
    // Check if database connection is established
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Database connection not established");
    }
    
    // Test database connection
    if (!$conn->ping()) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
} catch (Exception $e) {
    logError("Critical error in create_sale.php: " . $e->getMessage());
    
    if ($is_production) {
        // In production, show a user-friendly error
        echo "<!DOCTYPE html>";
        echo "<html lang='en'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>System Error - GT Automotives</title>";
        echo "<style>";
        echo "body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }";
        echo ".error-container { max-width: 600px; margin: 0 auto; }";
        echo ".error-icon { font-size: 48px; color: #dc3545; }";
        echo ".error-message { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        echo "<div class='error-container'>";
        echo "<div class='error-icon'>⚠️</div>";
        echo "<h1>System Temporarily Unavailable</h1>";
        echo "<div class='error-message'>";
        echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
        echo "<p>If this problem persists, please contact support.</p>";
        echo "</div>";
        echo "<p><a href='/admin/index.php'>Return to Admin Panel</a></p>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
        exit;
    } else {
        // In development, show the actual error
        die("Critical error: " . $e->getMessage());
    }
}

// Require login
try {
    requireLogin();
} catch (Exception $e) {
    logError("Authentication error: " . $e->getMessage());
    // This should redirect to login, but if it fails, handle gracefully
    header("Location: login.php");
    exit;
}

// Set page title
$page_title = 'Create New Sale';

// Get all products with brand names
try {
    $products_query = "SELECT t.id, t.name, t.size, t.price, t.stock_quantity, t.`condition`, b.name as brand_name, l.name as location_name 
        FROM tires t 
        LEFT JOIN brands b ON t.brand_id = b.id 
        LEFT JOIN locations l ON t.location_id = l.id 
        WHERE t.stock_quantity > 0 
        ORDER BY t.`condition` DESC, b.name, t.name";
    $products_result = $conn->query($products_query);
    
    if (!$products_result) {
        throw new Exception("Failed to fetch products: " . $conn->error);
    }
    
    $products = [];
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
} catch (Exception $e) {
    logError("Error fetching products: " . $e->getMessage());
    $products = [];
    $products_error = "Unable to load products. Please try again.";
}

// Get all active services
try {
    $services_query = "SELECT s.*, sc.name as category_name FROM services s 
                      LEFT JOIN service_categories sc ON s.category = sc.name 
                      WHERE s.is_active = 1 
                      ORDER BY s.category, s.name";
    $services_result = $conn->query($services_query);
    
    if (!$services_result) {
        throw new Exception("Failed to fetch services: " . $conn->error);
    }
    
    $services = [];
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }
} catch (Exception $e) {
    logError("Error fetching services: " . $e->getMessage());
    $services = [];
    $services_error = "Unable to load services. Please try again.";
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
                if (!$stock_stmt) {
                    throw new Exception("Failed to prepare stock query: " . $conn->error);
                }
                
                $stock_stmt->bind_param("i", $tire_id);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();
                $stock_row = $stock_result->fetch_assoc();
                
                if (!$stock_row) {
                    $inventory_errors[] = "Product not found";
                    continue;
                }
                
                if ($stock_row['stock_quantity'] < $quantity) {
                    $inventory_errors[] = "Insufficient stock for {$stock_row['name']} (Available: {$stock_row['stock_quantity']}, Requested: {$quantity})";
                    continue;
                }
            }
            
            $subtotal += $total_price;
            $items_data[] = [
                'type' => $item_type,
                'tire_id' => $item['tire_id'] ?? null,
                'service_id' => $item['service_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total_price' => $total_price
            ];
        }
        
        if (!empty($inventory_errors)) {
            throw new Exception("Inventory validation failed: " . implode(", ", $inventory_errors));
        }
        
        // Get tax rates
        $gst_rate = (float)($_POST['gst_rate'] ?? 5.0);
        $pst_rate = (float)($_POST['pst_rate'] ?? 7.0);
        
        // Calculate taxes
        $gst_amount = $subtotal * ($gst_rate / 100);
        $pst_amount = $subtotal * ($pst_rate / 100);
        $total = $subtotal + $gst_amount + $pst_amount;
        
        // Get payment method
        $payment_method = $_POST['payment_method'] ?? 'cash';
        $customer_name = $_POST['customer_name'];
        $customer_phone = $_POST['customer_phone'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert sale record
            $sale_query = "INSERT INTO sales (invoice_number, customer_name, customer_phone, customer_email, 
                           subtotal, gst_rate, gst_amount, pst_rate, pst_amount, total, payment_method, 
                           notes, created_by, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $sale_stmt = $conn->prepare($sale_query);
            if (!$sale_stmt) {
                throw new Exception("Failed to prepare sale insert: " . $conn->error);
            }
            
            $sale_stmt->bind_param("ssssddddddss", 
                $invoice_number, $customer_name, $customer_phone, $customer_email,
                $subtotal, $gst_rate, $gst_amount, $pst_rate, $pst_amount, 
                $total, $payment_method, $notes, $_SESSION['username']
            );
            
            if (!$sale_stmt->execute()) {
                throw new Exception("Failed to insert sale: " . $sale_stmt->error);
            }
            
            $sale_id = $conn->insert_id;
            
            // Insert sale items
            foreach ($items_data as $item) {
                if ($item['type'] === 'product') {
                    // Insert tire sale item
                    $item_query = "INSERT INTO sale_items (sale_id, tire_id, quantity, unit_price, total_price) 
                                  VALUES (?, ?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($item_query);
                    $item_stmt->bind_param("iidd", $sale_id, $item['tire_id'], $item['quantity'], $item['unit_price'], $item['total_price']);
                    
                    if (!$item_stmt->execute()) {
                        throw new Exception("Failed to insert tire sale item: " . $item_stmt->error);
                    }
                    
                    // Update inventory
                    $update_query = "UPDATE tires SET stock_quantity = stock_quantity - ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ii", $item['quantity'], $item['tire_id']);
                    
                    if (!$update_stmt->execute()) {
                        throw new Exception("Failed to update inventory: " . $update_stmt->error);
                    }
                } else {
                    // Insert service sale item
                    $item_query = "INSERT INTO sale_items (sale_id, service_id, quantity, unit_price, total_price) 
                                  VALUES (?, ?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($item_query);
                    $item_stmt->bind_param("iidd", $sale_id, $item['service_id'], $item['quantity'], $item['unit_price'], $item['total_price']);
                    
                    if (!$item_stmt->execute()) {
                        throw new Exception("Failed to insert service sale item: " . $item_stmt->error);
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Success message
            $_SESSION['success'] = "Sale created successfully! Invoice #: {$invoice_number}";
            
            // Redirect to view sale page
            header("Location: view_sale.php?id={$sale_id}");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        logError("Sale creation error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to create sale: " . $e->getMessage();
    }
}

// Include header
include_once $base_path . '/admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create New Sale</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($products_error)): ?>
                        <div class="alert alert-warning">
                            <?php echo $products_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($services_error)): ?>
                        <div class="alert alert-warning">
                            <?php echo $services_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="create-sale-form">
                        <!-- Customer Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-3">
                                <label for="customer_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone">
                            </div>
                            <div class="col-md-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                        </div>
                        
                        <!-- Items Section -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Items</h5>
                            </div>
                            <div class="card-body">
                                <div id="items-container">
                                    <!-- Items will be added here dynamically -->
                                </div>
                                <button type="button" class="btn btn-secondary" onclick="addItem()">Add Item</button>
                            </div>
                        </div>
                        
                        <!-- Totals Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Payment & Tax</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">Payment Method</label>
                                            <select class="form-control" id="payment_method" name="payment_method" onchange="updateSubmitButton()">
                                                <option value="cash">Cash</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="debit_card">Debit Card</option>
                                                <option value="e_transfer">E-Transfer</option>
                                                <option value="cash_without_invoice">Cash (No Invoice)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="gst_rate" class="form-label">GST Rate (%)</label>
                                            <input type="number" class="form-control" id="gst_rate" name="gst_rate" value="5.0" step="0.01" min="0" max="100">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="pst_rate" class="form-label">PST Rate (%)</label>
                                            <input type="number" class="form-control" id="pst_rate" name="pst_rate" value="7.0" step="0.01" min="0" max="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Totals</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="total-item d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">$0.00</span>
                                        </div>
                                        <div class="total-item d-flex justify-content-between mb-2">
                                            <span>GST:</span>
                                            <span id="gst-amount">$0.00</span>
                                        </div>
                                        <div class="total-item d-flex justify-content-between mb-2">
                                            <span>PST:</span>
                                            <span id="pst-amount">$0.00</span>
                                        </div>
                                        <hr>
                                        <div class="total-item d-flex justify-content-between mb-2">
                                            <strong>Total:</strong>
                                            <strong id="total">$0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span id="submit-text">Create Sale</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for dynamic item management
let itemCounter = 0;

function addItem() {
    itemCounter++;
    const container = document.getElementById('items-container');
    
    const itemHtml = `
        <div class="item-row border rounded p-3 mb-3" data-item-id="${itemCounter}">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Item Type</label>
                    <select class="form-control item-type" onchange="updateItemFields(${itemCounter})">
                        <option value="">Select Type</option>
                        <option value="product">Product</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div class="col-md-3 product-field" style="display: none;">
                    <label class="form-label">Product</label>
                    <select class="form-control" name="items[${itemCounter}][tire_id]">
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                <?php echo htmlspecialchars($product['name'] . ' - ' . $product['size'] . ' (' . $product['brand_name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 service-field" style="display: none;">
                    <label class="form-label">Service</label>
                    <select class="form-control" name="items[${itemCounter}][service_id]">
                        <option value="">Select Service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>">
                                <?php echo htmlspecialchars($service['name'] . ' (' . $service['category_name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control quantity-input" name="items[${itemCounter}][quantity]" min="1" value="1" onchange="calculateItemTotal(${itemCounter})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" class="form-control unit-price-input" name="items[${itemCounter}][unit_price]" step="0.01" min="0" onchange="calculateItemTotal(${itemCounter})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control item-total" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${itemCounter})">×</button>
                </div>
            </div>
            <input type="hidden" name="items[${itemCounter}][item_type]" class="item-type-hidden">
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeItem(itemId) {
    const item = document.querySelector(`[data-item-id="${itemId}"]`);
    if (item) {
        item.remove();
        calculateTotals();
    }
}

function updateItemFields(itemId) {
    const item = document.querySelector(`[data-item-id="${itemId}"]`);
    const itemType = item.querySelector('.item-type').value;
    const productField = item.querySelector('.product-field');
    const serviceField = item.querySelector('.service-field');
    const typeHidden = item.querySelector('.item-type-hidden');
    
    // Hide all fields first
    productField.style.display = 'none';
    serviceField.style.display = 'none';
    
    // Show relevant field
    if (itemType === 'product') {
        productField.style.display = 'block';
        typeHidden.value = 'product';
    } else if (itemType === 'service') {
        serviceField.style.display = 'block';
        typeHidden.value = 'service';
    }
    
    // Clear values
    item.querySelector('.quantity-input').value = '1';
    item.querySelector('.unit-price-input').value = '';
    item.querySelector('.item-total').value = '';
    
    calculateTotals();
}

function calculateItemTotal(itemId) {
    const item = document.querySelector(`[data-item-id="${itemId}"]`);
    const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
    const unitPrice = parseFloat(item.querySelector('.unit-price-input').value) || 0;
    const total = quantity * unitPrice;
    
    item.querySelector('.item-total').value = '$' + total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-row').forEach(itemRow => {
        const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(itemRow.querySelector('.unit-price-input').value) || 0;
        subtotal += quantity * unitPrice;
    });
    
    const gstRate = parseFloat(document.getElementById('gst_rate').value) / 100;
    const pstRate = parseFloat(document.getElementById('pst_rate').value) / 100;
    
    const gstAmount = subtotal * gstRate;
    const pstAmount = subtotal * pstRate;
    const total = subtotal + gstAmount + pstAmount;
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('gst-amount').textContent = '$' + gstAmount.toFixed(2);
    document.getElementById('pst-amount').textContent = '$' + pstAmount.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
}

function updateSubmitButton() {
    const paymentMethod = document.getElementById('payment_method').value;
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    
    if (paymentMethod === 'cash_without_invoice') {
        submitText.textContent = 'Complete Cash Transaction';
        submitBtn.className = 'btn btn-success';
    } else {
        submitText.textContent = 'Create Sale';
        submitBtn.className = 'btn btn-primary';
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateSubmitButton();
    addItem(); // Add first item by default
});

// Auto-populate unit price when product/service is selected
document.addEventListener('change', function(e) {
    if (e.target.name && e.target.name.includes('[tire_id]')) {
        const option = e.target.options[e.target.selectedIndex];
        const price = option.dataset.price;
        if (price) {
            const itemRow = e.target.closest('.item-row');
            itemRow.querySelector('.unit-price-input').value = price;
            calculateItemTotal(itemRow.dataset.itemId);
        }
    }
    
    if (e.target.name && e.target.name.includes('[service_id]')) {
        const option = e.target.options[e.target.selectedIndex];
        const price = option.dataset.price;
        if (price) {
            const itemRow = e.target.closest('.item-row');
            itemRow.querySelector('.unit-price-input').value = price;
            calculateItemTotal(itemRow.dataset.itemId);
        }
    }
});
</script>

<?php
// Include footer
include_once $base_path . '/admin/includes/footer.php';
?> 