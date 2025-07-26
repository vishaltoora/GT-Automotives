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
$page_title = 'Edit Location';

// Get location ID
$location_id = intval($_GET['id'] ?? 0);

if ($location_id <= 0) {
    $_SESSION['error_message'] = 'Invalid location ID';
    header('Location: locations.php');
    exit;
}

// Get location data
$location_stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
$location_stmt->bind_param("i", $location_id);
$location_result = $location_stmt->get_result();
$location = $location_result->fetch_assoc();

if (!$location) {
    $_SESSION['error_message'] = 'Location not found';
    header('Location: locations.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Location name is required';
    }
    
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE locations SET name = ?, description = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $address, $location_id);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Location updated successfully';
            header('Location: locations.php');
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
    <form action="" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Location Name *</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $location['name']); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? $location['description']); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? $location['address']); ?></textarea>
            </div>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Location
            </button>
            <a href="locations.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?> 