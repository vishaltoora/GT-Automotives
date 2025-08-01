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
$page_title = 'Profile';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate current password
    $validate_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $validate_stmt->bind_param("s", $_SESSION['username']);
    $validate_result = $validate_stmt->get_result();
    $user = $validate_result->fetch_assoc();
    $validate_stmt->close();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        $_SESSION['error_message'] = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error_message'] = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = 'New passwords do not match.';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $hashed_password, $_SESSION['username']);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = 'Password updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to update password. Please try again.';
        }
        $update_stmt->close();
    }
    
    // Redirect to prevent form resubmission
    header('Location: profile.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Get user information
$user_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$user_stmt->bind_param("s", $_SESSION['username']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();
$user_stmt->close();

// Check if user info exists
if (!$user_info) {
    $_SESSION['error_message'] = 'User information not found. Please log in again.';
    header('Location: login.php');
    exit();
}

// Include header
include_once 'includes/header.php';
?>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="profile-info">
            <?php 
            $full_name = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
            $display_name = $full_name ?: $user_info['username'];
            ?>
            <h1><?php echo htmlspecialchars($display_name); ?></h1>
            <p><?php echo $user_info['is_admin'] ? 'Administrator' : 'User'; ?></p>
        </div>
    </div>

    <div class="profile-grid">
        <!-- User Information Card -->
        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h2>Account Information</h2>
            </div>
            <div class="card-content">
                <?php if (!empty($user_info['first_name']) || !empty($user_info['last_name'])): ?>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user"></i>
                        <span>Full Name</span>
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($full_name); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user-tag"></i>
                        <span>Username</span>
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['username'] ?? ''); ?></div>
                </div>
                
                <?php if (!empty($user_info['email'])): ?>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['email']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Account Created</span>
                    </div>
                    <div class="info-value"><?php echo isset($user_info['created_at']) ? date('F j, Y', strtotime($user_info['created_at'])) : 'Not available'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-clock"></i>
                        <span>Last Login</span>
                    </div>
                    <div class="info-value">
                        <?php echo isset($_SESSION['last_login']) ? date('F j, Y g:i A', strtotime($_SESSION['last_login'])) : 'Not available'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Change Password Card -->
        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Change Password</h2>
            </div>
            <div class="card-content">
                <form method="POST" action="" class="password-form">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i>
                            Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i>
                            New Password
                        </label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Actions Card -->
        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h2>Account Actions</h2>
            </div>
            <div class="card-content">
                <div class="action-buttons">
                    <a href="#" class="btn btn-danger" onclick="showCustomConfirm('Are you sure you want to logout?', function(confirmed) { if(confirmed) window.location.href='profile.php?logout=1'; }); return false;">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.profile-avatar {
    font-size: 4rem;
    opacity: 0.9;
}

.profile-info h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 600;
}

.profile-info p {
    margin: 0;
    opacity: 0.8;
    font-size: 1.1rem;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.profile-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid #eee;
}

.card-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.card-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
    font-weight: 600;
}

.card-content {
    padding: 2rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
    color: #555;
}

.info-label i {
    color: #007bff;
    width: 16px;
}

.info-value {
    color: #333;
    font-weight: 500;
}

.password-form {
    margin-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-weight: 500;
    color: #333;
}

.form-group label i {
    color: #007bff;
    width: 16px;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-actions {
    margin-top: 2rem;
}

.form-actions button {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.action-buttons .btn {
    flex: 1;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

/* Animation for cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-card {
    animation: fadeInUp 0.6s ease forwards;
}

.profile-card:nth-child(1) { animation-delay: 0.1s; }
.profile-card:nth-child(2) { animation-delay: 0.2s; }
.profile-card:nth-child(3) { animation-delay: 0.3s; }
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 