<?php
// Database Migration System
// This script manages database schema changes across environments

require_once '../includes/db_connect.php';

class DatabaseMigration {
    private $conn;
    private $migrations_table = 'database_migrations';
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrations_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'executed', 'failed') DEFAULT 'pending',
            error_message TEXT
        )";
        
        $this->conn->query($sql);
    }
    
    public function getMigrations() {
        return [
            '001_create_users_table' => [
                'description' => 'Create users table with proper structure',
                'sql' => "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    first_name VARCHAR(255) NOT NULL,
                    last_name VARCHAR(255) NOT NULL,
                    email VARCHAR(255),
                    password VARCHAR(255) NOT NULL,
                    is_admin TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )"
            ],
            '002_add_missing_user_columns' => [
                'description' => 'Add missing columns to existing users table',
                'sql' => "ALTER TABLE users 
                    ADD COLUMN IF NOT EXISTS first_name VARCHAR(255) NOT NULL DEFAULT '',
                    ADD COLUMN IF NOT EXISTS last_name VARCHAR(255) NOT NULL DEFAULT '',
                    ADD COLUMN IF NOT EXISTS email VARCHAR(255),
                    ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
            ],
            '003_create_brands_table' => [
                'description' => 'Create brands table',
                'sql' => "CREATE TABLE IF NOT EXISTS brands (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    logo_url TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )"
            ],
            '004_create_sizes_table' => [
                'description' => 'Create sizes table',
                'sql' => "CREATE TABLE IF NOT EXISTS sizes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    size VARCHAR(50) NOT NULL UNIQUE,
                    width INT,
                    aspect_ratio INT,
                    diameter INT,
                    load_index VARCHAR(10),
                    speed_rating VARCHAR(5),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )"
            ],
            '005_create_tires_table' => [
                'description' => 'Create tires table',
                'sql' => "CREATE TABLE IF NOT EXISTS tires (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    brand_id INT,
                    size VARCHAR(100),
                    price DECIMAL(10,2) NOT NULL,
                    stock_quantity INT DEFAULT 0,
                    image_url TEXT,
                    condition ENUM('new', 'used') DEFAULT 'new',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
                )"
            ],
            '006_create_sales_table' => [
                'description' => 'Create sales table',
                'sql' => "CREATE TABLE IF NOT EXISTS sales (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_number VARCHAR(100) NOT NULL UNIQUE,
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255),
                    customer_phone VARCHAR(50),
                    customer_address TEXT,
                    customer_business_name VARCHAR(255),
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
                    gst_rate DECIMAL(5,4) NOT NULL DEFAULT 0.05,
                    gst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    pst_rate DECIMAL(5,4) NOT NULL DEFAULT 0.07,
                    pst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    payment_method ENUM('cash_with_invoice', 'credit_card', 'bank_transfer') DEFAULT 'cash_with_invoice',
                    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
                    notes TEXT,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id)
                )"
            ],
            '007_create_sale_items_table' => [
                'description' => 'Create sale_items table',
                'sql' => "CREATE TABLE IF NOT EXISTS sale_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    sale_id INT NOT NULL,
                    tire_id INT NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    unit_price DECIMAL(10,2) NOT NULL,
                    total_price DECIMAL(10,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
                    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
                )"
            ],
            '008_create_service_categories_table' => [
                'description' => 'Create service_categories table',
                'sql' => "CREATE TABLE IF NOT EXISTS service_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    description TEXT,
                    sort_order INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )"
            ],
            '009_create_services_table' => [
                'description' => 'Create services table',
                'sql' => "CREATE TABLE IF NOT EXISTS services (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    category VARCHAR(255) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    duration_minutes INT DEFAULT 60,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )"
            ],
            '010_create_inquiries_table' => [
                'description' => 'Create inquiries table',
                'sql' => "CREATE TABLE IF NOT EXISTS inquiries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    message TEXT NOT NULL,
                    tire_id INT,
                    status ENUM('new', 'in_progress', 'completed') DEFAULT 'new',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE SET NULL
                )"
            ],
            '011_insert_default_admin_user' => [
                'description' => 'Insert default admin user if no users exist',
                'sql' => function() {
                    $password_hash = '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q';
                    return "INSERT INTO users (username, first_name, last_name, password, email, is_admin) 
                        SELECT 'admin', 'Admin', 'User', '$password_hash', 'admin@gtautomotives.com', 1 
                        WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin')";
                }
            ],
            '012_insert_sample_data' => [
                'description' => 'Insert sample brands and categories',
                'sql' => "INSERT IGNORE INTO brands (name, logo_url) VALUES 
                    ('MICHELIN', 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Michelin.svg/200px-Michelin.svg.png'),
                    ('BRIDGESTONE', 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bridgestone_logo.svg/200px-Bridgestone_logo.svg.png'),
                    ('GOODYEAR', 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Goodyear_logo.svg/200px-Goodyear_logo.svg.png'),
                    ('CONTINENTAL', 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/200px-Continental_AG_logo.svg.png'),
                    ('PIRELLI', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Pirelli_logo.svg/200px-Pirelli_logo.svg.png'),
                    ('YOKOHAMA', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Yokohama_Tire_Logo.svg/200px-Yokohama_Tire_Logo.svg.png'),
                    ('TOYO', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Toyo_Tire_logo.svg/200px-Toyo_Tire_logo.svg.png'),
                    ('HANKOOK', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Hankook_Tire_logo.svg/200px-Hankook_Tire_logo.svg.png')"
            ]
        ];
    }
    
    public function getExecutedMigrations() {
        $sql = "SELECT migration_name FROM {$this->migrations_table} WHERE status = 'executed'";
        $result = $this->conn->query($sql);
        $executed = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $executed[] = $row['migration_name'];
            }
        }
        return $executed;
    }
    
    public function executeMigration($migration_name, $sql) {
        try {
            // Handle both string and function SQL definitions
            if (is_callable($sql)) {
                $sql = $sql();
            }
            
            // Execute the migration
            if ($this->conn->query($sql)) {
                // Record successful execution
                $insert_sql = "INSERT INTO {$this->migrations_table} (migration_name, status) VALUES (?, 'executed')";
                $stmt = $this->conn->prepare($insert_sql);
                $stmt->bind_param("s", $migration_name);
                $stmt->execute();
                return true;
            } else {
                // Record failed execution
                $insert_sql = "INSERT INTO {$this->migrations_table} (migration_name, status, error_message) VALUES (?, 'failed', ?)";
                $stmt = $this->conn->prepare($insert_sql);
                $error_msg = $this->conn->error;
                $stmt->bind_param("ss", $migration_name, $error_msg);
                $stmt->execute();
                return false;
            }
        } catch (Exception $e) {
            // Record failed execution
            $insert_sql = "INSERT INTO {$this->migrations_table} (migration_name, status, error_message) VALUES (?, 'failed', ?)";
            $stmt = $this->conn->prepare($insert_sql);
            $error_msg = $e->getMessage();
            $stmt->bind_param("ss", $migration_name, $error_msg);
            $stmt->execute();
            return false;
        }
    }
    
    public function runMigrations() {
        $migrations = $this->getMigrations();
        $executed = $this->getExecutedMigrations();
        $pending = array_diff(array_keys($migrations), $executed);
        
        if (empty($pending)) {
            return ['status' => 'success', 'message' => 'All migrations are up to date'];
        }
        
        $results = [];
        foreach ($pending as $migration_name) {
            $migration = $migrations[$migration_name];
            $success = $this->executeMigration($migration_name, $migration['sql']);
            $results[$migration_name] = [
                'success' => $success,
                'description' => $migration['description']
            ];
        }
        
        return $results;
    }
}

// Handle the migration execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    $migration = new DatabaseMigration($conn);
    $results = $migration->runMigrations();
    
    if (is_array($results)) {
        echo "<h2>Migration Results</h2>";
        foreach ($results as $migration_name => $result) {
            $status = $result['success'] ? '✅ Success' : '❌ Failed';
            $color = $result['success'] ? 'green' : 'red';
            echo "<p style='color: {$color};'><strong>{$migration_name}:</strong> {$status} - {$result['description']}</p>";
        }
    } else {
        echo "<p style='color: green;'>{$results['message']}</p>";
    }
}

// Display migration status
$migration = new DatabaseMigration($conn);
$migrations = $migration->getMigrations();
$executed = $migration->getExecutedMigrations();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .migration-item { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            border-left: 4px solid;
        }
        .executed { background: #d4edda; border-color: #28a745; }
        .pending { background: #fff3cd; border-color: #ffc107; }
        .failed { background: #f8d7da; border-color: #dc3545; }
        .btn { 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            margin: 10px 5px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-executed { background: #28a745; color: white; }
        .status-pending { background: #ffc107; color: black; }
        .status-failed { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Migrations</h1>
        
        <div style="margin: 20px 0;">
            <form method="POST">
                <button type="submit" name="run_migrations" class="btn btn-primary">
                    🔄 Run Pending Migrations
                </button>
            </form>
        </div>
        
        <h2>Migration Status</h2>
        <p>Total Migrations: <?php echo count($migrations); ?></p>
        <p>Executed: <?php echo count($executed); ?></p>
        <p>Pending: <?php echo count($migrations) - count($executed); ?></p>
        
        <div style="margin: 20px 0;">
            <?php foreach ($migrations as $migration_name => $migration_data): ?>
                <?php 
                $is_executed = in_array($migration_name, $executed);
                $status_class = $is_executed ? 'executed' : 'pending';
                $status_text = $is_executed ? 'EXECUTED' : 'PENDING';
                $status_badge_class = $is_executed ? 'status-executed' : 'status-pending';
                ?>
                <div class="migration-item <?php echo $status_class; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3><?php echo $migration_name; ?></h3>
                            <p><?php echo $migration_data['description']; ?></p>
                        </div>
                        <span class="status-badge <?php echo $status_badge_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="../admin/users.php" class="btn btn-success">👥 Manage Users</a>
            <a href="../admin/index.php" class="btn btn-warning">🏠 Admin Dashboard</a>
        </div>
    </div>
</body>
</html> 