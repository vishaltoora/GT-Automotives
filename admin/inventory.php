<?php

// Set base path for includes
$base_path = dirname(__DIR__);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering
ob_start();

try {
    // Include database connection
    if (file_exists('$base_path . '/includes/db_connect.php'')) {
        require_once '$base_path . '/includes/db_connect.php'';
    }

    if (file_exists('$base_path . '/includes/auth.php'')) {
        require_once '$base_path . '/includes/auth.php'';
    }

    // Require login
    requireLogin();

    // Set page title
    $page_title = 'Product Inventory Management';

    // Initialize variables
    $total_products = 0;
    $new_tires_count = 0;
    $used_tires_count = 0;
    $low_stock_count = 0;
    $out_of_stock_count = 0;
    $total_value = 0;
    $inventory_items = [];
    $brands = [];
    $sizes = [];

    // Get filter parameters
    $condition_filter = $_GET['condition'] ?? 'all';
    $brand_filter = $_GET['brand'] ?? '';
    $size_filter = $_GET['size'] ?? '';
    $search_filter = $_GET['search'] ?? '';

    if (isset($conn)) {
        // Get inventory statistics
        $total_products_result = $conn->query("SELECT COUNT(*) as count FROM tires");
        if ($total_products_result) {
            $total_products = $total_products_result->fetch_assoc()['count'];
        }

        $new_tires_result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE `condition` = 'new'");
        if ($new_tires_result) {
            $new_tires_count = $new_tires_result->fetch_assoc()['count'];
        }

        $used_tires_result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE `condition` = 'used'");
        if ($used_tires_result) {
            $used_tires_count = $used_tires_result->fetch_assoc()['count'];
        }

        $low_stock_result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE stock_quantity > 0 AND stock_quantity <= 5");
        if ($low_stock_result) {
            $low_stock_count = $low_stock_result->fetch_assoc()['count'];
        }

        $out_of_stock_result = $conn->query("SELECT COUNT(*) as count FROM tires WHERE stock_quantity = 0");
        if ($out_of_stock_result) {
            $out_of_stock_count = $out_of_stock_result->fetch_assoc()['count'];
        }

        $total_value_result = $conn->query("SELECT SUM(price * stock_quantity) as total_value FROM tires");
        if ($total_value_result) {
            $total_value = $total_value_result->fetch_assoc()['total_value'] ?? 0;
        }

        // Build inventory query with filters
        $inventory_query = "SELECT t.*, b.name as brand_name FROM tires t LEFT JOIN brands b ON t.brand_id = b.id";

        $where_conditions = [];

        if ($condition_filter !== 'all') {
            $where_conditions[] = "t.`condition` = '" . mysqli_real_escape_string($conn, $condition_filter) . "'";
        }

        if (!empty($brand_filter)) {
            $where_conditions[] = "b.name = '" . mysqli_real_escape_string($conn, $brand_filter) . "'";
        }

        if (!empty($size_filter)) {
            $where_conditions[] = "t.size = '" . mysqli_real_escape_string($conn, $size_filter) . "'";
        }

        if (!empty($search_filter)) {
            $escaped_search = mysqli_real_escape_string($conn, $search_filter);
            $where_conditions[] = "(t.name LIKE '%$escaped_search%' OR t.description LIKE '%$escaped_search%' OR b.name LIKE '%$escaped_search%')";
        }

        if (!empty($where_conditions)) {
            $inventory_query .= " WHERE " . implode(' AND ', $where_conditions);
        }

        $inventory_query .= " ORDER BY t.stock_quantity ASC, b.name, t.name";
        $inventory_result = $conn->query($inventory_query);

        if ($inventory_result) {
            while ($row = $inventory_result->fetch_assoc()) {
                $inventory_items[] = $row;
            }
        }

        // Get distinct brands and sizes for filter dropdowns
        $brands_query = "SELECT DISTINCT b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id WHERE b.name IS NOT NULL ORDER BY b.name";
        $brands_result = $conn->query($brands_query);
        if ($brands_result) {
            while ($row = $brands_result->fetch_assoc()) {
                $brands[] = $row;
            }
        }

        $sizes_query = "SELECT DISTINCT size FROM tires WHERE size IS NOT NULL ORDER BY size";
        $sizes_result = $conn->query($sizes_query);
        if ($sizes_result) {
            while ($row = $sizes_result->fetch_assoc()) {
                $sizes[] = $row;
            }
        }
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in admin/inventory.php: " . $e->getMessage());
}

// Flush any output so far
ob_flush();

// Include header
if (file_exists('includes/header.php')) {
    include_once 'includes/header.php';
}
?>

<div class="admin-header">
    <div class="admin-actions">
        <a href="products.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        <a href="add_product.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<!-- Inventory Statistics -->
<div class="inventory-stats">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <h3><?php echo $total_products; ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🆕</div>
        <div class="stat-content">
            <h3><?php echo $new_tires_count; ?></h3>
            <p>New Tires</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🔄</div>
        <div class="stat-content">
            <h3><?php echo $used_tires_count; ?></h3>
            <p>Used Tires</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-content">
            <h3><?php echo $low_stock_count; ?></h3>
            <p>Low Stock (≤5)</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">❌</div>
        <div class="stat-content">
            <h3><?php echo $out_of_stock_count; ?></h3>
            <p>Out of Stock</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3>$<?php echo number_format($total_value, 2); ?></h3>
            <p>Total Value</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="inventory-filters">
    <form method="GET" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="condition">Condition:</label>
                <select name="condition" id="condition">
                    <option value="all" <?php echo $condition_filter === 'all' ? 'selected' : ''; ?>>All Conditions</option>
                    <option value="new" <?php echo $condition_filter === 'new' ? 'selected' : ''; ?>>New Only</option>
                    <option value="used" <?php echo $condition_filter === 'used' ? 'selected' : ''; ?>>Used Only</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="brand">Brand:</label>
                <select name="brand" id="brand">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo htmlspecialchars($brand['brand']); ?>" <?php echo $brand_filter === $brand['brand'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['brand']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="size">Size:</label>
                <select name="size" id="size">
                    <option value="">All Sizes</option>
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?php echo htmlspecialchars($size['size']); ?>" <?php echo $size_filter === $size['size'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($size['size']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Search products...">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="inventory.php" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Inventory Table -->
<div class="inventory-table">
    <h2>Inventory Items (<?php echo count($inventory_items); ?>)</h2>
    
    <?php if (!empty($inventory_items)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Brand</th>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Condition</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_items as $item): ?>
                    <tr class="<?php echo $item['stock_quantity'] == 0 ? 'out-of-stock' : ($item['stock_quantity'] <= 5 ? 'low-stock' : ''); ?>">
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['brand_name'] ?? 'No Brand'); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $item['condition']; ?>">
                                <?php echo ucfirst($item['condition']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="stock-level <?php echo $item['stock_quantity'] == 0 ? 'out-of-stock' : ($item['stock_quantity'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                                <?php echo $item['stock_quantity']; ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['stock_quantity'], 2); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="view_product.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-items">
            <i class="fas fa-box-open fa-3x"></i>
            <h3>No inventory items found</h3>
            <p>No products match your current filters.</p>
            <a href="add_product.php" class="btn btn-primary">Add Your First Product</a>
        </div>
    <?php endif; ?>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admin-actions {
    display: flex;
    gap: 15px;
}

.inventory-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2rem;
}

.stat-content h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.inventory-filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
}

.filter-group input,
.filter-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.inventory-table {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.admin-table th,
.admin-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.out-of-stock {
    background-color: #fff5f5;
    color: #dc3545;
}

.low-stock {
    background-color: #fff3cd;
    color: #856404;
}

.stock-level {
    font-weight: 600;
}

.stock-level.out-of-stock {
    color: #dc3545;
}

.stock-level.low-stock {
    color: #856404;
}

.stock-level.in-stock {
    color: #28a745;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-new {
    background-color: #d4edda;
    color: #155724;
}

.status-used {
    background-color: #fff3cd;
    color: #856404;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #243c55;
    color: white;
}

.btn-primary:hover {
    background-color: #1a2c3f;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

.btn-success {
    background-color: #243c55;
    color: white;
}

.btn-success:hover {
    background-color: #1a2c3f;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.no-items {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-items i {
    color: #ddd;
    margin-bottom: 20px;
}

.no-items h3 {
    margin-bottom: 10px;
    color: #333;
}

.no-items p {
    margin-bottom: 20px;
}
</style>

<?php
// Include footer
if (file_exists('includes/footer.php')) {
    include_once 'includes/footer.php';
}
?> 