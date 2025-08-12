<?php

// Set base path for includes
$base_path = dirname(__DIR__);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to catch any early errors
ob_start();

// Initialize variables with default values
$total_products = 0;
$has_products = false;
$products_data = [];
$total_pages = 1;

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
    $page_title = 'Manage Products';

    // Pagination settings
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $limit;

    // Search filter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $product_type_filter = isset($_GET['product_type']) ? $_GET['product_type'] : 'all';
    $location_filter = isset($_GET['location']) ? $_GET['location'] : 'all';
    $search_condition = '';

    if (!empty($search)) {
        $escaped_search = mysqli_real_escape_string($conn, $search);
        $search_condition .= "WHERE t.name LIKE '%$escaped_search%' 
                           OR t.size LIKE '%$escaped_search%' 
                           OR l.description LIKE '%$escaped_search%'
                           OR b.name LIKE '%$escaped_search%'";
    }

    if ($status_filter !== 'all') {
        $escaped_status = mysqli_real_escape_string($conn, $status_filter);
        if (!empty($search_condition)) {
            $search_condition .= " AND t.`condition` = '$escaped_status'";
        } else {
            $search_condition = "WHERE t.`condition` = '$escaped_status'";
        }
    }

    if ($product_type_filter !== 'all') {
        $escaped_product_type = mysqli_real_escape_string($conn, $product_type_filter);
        if (!empty($search_condition)) {
            $search_condition .= " AND t.name = '$escaped_product_type'";
        } else {
            $search_condition = "WHERE t.name = '$escaped_product_type'";
        }
    }

    if ($location_filter !== 'all') {
        $escaped_location = mysqli_real_escape_string($conn, $location_filter);
        if (!empty($search_condition)) {
            $search_condition .= " AND t.location_id = '$escaped_location'";
        } else {
            $search_condition = "WHERE t.location_id = '$escaped_location'";
        }
    }

    // Get total products for pagination
    if (isset($conn)) {
        $total_query = "SELECT COUNT(*) as count FROM tires t LEFT JOIN brands b ON t.brand_id = b.id LEFT JOIN locations l ON t.location_id = l.id $search_condition";
        $total_result = $conn->query($total_query);
        
        if (!$total_result) {
            $total_products = 0;
        } else {
            $total_products = $total_result->fetch_assoc()['count'];
        }
        
        $total_pages = ceil($total_products / $limit);

        // Get products for this page (with brand name, location name, and location description)
        $products_query = "SELECT t.*, b.name as brand_name, l.name as location_name, l.description as location_description FROM tires t LEFT JOIN brands b ON t.brand_id = b.id LEFT JOIN locations l ON t.location_id = l.id $search_condition ORDER BY t.id DESC LIMIT $start, $limit";
        $products_result = $conn->query($products_query);
        
        if ($products_result) {
            // Check if there are any products
            while ($product = $products_result->fetch_assoc()) {
                $products_data[] = $product;
                $has_products = true;
            }
        }
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in admin/products.php: " . $e->getMessage());
}

// Flush any output so far
ob_flush();

// Include header
if (file_exists('includes/header.php')) {
    include_once 'includes/header.php';
}
?>

<!-- Search Form -->
<div class="admin-search" style="margin-bottom: 2rem;">
    <form action="" method="GET" class="admin-form" style="display: flex; gap: 1rem; padding: 1rem; align-items: end;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by brand, product type, size, or location description..." style="width: 100%;">
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
                if (isset($conn)) {
                    $locations_query = "SELECT id, name FROM locations ORDER BY name";
                    $locations_result = $conn->query($locations_query);
                    if ($locations_result) {
                        while ($location = $locations_result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($location['id']) . '"';
                            if ($location_filter === $location['id']) {
                                echo ' selected';
                            }
                            echo '>' . htmlspecialchars($location['name']) . '</option>';
                        }
                    }
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
        <?php if (!empty($search) || $status_filter !== 'all' || $product_type_filter !== 'all' || $location_filter !== 'all'): ?>
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
        <button type="button" class="btn btn-primary" onclick="openAddProductDialog()">
            <i class="fas fa-plus"></i> Add Product
        </button>
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
                <th>Location Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products_data as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?></td>
                    <td>
                        <?php 
                        $product_type = htmlspecialchars($product['name'] ?? 'Unknown');
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
                    <td><?php echo htmlspecialchars($product['size'] ?? 'N/A'); ?></td>
                    <td>$<?php echo number_format($product['price'] ?? 0, 2); ?></td>
                    <td><?php echo $product['stock_quantity'] ?? 0; ?></td>
                    <td>
                        <span class="location-badge">
                            <?php echo htmlspecialchars($product['location_name'] ?? 'Unknown'); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $location_description = htmlspecialchars($product['location_description'] ?? '');
                        if (!empty($location_description)) {
                            // Truncate description if too long
                            if (strlen($location_description) > 50) {
                                echo '<span title="' . htmlspecialchars($location_description) . '">' . substr($location_description, 0, 50) . '...</span>';
                            } else {
                                echo $location_description;
                            }
                        } else {
                            echo '<span style="color: #999; font-style: italic;">No location description</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        $status = $product['condition'] ?? 'new';
                        $status_class = $status === 'new' ? 'status-new' : 'status-used';
                        $status_text = $status === 'new' ? 'New' : 'Used';
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="view_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-warning" title="Edit" onclick="openEditProductDialog(<?php echo $product['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="#" class="btn btn-sm btn-danger" title="Delete" onclick="showCustomConfirm('Are you sure you want to delete this product?', function(confirmed) { if(confirmed) window.location.href='delete_product.php?id=<?php echo $product['id']; ?>'; }); return false;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination" style="margin-top: 2rem; text-align: center;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&product_type=<?php echo urlencode($product_type_filter); ?>&location=<?php echo urlencode($location_filter); ?>" class="btn btn-secondary">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="btn btn-primary"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&product_type=<?php echo urlencode($product_type_filter); ?>&location=<?php echo urlencode($location_filter); ?>" class="btn btn-secondary"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&product_type=<?php echo urlencode($product_type_filter); ?>&location=<?php echo urlencode($location_filter); ?>" class="btn btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="no-products" style="text-align: center; padding: 3rem; color: #666;">
        <i class="fas fa-box-open fa-3x" style="color: #ddd; margin-bottom: 1rem;"></i>
        <h3>No products found</h3>
        <p>No products match your current search criteria.</p>
        <p>Try adjusting your filters or add some products.</p>
        <div style="margin-top: 2rem;">
            <button type="button" class="btn btn-primary" onclick="openAddProductDialog()">
                <i class="fas fa-plus"></i> Add Your First Product
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Add Product Dialog -->
<div id="addProductDialog" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <span class="close" onclick="closeAddProductDialog()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addProductForm" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="brand_id">Brand *</label>
                        <select name="brand_id" id="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php
                            // Fetch brands for dropdown
                            $brands_result = $conn->query('SELECT id, name FROM brands ORDER BY name ASC');
                            if ($brands_result) {
                                while ($brand = $brands_result->fetch_assoc()) {
                                    echo '<option value="' . $brand['id'] . '">' . htmlspecialchars($brand['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location_id">Location *</label>
                        <select name="location_id" id="location_id" required>
                            <option value="">Select Location</option>
                            <?php
                            // Fetch locations for dropdown
                            $locations_result = $conn->query('SELECT id, name FROM locations ORDER BY name ASC');
                            if ($locations_result) {
                                while ($location = $locations_result->fetch_assoc()) {
                                    echo '<option value="' . $location['id'] . '">' . htmlspecialchars($location['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Type *</label>
                        <select name="name" id="name" required>
                            <option value="">Select Product Type</option>
                            <option value="Winter Tires">❄️ Winter Tires</option>
                            <option value="Summer Tires">☀️ Summer Tires</option>
                            <option value="All Season Tires">🌦️ All Season Tires</option>
                            <option value="Studded Tires">🔩 Studded Tires</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="size">Size *</label>
                        <select name="size" id="size" required>
                            <option value="">Select Size</option>
                            <?php
                            // Fetch available sizes for dropdown
                            $sizes_result = $conn->query('SELECT id, name, description FROM sizes WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
                            if ($sizes_result) {
                                while ($size = $sizes_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($size['name']) . '">' . htmlspecialchars($size['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" min="0" value="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="condition">Condition</label>
                        <select name="condition" id="condition">
                            <option value="new">New</option>
                            <option value="used">Used</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddProductDialog()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Dialog -->
<div id="editProductDialog" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>Edit Product</h2>
            <span class="close" onclick="closeEditProductDialog()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editProductForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_brand_id">Brand *</label>
                        <select name="brand_id" id="edit_brand_id" required>
                            <option value="">Select Brand</option>
                            <?php
                            // Fetch brands for dropdown
                            $brands_result = $conn->query('SELECT id, name FROM brands ORDER BY name ASC');
                            if ($brands_result) {
                                while ($brand = $brands_result->fetch_assoc()) {
                                    echo '<option value="' . $brand['id'] . '">' . htmlspecialchars($brand['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_location_id">Location *</label>
                        <select name="location_id" id="edit_location_id" required>
                            <option value="">Select Location</option>
                            <?php
                            // Fetch locations for dropdown
                            $locations_result = $conn->query('SELECT id, name FROM locations ORDER BY name ASC');
                            if ($locations_result) {
                                while ($location = $locations_result->fetch_assoc()) {
                                    echo '<option value="' . $location['id'] . '">' . htmlspecialchars($location['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_name">Product Type *</label>
                        <select name="name" id="edit_name" required>
                            <option value="">Select Product Type</option>
                            <option value="Winter Tires">❄️ Winter Tires</option>
                            <option value="Summer Tires">☀️ Summer Tires</option>
                            <option value="All Season Tires">🌦️ All Season Tires</option>
                            <option value="Studded Tires">🔩 Studded Tires</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_size">Size *</label>
                        <select name="size" id="edit_size" required>
                            <option value="">Select Size</option>
                            <?php
                            // Fetch available sizes for dropdown
                            $sizes_result = $conn->query('SELECT id, name, description FROM sizes WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
                            if ($sizes_result) {
                                while ($size = $sizes_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($size['name']) . '">' . htmlspecialchars($size['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_price">Price *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_stock_quantity">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity" min="0" value="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_condition">Condition</label>
                        <select name="condition" id="edit_condition">
                            <option value="new">New</option>
                            <option value="used">Used</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditProductDialog()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 30px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
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
    background-color: #1a2d3f;
}

.btn-secondary {
    background-color: #4a5c6b;
    color: white;
}

.btn-secondary:hover {
    background-color: #3d4c59;
}
</style>

<script>
function openAddProductDialog() {
    document.getElementById('addProductDialog').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddProductDialog() {
    document.getElementById('addProductDialog').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openEditProductDialog(productId) {
    document.getElementById('editProductDialog').style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Fetch product data for editing
    fetch('get_product_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_brand_id').value = data.brand_id;
            document.getElementById('edit_location_id').value = data.location_id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_size').value = data.size;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_stock_quantity').value = data.stock_quantity;
            document.getElementById('edit_condition').value = data.condition;
            document.getElementById('edit_description').value = data.description;
        } else {
            alert('Error fetching product data for editing: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching product data for editing');
    });
}

function closeEditProductDialog() {
    document.getElementById('editProductDialog').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('addProductDialog');
    if (event.target == modal) {
        closeAddProductDialog();
    }
    var editModal = document.getElementById('editProductDialog');
    if (event.target == editModal) {
        closeEditProductDialog();
    }
}

// Handle form submission
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('add_product_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added successfully!');
            closeAddProductDialog();
            // Reload the page to show the new product
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add product'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the product');
    });
});

document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('edit_product_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product updated successfully!');
            closeEditProductDialog();
            // Reload the page to show the updated product
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update product'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the product');
    });
});
</script>

<?php
// Include footer
if (file_exists('includes/footer.php')) {
    include_once 'includes/footer.php';
}
?> 