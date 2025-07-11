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
$page_title = 'Manage Locations';

// Get all locations
$result = $conn->query('SELECT * FROM locations ORDER BY name ASC');

// Include header
include_once 'includes/header.php';
?>

<div class="admin-actions" style="margin-bottom: 2rem;">
    <a href="add_location.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Location
    </a>
</div>

<?php if ($result->numColumns() > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($location = $result->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <td><?php echo $location['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($location['name']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($location['description'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($location['address'] ?? ''); ?></td>
                    <td class="admin-actions">
                        <a href="edit_location.php?id=<?php echo $location['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_location.php?id=<?php echo $location['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this location?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-map-marker-alt fa-3x"></i>
        <h3>No Locations Found</h3>
        <p>Add locations to organize your inventory across different facilities.</p>
        <a href="add_location.php" class="btn btn-primary">Add First Location</a>
    </div>
<?php endif; ?>

<style>
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.empty-state i {
    color: #ccc;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 1rem 0 0.5rem 0;
    color: #666;
}

.empty-state p {
    color: #999;
    margin-bottom: 1.5rem;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 