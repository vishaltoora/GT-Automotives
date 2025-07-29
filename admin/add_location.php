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
$page_title = 'Add Location';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hours = trim($_POST['hours'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Location name is required';
    }
    
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid contact email format';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO locations (name, description, address, contact_person, contact_phone, contact_email, phone, email, hours, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssii", $name, $description, $address, $contact_person, $contact_phone, $contact_email, $phone, $email, $hours, $is_active, $sort_order);
        
        if ($stmt->execute()) {
            // Success - set message and redirect
            $_SESSION['success_message'] = 'Location added successfully';
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
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" name="contact_person" id="contact_person" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input type="tel" name="contact_phone" id="contact_phone" value="<?php echo htmlspecialchars($_POST['contact_phone'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input type="email" name="contact_email" id="contact_email" value="<?php echo htmlspecialchars($_POST['contact_email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone">General Phone</label>
                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">General Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? 0); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="hours">Business Hours</label>
                <textarea name="hours" id="hours" rows="2"><?php echo htmlspecialchars($_POST['hours'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?php echo (($_POST['is_active'] ?? 1) == 1) ? 'checked' : ''; ?>>
                    Active Location
                </label>
            </div>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Location
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