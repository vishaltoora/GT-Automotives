<?php
// Require login for all admin pages except login.php
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file !== 'login.php') {
    requireLogin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GT Automotives</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Admin Panel Styles */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: #243c55;
            color: white;
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .admin-brand {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: bold;
            position: relative;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.2rem;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .admin-nav i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #333;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
        }
        
        .admin-user span {
            margin-right: 1rem;
            color: #666;
        }
        
        .admin-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .admin-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .admin-card-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }
        
        .admin-card-icon {
            font-size: 1.5rem;
            color: #007bff;
        }
        
        .admin-card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .admin-card-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .admin-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .admin-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            padding: 0.4rem 0.7rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.3s ease;
        }
        
        .btn-view {
            background: #e3f2fd;
            color: #0d47a1;
        }
        
        .btn-edit {
            background: #e8f5e9;
            color: #1b5e20;
        }
        
        .btn-delete {
            background: #ffebee;
            color: #b71c1c;
        }
        
        .btn-view:hover {
            background: #bbdefb;
        }
        
        .btn-edit:hover {
            background: #c8e6c9;
        }
        
        .btn-delete:hover {
            background: #ffcdd2;
        }
        
        .admin-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-submit {
            margin-top: 1.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            border-left: 4px solid #4caf50;
        }
        
        .alert-danger {
            background: #ffebee;
            color: #b71c1c;
            border-left: 4px solid #f44336;
        }
        
        .admin-pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .admin-pagination a {
            padding: 0.5rem 0.8rem;
            margin: 0 0.25rem;
            border-radius: 4px;
            background: white;
            color: #333;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: background 0.3s ease;
        }
        
        .admin-pagination a:hover,
        .admin-pagination a.active {
            background: #007bff;
            color: white;
        }

        .admin-nav li a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .admin-nav li a:hover {
            background: #f8f9fa;
            color: #007bff;
        }
        
        .admin-nav li a.active {
            background: #007bff;
            color: white;
        }
        
        .admin-nav li a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        /* Navigation Section Styling */
        .nav-section a {
            font-weight: 600;
            color: #007bff;
            background: #e3f2fd;
        }
        
        .nav-section a:hover {
            background: #bbdefb;
            color: #1565c0;
        }
        
        .nav-section a.active {
            background: #007bff;
            color: white;
        }
        
        /* Navigation Separator Styling */
        .nav-separator {
            margin: 1.5rem 0 0.5rem 0;
            padding: 0 1rem;
        }
        
        .nav-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Add some spacing after separators */
        .nav-separator + li {
            margin-top: 0.5rem;
        }
        
        /* Enhanced sidebar styling */
        .admin-sidebar {
            background: #243c55;
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .admin-brand {
            background: rgba(255,255,255,0.1);
            padding: 1.5rem 1rem;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 1rem;
        }
        
        /* Adjust nav items for better contrast */
        .admin-nav li a {
            color: rgba(255,255,255,0.8);
            margin: 0.125rem 0.5rem;
            border-radius: 6px;
        }
        
        .admin-nav li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-nav li a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Section styling for better visibility */
        .nav-section a {
            background: rgba(255,255,255,0.05);
            color: white;
            font-weight: 600;
        }
        
        .nav-section a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-section a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        /* Separator styling for better visibility */
        .nav-label {
            color: rgba(255,255,255,0.6);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            font-size: 0.7rem;
            padding: 0.75rem 0 0.25rem 0;
        }
        
        /* Expandable sections styling */
        .nav-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            cursor: pointer;
            transition: background 0.3s ease;
            border-radius: 6px;
            margin: 0.125rem 0.5rem;
        }
        
        .nav-section-header:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-section-header i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .nav-section-header .section-toggle {
            transition: transform 0.3s ease;
        }
        
        .nav-section-header.collapsed .section-toggle {
            transform: rotate(-90deg);
        }
        
        .nav-section-items {
            max-height: 500px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .nav-section-items.collapsed {
            max-height: 0;
        }
        
        .nav-section-items li a {
            padding-left: 3rem;
            font-size: 0.9rem;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 60px;
            }
            
            .admin-sidebar .admin-brand span,
            .admin-sidebar .admin-nav li a span,
            .admin-sidebar .nav-label {
                display: none;
            }
            
            .admin-sidebar .admin-nav li a {
                padding: 0.75rem;
                text-align: center;
                justify-content: center;
            }
            
            .admin-sidebar .admin-nav li a i {
                margin-right: 0;
                width: auto;
            }
            
            .admin-content {
                margin-left: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                GT Automotives
            </div>
            
            <ul class="admin-nav">
                <!-- Dashboard Section -->
                <li class="nav-section">
                    <a href="index.php" class="<?php echo $current_file === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <!-- Sales & Transactions Section -->
                <li class="nav-separator">
                    <span class="nav-label">Sales & Transactions</span>
                </li>
                <li>
                    <a href="sales.php" class="<?php echo in_array($current_file, ['sales.php', 'create_sale.php', 'view_sale.php', 'edit_sale.php', 'generate_invoice.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-file-invoice"></i> Invoices
                    </a>
                </li>
                
                <!-- Inventory Management Section -->
                <li class="nav-separator">
                    <span class="nav-label">Inventory Management</span>
                </li>
                <li>
                    <div class="nav-section-header" data-section="inventory">
                        <div>
                            <i class="fas fa-boxes"></i> Inventory
                        </div>
                        <i class="fas fa-chevron-down section-toggle"></i>
                    </div>
                    <ul class="nav-section-items" id="inventory-section">
                        <li>
                            <a href="products.php" class="<?php echo $current_file === 'products.php' ? 'active' : ''; ?>">
                                <i class="fas fa-car-alt"></i> Products
                            </a>
                        </li>
                        <li>
                            <a href="add_product.php" class="<?php echo $current_file === 'add_product.php' ? 'active' : ''; ?>">
                                <i class="fas fa-plus-circle"></i> Add Product
                            </a>
                        </li>
                        <li>
                            <a href="inventory.php" class="<?php echo $current_file === 'inventory.php' ? 'active' : ''; ?>">
                                <i class="fas fa-boxes"></i> Inventory Overview
                            </a>
                        </li>
                        <li>
                            <a href="brands.php" class="<?php echo $current_file === 'brands.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tags"></i> Brands
                            </a>
                        </li>
                        <li>
                            <a href="sizes.php" class="<?php echo $current_file === 'sizes.php' ? 'active' : ''; ?>">
                                <i class="fas fa-ruler"></i> Sizes
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Location Management Section -->
                <li class="nav-separator">
                    <span class="nav-label">Location Management</span>
                </li>
                <li>
                    <div class="nav-section-header" data-section="locations">
                        <div>
                            <i class="fas fa-map-marker-alt"></i> Locations
                        </div>
                        <i class="fas fa-chevron-down section-toggle"></i>
                    </div>
                    <ul class="nav-section-items" id="locations-section">
                        <li>
                            <a href="locations.php" class="<?php echo in_array($current_file, ['locations.php', 'add_location.php', 'edit_location.php']) ? 'active' : ''; ?>">
                                <i class="fas fa-map-marker-alt"></i> Locations
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Services Section -->
                <li class="nav-separator">
                    <span class="nav-label">Services</span>
                </li>
                <li>
                    <div class="nav-section-header" data-section="services">
                        <div>
                            <i class="fas fa-tools"></i> Services
                        </div>
                        <i class="fas fa-chevron-down section-toggle"></i>
                    </div>
                    <ul class="nav-section-items" id="services-section">
                        <li>
                            <a href="services.php" class="<?php echo $current_file === 'services.php' ? 'active' : ''; ?>" data-title="Services">
                                <i class="fas fa-tools"></i> <span>Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="add_service.php" class="<?php echo $current_file === 'add_service.php' ? 'active' : ''; ?>" data-title="Add Service">
                                <i class="fas fa-plus-circle"></i> <span>Add Service</span>
                            </a>
                        </li>
                        <li>
                            <a href="service_categories.php" class="<?php echo $current_file === 'service_categories.php' ? 'active' : ''; ?>" data-title="Service Categories">
                                <i class="fas fa-tags"></i> <span>Service Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="image_compressor.php" class="<?php echo $current_file === 'image_compressor.php' ? 'active' : ''; ?>" data-title="Image Compressor">
                                <i class="fas fa-compress"></i> <span>Image Compressor</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- User Management Section -->
                <li class="nav-separator">
                    <span class="nav-label">User Management</span>
                </li>
                <li>
                    <div class="nav-section-header" data-section="users">
                        <div>
                            <i class="fas fa-users"></i> Users
                        </div>
                        <i class="fas fa-chevron-down section-toggle"></i>
                    </div>
                    <ul class="nav-section-items" id="users-section">
                        <li>
                            <a href="profile.php" class="<?php echo $current_file === 'profile.php' ? 'active' : ''; ?>">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
                
                <div class="admin-user">
                    <a href="create_sale.php" class="btn btn-success" style="margin-right: 1rem;">
                        <i class="fas fa-plus-circle"></i> Create New Sale
                    </a>

                    <a href="profile.php" class="btn btn-primary">Profile</a>
                </div>
            </header>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo htmlspecialchars($_SESSION['success_message']);
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo htmlspecialchars($_SESSION['error_message']);
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <script>
                // Expandable sections functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const sectionHeaders = document.querySelectorAll('.nav-section-header');
                    
                    sectionHeaders.forEach(header => {
                        const sectionName = header.getAttribute('data-section');
                        const sectionItems = document.getElementById(sectionName + '-section');
                        
                        // Check if section state is stored in localStorage
                        const isCollapsed = localStorage.getItem('section_' + sectionName) === 'collapsed';
                        
                        if (isCollapsed) {
                            header.classList.add('collapsed');
                            sectionItems.classList.add('collapsed');
                        }
                        
                        header.addEventListener('click', function() {
                            header.classList.toggle('collapsed');
                            sectionItems.classList.toggle('collapsed');
                            
                            // Store the state in localStorage
                            const isNowCollapsed = header.classList.contains('collapsed');
                            localStorage.setItem('section_' + sectionName, isNowCollapsed ? 'collapsed' : 'expanded');
                        });
                    });
                });
            </script> 