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
$page_title = 'Manage Sizes';

// Get sizes from database
$sizes_query = "SELECT * FROM sizes ORDER BY sort_order ASC, name ASC";
$sizes_result = $conn->query($sizes_query);

$sizes = [];
while ($row = $sizes_result->fetch_assoc()) {
    $sizes[] = $row;
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Manage Sizes</h1>
    <div class="admin-actions">
        <a href="add_size.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Size
        </a>
    </div>
</div>

<?php if (count($sizes) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Size Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sizes as $size): ?>
                <tr>
                    <td><?php echo $size['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($size['name']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($size['description'] ?? ''); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $size['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $size['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_size.php?id=<?php echo $size['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_size.php?id=<?php echo $size['id']; ?>" class="btn-action btn-delete delete-confirm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h3>No Sizes Found</h3>
        <p>You haven't added any tire sizes yet. Start by adding your first size.</p>
        <a href="add_size.php" class="btn btn-primary">Add First Size</a>
    </div>
<?php endif; ?>

<div class="admin-actions" style="margin-top: 2rem;">
    <a href="add_size.php" class="btn btn-primary">Add New Size</a>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<script>
// Confirm delete action
document.querySelectorAll('.delete-confirm').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this size? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 