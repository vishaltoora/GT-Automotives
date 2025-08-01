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
$page_title = 'Admin Dashboard';

// Get comprehensive statistics
$stats = [];

try {
    // Product statistics
    $product_count_query = "SELECT COUNT(*) as count FROM tires";
    $product_count_result = $conn->query($product_count_query);
    $stats['total_products'] = $product_count_result ? $product_count_result->fetch_assoc()['count'] : 0;

    // Inventory value
    $inventory_value_query = "SELECT SUM(price * stock_quantity) as total_value FROM tires WHERE stock_quantity > 0";
    $inventory_value_result = $conn->query($inventory_value_query);
    $stats['inventory_value'] = $inventory_value_result ? ($inventory_value_result->fetch_assoc()['total_value'] ?? 0) : 0;

    // Sales statistics
    $total_sales_query = "SELECT COUNT(*) as count FROM sales";
    $total_sales_result = $conn->query($total_sales_query);
    $stats['total_sales'] = $total_sales_result ? $total_sales_result->fetch_assoc()['count'] : 0;

    // Revenue statistics
    $total_revenue_query = "SELECT SUM(total_amount) as total FROM sales WHERE payment_status = 'paid'";
    $total_revenue_result = $conn->query($total_revenue_query);
    $stats['total_revenue'] = $total_revenue_result ? ($total_revenue_result->fetch_assoc()['total'] ?? 0) : 0;

    // Pending payments
    $pending_payments_query = "SELECT SUM(total_amount) as total FROM sales WHERE payment_status = 'pending'";
    $pending_payments_result = $conn->query($pending_payments_query);
    $stats['pending_payments'] = $pending_payments_result ? ($pending_payments_result->fetch_assoc()['total'] ?? 0) : 0;

    // Low stock products (less than 10 items)
    $low_stock_query = "SELECT COUNT(*) as count FROM tires WHERE stock_quantity > 0 AND stock_quantity <= 5";
    $low_stock_result = $conn->query($low_stock_query);
    $stats['low_stock_products'] = $low_stock_result ? $low_stock_result->fetch_assoc()['count'] : 0;

    // Recent products (with brand name)
    $recent_products_query = "SELECT t.*, b.name as brand_name FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY t.id DESC LIMIT 5";
    $recent_products_result = $conn->query($recent_products_query);

    // Count rows for recent products
    $recent_products_rows = [];
    if ($recent_products_result) {
        while ($row = $recent_products_result->fetch_assoc()) {
            $recent_products_rows[] = $row;
        }
    }

    // Recent sales
    $recent_sales_query = "SELECT s.*, u.username as created_by_name FROM sales s 
                           LEFT JOIN users u ON s.created_by = u.id 
                           ORDER BY s.created_at DESC LIMIT 5";
    $recent_sales_result = $conn->query($recent_sales_query);

    // Count rows for recent sales
    $recent_sales_rows = [];
    if ($recent_sales_result) {
        while ($row = $recent_sales_result->fetch_assoc()) {
            $recent_sales_rows[] = $row;
        }
    }

    // Get current user information for display
    $current_user_query = "SELECT first_name, last_name, username FROM users WHERE username = ?";
    $current_user_stmt = $conn->prepare($current_user_query);
    if ($current_user_stmt) {
        $current_user_stmt->bind_param("s", $_SESSION['username']);
        $current_user_stmt->execute();
        $current_user_result = $current_user_stmt->get_result();
        $current_user = $current_user_result->fetch_assoc();
        $current_user_stmt->close();
    } else {
        $current_user = null;
    }

} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error in admin/index.php: " . $e->getMessage());
    $stats = [
        'total_products' => 0,
        'inventory_value' => 0,
        'total_sales' => 0,
        'total_revenue' => 0,
        'pending_payments' => 0,
        'low_stock_products' => 0
    ];
    $recent_products_rows = [];
    $recent_sales_rows = [];
    $current_user = null;
}

// Determine display name
$display_name = '';
if ($current_user) {
    $full_name = trim($current_user['first_name'] . ' ' . $current_user['last_name']);
    $display_name = $full_name ?: $current_user['username'];
} else {
    $display_name = $_SESSION['username'] ?? 'Admin';
}

// Include header
include_once 'includes/header.php';
?>

<!-- Welcome Section and Action Buttons -->
<div class="dashboard-header">
    <div class="dashboard-welcome">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h1>
            <p>Here's what's happening with your business today.</p>
        </div>
        <div class="calendar-date">
            <div class="calendar-day">
                <div class="day-number"><?php echo date('j'); ?></div>
                <div class="day-name"><?php echo date('D'); ?></div>
            </div>
            <div class="calendar-month">
                <div class="month-name"><?php echo date('M'); ?></div>
                <div class="year"><?php echo date('Y'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-change positive">+12.5% from last month</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['total_sales']; ?></div>
            <div class="stat-label">Total Sales</div>
            <div class="stat-change positive">+8.3% from last month</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            <div class="stat-label">Products</div>
            <div class="stat-change neutral">No change</div>
        </div>
    </div>
    
    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">$<?php echo number_format($stats['pending_payments'], 2); ?></div>
            <div class="stat-label">Pending Payments</div>
            <div class="stat-change negative">+2.1% from last month</div>
        </div>
    </div>
</div>

<!-- Dashboard Content Grid -->
<div class="dashboard-grid">
    <!-- Recent Sales -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Recent Sales</h3>
            <a href="sales.php" class="btn-link">View All</a>
        </div>
        <div class="card-content">
            <?php if (count($recent_sales_rows) > 0): ?>
                <div class="recent-list">
                    <?php foreach ($recent_sales_rows as $sale): ?>
                        <div class="recent-item">
                            <div class="recent-item-main">
                                <div class="recent-item-title">
                                    <strong><?php echo htmlspecialchars($sale['customer_name'] ?? 'Unknown Customer'); ?></strong>
                                    <span class="status-badge status-<?php echo $sale['payment_status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($sale['payment_status'] ?? 'pending'); ?>
                                    </span>
                                </div>
                                <div class="recent-item-subtitle">
                                    Invoice #<?php echo htmlspecialchars($sale['invoice_number'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <div class="recent-item-amount">
                                $<?php echo number_format($sale['total_amount'] ?? 0, 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <p>No recent sales</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-car-alt"></i> Recent Products</h3>
            <div class="card-actions">
                <a href="add_product.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <a href="products.php" class="btn-link">View All</a>
            </div>
        </div>
        <div class="card-content">
            <?php if (count($recent_products_rows) > 0): ?>
                <div class="recent-list">
                    <?php foreach ($recent_products_rows as $product): ?>
                        <div class="recent-item">
                            <div class="recent-item-main">
                                <div class="recent-item-title">
                                    <strong><?php echo htmlspecialchars($product['name'] ?? 'Unknown Product'); ?></strong>
                                    <span class="stock-badge <?php echo ($product['stock_quantity'] ?? 0) < 10 ? 'low-stock' : 'in-stock'; ?>">
                                        <?php echo $product['stock_quantity'] ?? 0; ?> in stock
                                    </span>
                                </div>
                                <div class="recent-item-subtitle">
                                    <?php echo htmlspecialchars($product['brand_name'] ?? 'Unknown Brand'); ?> • <?php echo htmlspecialchars($product['size'] ?? 'Unknown Size'); ?>
                                </div>
                            </div>
                            <div class="recent-item-amount">
                                $<?php echo number_format($product['price'] ?? 0, 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-car-alt"></i>
                    <p>No products added yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="card-content">
            <div class="quick-actions">
                <a href="create_sale.php" class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Sale</span>
                </a>
                <a href="add_product.php" class="quick-action">
                    <i class="fas fa-plus"></i>
                    <span>Add Product</span>
                </a>
                <a href="inventory.php" class="quick-action">
                    <i class="fas fa-boxes"></i>
                    <span>Check Inventory</span>
                </a>
                <a href="sales.php" class="quick-action">
                    <i class="fas fa-file-invoice"></i>
                    <span>View Sales</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bell"></i> Alerts</h3>
        </div>
        <div class="card-content">
            <div class="alerts-list">
                <?php if ($stats['low_stock_products'] > 0): ?>
                    <div class="alert-item alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="alert-content">
                            <strong><?php echo $stats['low_stock_products']; ?> products</strong> are running low on stock
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($stats['pending_payments'] > 0): ?>
                    <div class="alert-item alert-info">
                        <i class="fas fa-clock"></i>
                        <div class="alert-content">
                            <strong>$<?php echo number_format($stats['pending_payments'], 2); ?></strong> in pending payments
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($stats['low_stock_products'] == 0 && $stats['pending_payments'] == 0): ?>
                    <div class="alert-item alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <strong>All good!</strong> No urgent alerts
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Header Section */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 2rem;
}

/* Dashboard Welcome Section */
.dashboard-welcome {
    background: white;
    color: #243c55;
    padding: 1.5rem;
    border-radius: 12px;
    flex: 1;
    border: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.welcome-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.welcome-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
}

/* Calendar Date Display */
.calendar-date {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #f8fafc;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.calendar-day {
    text-align: center;
}

.day-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
}

.day-name {
    font-size: 0.85rem;
    color: #718096;
    text-transform: uppercase;
}

.calendar-month {
    text-align: center;
}

.month-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
}

.year {
    font-size: 0.9rem;
    color: #718096;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #718096;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.stat-change {
    font-size: 0.8rem;
    font-weight: 600;
}

.stat-change.positive {
    color: #38a169;
}

.stat-change.negative {
    color: #e53e3e;
}

.stat-change.neutral {
    color: #718096;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.btn-link:hover {
    color: #5a67d8;
}

.card-content {
    padding: 1.5rem;
}

/* Recent List */
.recent-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.recent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    transition: background 0.3s ease;
}

.recent-item:hover {
    background: #f1f5f9;
}

.recent-item-main {
    flex: 1;
}

.recent-item-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.recent-item-subtitle {
    color: #718096;
    font-size: 0.85rem;
}

.recent-item-amount {
    font-weight: 600;
    color: #2d3748;
    font-size: 1.1rem;
}

/* Status Badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.stock-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.in-stock {
    background: #d1fae5;
    color: #065f46;
}

.low-stock {
    background: #fef3c7;
    color: #92400e;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 8px;
    text-decoration: none;
    color: #2d3748;
    transition: all 0.3s ease;
    text-align: center;
}

.quick-action:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

.quick-action i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.quick-action span {
    font-weight: 500;
    font-size: 0.9rem;
}

/* Alerts */
.alerts-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
}

.alert-item i {
    font-size: 1.1rem;
}

.alert-content strong {
    font-weight: 600;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #718096;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .dashboard-welcome {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .card-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 