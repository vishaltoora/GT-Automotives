<?php
// Include database connection
require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Create Database Tables - GT Automotives</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }";
echo ".success { background: #e8f5e9; border-color: #4caf50; }";
echo ".error { background: #ffebee; border-color: #f44336; }";
echo ".warning { background: #fff3cd; border-color: #ffc107; }";
echo ".info { background: #e3f2fd; border-color: #2196f3; }";
echo ".btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🗄️ Create Database Tables</h1>";

$action = $_GET['action'] ?? '';

if ($action === 'create_tables') {
    echo "<div class='section info'>";
    echo "<h2>Creating Database Tables...</h2>";
    
    // Create brands table
    $create_brands = "CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_brands)) {
        echo "<p class='success'>✅ Brands table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Error creating brands table: " . $conn->error . "</p>";
    }
    
    // Create tires table
    $create_tires = "CREATE TABLE IF NOT EXISTS tires (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        brand_id INT,
        size VARCHAR(50),
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT DEFAULT 0,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
    )";
    
    if ($conn->query($create_tires)) {
        echo "<p class='success'>✅ Tires table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Error creating tires table: " . $conn->error . "</p>";
    }
    
    // Create users table
    $create_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        first_name VARCHAR(255) NULL,
        last_name VARCHAR(255) NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NULL,
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_users)) {
        echo "<p class='success'>✅ Users table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Error creating users table: " . $conn->error . "</p>";
    }
    
    // Create sales table
    $create_sales = "CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100),
        customer_phone VARCHAR(20),
        total_amount DECIMAL(10,2) NOT NULL,
        payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    if ($conn->query($create_sales)) {
        echo "<p class='success'>✅ Sales table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Error creating sales table: " . $conn->error . "</p>";
    }
    
    echo "</div>";
    
} elseif ($action === 'insert_sample_data') {
    echo "<div class='section info'>";
    echo "<h2>Inserting Sample Data...</h2>";
    
    // Insert sample brands
    $brands_data = [
        ['MICHELIN', 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Michelin.svg/200px-Michelin.svg.png'],
        ['BRIDGESTONE', 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bridgestone_logo.svg/200px-Bridgestone_logo.svg.png'],
        ['GOODYEAR', 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Goodyear_logo.svg/200px-Goodyear_logo.svg.png'],
        ['CONTINENTAL', 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/200px-Continental_AG_logo.svg.png'],
        ['PIRELLI', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Pirelli_logo.svg/200px-Pirelli_logo.svg.png'],
        ['YOKOHAMA', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Yokohama_Tire_Logo.svg/200px-Yokohama_Tire_Logo.svg.png'],
        ['TOYO', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Toyo_Tire_logo.svg/200px-Toyo_Tire_logo.svg.png'],
        ['HANKOOK', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Hankook_Tire_logo.svg/200px-Hankook_Tire_logo.svg.png']
    ];
    
    foreach ($brands_data as $brand) {
        $insert_brand = "INSERT IGNORE INTO brands (name, logo_url) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_brand);
        $stmt->bind_param("ss", $brand[0], $brand[1]);
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Brand '{$brand[0]}' inserted</p>";
        } else {
            echo "<p class='error'>❌ Error inserting brand '{$brand[0]}': " . $stmt->error . "</p>";
        }
    }
    
    // Insert sample tires
    $tires_data = [
        ['Michelin Primacy 4', 'Premium touring tire with excellent wet grip', 1, '205/55R16', 189.99, 25],
        ['Michelin Pilot Sport 4', 'High-performance summer tire', 1, '225/45R17', 245.99, 15],
        ['Bridgestone Turanza T005', 'Comfortable touring tire', 2, '215/60R16', 165.99, 30],
        ['Bridgestone Potenza S007', 'Ultra-high performance tire', 2, '245/40R18', 289.99, 10],
        ['Goodyear Eagle F1', 'Performance tire for sports cars', 3, '225/40R18', 275.99, 20],
        ['Goodyear Assurance WeatherReady', 'All-weather tire', 3, '215/55R17', 195.99, 35],
        ['Continental PremiumContact 6', 'Premium summer tire', 4, '225/45R17', 235.99, 18],
        ['Continental WinterContact TS860', 'Winter tire', 4, '205/55R16', 185.99, 12],
        ['Pirelli P Zero', 'Ultra-high performance tire', 5, '245/35R19', 325.99, 8],
        ['Pirelli Cinturato P7', 'Eco-friendly touring tire', 5, '205/55R16', 175.99, 22],
        ['Yokohama Advan Sport V105', 'Performance tire', 6, '225/40R18', 265.99, 14],
        ['Yokohama Geolandar A/T G015', 'All-terrain tire', 6, '265/70R16', 285.99, 16],
        ['Toyo Proxes Sport A/S', 'All-season performance tire', 7, '225/45R17', 225.99, 19],
        ['Toyo Open Country A/T III', 'All-terrain tire', 7, '265/70R17', 295.99, 11],
        ['Hankook Ventus V12 evo2', 'Performance tire', 8, '225/40R18', 255.99, 13],
        ['Hankook Kinergy PT H737', 'Touring tire', 8, '205/55R16', 155.99, 28]
    ];
    
    foreach ($tires_data as $tire) {
        $insert_tire = "INSERT IGNORE INTO tires (name, description, brand_id, size, price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_tire);
        $stmt->bind_param("ssisdi", $tire[0], $tire[1], $tire[2], $tire[3], $tire[4], $tire[5]);
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Tire '{$tire[0]}' inserted</p>";
        } else {
            echo "<p class='error'>❌ Error inserting tire '{$tire[0]}': " . $stmt->error . "</p>";
        }
    }
    
    echo "</div>";
    
} else {
    echo "<div class='section'>";
    echo "<h2>🔧 Database Setup Options</h2>";
    echo "<p>Choose an action to set up your database:</p>";
    echo "<ul>";
    echo "<li><a href='?action=create_tables' class='btn'>Create Missing Tables</a></li>";
    echo "<li><a href='?action=insert_sample_data' class='btn'>Insert Sample Data</a></li>";
    echo "<li><a href='check_database.php' class='btn'>Check Database Status</a></li>";
    echo "<li><a href='products.php' class='btn'>Go to Products Page</a></li>";
    echo "<li><a href='admin/index.php' class='btn'>Go to Admin Panel</a></li>";
    echo "</ul>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Run the database diagnostic tool to check for issues</li>";
echo "<li>Create missing tables if they don't exist</li>";
echo "<li>Insert sample data to populate the database</li>";
echo "<li>Test the products page</li>";
echo "<li>Access the admin panel to manage products</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?> 