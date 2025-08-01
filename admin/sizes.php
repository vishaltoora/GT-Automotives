<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering
ob_start();

try {
    // Include database connection
    if (file_exists('../includes/db_connect.php')) {
        require_once '../includes/db_connect.php';
    }

    if (file_exists('../includes/auth.php')) {
        require_once '../includes/auth.php';
    }

    // Require login
    requireLogin();

    // Set page title
    $page_title = 'Manage Sizes';

    // Initialize variables
    $sizes = [];
    $errors = [];
    $success_message = '';

    // Process form submission for adding new size
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $size = trim($_POST['size'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($size)) {
                $errors[] = 'Size is required';
            }
            
            if (empty($errors) && isset($conn)) {
                $insert_query = "INSERT INTO sizes (name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                
                if ($stmt) {
                    $stmt->bind_param("ss", $size, $description);
                    
                    if ($stmt->execute()) {
                        $success_message = "Size added successfully!";
                    } else {
                        $errors[] = "Error adding size: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $errors[] = "Error preparing statement: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'toggle_status') {
            $size_id = intval($_POST['size_id'] ?? 0);
            $new_status = intval($_POST['new_status'] ?? 0);
            
            if ($size_id > 0 && isset($conn)) {
                $update_query = "UPDATE sizes SET is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                
                if ($stmt) {
                    $stmt->bind_param("ii", $new_status, $size_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Size status updated successfully!";
                    } else {
                        $errors[] = "Error updating size: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $errors[] = "Error preparing statement: " . $conn->error;
                }
            }
        }
    }

    // Fetch all sizes
    if (isset($conn)) {
        $sizes_query = "SELECT * FROM sizes ORDER BY name ASC";
        $sizes_result = $conn->query($sizes_query);
        
        if ($sizes_result) {
            while ($row = $sizes_result->fetch_assoc()) {
                $sizes[] = $row;
            }
        }
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in admin/sizes.php: " . $e->getMessage());
}

// Flush any output so far
ob_flush();

// Include header
if (file_exists('includes/header.php')) {
    include_once 'includes/header.php';
}
?>

<div class="admin-content">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Add New Size Form -->
    <div class="admin-form">
        <h3>Add New Size</h3>
        <form method="POST" class="form-row">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="size">Size *</label>
                <input type="text" name="size" id="size" placeholder="e.g., 225/45R17" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" placeholder="e.g., Standard size for 17-inch wheels">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Size
                </button>
            </div>
        </form>
    </div>

    <!-- Sizes List -->
    <div class="admin-table">
        <h3>All Sizes (<?php echo count($sizes); ?> total)</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Size</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sizes as $size): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($size['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($size['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($size['description'] ?? ''); ?></td>
                            <td>
                                <span class="status-badge <?php echo $size['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $size['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="size_id" value="<?php echo $size['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $size['is_active'] ? '0' : '1'; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $size['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <?php echo $size['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.admin-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.admin-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    width: 100%;
    box-sizing: border-box;
}

.admin-table {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    box-sizing: border-box;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.form-group {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group button {
    align-self: flex-end;
    margin-top: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    overflow-x: auto;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    word-wrap: break-word;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s;
    white-space: nowrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-primary {
    background-color: #243c55;
    color: white;
}

.btn-primary:hover {
    background-color: #1a2d3f;
}

.btn-secondary {
    background-color: #4a5c6b;
    color: white;
}

.btn-secondary:hover {
    background-color: #3d4c59;
}

.btn-success {
    background-color: #243c55;
    color: white;
}

.btn-success:hover {
    background-color: #1a2d3f;
}

.btn-warning {
    background-color: #8b2635;
    color: white;
}

.btn-warning:hover {
    background-color: #7a1f2d;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .admin-content {
        max-width: 100%;
        padding: 15px;
    }
}

@media (max-width: 991px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        text-align: center;
    }
    
    .admin-header h1 {
        font-size: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-group button {
        align-self: stretch;
        width: 100%;
    }
}

@media (max-width: 767px) {
    .admin-content {
        padding: 10px;
    }
    
    .admin-form,
    .admin-table {
        padding: 20px;
    }
    
    .admin-header {
        margin-bottom: 20px;
    }
    
    .admin-header h1 {
        font-size: 1.3rem;
    }
    
    table {
        font-size: 0.9rem;
    }
    
    th, td {
        padding: 8px;
    }
    
    .btn {
        padding: 8px 16px;
        font-size: 12px;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 11px;
    }
}

@media (max-width: 575px) {
    .admin-content {
        padding: 5px;
    }
    
    .admin-form,
    .admin-table {
        padding: 15px;
    }
    
    .admin-header h1 {
        font-size: 1.2rem;
    }
    
    table {
        font-size: 0.8rem;
    }
    
    th, td {
        padding: 6px 4px;
    }
    
    .btn {
        padding: 6px 12px;
        font-size: 11px;
    }
    
    .btn-sm {
        padding: 3px 6px;
        font-size: 10px;
    }
    
    .status-badge {
        padding: 2px 6px;
        font-size: 10px;
    }
}

/* Table responsive wrapper */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 1rem;
}

/* Ensure proper centering on all screen sizes */
@media (min-width: 1200px) {
    .admin-content {
        max-width: 1200px;
        margin: 0 auto;
    }
}

@media (min-width: 992px) and (max-width: 1199px) {
    .admin-content {
        max-width: 960px;
        margin: 0 auto;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .admin-content {
        max-width: 720px;
        margin: 0 auto;
    }
}

@media (min-width: 576px) and (max-width: 767px) {
    .admin-content {
        max-width: 540px;
        margin: 0 auto;
    }
}

@media (max-width: 575px) {
    .admin-content {
        max-width: 100%;
        margin: 0 auto;
    }
}
</style>

<?php
// Include footer
if (file_exists('includes/footer.php')) {
    include_once 'includes/footer.php';
}
?> 