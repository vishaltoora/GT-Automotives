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
$page_title = 'Invoice Management';

// Get filter parameters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build the query with filters
$where_conditions = [];
$params = [];
$param_types = [];

if (!empty($search_name)) {
    $where_conditions[] = "s.customer_name LIKE ?";
    $params[] = '%' . $search_name . '%';
    $param_types[] = 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(s.created_at) >= ?";
    $params[] = $date_from;
    $param_types[] = 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(s.created_at) <= ?";
    $params[] = $date_to;
    $param_types[] = 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get all sales with customer info and filters
$sales_query = "SELECT s.*, u.username as created_by_name FROM sales s 
                LEFT JOIN users u ON s.created_by = u.id 
                $where_clause
                ORDER BY s.created_at DESC";

$sales_stmt = $conn->prepare($sales_query);

// Bind parameters if any
if (!empty($params)) {
    $types = implode('', $param_types);
    $sales_stmt->bind_param($types, ...$params);
}

$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();

// Count rows for sales
$sales_rows = [];
while ($row = $sales_result->fetch_assoc()) {
    $sales_rows[] = $row;
}

// Include header
include_once 'includes/header.php';
?>



<!-- Sales Statistics Cards -->
<div class="admin-cards">
    <?php
    // Get total sales count
    $total_sales_query = "SELECT COUNT(*) as count FROM sales";
    $total_sales_result = $conn->query($total_sales_query);
    $total_sales = $total_sales_result->fetch_assoc()['count'];
    
    // Get total revenue
    $total_revenue_query = "SELECT SUM(total_amount) as total FROM sales WHERE payment_status = 'paid'";
    $total_revenue_result = $conn->query($total_revenue_query);
    $total_revenue = $total_revenue_result->fetch_assoc()['total'] ?? 0;
    
    // Get pending payments
    $pending_payments_query = "SELECT SUM(total_amount) as total FROM sales WHERE payment_status = 'pending'";
    $pending_payments_result = $conn->query($pending_payments_query);
    $pending_payments = $pending_payments_result->fetch_assoc()['total'] ?? 0;
    
    // Get total tax collected
    $total_tax_query = "SELECT SUM(gst_amount + pst_amount) as total FROM sales WHERE payment_status = 'paid'";
    $total_tax_result = $conn->query($total_tax_query);
    $total_tax = $total_tax_result->fetch_assoc()['total'] ?? 0;
    ?>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Total Invoices</h2>
            <div class="admin-card-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $total_sales; ?></div>
        <div class="admin-card-label">Total invoice transactions</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Total Revenue</h2>
            <div class="admin-card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="admin-card-value">$<?php echo number_format($total_revenue, 2); ?></div>
        <div class="admin-card-label">Paid invoice revenue</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Pending Payments</h2>
            <div class="admin-card-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="admin-card-value">$<?php echo number_format($pending_payments, 2); ?></div>
        <div class="admin-card-label">Awaiting payment</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Total Tax Collected</h2>
            <div class="admin-card-icon">
                <i class="fas fa-receipt"></i>
            </div>
        </div>
        <div class="admin-card-value">$<?php echo number_format($total_tax, 2); ?></div>
        <div class="admin-card-label">GST & PST from paid invoices</div>
    </div>
</div>

<!-- Sales Table -->
<h2>Recent Invoices</h2>

<!-- Search Filter Form -->
<div class="search-filter-container">
    <form method="GET" action="" class="search-filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search_name">Customer Name:</label>
                <input type="text" id="search_name" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Search by customer name...">
            </div>
            
            <div class="filter-group">
                <label for="date_from">Date From:</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            
            <div class="filter-group">
                <label for="date_to">Date To:</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="sales.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </div>
    </form>
    
    <?php if (!empty($search_name) || !empty($date_from) || !empty($date_to)): ?>
        <div class="filter-results">
            <p>
                <strong>Filtered Results:</strong> 
                <?php echo count($sales_rows); ?> sales found
                <?php if (!empty($search_name)): ?>
                    for customer name containing "<?php echo htmlspecialchars($search_name); ?>"
                <?php endif; ?>
                <?php if (!empty($date_from) || !empty($date_to)): ?>
                    between <?php echo !empty($date_from) ? date('M j, Y', strtotime($date_from)) : 'any date'; ?> 
                    and <?php echo !empty($date_to) ? date('M j, Y', strtotime($date_to)) : 'any date'; ?>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php if (count($sales_rows) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Date</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_rows as $sale): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($sale['invoice_number']); ?></strong>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($sale['customer_name']); ?></div>
                        <?php if ($sale['customer_email']): ?>
                            <small><?php echo htmlspecialchars($sale['customer_email']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong>$<?php echo number_format($sale['total_amount'], 2); ?></strong>
                    </td>
                    <td>
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
                    </td>
                    <td>
                        <?php 
                        $created_date = date('M j, Y g:i A', strtotime($sale['created_at']));
                        $updated_date = date('M j, Y g:i A', strtotime($sale['updated_at']));
                        
                        // Check if the sale has been modified
                        $is_modified = $sale['created_at'] !== $sale['updated_at'];
                        
                        echo $created_date;
                        
                        if ($is_modified) {
                            echo ' <span class="badge badge-warning" title="Modified on ' . $updated_date . '">📝</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($sale['created_by_name']); ?>
                    </td>
                    <td class="admin-actions">
                        <a href="view_sale.php?id=<?php echo $sale['id']; ?>" class="btn-action btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="edit_sale.php?id=<?php echo $sale['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="generate_invoice.php?id=<?php echo $sale['id']; ?>" class="btn-action btn-view" target="_blank">
                            <i class="fas fa-file-pdf"></i> Invoice
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-file-invoice fa-3x"></i>
        <h3>No Invoices Found</h3>
        <p>
            <?php if (!empty($search_name) || !empty($date_from) || !empty($date_to)): ?>
                No invoices match your current filters. Try adjusting your search criteria.
            <?php else: ?>
                Start creating invoices to track your business transactions.
            <?php endif; ?>
        </p>
        <?php if (empty($search_name) && empty($date_from) && empty($date_to)): ?>
            <a href="create_sale.php" class="btn btn-primary">Create Your First Invoice</a>
        <?php else: ?>
            <a href="sales.php" class="btn btn-secondary">Clear Filters</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<style>
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

.search-filter-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    padding: 1.5rem;
}

.search-filter-form {
    margin-bottom: 1rem;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.filter-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;
}

.filter-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: end;
}

.filter-actions .btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}

.filter-results {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    border-left: 4px solid #007bff;
}

.filter-results p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-state i {
    color: #ccc;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 1rem 0 0.5rem 0;
    color: #666;
}

.empty-state p {
    color: #999;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
}

.badge-warning {
    color: #856404;
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
}

.badge-warning:hover {
    background-color: #ffeaa7;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 