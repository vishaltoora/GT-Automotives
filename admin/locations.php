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
    $page_title = 'Manage Locations';

    // Initialize variables
    $locations = [];
    $error_message = '';

    // Get all locations
    if (isset($conn)) {
        $result = $conn->query('SELECT * FROM locations ORDER BY name ASC');
        if ($result) {
            while ($location = $result->fetch_assoc()) {
                $locations[] = $location;
            }
        } else {
            $error_message = "Error fetching locations: " . $conn->error;
        }
    }

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in admin/locations.php: " . $e->getMessage());
}

// Flush any output so far
ob_flush();

// Include header
if (file_exists('includes/header.php')) {
    include_once 'includes/header.php';
}
?>

<div class="admin-header">
    <h1>Locations</h1>
    <div class="admin-actions">
        <a href="add_location.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Location
        </a>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($locations)): ?>
    <div class="locations-grid">
        <?php foreach ($locations as $location): ?>
            <div class="location-card">
                <div class="location-header">
                    <h3><?php echo htmlspecialchars($location['name']); ?></h3>
                    <span class="status-badge <?php echo ($location['is_active'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ($location['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                
                <div class="location-details">
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($location['address']); ?></span>
                    </div>
                    <?php if (!empty($location['phone'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($location['phone']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($location['email'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($location['email']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($location['hours'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($location['hours']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="location-actions">
                    <a href="edit_location.php?id=<?php echo $location['id']; ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="#" class="btn btn-sm btn-danger" onclick="showCustomConfirm('Are you sure you want to delete this location?', function(confirmed) { if(confirmed) window.location.href='delete_location.php?id=<?php echo $location['id']; ?>'; }); return false;">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-map-marker-alt fa-3x"></i>
        <h3>No Locations Found</h3>
        <p>Add locations to organize your business across different facilities.</p>
        <a href="add_location.php" class="btn btn-primary">Add First Location</a>
    </div>
<?php endif; ?>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admin-header h1 {
    margin: 0;
    color: #333;
}

.admin-actions {
    display: flex;
    gap: 15px;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.location-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    transition: transform 0.2s;
}

.location-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.location-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.location-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.location-details {
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #666;
}

.detail-item i {
    width: 16px;
    color: #007bff;
}

.location-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.status-badge {
    padding: 4px 12px;
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
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #333;
}

.empty-state p {
    margin-bottom: 20px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<?php
// Include footer
if (file_exists('includes/footer.php')) {
    include_once 'includes/footer.php';
}
?> 