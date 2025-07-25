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
$page_title = 'Manage Products';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$location_filter = isset($_GET['location']) ? $_GET['location'] : 'all';
$product_type_filter = isset($_GET['product_type']) ? $_GET['product_type'] : 'all';
$search_condition = '';

if (!empty($search)) {
    $escaped_search = SQLite3::escapeString($search);
    $search_condition .= "WHERE t.name LIKE '%$escaped_search%' 
                       OR t.size LIKE '%$escaped_search%' 
                       OR b.name LIKE '%$escaped_search%'";
}

if ($status_filter !== 'all') {
    $escaped_status = SQLite3::escapeString($status_filter);
    if (!empty($search_condition)) {
        $search_condition .= " AND t.`condition` = '$escaped_status'";
    } else {
        $search_condition = "WHERE t.`condition` = '$escaped_status'";
    }
}

if ($location_filter !== 'all') {
    $escaped_location = SQLite3::escapeString($location_filter);
    if (!empty($search_condition)) {
        $search_condition .= " AND t.location_id = '$escaped_location'";
    } else {
        $search_condition = "WHERE t.location_id = '$escaped_location'";
    }
}

if ($product_type_filter !== 'all') {
    $escaped_product_type = SQLite3::escapeString($product_type_filter);
    if (!empty($search_condition)) {
        $search_condition .= " AND t.name = '$escaped_product_type'";
    } else {
        $search_condition = "WHERE t.name = '$escaped_product_type'";
    }
}

// Get total products for pagination
$total_query = "SELECT COUNT(*) as count FROM tires t LEFT JOIN brands b ON t.brand_id = b.id LEFT JOIN locations l ON t.location_id = l.id $search_condition";
$total_result = $conn->query($total_query);
$total_products = $total_result->fetchArray(SQLITE3_ASSOC)['count'];
$total_pages = ceil($total_products / $limit);

// Get products for this page (with brand name and location)
$products_query = "SELECT t.*, b.name as brand_name, l.name as location_name FROM tires t LEFT JOIN brands b ON t.brand_id = b.id LEFT JOIN locations l ON t.location_id = l.id $search_condition ORDER BY t.id DESC LIMIT $start, $limit";
$products_result = $conn->query($products_query);

// Check if there are any products
$has_products = false;
$products_data = [];
while ($product = $products_result->fetchArray(SQLITE3_ASSOC)) {
    $products_data[] = $product;
    $has_products = true;
}

// Include header
include_once 'includes/header.php';
?>

<!-- Search Form -->
<div class="admin-search" style="margin-bottom: 2rem;">
    <form action="" method="GET" class="admin-form" style="display: flex; gap: 1rem; padding: 1rem; align-items: end;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by brand, product type, or size..." style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <select name="status" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New Only</option>
                <option value="used" <?php echo $status_filter === 'used' ? 'selected' : ''; ?>>Used Only</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <select name="location" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="all" <?php echo $location_filter === 'all' ? 'selected' : ''; ?>>All Locations</option>
                <?php
                $locations_query = "SELECT id, name FROM locations ORDER BY name";
                $locations_result = $conn->query($locations_query);
                while ($location = $locations_result->fetchArray(SQLITE3_ASSOC)) {
                    echo '<option value="' . htmlspecialchars($location['id']) . '"';
                    if ($location_filter === $location['id']) {
                        echo ' selected';
                    }
                    echo '>' . htmlspecialchars($location['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <select name="product_type" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <option value="all" <?php echo $product_type_filter === 'all' ? 'selected' : ''; ?>>All Product Types</option>
                <option value="Winter Tires" <?php echo $product_type_filter === 'Winter Tires' ? 'selected' : ''; ?>>❄️ Winter Tires</option>
                <option value="Summer Tires" <?php echo $product_type_filter === 'Summer Tires' ? 'selected' : ''; ?>>☀️ Summer Tires</option>
                <option value="All Season Tires" <?php echo $product_type_filter === 'All Season Tires' ? 'selected' : ''; ?>>🌦️ All Season Tires</option>
                <option value="Studded Tires" <?php echo $product_type_filter === 'Studded Tires' ? 'selected' : ''; ?>>🔩 Studded Tires</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($search) || $status_filter !== 'all' || $location_filter !== 'all' || $product_type_filter !== 'all'): ?>
            <a href="products.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Products Table -->
<div class="admin-actions" style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <h2>Products (<?php echo $total_products; ?>)</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="brands.php" class="btn btn-secondary">
            <i class="fas fa-tags"></i> Manage Brands
        </a>
        <a href="add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<?php if ($has_products): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Product Type</th>
                <th>Size</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products_data as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                    <td>
                        <?php 
                        $product_type = htmlspecialchars($product['name']);
                        $icon = '';
                        $css_class = '';
                        switch ($product_type) {
                            case 'Winter Tires':
                                $icon = '❄️';
                                $css_class = 'product-type-winter';
                                break;
                            case 'Summer Tires':
                                $icon = '☀️';
                                $css_class = 'product-type-summer';
                                break;
                            case 'All Season Tires':
                                $icon = '🌦️';
                                $css_class = 'product-type-all-season';
                                break;
                            case 'Studded Tires':
                                $icon = '🔩';
                                $css_class = 'product-type-studded';
                                break;
                            default:
                                $icon = '🛞';
                                $css_class = '';
                        }
                        ?>
                        <div class="product-type-cell">
                            <span class="product-type-icon"><?php echo $icon; ?></span>
                            <span class="product-type-text <?php echo $css_class; ?>"><?php echo $product_type; ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($product['size']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                    <td>
                        <span class="location-badge">
                            <?php echo htmlspecialchars($product['location_name'] ?? 'Unknown'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $product['condition']; ?>">
                            <?php echo ucfirst($product['condition']); ?>
                        </span>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-action btn-delete delete-confirm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== 'all' ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $location_filter !== 'all' ? '&location=' . urlencode($location_filter) : ''; ?><?php echo $product_type_filter !== 'all' ? '&product_type=' . urlencode($product_type_filter) : ''; ?>">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== 'all' ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $location_filter !== 'all' ? '&location=' . urlencode($location_filter) : ''; ?><?php echo $product_type_filter !== 'all' ? '&product_type=' . urlencode($product_type_filter) : ''; ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== 'all' ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $location_filter !== 'all' ? '&location=' . urlencode($location_filter) : ''; ?><?php echo $product_type_filter !== 'all' ? '&product_type=' . urlencode($product_type_filter) : ''; ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-danger">
        No products found. <?php echo !empty($search) ? 'Try a different search term or ' : ''; ?><a href="add_product.php">add a new product</a>.
    </div>
<?php endif; ?>

<style>
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.status-new {
    background: #d4edda;
    color: #155724;
}

.status-used {
    background: #fff3cd;
    color: #856404;
}

.location-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #e3f2fd;
    color: #1565c0;
    display: inline-block;
}

/* Filter dropdown styling */
.admin-search select {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.5rem;
    font-size: 1rem;
    min-width: 150px;
}

.admin-search select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

/* Product type filter styling */
.admin-search select[name="product_type"] option {
    padding: 0.5rem;
}

.admin-search select[name="product_type"] option[value="Winter Tires"] {
    background: #e3f2fd;
}

.admin-search select[name="product_type"] option[value="Summer Tires"] {
    background: #fff3e0;
}

.admin-search select[name="product_type"] option[value="All Season Tires"] {
    background: #f3e5f5;
}

.admin-search select[name="product_type"] option[value="Studded Tires"] {
    background: #ffebee;
}

/* Product type column styling */
.product-type-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.product-type-icon {
    font-size: 1.2rem;
    display: inline-block;
    width: 1.5rem;
    text-align: center;
}

.product-type-text {
    font-weight: 500;
    color: #333;
}

/* Winter tires styling */
.product-type-winter {
    color: #1565c0;
}

/* Summer tires styling */
.product-type-summer {
    color: #f57c00;
}

/* All season tires styling */
.product-type-all-season {
    color: #7b1fa2;
}

/* Studded tires styling */
.product-type-studded {
    color: #d32f2f;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 