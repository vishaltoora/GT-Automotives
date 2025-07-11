<?php
// Fix sale_items table schema to support both products and services
require_once __DIR__ . '/../includes/db_connect.php';

try {
    echo "Fixing sale_items table schema to support both products and services...\n";
    
    // First, let's check the current structure
    $columns = $conn->query("PRAGMA table_info(sale_items)");
    $current_columns = [];
    while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
        $current_columns[$column['name']] = $column;
    }
    
    echo "Current sale_items table structure:\n";
    foreach ($current_columns as $name => $column) {
        echo "- $name: " . $column['type'] . " (" . ($column['notnull'] ? 'NOT NULL' : 'NULL') . ")\n";
    }
    
    // Create a new table with the correct structure
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sale_items_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            tire_id INTEGER DEFAULT NULL,
            service_id INTEGER DEFAULT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            unit_price REAL NOT NULL,
            total_price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        )
    ");
    
    echo "✓ Created new sale_items table structure\n";
    
    // Copy data from old table to new table
    if (isset($current_columns['id'])) {
        $conn->exec("
            INSERT INTO sale_items_new (id, sale_id, tire_id, service_id, quantity, unit_price, total_price, created_at)
            SELECT id, sale_id, tire_id, NULL as service_id, quantity, unit_price, total_price, created_at
            FROM sale_items
        ");
        echo "✓ Migrated existing data to new table structure\n";
    }
    
    // Drop the old table and rename the new one
    $conn->exec("DROP TABLE IF EXISTS sale_items");
    $conn->exec("ALTER TABLE sale_items_new RENAME TO sale_items");
    
    echo "✓ Replaced old table with new structure\n";
    
    // Create indexes for better performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_sale_items_sale_id ON sale_items(sale_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_sale_items_tire_id ON sale_items(tire_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_sale_items_service_id ON sale_items(service_id)");
    
    echo "✓ Created indexes for better performance\n";
    
    // Verify the new structure
    $new_columns = $conn->query("PRAGMA table_info(sale_items)");
    echo "\nNew sale_items table structure:\n";
    while ($column = $new_columns->fetchArray(SQLITE3_ASSOC)) {
        echo "- $column[name]: $column[type] (" . ($column['notnull'] ? 'NOT NULL' : 'NULL') . ")\n";
    }
    
    echo "\n✓ Sale items table schema updated successfully!\n";
    echo "The table now supports both products (tire_id) and services (service_id).\n";
    
} catch (Exception $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?> 