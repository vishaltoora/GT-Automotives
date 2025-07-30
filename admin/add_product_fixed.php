<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering
ob_start();

try {
    // Include database connection
    if (file_exists('../includes/db_connect.php')) {
        require_once '../includes/db_connect.php';
    } else {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Error:</strong> db_connect.php not found";
        echo "</div>";
    }

    if (file_exists('../includes/auth.php')) {
        require_once '../includes/auth.php';
    } else {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Error:</strong> auth.php not found";
        echo "</div>";
    }

    // Test database connection
    if (isset($conn) && !$conn->connect_error) {
        echo "<div style='background: #e8f5e9; border: 1px solid #4caf50; padding: 10px; margin: 10px; border-radius: 4px;'>✅ Database connection successful</div>";
    } else {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Database Error:</strong> Connection failed";
        echo "</div>";
    }

    // Require login
    requireLogin();

    // Set page title
    $page_title = 'Add Product';

    // Initialize variables
    $brands = [];
    $locations = [];
    $errors = [];
    $success_message = '';

    // Fetch brands for dropdown
    if (isset($conn)) {
        $brands_result = $conn->query('SELECT id, name FROM brands ORDER BY name ASC');
        if ($brands_result) {
            while ($row = $brands_result->fetch_assoc()) {
                $brands[] = $row;
            }
        } else {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Error:</strong> Could not fetch brands: " . $conn->error;
            echo "</div>";
        }

        // Fetch locations for dropdown
        $locations_result = $conn->query('SELECT id, name FROM locations ORDER BY name ASC');
        if ($locations_result) {
            while ($row = $locations_result->fetch_assoc()) {
                $locations[] = $row;
            }
        } else {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Error:</strong> Could not fetch locations: " . $conn->error;
            echo "</div>";
        }
    }

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $brand_id = intval($_POST['brand_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $size = trim($_POST['size'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $condition = $_POST['condition'] ?? 'new';
        
        // Validate input
        if ($brand_id <= 0) {
            $errors[] = 'Brand is required';
        }
        
        if (empty($name)) {
            $errors[] = 'Product name is required';
        }
        
        if (empty($size)) {
            $errors[] = 'Size is required';
        }
        
        if ($price <= 0) {
            $errors[] = 'Price must be greater than zero';
        }
        
        if ($stock_quantity < 0) {
            $errors[] = 'Stock quantity cannot be negative';
        }
        
        if (!in_array($condition, ['new', 'used'])) {
            $errors[] = 'Invalid condition selected';
        }
        
        // If no errors, insert the product
        if (empty($errors) && isset($conn)) {
            $insert_query = "INSERT INTO tires (brand_id, name, size, price, description, stock_quantity, `condition`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            
            if ($stmt) {
                $stmt->bind_param("issdsis", $brand_id, $name, $size, $price, $description, $stock_quantity, $condition);
                
                if ($stmt->execute()) {
                    $success_message = "Product added successfully! Product ID: " . $conn->insert_id;
                    
                    // Clear form data after successful submission
                    $brand_id = 0;
                    $name = '';
                    $size = '';
                    $price = 0;
                    $description = '';
                    $stock_quantity = 0;
                    $condition = 'new';
                } else {
                    $errors[] = "Error adding product: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                $errors[] = "Error preparing statement: " . $conn->error;
            }
        }
    }

} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// Flush any output so far
ob_flush();

// Include header
if (file_exists('includes/header.php')) {
    include_once 'includes/header.php';
} else {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
    echo "<strong>Error:</strong> header.php not found";
    echo "</div>";
}
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Add New Product</h1>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-row">
            <div class="form-group">
                <label for="brand_id">Brand *</label>
                <select name="brand_id" id="brand_id" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['id']; ?>" <?php echo ($brand_id == $brand['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="size">Size *</label>
                <input type="text" name="size" id="size" value="<?php echo htmlspecialchars($size ?? ''); ?>" placeholder="e.g., 225/45R17" required>
            </div>

            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" min="0" value="<?php echo htmlspecialchars($stock_quantity ?? 0); ?>">
            </div>

            <div class="form-group">
                <label for="condition">Condition</label>
                <select name="condition" id="condition">
                    <option value="new" <?php echo ($condition ?? 'new') === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="used" <?php echo ($condition ?? 'new') === 'used' ? 'selected' : ''; ?>>Used</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.admin-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admin-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
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

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}
</style>

<?php
// Include footer
if (file_exists('includes/footer.php')) {
    include_once 'includes/footer.php';
}
?> 