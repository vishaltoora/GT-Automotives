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
$page_title = 'Edit Product';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No product ID provided';
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Fetch brands for dropdown
$brands = [];
$result = $conn->query('SELECT id, name FROM brands ORDER BY name ASC');
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $brands[] = $row;
}

// Fetch locations for dropdown
$locations = [];
$locations_result = $conn->query('SELECT id, name FROM locations ORDER BY name ASC');
while ($row = $locations_result->fetchArray(SQLITE3_ASSOC)) {
    $locations[] = $row;
}

// Fetch sizes for dropdown
$sizes = [];
$sizes_result = $conn->query('SELECT id, name, description FROM sizes WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
while ($row = $sizes_result->fetchArray(SQLITE3_ASSOC)) {
    $sizes[] = $row;
}

// Get product data
$query = "SELECT * FROM tires WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
$result = $stmt->execute();

if ($result->numColumns() === 0) {
    $_SESSION['error_message'] = 'Product not found';
    header('Location: products.php');
    exit;
}

$product = $result->fetchArray(SQLITE3_ASSOC);

// Get existing photos for used tires
$existing_photos = [];
if ($product['condition'] === 'used') {
    $photos_query = "SELECT * FROM used_tire_photos WHERE tire_id = ? ORDER BY photo_order ASC";
    $photos_stmt = $conn->prepare($photos_query);
    $photos_stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
    $photos_result = $photos_stmt->execute();
    
    while ($photo = $photos_result->fetchArray(SQLITE3_ASSOC)) {
        $existing_photos[] = $photo;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $product_type = trim($_POST['product_type'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $condition = $_POST['condition'] ?? 'new';
    $location_id = intval($_POST['location_id'] ?? $product['location_id'] ?? 1);
    
    // Validate input
    $errors = [];
    
    if ($brand_id <= 0) {
        $errors[] = 'Brand is required.';
    }
    
    if (empty($product_type)) {
        $errors[] = 'Product type is required';
    }
    
    if (empty($size)) {
        $errors[] = 'Size is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if ($stock_quantity < 0) {
        $errors[] = 'Stock quantity cannot be negative';
    }
    
    if ($location_id <= 0) {
        $errors[] = 'Location is required';
    }
    
    if (!in_array($condition, ['new', 'used'])) {
        $errors[] = 'Invalid condition selected';
    }
    
    // Handle photo uploads for used tires
    $uploaded_photos = [];
    if ($condition === 'used' && isset($_FILES['photos'])) {
        $upload_dir = '../images/used_tires/photos/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Process each uploaded file
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_size = $_FILES['photos']['size'][$key];
                $file_type = $_FILES['photos']['type'][$key];
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file_type, $allowed_types)) {
                    $errors[] = "File '$file_name' is not a valid image type. Allowed: JPG, PNG, GIF";
                    continue;
                }
                
                // Validate file size (max 5MB)
                if ($file_size > 5 * 1024 * 1024) {
                    $errors[] = "File '$file_name' is too large. Maximum size is 5MB";
                    continue;
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $uploaded_photos[] = 'images/used_tires/photos/' . $unique_filename;
                } else {
                    $errors[] = "Failed to upload file '$file_name'";
                }
            }
        }
    }
    
    // Handle photo deletions
    $photos_to_delete = $_POST['delete_photos'] ?? [];
    
    // If no errors, update the product
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE tires 
            SET brand_id = ?, name = ?, size = ?, price = ?, description = ?, stock_quantity = ?, `condition` = ?, location_id = ?
            WHERE id = ?
        ");
        
        $stmt->bindValue(1, $brand_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $product_type, SQLITE3_TEXT);
        $stmt->bindValue(3, $size, SQLITE3_TEXT);
        $stmt->bindValue(4, $price, SQLITE3_FLOAT);
        $stmt->bindValue(5, $description, SQLITE3_TEXT);
        $stmt->bindValue(6, $stock_quantity, SQLITE3_INTEGER);
        $stmt->bindValue(7, $condition, SQLITE3_TEXT);
        $stmt->bindValue(8, $location_id, SQLITE3_INTEGER);
        $stmt->bindValue(9, $product_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            // Handle photo management for used tires
            if ($condition === 'used') {
                // Delete photos that were marked for deletion
                foreach ($photos_to_delete as $photo_id) {
                    $delete_query = "DELETE FROM used_tire_photos WHERE id = ? AND tire_id = ?";
                    $delete_stmt = $conn->prepare($delete_query);
                    $delete_stmt->bindValue(1, $photo_id, SQLITE3_INTEGER);
                    $delete_stmt->bindValue(2, $product_id, SQLITE3_INTEGER);
                    $delete_stmt->execute();
                }
                
                // Insert new photos
                foreach ($uploaded_photos as $index => $photo_url) {
                    $photo_query = "INSERT INTO used_tire_photos (tire_id, photo_url, photo_order) VALUES (?, ?, ?)";
                    $photo_stmt = $conn->prepare($photo_query);
                    $photo_stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
                    $photo_stmt->bindValue(2, $photo_url, SQLITE3_TEXT);
                    $photo_stmt->bindValue(3, $index, SQLITE3_INTEGER);
                    $photo_stmt->execute();
                }
            } else {
                // If changing from used to new, delete all photos
                $delete_all_photos = "DELETE FROM used_tire_photos WHERE tire_id = ?";
                $delete_stmt = $conn->prepare($delete_all_photos);
                $delete_stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
                $delete_stmt->execute();
            }
            
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Product updated successfully';
            header('Location: products.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->lastErrorMsg();
        }
    }
    
    // Set error message if there are errors
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-form">
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label for="brand_id">Brand</label>
                <select name="brand_id" id="brand_id" class="select2-enhanced" required>
                    <option value="">Search and select brand...</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['id']; ?>" <?php echo ((isset($_POST['brand_id']) ? $_POST['brand_id'] : $product['brand_id']) == $brand['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="product_type">Product Type</label>
                <select name="product_type" id="product_type" class="select2-enhanced" required>
                    <option value="">Select product type...</option>
                    <option value="Winter Tires" <?php echo ((isset($_POST['product_type']) ? $_POST['product_type'] : $product['name']) === 'Winter Tires') ? 'selected' : ''; ?>>Winter Tires</option>
                    <option value="Summer Tires" <?php echo ((isset($_POST['product_type']) ? $_POST['product_type'] : $product['name']) === 'Summer Tires') ? 'selected' : ''; ?>>Summer Tires</option>
                    <option value="All Season Tires" <?php echo ((isset($_POST['product_type']) ? $_POST['product_type'] : $product['name']) === 'All Season Tires') ? 'selected' : ''; ?>>All Season Tires</option>
                    <option value="Studded Tires" <?php echo ((isset($_POST['product_type']) ? $_POST['product_type'] : $product['name']) === 'Studded Tires') ? 'selected' : ''; ?>>Studded Tires</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="size">Size</label>
                <select name="size" id="size" class="select2-enhanced" required>
                    <option value="">Search and select size...</option>
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?php echo htmlspecialchars($size['name']); ?>" <?php echo ((isset($_POST['size']) ? $_POST['size'] : $product['size']) === $size['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($size['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" name="price" id="price" min="0.01" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" min="0" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? $product['stock_quantity']); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="condition">Condition</label>
                <select name="condition" id="condition" class="select2-enhanced" required>
                    <option value="new" <?php echo ((isset($_POST['condition']) ? $_POST['condition'] : $product['condition']) === 'new') ? 'selected' : ''; ?>>New</option>
                    <option value="used" <?php echo ((isset($_POST['condition']) ? $_POST['condition'] : $product['condition']) === 'used') ? 'selected' : ''; ?>>Used</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="location_id">Location</label>
                <select name="location_id" id="location_id" class="select2-enhanced" required>
                    <option value="">Select location...</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?php echo $location['id']; ?>" <?php echo ((isset($_POST['location_id']) ? $_POST['location_id'] : $product['location_id']) == $location['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($location['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? $product['description']); ?></textarea>
        </div>
        
        <!-- Used Tire Photos Section -->
        <div id="used-tire-photos" style="display: none;">
            <div class="form-group">
                <label for="photos">Upload New Photos (for used tires)</label>
                <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="form-control">
                <small class="form-text text-muted">
                    Upload additional photos of the used tire. Allowed formats: JPG, PNG, GIF. Maximum file size: 5MB per image.
                </small>
            </div>
            
            <!-- Existing Photos -->
            <?php if (!empty($existing_photos)): ?>
                <div class="form-group">
                    <label>Existing Photos</label>
                    <div class="existing-photos-grid">
                        <?php foreach ($existing_photos as $photo): ?>
                            <div class="existing-photo-item">
                                <img src="../<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Tire Photo">
                                <label class="delete-photo-label">
                                    <input type="checkbox" name="delete_photos[]" value="<?php echo $photo['id']; ?>">
                                    Delete
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div id="photo-preview" class="photo-preview-grid"></div>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="brands.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Brands
            </a>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.photo-preview-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.photo-preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.photo-preview-item .remove-photo {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(255, 0, 0, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    cursor: pointer;
    font-size: 12px;
}

.existing-photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.existing-photo-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.existing-photo-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.delete-photo-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem;
    text-align: center;
    font-size: 0.8rem;
    cursor: pointer;
}

.delete-photo-label input[type="checkbox"] {
    margin-right: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Enhanced Select2 Styles */
.select2-container {
    width: 100% !important;
    margin-bottom: 0.5rem;
}

.select2-container--default .select2-selection--single {
    height: 45px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    background-color: white !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 43px !important;
    padding-left: 12px !important;
    padding-right: 60px !important;
    color: #333 !important;
    font-size: 1rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #6c757d !important;
}

.select2-container--default .select2-selection--single .select2-selection__clear {
    position: absolute !important;
    right: 35px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 20px !important;
    height: 20px !important;
    background: #dc3545 !important;
    color: white !important;
    border: none !important;
    border-radius: 50% !important;
    font-size: 14px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 10 !important;
}

.select2-container--default .select2-selection--single .select2-selection__clear:hover {
    background: #c82333 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 43px !important;
    width: 30px !important;
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #666 transparent transparent transparent !important;
    border-style: solid !important;
    border-width: 5px 4px 0 4px !important;
    height: 0 !important;
    left: 50% !important;
    margin-left: -4px !important;
    margin-top: -2px !important;
    position: absolute !important;
    top: 50% !important;
    width: 0 !important;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #666 transparent !important;
    border-width: 0 4px 5px 4px !important;
}

.select2-dropdown {
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    z-index: 9999 !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    padding: 8px 12px !important;
    font-size: 1rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.select2-container--default .select2-results__option {
    padding: 10px 12px !important;
    font-size: 1rem !important;
    border-bottom: 1px solid #f0f0f0 !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #007bff !important;
    color: white !important;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #e9ecef !important;
    color: #333 !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected=true] {
    background-color: #007bff !important;
    color: white !important;
}

.select2-results__option {
    cursor: pointer !important;
}

.select2-results__option:hover {
    background-color: #f8f9fa !important;
}

/* Form group spacing for enhanced selects */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
}

/* Ensure proper spacing */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-row .form-group:nth-child(3) {
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2 for enhanced select boxes
    $('.select2-enhanced').select2({
        placeholder: function() {
            return $(this).data('placeholder') || 'Search and select...';
        },
        allowClear: true,
        width: '100%',
        dropdownParent: $('body'),
        minimumResultsForSearch: 0, // Always show search
        language: {
            noResults: function() {
                return "No results found";
            },
            searching: function() {
                return "Searching...";
            }
        }
    });
    
    // Set placeholders
    $('#brand_id').attr('data-placeholder', 'Search and select brand...');
    $('#product_type').attr('data-placeholder', 'Select product type...');
    $('#size').attr('data-placeholder', 'Search and select size...');
    $('#condition').attr('data-placeholder', 'Select condition...');
    $('#location_id').attr('data-placeholder', 'Select location...');
    
    // Force refresh Select2 after initialization
    setTimeout(function() {
        $('.select2-enhanced').select2('destroy').select2({
            placeholder: function() {
                return $(this).data('placeholder') || 'Search and select...';
            },
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
            minimumResultsForSearch: 0
        });
    }, 100);
    
    const conditionSelect = document.getElementById('condition');
    const usedTirePhotos = document.getElementById('used-tire-photos');
    const photoInput = document.getElementById('photos');
    const photoPreview = document.getElementById('photo-preview');
    
    // Show/hide photo upload section based on condition
    function togglePhotoSection() {
        if (conditionSelect.value === 'used') {
            usedTirePhotos.style.display = 'block';
        } else {
            usedTirePhotos.style.display = 'none';
            photoPreview.innerHTML = '';
        }
    }
    
    conditionSelect.addEventListener('change', togglePhotoSection);
    togglePhotoSection(); // Initial state
    
    // Handle photo preview
    photoInput.addEventListener('change', function(e) {
        photoPreview.innerHTML = '';
        const files = Array.from(e.target.files);
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'photo-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-photo" onclick="removePhoto(${index})">×</button>
                    `;
                    photoPreview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Remove photo function
    window.removePhoto = function(index) {
        const dt = new DataTransfer();
        const files = Array.from(photoInput.files);
        files.splice(index, 1);
        files.forEach(file => dt.items.add(file));
        photoInput.files = dt.files;
        
        // Re-trigger change event to update preview
        photoInput.dispatchEvent(new Event('change'));
    };
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 