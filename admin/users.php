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
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <button class="btn btn-primary" onclick="showAddUserForm()">
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
    <div id="addUserForm" class="admin-form-container" style="display: none;">
        <h2><i class="fas fa-user-plus"></i> Add New User</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            
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
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter password" minlength="6">
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

    <!-- Users List -->
    <div class="admin-content">
        <h2><i class="fas fa-list"></i> All Users</h2>
        
        <?php if ($users_result->num_rows > 0): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge badge-primary">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td class="admin-actions">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn-action btn-delete delete-confirm" 
                                                onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
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
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No users found. 
                <button class="btn btn-primary" onclick="showAddUserForm()">Add the first user</button>
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
    document.getElementById('addUserForm').style.display = 'block';
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
</script>

<style>
.admin-form-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 0.5rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-admin {
    background: #d4edda;
    color: #155724;
}

.badge-user {
    background: #e9ecef;
    color: #495057;
}

.badge-primary {
    background: #007bff;
    color: white;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}

.admin-actions {
    white-space: nowrap;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.alert {
    transition: opacity 0.5s ease;
}
</style>

<?php include_once 'includes/footer.php'; ?> 