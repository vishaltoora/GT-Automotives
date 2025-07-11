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
$page_title = 'Manage Service Categories';

// Get categories
$categories_query = "SELECT sc.*, COUNT(s.id) as service_count FROM service_categories sc 
                    LEFT JOIN services s ON sc.name = s.category 
                    GROUP BY sc.id ORDER BY sc.sort_order, sc.name";
$categories_result = $conn->query($categories_query);

// Include header
include_once 'includes/header.php';
?>

<div class="admin-actions" style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <h2>Service Categories</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="services.php" class="btn btn-secondary">
            <i class="fas fa-tools"></i> Manage Services
        </a>
        <a href="add_service_category.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>
</div>

<?php if ($categories_result->numColumns() > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Icon</th>
                <th>Sort Order</th>
                <th>Services Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($category = $categories_result->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($category['description'] ?? ''); ?>
                    </td>
                    <td>
                        <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                        <small><?php echo htmlspecialchars($category['icon']); ?></small>
                    </td>
                    <td><?php echo $category['sort_order']; ?></td>
                    <td>
                        <span class="count-badge">
                            <?php echo $category['service_count']; ?> services
                        </span>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_service_category.php?id=<?php echo $category['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <?php if ($category['service_count'] == 0): ?>
                            <a href="delete_service_category.php?id=<?php echo $category['id']; ?>" class="btn-action btn-delete delete-confirm">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        <?php else: ?>
                            <span class="btn-action btn-disabled" title="Cannot delete category with services">
                                <i class="fas fa-trash"></i> Delete
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-danger">
        No service categories found. <a href="add_service_category.php">Add a new category</a>.
    </div>
<?php endif; ?>

<style>
.count-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    background: #e9ecef;
    color: #495057;
}

.btn-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    color: #999;
}

.btn-disabled:hover {
    background: none;
    color: #999;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 