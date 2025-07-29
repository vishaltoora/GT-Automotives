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
$page_title = 'Add Size';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Size name is required';
    }
    
    if (strlen($name) > 50) {
        $errors[] = 'Size name must be 50 characters or less';
    }
    
    if (strlen($description) > 255) {
        $errors[] = 'Description must be 255 characters or less';
    }
    
    if ($sort_order < 0) {
        $errors[] = 'Sort order must be 0 or greater';
    }
    
    // Check if size name already exists
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM sizes WHERE name = ?");
        $check_stmt->bind_param("s", $name);
        $check_result = $check_stmt->get_result();
        $existing_count = $check_result->fetch_assoc()['count'];
        
        if ($existing_count > 0) {
            $errors[] = 'A size with this name already exists';
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sizes (name, description, is_active, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $description, $is_active, $sort_order);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Size added successfully';
            header('Location: sizes.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error();
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
    <h2>Add New Size</h2>
    
    <form action="" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Size Name *</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required maxlength="50">
                <small class="form-text">Enter the tire size (e.g., 205/55R16)</small>
            </div>
            
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>" min="0">
                <small class="form-text">Lower numbers appear first in lists</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" maxlength="255"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            <small class="form-text">Optional description for this tire size</small>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" id="is_active" <?php echo (isset($_POST['is_active']) ? 'checked' : 'checked'); ?>>
                Active
            </label>
            <small class="form-text">Inactive sizes won't appear in dropdowns</small>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Add Size</button>
            <a href="sizes.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
}

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