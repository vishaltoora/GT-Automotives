<?php
// Update database schema to add brands table
require_once __DIR__ . '/../includes/db_connect.php';

echo "Updating database schema...\n";

try {
    // Create brands table
    $conn->exec("CREATE TABLE IF NOT EXISTS brands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        website TEXT,
        logo_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created brands table.\n";
    
    // Insert sample brand data
    $brands = [
        ['Michelin', 'French tire manufacturer known for high-performance and innovative tire technology.', 'https://www.michelin.com'],
        ['Bridgestone', 'Japanese multinational tire and rubber company, the largest tire manufacturer in the world.', 'https://www.bridgestone.com'],
        ['Goodyear', 'American multinational tire manufacturing company founded in 1898.', 'https://www.goodyear.com'],
        ['Continental', 'German automotive manufacturing company specializing in brake systems, interior electronics, and tires.', 'https://www.continental-tires.com'],
        ['Pirelli', 'Italian multinational tire manufacturer focused on high-performance and luxury vehicles.', 'https://www.pirelli.com'],
        ['Yokohama', 'Japanese tire manufacturer known for high-performance and all-season tires.', 'https://www.yokohamatire.com'],
        ['Toyo', 'Japanese tire manufacturer specializing in performance and off-road tires.', 'https://www.toyotires.com'],
        ['Hankook', 'South Korean tire manufacturer known for quality and performance at competitive prices.', 'https://www.hankooktire.com']
    ];
    
    foreach ($brands as $brand) {
        $stmt = $conn->prepare("INSERT OR IGNORE INTO brands (name, description, website) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $brand[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $brand[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $brand[2], SQLITE3_TEXT);
        $stmt->execute();
    }
    echo "Inserted brand data.\n";
    
    // Check if tires table has brand_id column
    $result = $conn->query("PRAGMA table_info(tires)");
    $has_brand_id = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === 'brand_id') {
            $has_brand_id = true;
            break;
        }
    }
    
    if (!$has_brand_id) {
        // Add brand_id column to tires table
        $conn->exec("ALTER TABLE tires ADD COLUMN brand_id INTEGER");
        echo "Added brand_id column to tires table.\n";
        
        // Update existing tires to use brand_id
        $brand_map = [
            'Michelin' => 1,
            'Bridgestone' => 2,
            'Goodyear' => 3,
            'Continental' => 4,
            'Pirelli' => 5,
            'Yokohama' => 6,
            'Toyo' => 7,
            'Hankook' => 8
        ];
        
        foreach ($brand_map as $brand_name => $brand_id) {
            $stmt = $conn->prepare("UPDATE tires SET brand_id = ? WHERE brand = ?");
            $stmt->bindValue(1, $brand_id, SQLITE3_INTEGER);
            $stmt->bindValue(2, $brand_name, SQLITE3_TEXT);
            $stmt->execute();
        }
        echo "Updated existing tires with brand_id.\n";
        
        // Remove the old brand column
        $conn->exec("CREATE TABLE tires_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            brand_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            size TEXT NOT NULL,
            price REAL NOT NULL,
            description TEXT,
            image_url TEXT,
            stock_quantity INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $conn->exec("INSERT INTO tires_new SELECT id, brand_id, name, size, price, description, image_url, stock_quantity, created_at, updated_at FROM tires");
        $conn->exec("DROP TABLE tires");
        $conn->exec("ALTER TABLE tires_new RENAME TO tires");
        
        echo "Removed old brand column and restructured tires table.\n";
    }
    
    echo "Database schema updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 