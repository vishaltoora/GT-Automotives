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
$page_title = 'Edit Service Category';

// Get category ID
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['error_message'] = 'Invalid category ID';
    header('Location: service_categories.php');
    exit;
}

// Get category data
$category_query = "SELECT * FROM service_categories WHERE id = ?";
$stmt = $conn->prepare($category_query);
$stmt->bindValue(1, $category_id, SQLITE3_INTEGER);
$category_result = $stmt->execute();
$category = $category_result->fetchArray(SQLITE3_ASSOC);

if (!$category) {
    $_SESSION['error_message'] = 'Category not found';
    header('Location: service_categories.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-tools');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Category name is required';
    }
    
    if (empty($icon)) {
        $errors[] = 'Icon is required';
    }
    
    // Check if category name already exists (excluding current category)
    $check_query = "SELECT COUNT(*) as count FROM service_categories WHERE name = ? AND id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $category_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
    
    if ($count > 0) {
        $errors[] = 'Category name already exists';
    }
    
    // If no errors, update the category
    if (empty($errors)) {
        $query = "UPDATE service_categories SET name = ?, description = ?, icon = ?, sort_order = ? WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $description);
        $stmt->bindValue(3, $icon);
        $stmt->bindValue(4, $sort_order, SQLITE3_INTEGER);
        $stmt->bindValue(5, $category_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Service category updated successfully';
            header('Location: service_categories.php');
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
                <label for="name">Category Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="icon">Icon Class</label>
                <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($category['icon']); ?>" placeholder="fas fa-tools" required>
                <small class="form-text">FontAwesome icon class (e.g., fas fa-tools, fas fa-oil-can)</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="<?php echo htmlspecialchars($category['sort_order']); ?>" required>
                <small class="form-text">Lower numbers appear first</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Brief description of this service category..."><?php echo htmlspecialchars($category['description']); ?></textarea>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="service_categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 