<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login and admin access
requireLogin();
requireAdmin();

// Set page title
$page_title = 'Manage Users';

// Initialize variables
$error = '';
$success = '';
$users_result = null;
$admin_count = 0;
$user_count = 0;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $username = trim($_POST['username'] ?? '');
                    $first_name = trim($_POST['first_name'] ?? '');
                    $last_name = trim($_POST['last_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                    
                    // Validate input - username, first_name, last_name, and password are required
                    if (empty($username) || empty($first_name) || empty($last_name) || empty($password)) {
                        $error = "Username, first name, last name, and password are required.";
                    } else {
                        // Check if username already exists
                        $check_query = "SELECT id FROM users WHERE username = ?";
                        $check_stmt = $conn->prepare($check_query);
                        if ($check_stmt) {
                            $check_stmt->bind_param("s", $username);
                            $check_stmt->execute();
                            
                            if ($check_stmt->get_result()->num_rows > 0) {
                                $error = "Username already exists.";
                            } else {
                                // Hash password and insert user
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $insert_query = "INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
                                $insert_stmt = $conn->prepare($insert_query);
                                if ($insert_stmt) {
                                    $insert_stmt->bind_param("sssssi", $username, $first_name, $last_name, $hashed_password, $email, $is_admin);
                                    
                                    if ($insert_stmt->execute()) {
                                        $success = "User created successfully!";
                                    } else {
                                        $error = "Error creating user: " . $conn->error;
                                    }
                                } else {
                                    $error = "Error preparing insert statement.";
                                }
                            }
                        } else {
                            $error = "Error preparing check statement.";
                        }
                    }
                    break;
                    
                case 'edit':
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $username = trim($_POST['username'] ?? '');
                    $first_name = trim($_POST['first_name'] ?? '');
                    $last_name = trim($_POST['last_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                    
                    // Validate input
                    if (empty($username) || empty($first_name) || empty($last_name)) {
                        $error = "Username, first name, and last name are required.";
                    } else {
                        // Check if username already exists for other users
                        $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
                        $check_stmt = $conn->prepare($check_query);
                        if ($check_stmt) {
                            $check_stmt->bind_param("si", $username, $user_id);
                            $check_stmt->execute();
                            
                            if ($check_stmt->get_result()->num_rows > 0) {
                                $error = "Username already exists.";
                            } else {
                                // Update user without password
                                $update_query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, is_admin = ? WHERE id = ?";
                                $update_stmt = $conn->prepare($update_query);
                                if ($update_stmt) {
                                    $update_stmt->bind_param("ssssii", $username, $first_name, $last_name, $email, $is_admin, $user_id);
                                    
                                    if ($update_stmt->execute()) {
                                        $success = "User updated successfully!";
                                    } else {
                                        $error = "Error updating user: " . $conn->error;
                                    }
                                } else {
                                    $error = "Error preparing update statement.";
                                }
                            }
                        } else {
                            $error = "Error preparing check statement.";
                        }
                    }
                    break;
                    
                case 'delete':
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    
                    // Prevent deleting own account
                    if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                        $error = "You cannot delete your own account.";
                    } else {
                        $delete_query = "DELETE FROM users WHERE id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        if ($delete_stmt) {
                            $delete_stmt->bind_param("i", $user_id);
                            
                            if ($delete_stmt->execute()) {
                                $success = "User deleted successfully!";
                            } else {
                                $error = "Error deleting user: " . $conn->error;
                            }
                        } else {
                            $error = "Error preparing delete statement.";
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all users
try {
    $users_query = "SELECT id, username, first_name, last_name, email, is_admin, created_at FROM users ORDER BY created_at DESC";
    $users_result = $conn->query($users_query);
    
    if ($users_result) {
        // Get admin count
        $admin_query = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
        $admin_result = $conn->query($admin_query);
        if ($admin_result) {
            $admin_count = $admin_result->fetch_assoc()['admin_count'] ?? 0;
        }
        
        $user_count = $users_result->num_rows - $admin_count;
    } else {
        $users_result = null;
        $admin_count = 0;
        $user_count = 0;
    }
} catch (Exception $e) {
    error_log("Database error in admin/users.php: " . $e->getMessage());
    $users_result = null;
    $admin_count = 0;
    $user_count = 0;
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-container">
    <!-- Alerts -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Add User Modal -->
    <div id="addUserForm" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <button class="modal-close" onclick="hideAddUserForm()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter username" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               placeholder="Enter email address (optional)" maxlength="255">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               placeholder="Enter first name" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required 
                               placeholder="Enter last name" maxlength="255">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter password (minimum 6 characters)" minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1">
                        <span class="checkmark"></span>
                        Grant admin privileges
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddUserForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserForm" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Edit User</h2>
                <button class="modal-close" onclick="hideEditUserForm()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_username">Username *</label>
                        <input type="text" id="edit_username" name="username" required 
                               placeholder="Enter username" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" 
                               placeholder="Enter email address (optional)" maxlength="255">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" required 
                               placeholder="Enter first name" maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_last_name">Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" required 
                               placeholder="Enter last name" maxlength="255">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1">
                        <span class="checkmark"></span>
                        Grant admin privileges
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditUserForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Dashboard -->
    <div class="users-dashboard">
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $users_result ? $users_result->num_rows : 0; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $admin_count; ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $user_count; ?></div>
                    <div class="stat-label">Regular Users</div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="users-table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> All Users</h2>
                <div class="table-actions">
                    <button class="btn btn-primary" onclick="showAddUserForm()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </div>
            
            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="admin-table users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset result pointer
                            $users_result->data_seek(0);
                            while ($user = $users_result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">
                                                    <?php 
                                                    $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                                    echo htmlspecialchars($full_name ?: 'No Name Set');
                                                    ?>
                                                    <?php if ($user['id'] == ($_SESSION['user_id'] ?? 0)): ?>
                                                        <span class="badge badge-primary">You</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="user-id">ID: #<?php echo $user['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <?php if (!empty($user['email'])): ?>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No email</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                            <button class="btn-action btn-edit" 
                                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['first_name']); ?>', '<?php echo htmlspecialchars($user['last_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', <?php echo $user['is_admin']; ?>)"
                                                    title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                                    title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>No Users Found</h3>
                    <p>Get started by adding the first user to your system.</p>
                    <button class="btn btn-primary" onclick="showAddUserForm()">
                        <i class="fas fa-plus"></i> Add First User
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete User Form -->
<form id="deleteUserForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<script>
function showAddUserForm() {
    document.getElementById('addUserForm').style.display = 'flex';
    document.getElementById('username').focus();
}

function hideAddUserForm() {
    document.getElementById('addUserForm').style.display = 'none';
    document.getElementById('addUserForm').querySelector('form').reset();
}

function showEditUserForm(id, username, firstName, lastName, email, isAdmin) {
    document.getElementById('editUserId').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_email').value = email;
    document.querySelector('#editUserForm input[name="is_admin"]').checked = isAdmin === 1;
    document.getElementById('editUserForm').style.display = 'flex';
    document.getElementById('edit_username').focus();
}

function hideEditUserForm() {
    document.getElementById('editUserForm').style.display = 'none';
    document.getElementById('editUserForm').querySelector('form').reset();
}

function editUser(id, username, firstName, lastName, email, isAdmin) {
    showEditUserForm(id, username, firstName, lastName, email, isAdmin);
}

function deleteUser(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserForm').submit();
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('addUserForm');
    if (event.target === modal) {
        hideAddUserForm();
    }
    const editModal = document.getElementById('editUserForm');
    if (event.target === editModal) {
        hideEditUserForm();
    }
});
</script>

<style>
/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: #243c55;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #243c55;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

/* Users Table - Full Width */
.users-dashboard {
    width: 100%;
}

.users-table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.table-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-actions {
    display: flex;
    gap: 1rem;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
}

.users-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.users-table tbody tr:hover {
    background: #f8f9fa;
}

/* User Info in Table */
.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: #243c55;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-id {
    font-size: 0.85rem;
    color: #666;
}

/* Badges */
.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-primary {
    background: #e3f2fd;
    color: #0d47a1;
}

.badge-admin {
    background: #e8f5e9;
    color: #1b5e20;
}

.badge-user {
    background: #fff3e0;
    color: #e65100;
}

/* Actions */
.btn-action {
    padding: 0.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-edit {
    background: #e3f2fd;
    color: #0d47a1;
}

.btn-edit:hover {
    background: #bbdefb;
    transform: scale(1.05);
}

.btn-delete {
    background: #ffebee;
    color: #b71c1c;
}

.btn-delete:hover {
    background: #ffcdd2;
    transform: scale(1.05);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e9ecef;
    background: #243c55;
    color: white;
    border-radius: 15px 15px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.3s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
}

/* Form Styles */
.admin-form {
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #243c55;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
    transition: opacity 0.3s ease;
}

.alert-danger {
    background: #ffebee;
    color: #b71c1c;
    border: 1px solid #ffcdd2;
}

.alert-success {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 2rem 0;
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .users-table {
        font-size: 0.9rem;
    }
    
    .users-table th,
    .users-table td {
        padding: 0.75rem;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?> 