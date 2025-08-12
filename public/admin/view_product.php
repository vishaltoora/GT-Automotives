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
$page_title = 'View Product';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No product ID provided';
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product data with brand name
$stmt = $conn->prepare("SELECT t.*, b.name as brand_name FROM tires t LEFT JOIN brands b ON t.brand_id = b.id WHERE t.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Product not found';
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Get photos for used tires
$photos = [];
if ($product['condition'] === 'used') {
    $photos_stmt = $conn->prepare("SELECT * FROM used_tire_photos WHERE tire_id = ? ORDER BY photo_order");
    $photos_stmt->bind_param("i", $product_id);
    $photos_stmt->execute();
    $photos_result = $photos_stmt->get_result();
    
    while ($photo = $photos_result->fetch_assoc()) {
        $photos[] = $photo;
    }
    $photos_stmt->close();
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Product Details</h1>
    <div class="admin-actions">
        <button type="button" class="btn btn-primary" onclick="openEditProductDialog(<?php echo $product_id; ?>)">
            <i class="fas fa-edit"></i> Edit Product
        </button>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<div class="product-details-container">
    <div class="product-info-section">
        <div class="product-header">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <span class="condition-badge condition-<?php echo $product['condition']; ?>">
                <?php echo ucfirst($product['condition']); ?>
            </span>
        </div>
        
        <div class="product-details-grid">
            <div class="detail-item">
                <label>Brand:</label>
                <span><?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?></span>
            </div>
            
            <div class="detail-item">
                <label>Size:</label>
                <span><?php echo htmlspecialchars($product['size']); ?></span>
            </div>
            
            <div class="detail-item">
                <label>Price:</label>
                <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
            </div>
            
            <div class="detail-item">
                <label>Stock Quantity:</label>
                <span class="stock-level <?php echo $product['stock_quantity'] === 0 ? 'out-of-stock' : ($product['stock_quantity'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                    <?php echo $product['stock_quantity']; ?> units
                </span>
            </div>
            
            <?php if ($product['description']): ?>
                <div class="detail-item full-width">
                    <label>Description:</label>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($product['condition'] === 'used' && !empty($photos)): ?>
        <div class="product-photos-section">
            <h3>Product Photos</h3>
            <div class="photos-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="../<?php echo htmlspecialchars($photo['photo_url']); ?>" 
                             alt="Tire Photo" 
                             onclick="openPhotoModal(this.src)">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="modal" onclick="closePhotoModal()">
    <span class="close">&times;</span>
    <img id="modalImage" class="modal-content">
</div>

<style>
.product-details-container {
    max-width: 1200px;
    margin: 0 auto;
}

.product-info-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.product-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.product-header h2 {
    margin: 0;
    color: #333;
}

.condition-badge {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
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

.product-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
}

.detail-item span,
.detail-item p {
    color: #333;
    font-size: 1rem;
}

.detail-item .price {
    font-weight: bold;
    color: #007bff;
    font-size: 1.2rem;
}

.stock-level {
    font-weight: bold;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
    display: inline-block;
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

.product-photos-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-photos-section h3 {
    margin: 0 0 1.5rem 0;
    color: #333;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.photo-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.photo-item:hover {
    transform: scale(1.05);
}

.photo-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    margin-top: 5%;
}

.close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #bbb;
    text-decoration: none;
    cursor: pointer;
}

/* Modal Styles for Edit Dialog */
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
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

@media (max-width: 768px) {
    .product-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .product-details-grid {
        grid-template-columns: 1fr;
    }
    
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}
</style>

<script>
function openPhotoModal(src) {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modalImg.src = src;
}

function closePhotoModal() {
    document.getElementById('photoModal').style.display = 'none';
}

// Close modal when clicking the close button
document.querySelector('.close').addEventListener('click', closePhotoModal);

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePhotoModal();
    }
});
</script>

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

<script>
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
    var photoModal = document.getElementById('photoModal');
    if (event.target == photoModal) {
        closePhotoModal();
    }
    var editModal = document.getElementById('editProductDialog');
    if (event.target == editModal) {
        closeEditProductDialog();
    }
}

// Handle edit form submission
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
include_once 'includes/footer.php';
?> 