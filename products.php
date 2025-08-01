<?php
// Start output buffering to catch any early errors
ob_start();

try {
    // Include error handler
    if (file_exists('includes/error_handler.php')) {
        require_once 'includes/error_handler.php';
    }

    // Include database connection
    if (file_exists('includes/db_connect.php')) {
        require_once 'includes/db_connect.php';
    }

    // Filters for products
    $brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : '';
    $size_filter = isset($_GET['size']) ? trim($_GET['size']) : '';
    $search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build query based on filters
    $where_conditions = [];
    $params = [];

    if (!empty($brand_filter)) {
        $where_conditions[] = "b.name = '" . mysqli_real_escape_string($conn, $brand_filter) . "'";
    }

    if (!empty($size_filter)) {
        $where_conditions[] = "t.size = '" . mysqli_real_escape_string($conn, $size_filter) . "'";
    }

    if (!empty($search_filter)) {
        $escaped_search = mysqli_real_escape_string($conn, $search_filter);
        $where_conditions[] = "(t.name LIKE '%$escaped_search%' OR t.description LIKE '%$escaped_search%')";
    }

    // Construct the WHERE clause
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    }

    // Get products from database with brand names and logos
    $query = "SELECT t.*, b.name as brand, b.logo_url FROM tires t LEFT JOIN brands b ON t.brand_id = b.id $where_clause ORDER BY b.name, t.name";
    
    if (isset($conn)) {
        $result = $conn->query($query);
    } else {
        $result = false;
    }

    // Get distinct brands and sizes for filters
    if (isset($conn)) {
        $brands_query = "SELECT DISTINCT b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id WHERE b.name IS NOT NULL ORDER BY b.name";
        $brands_result = $conn->query($brands_query);

        $sizes_query = "SELECT name FROM sizes WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $sizes_result = $conn->query($sizes_query);
    } else {
        $brands_result = false;
        $sizes_result = false;
    }

    // Brand logo mapping for fallback
    $brand_logos = [
        'michelin' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Michelin.svg/200px-Michelin.svg.png',
        'bridgestone' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bridgestone_logo.svg/200px-Bridgestone_logo.svg.png',
        'goodyear' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Goodyear_logo.svg/200px-Goodyear_logo.svg.png',
        'continental' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/200px-Continental_AG_logo.svg.png',
        'pirelli' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Pirelli_logo.svg/200px-Pirelli_logo.svg.png',
        'yokohama' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Yokohama_Tire_Logo.svg/200px-Yokohama_Tire_Logo.svg.png',
        'toyo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Toyo_Tire_logo.svg/200px-Toyo_Tire_logo.svg.png',
        'hankook' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Hankook_Tire_logo.svg/200px-Hankook_Tire_logo.svg.png'
    ];

} catch (Exception $e) {
    // Handle error silently or log it
    error_log("Error in products.php: " . $e->getMessage());
}

// Flush any output so far
ob_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GT Automotives</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Enhanced filter styles */
        .filters {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
        }
        
        .search-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
        }
        
        .button-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background: white;
            width: 100%;
            box-sizing: border-box;
            min-width: 200px;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .search-group {
            width: 100%;
        }
        
        .filter-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
        }
        
        .filter-buttons .btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 140px;
            justify-content: center;
            height: 44px;
        }
        
        /* Search summary styles */
        .search-summary {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 4px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        
        .search-summary strong {
            color: #1565c0;
        }
        
        .result-count {
            background: #2196f3;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: auto;
        }
        
        /* Products grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            position: relative;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            overflow: hidden;
        }
        
        .brand-logo {
            max-width: 120px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .brand-logo-fallback {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
        }
        
        .brand-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-brand {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .product-size {
            color: #666;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        
        .product-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .product-features {
            margin-bottom: 1.5rem;
        }
        
        .product-features ul {
            list-style: none;
            padding: 0;
        }
        
        .product-features li {
            padding: 0.25rem 0;
            color: #666;
        }
        
        .product-features li:before {
            content: "✓";
            color: #4caf50;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .set-label {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .no-products {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-products i {
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .no-products h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .no-products p {
            margin-bottom: 0.5rem;
        }
        
        .no-products-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #243c55;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1a2d3f;
        }
        
        .btn-secondary {
            background: #4a5c6b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #3d4c59;
        }
        
        /* Stock info styles */
        .stock-info {
            margin-bottom: 1rem;
        }
        
        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-badge.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-badge.low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-badge.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Price info styles */
        .price-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .per-tire-price,
        .set-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .set-price {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
        }
        
        .price-label {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .price-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2e7d32;
        }
        
        .set-price .price-value {
            color: #1976d2;
            font-size: 1.2rem;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .filter-row {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 991px) {
            .filter-row {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            .filter-buttons {
                justify-content: center;
            }
        }
        
        @media (max-width: 767px) {
            .filter-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .filter-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-buttons .btn {
                width: 100%;
                min-width: auto;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .brand-logo {
                max-width: 100px;
                max-height: 60px;
            }
        }
        
        @media (max-width: 575px) {
            .filters {
                padding: 1rem;
            }
            
            .filter-group select,
            .filter-group input {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
            
            .filter-buttons .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            
            .brand-logo {
                max-width: 80px;
                max-height: 50px;
            }
        }
        
        /* Responsive price display */
        @media (max-width: 480px) {
            .price-info {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .per-tire-price,
            .set-price {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .price-label {
                margin-bottom: 0;
                margin-right: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">GT Automotives</a>
            <button class="mobile-nav-toggle" id="mobile-nav-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-links" id="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="products.php"><i class="fas fa-tire"></i> Products</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="admin/login.php"><i class="fas fa-user-shield"></i> Admin</a>
            </div>
        </div>
    </nav>

    <div class="products-container">
        <div class="filters">
            <form action="" method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="brand">Brand</label>
                        <select name="brand" id="brand">
                            <option value="">All Brands</option>
                            <?php if ($brands_result): while ($brand = $brands_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($brand['brand']); ?>" <?php echo $brand_filter === $brand['brand'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="size">Size</label>
                        <select name="size" id="size">
                            <option value="">All Sizes</option>
                            <?php if ($sizes_result): while ($size = $sizes_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($size['name']); ?>" <?php echo $size_filter === $size['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($size['name']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                </div>
                <div class="search-row">
                    <div class="filter-group search-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Search by name, description, brand, or size...">
                    </div>
                </div>
                <div class="button-row">
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Count rows for search summary -->
        <?php 
        $row_count = $result ? $result->num_rows : 0;
        ?>

        <!-- Search Results Summary -->
        <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
            <div class="search-summary">
                <strong>Active Filters:</strong>
                <?php 
                $active_filters = [];
                if (!empty($brand_filter)) $active_filters[] = "Brand: " . htmlspecialchars($brand_filter);
                if (!empty($size_filter)) $active_filters[] = "Size: " . htmlspecialchars($size_filter);
                if (!empty($search_filter)) $active_filters[] = "Search: " . htmlspecialchars($search_filter);
                echo implode(', ', $active_filters);
                ?>
                <span class="result-count">(<?php echo $row_count; ?> products found)</span>
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if ($result && $row_count > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php
                            // Get brand logo URL
                            $brand_name_lower = strtolower($product['brand'] ?? '');
                            $logo_url = $product['logo_url'] ?? '';
                            
                            // If no logo_url in database, use fallback mapping
                            if (empty($logo_url) && isset($brand_logos[$brand_name_lower])) {
                                $logo_url = $brand_logos[$brand_name_lower];
                            }
                            
                            if (!empty($logo_url)): ?>
                                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="<?php echo htmlspecialchars($product['brand'] ?? 'Brand'); ?> Logo" class="brand-logo">
                            <?php else: ?>
                                <div class="brand-logo-fallback">
                                    <i class="fas fa-tire"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="brand-overlay"><?php echo htmlspecialchars(strtoupper($product['brand'] ?? 'Unknown Brand')); ?></div>
                            <span class="set-label">Set of 4</span>
                        </div>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($product['brand'] ?? 'Unknown Brand'); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name'] ?? 'Unnamed Product'); ?></h3>
                            <div class="product-size"><?php echo htmlspecialchars($product['size'] ?? 'Size not specified'); ?></div>
                            
                            <!-- Price Display -->
                            <div class="price-info">
                                <div class="per-tire-price">
                                    <span class="price-label">Per Tire:</span>
                                    <span class="price-value">$<?php echo number_format($product['price'] ?? 0, 2); ?></span>
                                </div>
                                <div class="set-price">
                                    <span class="price-label">Set of 4:</span>
                                    <span class="price-value">$<?php echo number_format(($product['price'] ?? 0) * 4, 2); ?></span>
                                </div>
                            </div>
                            
                            <!-- Stock Quantity Display -->
                            <div class="stock-info">
                                <?php 
                                $stock_quantity = intval($product['stock_quantity'] ?? 0);
                                $stock_class = $stock_quantity > 10 ? 'in-stock' : ($stock_quantity > 0 ? 'low-stock' : 'out-of-stock');
                                $stock_text = $stock_quantity > 10 ? 'In Stock' : ($stock_quantity > 0 ? 'Low Stock' : 'Out of Stock');
                                ?>
                                <span class="stock-badge <?php echo $stock_class; ?>">
                                    <i class="fas fa-box"></i>
                                    <?php echo $stock_text; ?> (<?php echo $stock_quantity; ?> available)
                                </span>
                            </div>
                            
                            <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'No description available'); ?></p>
                            <div class="product-features">
                                <strong>Features:</strong>
                                <ul>
                                    <li>Premium quality tires</li>
                                    <li>All-season performance</li>
                                    <li>Excellent traction</li>
                                    <li>Long-lasting tread life</li>
                                </ul>
                            </div>
                            <a href="contact.php" class="btn btn-primary">Inquire Now</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-search fa-3x"></i>
                    <h3>No products found</h3>
                    <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
                        <p>No products match your current search criteria.</p>
                        <p>Try adjusting your filters or search terms.</p>
                    <?php else: ?>
                        <p>No products available at the moment.</p>
                    <?php endif; ?>
                    <div class="no-products-actions">
                        <?php if (!empty($brand_filter) || !empty($size_filter) || !empty($search_filter)): ?>
                            <a href="products.php" class="btn btn-secondary">Clear Filters</a>
                        <?php endif; ?>
                        <a href="contact.php" class="btn btn-primary">Contact Us</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                
                <!-- First Contact Person -->
                <div class="footer-contact-person">
                    <h4>
                        <div class="contact-avatar johny">J</div>
                        Johny
                    </h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> (250) 986-9191</li>
                        <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    </ul>
                </div>
                
                <!-- Second Contact Person -->
                <div class="footer-contact-person">
                    <h4>
                        <div class="contact-avatar harjinder">H</div>
                        Harjinder Gill
                    </h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> (250) 565-1571</li>
                        <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-section">
                <h3>Business Hours</h3>
                <ul>
                    <li>Monday - Friday: 8:00 AM - 6:00 PM</li>
                    <li>Saturday - Sunday: 9:00 AM - 5:00 PM</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when brand or size filters change
        const brandSelect = document.getElementById('brand');
        const sizeSelect = document.getElementById('size');
        
        if (brandSelect) {
            brandSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
        
        if (sizeSelect) {
            sizeSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
        
        // Add visual feedback for active filters
        const filterGroups = document.querySelectorAll('.filter-group');
        filterGroups.forEach(group => {
            const select = group.querySelector('select');
            const input = group.querySelector('input');
            
            if (select && select.value && select.value !== '') {
                group.classList.add('filter-active');
            }
            
            if (input && input.value) {
                group.classList.add('filter-active');
            }
        });
        
        // Clear all filters functionality
        const clearButton = document.querySelector('a[href="products.php"]');
        if (clearButton) {
            clearButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'products.php';
            });
        }
        
        // Search input with debounce
        const searchInput = document.getElementById('search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        this.form.submit();
                    }
                }, 500);
            });
        }

        // Mobile Navigation Toggle
        const mobileNavToggle = document.getElementById('mobile-nav-toggle');
        const navLinks = document.getElementById('nav-links');
        
        if (mobileNavToggle && navLinks) {
            mobileNavToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                
                // Change icon based on state
                const icon = this.querySelector('i');
                if (navLinks.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            });
            
            // Close mobile menu when clicking on a link
            const navLinksItems = navLinks.querySelectorAll('a');
            navLinksItems.forEach(link => {
                link.addEventListener('click', function() {
                    navLinks.classList.remove('active');
                    const icon = mobileNavToggle.querySelector('i');
                    icon.className = 'fas fa-bars';
                });
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileNavToggle.contains(event.target) && !navLinks.contains(event.target)) {
                    navLinks.classList.remove('active');
                    const icon = mobileNavToggle.querySelector('i');
                    icon.className = 'fas fa-bars';
                }
            });
        }
    });
    </script>
</body>
</html> 