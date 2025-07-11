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
$page_title = 'Edit Service';

// Get service ID
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id <= 0) {
    $_SESSION['error_message'] = 'Invalid service ID';
    header('Location: services.php');
    exit;
}

// Fetch service categories for dropdown
$categories = [];
$result = $conn->query('SELECT name, description FROM service_categories ORDER BY sort_order, name');
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row;
}

// Get service data
$service_query = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($service_query);
$stmt->bindValue(1, $service_id, SQLITE3_INTEGER);
$service_result = $stmt->execute();
$service = $service_result->fetchArray(SQLITE3_ASSOC);

if (!$service) {
    $_SESSION['error_message'] = 'Service not found';
    header('Location: services.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $duration_minutes = intval($_POST['duration_minutes'] ?? 60);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Service name is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if ($duration_minutes <= 0) {
        $errors[] = 'Duration must be greater than zero';
    }
    
    // If no errors, update the service
    if (empty($errors)) {
        $query = "UPDATE services SET name = ?, description = ?, price = ?, category = ?, duration_minutes = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $description);
        $stmt->bindValue(3, $price);
        $stmt->bindValue(4, $category);
        $stmt->bindValue(5, $duration_minutes, SQLITE3_INTEGER);
        $stmt->bindValue(6, $is_active, SQLITE3_INTEGER);
        $stmt->bindValue(7, $service_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Service updated successfully';
            header('Location: services.php');
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
    <form action="" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Service Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="select2-enhanced" required>
                    <option value="">Select category...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo $service['category'] === $category['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" name="price" id="price" min="0.01" step="0.01" value="<?php echo htmlspecialchars($service['price']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="duration_minutes">Duration (minutes)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" min="15" step="15" value="<?php echo htmlspecialchars($service['duration_minutes']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="is_active" class="checkbox-label">
                    <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $service['is_active'] ? 'checked' : ''; ?>>
                    Active Service
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5" placeholder="Describe the service, what's included, and any special notes..."><?php echo htmlspecialchars($service['description']); ?></textarea>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Update Service</button>
            <a href="service_categories.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="services.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    width: auto;
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
    background: rgb(126, 125, 125) !important;
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
    background: rgb(17, 16, 16) !important;
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
</style>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-enhanced').select2({
        placeholder: 'Select an option...',
        allowClear: true
    });
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 