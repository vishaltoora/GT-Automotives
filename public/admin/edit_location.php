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
$location_stmt->execute();
$location_result = $location_stmt->get_result();
$location = $location_result->fetch_assoc();
$location_stmt->close();

if (!$location) {
    $_SESSION['error_message'] = 'Location not found';
    header('Location: locations.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hours = trim($_POST['hours'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Location name is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE locations SET name = ?, address = ?, phone = ?, email = ?, hours = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $name, $address, $phone, $email, $hours, $is_active, $location_id);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Location updated successfully';
            header('Location: locations.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
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
                <label for="address">Address *</label>
                <textarea name="address" id="address" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? $location['address'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? $location['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $location['email'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="hours">Business Hours</label>
                <textarea name="hours" id="hours" rows="2"><?php echo htmlspecialchars($_POST['hours'] ?? $location['hours'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo (($_POST['is_active'] ?? $location['is_active'] ?? 1) == 1) ? 'checked' : ''; ?>>
                    Active Location
                </label>
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