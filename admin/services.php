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
$page_title = 'Manage Services';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

$search_condition = '';
$params = [];

if (!empty($search)) {
    $search_condition .= "WHERE s.name LIKE ? OR s.description LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $search_condition .= empty($search_condition) ? "WHERE" : "AND";
    $search_condition .= " s.category = ?";
    $params[] = $category_filter;
}

// Get total services for pagination
$total_query = "SELECT COUNT(*) as count FROM services s $search_condition";
$total_stmt = $conn->prepare($total_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_services = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_services / $limit);
$total_stmt->close();

// Get services for this page
$services_query = "SELECT s.*, sc.name as category_name FROM services s 
                   LEFT JOIN service_categories sc ON s.category = sc.name 
                   $search_condition ORDER BY s.category, s.name LIMIT ?, ?";
$services_stmt = $conn->prepare($services_query);
if (!empty($params)) {
    $types = "ii" . str_repeat('s', count($params));
    $services_stmt->bind_param($types, $start, $limit, ...$params);
} else {
    $services_stmt->bind_param("ii", $start, $limit);
}
$services_stmt->execute();
$services_result = $services_stmt->get_result();
$services_stmt->close();

// Get categories for filter dropdown
$categories_query = "SELECT DISTINCT name, sort_order FROM service_categories ORDER BY sort_order, name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['name'];
}

// Include header
include_once 'includes/header.php';
?>

<!-- Search Form -->
<div class="admin-search" style="margin-bottom: 2rem;">
    <form action="" method="GET" class="admin-form" style="display: flex; gap: 1rem; padding: 1rem;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search services..." style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <select name="category" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($search) || !empty($category_filter)): ?>
            <a href="services.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Services Table -->
<div class="admin-actions" style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <h2>Services (<?php echo $total_services; ?>)</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="service_categories.php" class="btn btn-secondary">
            <i class="fas fa-tags"></i> Manage Categories
        </a>
        <a href="add_service.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Service
        </a>
    </div>
</div>

<?php if ($services_result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($service = $services_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $service['id']; ?></td>
                    <td>
                        <div class="service-info">
                            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                            <?php if ($service['description']): ?>
                                <small><?php echo htmlspecialchars($service['description']); ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="category-badge">
                            <?php echo htmlspecialchars($service['category_name'] ?? $service['category']); ?>
                        </span>
                    </td>
                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                    <td><?php echo $service['duration_minutes']; ?> min</td>
                    <td>
                        <span class="status-badge <?php echo $service['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="admin-actions">
                        <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_service.php?id=<?php echo $service['id']; ?>" class="btn-action btn-delete delete-confirm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-danger">
        No services found. <?php echo (!empty($search) || !empty($category_filter)) ? 'Try a different search term or ' : ''; ?><a href="add_service.php">add a new service</a>.
    </div>
<?php endif; ?>

<style>
.service-info {
    display: flex;
    flex-direction: column;
}

.service-info small {
    color: #666;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.category-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    background: #e9ecef;
    color: #495057;
}

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
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 