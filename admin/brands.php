<?php

// Set base path for includes
$base_path = dirname(__DIR__);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '$base_path . '/includes/db_connect.php'';
require_once '$base_path . '/includes/auth.php'';

// Require login
requireLogin();

// Set page title
$page_title = 'Manage Brands';

// Get all brands
$result = $conn->query('SELECT * FROM brands ORDER BY name ASC');

// Include header
include_once 'includes/header.php';
?>

<div class="admin-actions" style="margin-bottom: 2rem;">
    <a href="add_brand.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Brand
    </a>
</div>

<?php if ($result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Website</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($brand = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $brand['id']; ?></td>
                    <td><?php echo htmlspecialchars($brand['name']); ?></td>
                    <td><?php echo htmlspecialchars($brand['description']); ?></td>
                    <td>
                        <?php if (!empty($brand['website'])): ?>
                            <a href="<?php echo htmlspecialchars($brand['website']); ?>" target="_blank">
                                <?php echo htmlspecialchars($brand['website']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No website</span>
                        <?php endif; ?>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_brand.php?id=<?php echo $brand['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_brand.php?id=<?php echo $brand['id']; ?>" class="btn-action btn-delete delete-confirm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No brands found. <a href="add_brand.php">Add your first brand</a>.</p>
<?php endif; ?>

<?php
// Include footer
include_once 'includes/footer.php';
?> 