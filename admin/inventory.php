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
$page_title = 'Product Inventory Management';

// Get filter parameters
$condition_filter = $_GET['condition'] ?? 'all';
$brand_filter = $_GET['brand'] ?? '';
$size_filter = $_GET['size'] ?? '';
$location_filter = $_GET['location'] ?? '';
$search_filter = $_GET['search'] ?? '';

// Get inventory statistics
$total_products_query = "SELECT COUNT(*) as count FROM tires";
$total_products_result = $conn->query($total_products_query);
$total_products = $total_products_result->fetchArray(SQLITE3_ASSOC)['count'];

$new_tires_query = "SELECT COUNT(*) as count FROM tires WHERE `condition` = 'new'";
$new_tires_result = $conn->query($new_tires_query);
$new_tires_count = $new_tires_result->fetchArray(SQLITE3_ASSOC)['count'];

$used_tires_query = "SELECT COUNT(*) as count FROM tires WHERE `condition` = 'used'";
$used_tires_result = $conn->query($used_tires_query);
$used_tires_count = $used_tires_result->fetchArray(SQLITE3_ASSOC)['count'];

$low_stock_query = "SELECT COUNT(*) as count FROM tires WHERE stock_quantity <= 5 AND stock_quantity > 0";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetchArray(SQLITE3_ASSOC)['count'];

$out_of_stock_query = "SELECT COUNT(*) as count FROM tires WHERE stock_quantity = 0";
$out_of_stock_result = $conn->query($out_of_stock_query);
$out_of_stock_count = $out_of_stock_result->fetchArray(SQLITE3_ASSOC)['count'];

$total_value_query = "SELECT SUM(stock_quantity * price) as total_value FROM tires WHERE stock_quantity > 0";
$total_value_result = $conn->query($total_value_query);
$total_value = $total_value_result->fetchArray(SQLITE3_ASSOC)['total_value'] ?? 0;

// Build inventory query with filters
$inventory_query = "SELECT t.*, b.name as brand_name, l.name as location_name, 'tire' as item_type FROM tires t 
                   LEFT JOIN brands b ON t.brand_id = b.id 
                   LEFT JOIN locations l ON t.location_id = l.id";

$where_conditions = [];

if ($condition_filter !== 'all') {
    $where_conditions[] = "t.`condition` = '" . SQLite3::escapeString($condition_filter) . "'";
}

if (!empty($brand_filter)) {
    $where_conditions[] = "b.name = '" . SQLite3::escapeString($brand_filter) . "'";
}

if (!empty($size_filter)) {
    $where_conditions[] = "t.size = '" . SQLite3::escapeString($size_filter) . "'";
}

if (!empty($location_filter)) {
    $where_conditions[] = "t.location_id = '" . SQLite3::escapeString($location_filter) . "'";
}

if (!empty($search_filter)) {
    $escaped_search = SQLite3::escapeString($search_filter);
    $where_conditions[] = "(t.name LIKE '%$escaped_search%' OR t.description LIKE '%$escaped_search%' OR b.name LIKE '%$escaped_search%' OR t.size LIKE '%$escaped_search%')";
}

if (!empty($where_conditions)) {
    $inventory_query .= " WHERE " . implode(' AND ', $where_conditions);
}

$inventory_query .= " ORDER BY t.stock_quantity ASC, b.name, t.name";
$inventory_result = $conn->query($inventory_query);

$inventory_items = [];
while ($row = $inventory_result->fetchArray(SQLITE3_ASSOC)) {
    $inventory_items[] = $row;
}

// Get distinct brands and sizes for filter dropdowns
$brands_query = "SELECT DISTINCT b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY b.name";
$brands_result = $conn->query($brands_query);

$sizes_query = "SELECT DISTINCT size FROM tires ORDER BY size";
$sizes_result = $conn->query($sizes_query);

$locations_query = "SELECT l.* FROM locations l ORDER BY l.name ASC";
$locations_result = $conn->query($locations_query);

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
  
    <div class="admin-actions">
        <a href="products.php" class="btn btn-primary">
            <i class="fas fa-boxes"></i> Manage Products
        </a>
        <a href="services.php" class="btn btn-primary">
            <i class="fas fa-tools"></i> Manage Services
        </a>
    </div>
</div>

<!-- Enhanced Filter Section -->
<div class="filter-section">
    <form method="GET" action="" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="condition">Filter by Condition:</label>
                <select name="condition" id="condition" onchange="this.form.submit()">
                    <option value="all" <?php echo $condition_filter === 'all' ? 'selected' : ''; ?>>All Tires</option>
                    <option value="new" <?php echo $condition_filter === 'new' ? 'selected' : ''; ?>>New Tires</option>
                    <option value="used" <?php echo $condition_filter === 'used' ? 'selected' : ''; ?>>Used Tires</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="brand">Filter by Brand:</label>
                <select name="brand" id="brand">
                    <option value="">All Brands</option>
                    <?php while ($brand = $brands_result->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?php echo htmlspecialchars($brand['brand']); ?>" <?php echo $brand_filter === $brand['brand'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['brand']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="size">Filter by Size:</label>
                <select name="size" id="size">
                    <option value="">All Sizes</option>
                    <?php while ($size = $sizes_result->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?php echo htmlspecialchars($size['size']); ?>" <?php echo $size_filter === $size['size'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($size['size']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="location">Filter by Location:</label>
                <select name="location" id="location">
                    <option value="">All Locations</option>
                    <?php while ($location = $locations_result->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?php echo htmlspecialchars($location['id']); ?>" <?php echo $location_filter == $location['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($location['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group search-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Search by name, description, brand, or size...">
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="inventory.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear All
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Inventory Statistics Cards -->
<div class="admin-cards">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Total Products</h2>
            <div class="admin-card-icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $total_products; ?></div>
        <div class="admin-card-label">Products in inventory</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>New Tires</h2>
            <div class="admin-card-icon">
                <i class="fas fa-tag"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $new_tires_count; ?></div>
        <div class="admin-card-label">New tire products</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Used Tires</h2>
            <div class="admin-card-icon">
                <i class="fas fa-recycle"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $used_tires_count; ?></div>
        <div class="admin-card-label">Used tire products</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Low Stock</h2>
            <div class="admin-card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $low_stock_count; ?></div>
        <div class="admin-card-label">Products with ≤5 units</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Out of Stock</h2>
            <div class="admin-card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="admin-card-value"><?php echo $out_of_stock_count; ?></div>
        <div class="admin-card-label">Products with 0 units</div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Inventory Value</h2>
            <div class="admin-card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="admin-card-value">$<?php echo number_format($total_value, 2); ?></div>
        <div class="admin-card-label">Total inventory value</div>
    </div>
</div>

<!-- Inventory Table -->
<div class="inventory-header">
    <h2>
        Current Inventory 
        <?php if ($condition_filter !== 'all'): ?>
            (<?php echo ucfirst($condition_filter); ?> Tires)
        <?php endif; ?>
        <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
            - Search Results
        <?php endif; ?>
    </h2>
    
    <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
        <div class="search-summary">
            <strong>Active Filters:</strong>
            <?php 
            $active_filters = [];
            if (!empty($brand_filter)) $active_filters[] = "Brand: " . htmlspecialchars($brand_filter);
            if (!empty($size_filter)) $active_filters[] = "Size: " . htmlspecialchars($size_filter);
            if (!empty($location_filter)) {
                $location_name_query = "SELECT name FROM locations WHERE id = ?";
                $location_name_stmt = $conn->prepare($location_name_query);
                $location_name_stmt->bindValue(1, $location_filter, SQLITE3_INTEGER);
                $location_name_result = $location_name_stmt->execute();
                $location_name = $location_name_result->fetchArray(SQLITE3_ASSOC)['name'] ?? 'Unknown';
                $active_filters[] = "Location: " . htmlspecialchars($location_name);
            }
            if (!empty($search_filter)) $active_filters[] = "Search: " . htmlspecialchars($search_filter);
            echo implode(', ', $active_filters);
            ?>
            <span class="result-count">(<?php echo count($inventory_items); ?> items found)</span>
        </div>
    <?php endif; ?>
</div>

<?php if (count($inventory_items) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Brand/Category</th>
                <th>Size/Details</th>
                <th>Location</th>
                <th>Condition</th>
                <th>Stock Level</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory_items as $item): ?>
                <tr class="<?php echo $item['stock_quantity'] === 0 ? 'out-of-stock' : ($item['stock_quantity'] <= 5 ? 'low-stock' : ''); ?>">
                    <td>
                        <span class="type-badge type-tire">
                            Tire
                        </span>
                    </td>
                    <td>
                        <div class="product-info">
                            <strong><?php echo htmlspecialchars($item['brand_name']); ?></strong>
                            <small><?php echo htmlspecialchars($item['name']); ?></small>
                        </div>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($item['size']); ?>
                    </td>
                    <td>
                        <span class="location-badge">
                            <?php echo htmlspecialchars($item['location_name'] ?? 'Unknown'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="condition-badge condition-<?php echo $item['condition']; ?>">
                            <?php echo ucfirst($item['condition']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="stock-level <?php echo $item['stock_quantity'] === 0 ? 'out-of-stock' : ($item['stock_quantity'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                            <?php echo $item['stock_quantity']; ?> units
                        </span>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        $<?php echo number_format($item['stock_quantity'] * $item['price'], 2); ?>
                    </td>
                    <td>
                        <?php if ($item['stock_quantity'] === 0): ?>
                            <span class="status-badge status-out-of-stock">Out of Stock</span>
                        <?php elseif ($item['stock_quantity'] <= 5): ?>
                            <span class="status-badge status-low-stock">Low Stock</span>
                        <?php else: ?>
                            <span class="status-badge status-in-stock">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="view_product.php?id=<?php echo $item['id']; ?>" class="btn-action btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-search fa-3x"></i>
        <h3>No Products Found</h3>
        <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
            <p>No products match your current search criteria.</p>
            <p>Try adjusting your filters or search terms.</p>
        <?php else: ?>
            <p>No products in inventory. Add products to start managing your inventory.</p>
        <?php endif; ?>
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1rem;">
            <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
                <a href="inventory.php" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
            <a href="products.php" class="btn btn-primary">Add Products</a>
        </div>
    </div>
<?php endif; ?>

<style>
.out-of-stock {
    background-color: #f8d7da;
}

.low-stock {
    background-color: #fff3cd;
}

.stock-level {
    font-weight: bold;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

.stock-level.in-stock {
    color: #155724;
    background: #d4edda;
}

.stock-level.low-stock {
    color: #856404;
    background: #fff3cd;
}

.stock-level.out-of-stock {
    color: #721c24;
    background: #f8d7da;
}

.condition-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

.condition-new {
    background: #d4edda;
    color: #155724;
}

.condition-used {
    background: #fff3cd;
    color: #856404;
}

.location-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    background: #e3f2fd;
    color: #1976d2;
    display: inline-block;
}

.type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

.type-tire {
    background: #e2e3e5;
    color: #383d41;
}

.filter-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.filter-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 150px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.filter-group select,
.filter-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    background: white;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.search-group {
    flex: 1;
    min-width: 250px;
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}

.filter-buttons .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-buttons .btn-secondary {
    background: #6c757d;
    color: white;
}

.filter-buttons .btn-secondary:hover {
    background: #5a6268;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-in-stock {
    background: #d4edda;
    color: #155724;
}

.status-low-stock {
    background: #fff3cd;
    color: #856404;
}

.status-out-of-stock {
    background: #f8d7da;
    color: #721c24;
}

.product-info {
    display: flex;
    flex-direction: column;
}

.product-info small {
    color: #666;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.admin-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
    color: white;
}

.btn-view {
    background: #28a745;
    color: white;
}

.btn-view:hover {
    background: #1e7e34;
    color: white;
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

/* Responsive design for filters */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .search-group {
        min-width: auto;
    }
    
    .filter-buttons {
        justify-content: center;
    }
}

/* Active filter indicators */
.filter-active {
    background: #e3f2fd;
    border-color: #2196f3;
}

.filter-active label {
    color: #1976d2;
}

/* Inventory header styles */
.inventory-header {
    margin-bottom: 1.5rem;
}

.inventory-header h2 {
    margin-bottom: 0.5rem;
    color: #333;
}

.search-summary {
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: #1976d2;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.search-summary strong {
    color: #1565c0;
}

.result-count {
    background: #2196f3;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: auto;
}

/* Enhanced empty state for search results */
.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
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
    margin-bottom: 0.5rem;
}

.empty-state .btn {
    margin-top: 1rem;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when brand or size filters change
    const brandSelect = document.getElementById('brand');
    const sizeSelect = document.getElementById('size');
    
    if (brandSelect) {
        brandSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (sizeSelect) {
        sizeSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    // Add visual feedback for active filters
    const filterGroups = document.querySelectorAll('.filter-group');
    filterGroups.forEach(group => {
        const select = group.querySelector('select');
        const input = group.querySelector('input');
        
        if (select && select.value && select.value !== 'all' && select.value !== '') {
            group.classList.add('filter-active');
        }
        
        if (input && input.value) {
            group.classList.add('filter-active');
        }
    });
    
    // Clear all filters functionality
    const clearButton = document.querySelector('a[href="inventory.php"]');
    if (clearButton) {
        clearButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'inventory.php';
        });
    }
    
    // Search input with debounce
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
});
</script> 