<?php
// Database update script for used tires functionality
require_once 'includes/db_connect.php';

echo "Updating database schema for used tires functionality...\n";

try {
    // Add condition field to tires table if it doesn't exist
    $conn->exec("ALTER TABLE tires ADD COLUMN condition TEXT DEFAULT 'new' CHECK(condition IN ('new', 'used'))");
    echo "✓ Added condition field to tires table\n";
} catch (Exception $e) {
    echo "Condition field already exists or error: " . $e->getMessage() . "\n";
}

try {
    // Create used_tire_photos table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS used_tire_photos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tire_id INTEGER NOT NULL,
            photo_url TEXT NOT NULL,
            photo_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Created used_tire_photos table\n";
} catch (Exception $e) {
    echo "Used tire photos table already exists or error: " . $e->getMessage() . "\n";
}

try {
    // Add customer_business_name field to sales table if it doesn't exist
    $conn->exec("ALTER TABLE sales ADD COLUMN customer_business_name TEXT");
    echo "✓ Added customer_business_name field to sales table\n";
} catch (Exception $e) {
    echo "Customer business name field already exists or error: " . $e->getMessage() . "\n";
}

// Create images directory structure if it doesn't exist
$directories = [
    'images',
    'images/tires',
    'images/used_tires',
    'images/used_tires/photos'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created directory: $dir\n";
    }
}

echo "\nDatabase update completed successfully!\n";
echo "Used tires functionality is now available.\n";
?> 