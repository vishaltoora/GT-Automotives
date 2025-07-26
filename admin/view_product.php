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
$stmt = $conn->prepare("SELECT * FROM tires WHERE id = ?");
$stmt->bind_param("i", $product_id);
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Product not found';
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();

// Get photos for used tires
$photos = [];
if ($product['condition'] === 'used') {
    $photos_stmt = $conn->prepare("SELECT * FROM used_tire_photos WHERE tire_id = ? ORDER BY photo_order");
    $photos_stmt->bind_param("i", $product_id);
    $photos_result = $photos_stmt->get_result();
    
    while ($photo = $photos_result->fetch_assoc()) {
        $photos[] = $photo;
    }
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Product Details</h1>
    <div class="admin-actions">
        <a href="edit_product.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Product
        </a>
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
                <span><?php echo htmlspecialchars($product['brand_name']); ?></span>
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

<?php
// Include footer
include_once 'includes/footer.php';
?> 