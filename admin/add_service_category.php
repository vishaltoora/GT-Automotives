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
$page_title = 'Add Service Category';

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
    
    // Check if category name already exists
    $check_query = "SELECT COUNT(*) as count FROM service_categories WHERE name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bindValue(1, $name);
    $result = $stmt->execute();
    $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
    
    if ($count > 0) {
        $errors[] = 'Category name already exists';
    }
    
    // If no errors, insert the category
    if (empty($errors)) {
        $query = "INSERT INTO service_categories (name, description, icon, sort_order) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $description);
        $stmt->bindValue(3, $icon);
        $stmt->bindValue(4, $sort_order, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Service category added successfully';
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
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="icon">Icon Class</label>
                <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($_POST['icon'] ?? 'fas fa-tools'); ?>" placeholder="fas fa-tools" required>
                <small class="form-text">FontAwesome icon class (e.g., fas fa-tools, fas fa-oil-can)</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>" required>
                <small class="form-text">Lower numbers appear first</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Brief description of this service category..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Add Category</button>
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