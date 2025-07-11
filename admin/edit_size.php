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
$page_title = 'Edit Size';

// Get size ID from URL
$size_id = intval($_GET['id'] ?? 0);

if ($size_id <= 0) {
    $_SESSION['error_message'] = 'Invalid size ID';
    header('Location: sizes.php');
    exit;
}

// Get size data
$size_query = "SELECT * FROM sizes WHERE id = ?";
$size_stmt = $conn->prepare($size_query);
$size_stmt->bindValue(1, $size_id, SQLITE3_INTEGER);
$size_result = $size_stmt->execute();
$size = $size_result->fetchArray(SQLITE3_ASSOC);

if (!$size) {
    $_SESSION['error_message'] = 'Size not found';
    header('Location: sizes.php');
    exit;
}

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
    
    // Check if size name already exists (excluding current size)
    if (empty($errors)) {
        $check_query = "SELECT COUNT(*) as count FROM sizes WHERE name = ? AND id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindValue(1, $name, SQLITE3_TEXT);
        $check_stmt->bindValue(2, $size_id, SQLITE3_INTEGER);
        $check_result = $check_stmt->execute();
        $count = $check_result->fetchArray(SQLITE3_ASSOC)['count'];
        
        if ($count > 0) {
            $errors[] = 'A size with this name already exists';
        }
    }
    
    if (empty($errors)) {
        $query = "UPDATE sizes SET name = ?, description = ?, is_active = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $is_active, SQLITE3_INTEGER);
        $stmt->bindValue(4, $sort_order, SQLITE3_INTEGER);
        $stmt->bindValue(5, $size_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Size updated successfully';
            header('Location: sizes.php');
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
    <h2>Edit Size</h2>
    
    <form action="" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Size Name *</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $size['name']); ?>" required maxlength="50">
                <small class="form-text">Enter the tire size (e.g., 205/55R16)</small>
            </div>
            
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? $size['sort_order']); ?>" min="0">
                <small class="form-text">Lower numbers appear first in lists</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" maxlength="255"><?php echo htmlspecialchars($_POST['description'] ?? $size['description']); ?></textarea>
            <small class="form-text">Optional description for this tire size</small>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" id="is_active" <?php echo ((isset($_POST['is_active']) ? $_POST['is_active'] : $size['is_active']) ? 'checked' : ''); ?>>
                Active
            </label>
            <small class="form-text">Inactive sizes won't appear in dropdowns</small>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Update Size</button>
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