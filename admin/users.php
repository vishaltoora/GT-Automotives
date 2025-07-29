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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                
                // Validate input
                if (empty($username) || empty($email) || empty($password)) {
                    $error = "All fields are required.";
                } else {
                    // Check if username already exists
                    $check_query = "SELECT id FROM users WHERE username = ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param("s", $username);
                    $check_stmt->execute();
                    
                    if ($check_stmt->get_result()->num_rows > 0) {
                        $error = "Username already exists.";
                    } else {
                        // Hash password and insert user
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_query = "INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_query);
                        $insert_stmt->bind_param("sssi", $username, $hashed_password, $email, $is_admin);
                        
                        if ($insert_stmt->execute()) {
                            $success = "User created successfully!";
                        } else {
                            $error = "Error creating user: " . $conn->error;
                        }
                    }
                }
                break;
                
            case 'delete':
                $user_id = (int)$_POST['user_id'];
                
                // Prevent deleting own account
                if ($user_id == $_SESSION['user_id']) {
                    $error = "You cannot delete your own account.";
                } else {
                    $delete_query = "DELETE FROM users WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_query);
                    $delete_stmt->bind_param("i", $user_id);
                    
                    if ($delete_stmt->execute()) {
                        $success = "User deleted successfully!";
                    } else {
                        $error = "Error deleting user: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get all users
$users_query = "SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);

// Include header
include_once 'includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-content">
            <h1><i class="fas fa-users"></i> User Management</h1>
            <p class="header-subtitle">Manage system users and their permissions</p>
        </div>
        <button class="btn btn-primary add-user-btn" onclick="showAddUserForm()">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Add User Form (Hidden by default) -->
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
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter email address" maxlength="255">
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

    <!-- Users List -->
    <div class="users-container">
        <div class="users-header">
            <h2><i class="fas fa-list"></i> All Users</h2>
            <div class="users-stats">
                <span class="stat-item">
                    <i class="fas fa-users"></i>
                    <span class="stat-number"><?php echo $users_result->num_rows; ?></span>
                    <span class="stat-label">Total Users</span>
                </span>
            </div>
        </div>
        
        <?php if ($users_result->num_rows > 0): ?>
            <div class="users-grid">
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-info">
                                <h3 class="user-name">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge badge-primary">You</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div class="user-role">
                                <span class="badge <?php echo $user['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="user-card-body">
                            <div class="user-details">
                                <div class="detail-item">
                                    <span class="detail-label">ID:</span>
                                    <span class="detail-value">#<?php echo $user['id']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Created:</span>
                                    <span class="detail-value"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="user-card-actions">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn-action btn-delete" 
                                        onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php else: ?>
                                <span class="text-muted">Current User</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
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
});
</script>

<style>
/* Header Styles */
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.header-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
}

.add-user-btn {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
    color: white;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.add-user-btn:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: #f0f0f0;
    color: #333;
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

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
    color: #333;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin-right: 0.75rem;
    transform: scale(1.2);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

/* Users Container */
.users-container {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.users-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

.users-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    min-width: 100px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

/* Users Grid */
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.user-card {
    background: white;
    border: 2px solid #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.user-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.user-card-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin-right: 1rem;
}

.user-info {
    flex: 1;
}

.user-name {
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-email {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.user-role {
    margin-left: auto;
}

.user-card-body {
    margin-bottom: 1rem;
}

.user-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 600;
    color: #333;
}

.user-card-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid #f8f9fa;
}

/* Badge Styles */
.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-admin {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
}

.badge-user {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
}

.badge-primary {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 2rem 0;
    font-size: 1rem;
}

/* Button Styles */
.btn-action {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-delete {
    background: linear-gradient(135deg, #f44336, #d32f2f);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #d32f2f, #b71c1c);
    transform: translateY(-1px);
}

.text-muted {
    color: #6c757d;
    font-style: italic;
    font-size: 0.9rem;
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid;
    transition: opacity 0.5s ease;
}

.alert-success {
    background: #e8f5e9;
    color: #1b5e20;
    border-left-color: #4caf50;
}

.alert-danger {
    background: #ffebee;
    color: #b71c1c;
    border-left-color: #f44336;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .users-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .user-details {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?> 