<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireLogin();
$page_title = 'Add Brand';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $logo_url = trim($_POST['logo_url'] ?? '');
    if (empty($name)) {
        $errors[] = 'Brand name is required';
    }
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO brands (name, description, website, logo_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $description, $website, $logo_url);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Brand added successfully';
            header('Location: brands.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}
include_once 'includes/header.php';
?>
<div class="admin-form">
    <form action="" method="POST">
        <div class="form-group">
            <label for="name">Brand Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" name="website" id="website" value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="logo_url">Logo URL</label>
            <input type="text" name="logo_url" id="logo_url" value="<?php echo htmlspecialchars($_POST['logo_url'] ?? ''); ?>">
        </div>
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Add Brand</button>
            <a href="brands.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php include_once 'includes/footer.php'; ?> 