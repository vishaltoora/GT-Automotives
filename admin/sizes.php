<?php

// Set base path for includes
$base_path = dirname(__DIR__);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering
ob_start();

try {
    // Include database connection
    if (file_exists('$base_path . '/includes/db_connect.php'')) {
        require_once '$base_path . '/includes/db_connect.php'';
    }

    if (file_exists('$base_path . '/includes/auth.php'')) {
        require_once '$base_path . '/includes/auth.php'';
    }

    // Require login
    requireLogin();

    // Set page title
    $page_title = 'Manage Sizes';

    // Initialize variables
    $sizes = [];
    $errors = [];
    $success_message = '';

    // Pagination settings
    $items_per_page = 20; // Show 20 items per page
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $total_items = 0;
    $total_pages = 0;

    // Process form submission for adding new size
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $size = trim($_POST['size'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($size)) {
                $errors[] = 'Size is required';
            }
            
            if (empty($errors) && isset($conn)) {
                // Check which column exists in the database
                $check_column = $conn->query("SHOW COLUMNS FROM sizes LIKE 'name'");
                $column_name = ($check_column && $check_column->num_rows > 0) ? 'name' : 'size';
                
                $insert_query = "INSERT INTO sizes ($column_name, description, is_active, sort_order) VALUES (?, ?, 1, 0)";
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

    // Fetch sizes with pagination and search
    if (isset($conn)) {
        // Build the WHERE clause for search
        $where_clause = "";
        $search_params = [];
        
        if (!empty($search)) {
            $where_clause = "WHERE name LIKE ? OR description LIKE ?";
            $search_term = "%$search%";
            $search_params = [$search_term, $search_term];
        }

        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM sizes $where_clause";
        $count_stmt = $conn->prepare($count_query);
        
        if ($count_stmt) {
            if (!empty($search_params)) {
                $count_stmt->bind_param("ss", $search_params[0], $search_params[1]);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_items = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        }

        // Calculate pagination
        $total_pages = ceil($total_items / $items_per_page);
        $offset = ($current_page - 1) * $items_per_page;

        // Fetch sizes with pagination
        $sizes_query = "SELECT * FROM sizes $where_clause ORDER BY name ASC LIMIT ? OFFSET ?";
        $sizes_stmt = $conn->prepare($sizes_query);
        
        if ($sizes_stmt) {
            if (!empty($search_params)) {
                $sizes_stmt->bind_param("ssii", $search_params[0], $search_params[1], $items_per_page, $offset);
            } else {
                $sizes_stmt->bind_param("ii", $items_per_page, $offset);
            }
            
            $sizes_stmt->execute();
            $sizes_result = $sizes_stmt->get_result();
            
            while ($row = $sizes_result->fetch_assoc()) {
                $sizes[] = $row;
            }
            
            $sizes_stmt->close();
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
        <div class="table-header">
            <div class="table-title">
                <h3>All Sizes (<?php echo $total_items; ?> total)</h3>
            </div>
            <div class="table-search">
                <form method="GET" class="inline-search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search sizes..." class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="sizes.php" class="clear-btn" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($total_items > 0): ?>
            <!-- Pagination Info -->
            <div class="pagination-info">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> sizes
            </div>

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
                                <td><strong><?php echo htmlspecialchars($size['name'] ?? $size['size'] ?? ''); ?></strong></td>
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

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                            <i class="fas fa-angle-double-left"></i> First
                        </a>
                        <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                            <i class="fas fa-angle-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $i == $current_page ? 'btn-primary' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                            Next <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                            Last <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No sizes found<?php echo !empty($search) ? ' matching your search criteria' : ''; ?>.</p>
                <?php if (!empty($search)): ?>
                    <a href="sizes.php" class="btn btn-primary">View All Sizes</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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

.search-form .form-row {
    grid-template-columns: 1fr auto;
}

.search-form .form-group {
    margin-bottom: 0;
}

.pagination-info {
    margin-bottom: 20px;
    color: #666;
    font-size: 14px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.pagination .btn {
    min-width: 40px;
    text-align: center;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #666;
}

.no-results p {
    margin-bottom: 20px;
    font-size: 16px;
}

/* New integrated search styles */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.table-title h3 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

.table-search {
    flex-shrink: 0;
}

.inline-search-form {
    margin: 0;
}

.search-input-group {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: border-color 0.3s, box-shadow 0.3s;
}

.search-input-group:focus-within {
    border-color: #243c55;
    box-shadow: 0 2px 8px rgba(36, 60, 85, 0.2);
}

.search-input {
    border: none;
    padding: 10px 15px;
    font-size: 14px;
    background: transparent;
    outline: none;
    min-width: 250px;
    flex: 1;
}

.search-input::placeholder {
    color: #999;
}

.search-btn {
    background: #243c55;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-btn:hover {
    background: #1a2d3f;
}

.clear-btn {
    background: #8b2635;
    color: white;
    border: none;
    padding: 10px 12px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin-left: 1px;
}

.clear-btn:hover {
    background: #7a1f2d;
    color: white;
    text-decoration: none;
}

/* Responsive design for integrated search */
@media (max-width: 991px) {
    .table-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .table-search {
        width: 100%;
    }
    
    .search-input-group {
        width: 100%;
    }
    
    .search-input {
        min-width: auto;
    }
}

@media (max-width: 767px) {
    .table-title h3 {
        font-size: 1.3rem;
    }
    
    .search-input {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .search-btn,
    .clear-btn {
        padding: 8px 12px;
    }
}

@media (max-width: 575px) {
    .table-title h3 {
        font-size: 1.2rem;
    }
    
    .search-input {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .search-btn,
    .clear-btn {
        padding: 6px 10px;
    }
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
    
    .search-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .pagination {
        gap: 5px;
    }
    
    .pagination .btn {
        padding: 8px 12px;
        font-size: 12px;
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
    
    .pagination {
        flex-direction: column;
        gap: 10px;
    }
    
    .pagination .btn {
        width: 100%;
        justify-content: center;
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