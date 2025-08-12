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
$categories_query = "SELECT DISTINCT name FROM service_categories ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['name'];
}

// Get all categories with service counts for the dialog
$all_categories_query = "SELECT sc.*, COUNT(s.id) as service_count FROM service_categories sc 
                        LEFT JOIN services s ON sc.name = s.category 
                        GROUP BY sc.id ORDER BY sc.name";
$all_categories_result = $conn->query($all_categories_query);
$all_categories = [];
while ($row = $all_categories_result->fetch_assoc()) {
    $all_categories[] = $row;
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
        <button type="button" class="btn btn-secondary" onclick="openManageCategoriesDialog()">
            <i class="fas fa-tags"></i> Manage Categories
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="fas fa-plus"></i> Add Service
        </button>
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

<!-- Manage Categories Dialog -->
<div id="manageCategoriesModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>Manage Service Categories</h2>
            <span class="close" onclick="closeManageCategoriesDialog()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Add Category Form -->
            <div style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
                <h3 style="margin-bottom: 1rem;">Add New Category</h3>
                <form id="addCategoryForm" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_name">Category Name</label>
                            <input type="text" name="name" id="category_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_icon">Icon Class</label>
                            <input type="text" name="icon" id="category_icon" value="fas fa-tools" placeholder="fas fa-tools" required>
                            <small class="form-text">FontAwesome icon class (e.g., fas fa-tools, fas fa-oil-can)</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_description">Description</label>
                        <textarea name="description" id="category_description" rows="3" placeholder="Brief description of this service category..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Categories List -->
            <div>
                <h3 style="margin-bottom: 1rem;">Existing Categories</h3>
                <?php if (count($all_categories) > 0): ?>
                    <table class="admin-table" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Icon</th>
                                <th>Services Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_categories as $category): ?>
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
                                    <td>
                                        <span class="count-badge">
                                            <?php echo $category['service_count']; ?> services
                                        </span>
                                    </td>
                                    <td class="admin-actions">
                                        <button type="button" class="btn-action btn-edit" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['description'] ?? ''); ?>', '<?php echo htmlspecialchars($category['icon']); ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($category['service_count'] == 0): ?>
                                            <button type="button" class="btn-action btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php else: ?>
                                            <span class="btn-action btn-disabled" title="Cannot delete category with services">
                                                <i class="fas fa-trash"></i> Delete
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        No service categories found. Add your first category above.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Dialog -->
<div id="addServiceModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>Add New Service</h2>
            <span class="close" onclick="closeAddServiceDialog()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addServiceForm" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Service Name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="select2-enhanced" required>
                            <option value="">Select category...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" name="price" id="price" min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration_minutes">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="duration_minutes" min="15" step="15" value="60" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="is_active" class="checkbox-label">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                            Active Service
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="5" placeholder="Describe the service, what's included, and any special notes..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Service
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddServiceDialog()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    width: auto;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 30px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

/* Enhanced Select2 Styles */
.select2-container {
    width: 100% !important;
    margin-bottom: 0.5rem;
}

.select2-container--default .select2-selection--single {
    height: 45px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    background-color: white !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 43px !important;
    padding-left: 12px !important;
    padding-right: 60px !important;
    color: #333 !important;
    font-size: 1rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #6c757d !important;
}

.select2-container--default .select2-selection--single .select2-selection__clear {
    position: absolute !important;
    right: 35px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 20px !important;
    height: 20px !important;
    background: rgb(126, 125, 125) !important;
    color: white !important;
    border: none !important;
    border-radius: 50% !important;
    font-size: 14px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 10 !important;
}

.select2-container--default .select2-selection--single .select2-selection__clear:hover {
    background: rgb(17, 16, 16) !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 43px !important;
    width: 30px !important;
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #666 transparent transparent transparent !important;
    border-style: solid !important;
    border-width: 5px 4px 0 4px !important;
    height: 0 !important;
    left: 50% !important;
    margin-left: -4px !important;
    margin-top: -2px !important;
    position: absolute !important;
    top: 50% !important;
    width: 0 !important;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #666 transparent !important;
    border-width: 0 4px 5px 4px !important;
}

.select2-dropdown {
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    z-index: 9999 !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    padding: 8px 12px !important;
    font-size: 1rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.select2-container--default .select2-results__option {
    padding: 10px 12px !important;
    font-size: 1rem !important;
    border-bottom: 1px solid #f0f0f0 !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #243c55 !important;
    color: white !important;
}
</style>

<script>
function openAddServiceDialog() {
    document.getElementById('addServiceModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Initialize Select2 for the modal
    $('#category').select2({
        placeholder: 'Select an option...',
        allowClear: true
    });
}

function closeAddServiceDialog() {
    document.getElementById('addServiceModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('addServiceForm').reset();
}

function openManageCategoriesDialog() {
    document.getElementById('manageCategoriesModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeManageCategoriesDialog() {
    document.getElementById('manageCategoriesModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('addCategoryForm').reset();
}

function editCategory(id, name, description, icon) {
    // For now, we'll use a simple prompt approach
    // In a full implementation, you might want to create an edit dialog
    const newName = prompt('Enter new category name:', name);
    if (newName && newName !== name) {
        // Send AJAX request to update category
        fetch('update_service_category_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&name=${encodeURIComponent(newName)}&description=${encodeURIComponent(description)}&icon=${encodeURIComponent(icon)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Category updated successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update category'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the category');
        });
    }
}

function deleteCategory(id, name) {
    if (confirm(`Are you sure you want to delete the category "${name}"?`)) {
        fetch('delete_service_category_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Category deleted successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete category'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category');
        });
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    var addModal = document.getElementById('addServiceModal');
    var manageModal = document.getElementById('manageCategoriesModal');
    if (event.target == addModal) {
        closeAddServiceDialog();
    }
    if (event.target == manageModal) {
        closeManageCategoriesDialog();
    }
}

// Handle form submission
document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('add_service_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Service added successfully!');
            closeAddServiceDialog();
            // Reload the page to show the new service
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add service'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the service');
    });
});

// Handle add category form submission
document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('add_service_category_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category added successfully!');
            this.reset();
            // Reload the page to show the new category
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to add category'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the category');
    });
});

// Update the button click handler
document.addEventListener('DOMContentLoaded', function() {
    const addServiceBtn = document.querySelector('button[data-bs-target="#addServiceModal"]');
    if (addServiceBtn) {
        addServiceBtn.addEventListener('click', openAddServiceDialog);
    }
});
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?> 