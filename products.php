<?php
// Include error handler for debugging
require_once 'includes/error_handler.php';

// Include database connection first
require_once 'includes/db_connect.php';

// Enable debugging if requested
if (isset($_GET['debug'])) {
    echo "<div style='background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Debug Mode Enabled</strong><br>";
    echo "Server: " . $_SERVER['SERVER_NAME'] . "<br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
    echo "</div>";
}

// Test database connection
try {
    $db_status = testDatabaseConnection();
    echo isset($_GET['debug']) ? "<div style='background: #e8f5e9; border: 1px solid #4caf50; padding: 10px; margin: 10px; border-radius: 4px;'>✅ Database connection successful</div>" : "";
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Database Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
    // Continue with limited functionality
}

// Check required extensions
$missing_extensions = checkRequiredExtensions();
if (!empty($missing_extensions)) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Missing PHP Extensions:</strong> " . implode(', ', $missing_extensions);
    echo "</div>";
}

// Ensure uploads directory exists
try {
    ensureUploadsDirectory();
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Uploads Directory Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
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

// Get products from database with brand names
$query = "SELECT t.*, b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id $where_clause ORDER BY b.name, t.name";
$result = $conn->query($query);

// Get distinct brands and sizes for filters
$brands_query = "SELECT DISTINCT b.name as brand FROM tires t LEFT JOIN brands b ON t.brand_id = b.id ORDER BY b.name";
$brands_result = $conn->query($brands_query);

$sizes_query = "SELECT DISTINCT size FROM tires ORDER BY size";
$sizes_result = $conn->query($sizes_query);
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
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 150px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background: white;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .search-group {
            flex: 1;
            min-width: 250px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }
        
        .filter-buttons .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
        
        /* Enhanced no products state */
        .no-products {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .no-products i {
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .no-products h3 {
            margin: 1rem 0 0.5rem 0;
            color: #666;
        }
        
        .no-products p {
            color: #999;
            margin-bottom: 0.5rem;
        }
        
        .no-products-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: auto;
            }
            
            .search-group {
                min-width: auto;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .no-products-actions {
                flex-direction: column;
                align-items: center;
            }
        }
        
        /* Active filter indicators */
        .filter-active {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        
        .filter-active label {
            color: #1976d2;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">GT Automotives</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
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
                            <?php while ($brand = $brands_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($brand['brand']); ?>" <?php echo $brand_filter === $brand['brand'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="size">Size</label>
                        <select name="size" id="size">
                            <option value="">All Sizes</option>
                            <?php while ($size = $sizes_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($size['size']); ?>" <?php echo $size_filter === $size['size'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($size['size']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group search-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Search by name, description, brand, or size...">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear All
                    </a>
                </div>
            </form>
        </div>

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
            <?php 
            // Count rows for MySQL
            $row_count = $result->num_rows;
            ?>
            <?php if ($row_count > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <div class="emoji-container">
                                <span class="tire-emoji">🛞</span>
                                <span class="tire-emoji">🛞</span>
                                <span class="tire-emoji">🛞</span>
                                <span class="tire-emoji">🛞</span>
                            </div>
                            <div class="brand-overlay"><?php echo htmlspecialchars(strtoupper($product['brand'])); ?></div>
                            <span class="set-label">Set of 4</span>
                        </div>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-size"><?php echo htmlspecialchars($product['size']); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'] * 4, 2); ?></div>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
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
                <ul>
                    <li><i class="fas fa-phone"></i> (250) 986-9191</li>
                    <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    <li><i class="fas fa-user"></i> Contact: Johny</li>
                </ul>
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
    });
    </script>
</body>
</html> 