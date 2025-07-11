<?php
// Update database schema to include sizes table
require_once __DIR__ . '/../includes/db_connect.php';

echo "Updating database schema for sizes functionality...\n";

try {
    // Create sizes table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sizes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Created sizes table\n";
    
    // Insert default tire sizes
    $default_sizes = [
        '205/55R16' => 'Standard passenger car size',
        '225/45R17' => 'Common performance tire size',
        '245/40R18' => 'Wide performance tire size',
        '265/35R19' => 'High-performance tire size',
        '195/65R15' => 'Compact car tire size',
        '215/55R16' => 'Mid-size car tire size',
        '235/55R17' => 'SUV and truck tire size',
        '255/35R19' => 'Luxury vehicle tire size',
        '185/65R15' => 'Small car tire size',
        '215/60R16' => 'Family car tire size',
        '225/65R17' => 'Crossover SUV tire size',
        '275/40R19' => 'Sports car tire size',
        '175/65R14' => 'Economy car tire size',
        '225/55R16' => 'Sedan tire size',
        '235/65R17' => 'SUV tire size',
        '285/35R19' => 'High-performance luxury tire size'
    ];
    
    foreach ($default_sizes as $size => $description) {
        $conn->exec("
            INSERT OR IGNORE INTO sizes (name, description, sort_order) 
            VALUES ('$size', '$description', " . (array_search($size, array_keys($default_sizes)) + 1) . ")
        ");
    }
    echo "✓ Inserted default tire sizes\n";
    
    // Create indexes for better performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_sizes_active ON sizes(is_active)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_sizes_sort_order ON sizes(sort_order)");
    
    echo "✓ Created indexes for better performance\n";
    
    // Verify the structure
    $sizes_check = $conn->query("SELECT COUNT(*) as count FROM sizes");
    $sizes_count = $sizes_check->fetchArray(SQLITE3_ASSOC)['count'];
    
    echo "✓ Sizes table is ready for use. Total sizes: $sizes_count\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 