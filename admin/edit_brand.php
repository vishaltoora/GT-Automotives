<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
requireLogin();
$page_title = 'Edit Brand';
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No brand ID provided';
    header('Location: brands.php');
    exit;
}
$brand_id = intval($_GET['id']);
$stmt = $conn->prepare('SELECT * FROM brands WHERE id = ?');
$stmt->bindValue(1, $brand_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$brand = $result->fetchArray(SQLITE3_ASSOC);
if (!$brand) {
    $_SESSION['error_message'] = 'Brand not found';
    header('Location: brands.php');
    exit;
}
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
        $stmt = $conn->prepare('UPDATE brands SET name = ?, description = ?, website = ?, logo_url = ? WHERE id = ?');
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $website, SQLITE3_TEXT);
        $stmt->bindValue(4, $logo_url, SQLITE3_TEXT);
        $stmt->bindValue(5, $brand_id, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Brand updated successfully';
            header('Location: brands.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->lastErrorMsg();
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
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $brand['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? $brand['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" name="website" id="website" value="<?php echo htmlspecialchars($_POST['website'] ?? $brand['website']); ?>">
        </div>
        <div class="form-group">
            <label for="logo_url">Logo URL</label>
            <input type="text" name="logo_url" id="logo_url" value="<?php echo htmlspecialchars($_POST['logo_url'] ?? $brand['logo_url']); ?>">
        </div>
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Update Brand</button>
            <a href="brands.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php include_once 'includes/footer.php'; ?> 