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
$stmt = $conn->prepare("SELECT * FROM service_categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

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
    $stmt->bind_param("si", $name, $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        $errors[] = 'Category name already exists';
    }
    
    // If no errors, update the category
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE service_categories SET name = ?, description = ?, icon = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $icon, $category_id);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Service category updated successfully';
            header('Location: service_categories.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
        $stmt->close();
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

.form-submit {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

.form-submit .btn {
    min-width: 120px;
    text-align: center;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 