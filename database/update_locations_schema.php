<?php
// Update database schema to include locations functionality
require_once __DIR__ . '/../includes/db_connect.php';

echo "Updating database schema for locations functionality...\n";

try {
    // Create locations table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS locations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            address TEXT,
            contact_person TEXT,
            contact_phone TEXT,
            contact_email TEXT,
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Created locations table\n";
    
    // Insert default locations
    $conn->exec("
        INSERT OR IGNORE INTO locations (name, description, address, contact_person, contact_phone, contact_email, sort_order) VALUES
        ('Main Warehouse', 'Primary storage facility for all inventory', '123 Main Street, Victoria, BC', 'John Smith', '(250) 555-0101', 'warehouse@gtautomotives.com', 1),
        ('Showroom', 'Customer-facing display area', '456 Oak Avenue, Victoria, BC', 'Sarah Johnson', '(250) 555-0102', 'showroom@gtautomotives.com', 2),
        ('Service Bay', 'Automotive service and installation area', '789 Service Road, Victoria, BC', 'Mike Wilson', '(250) 555-0103', 'service@gtautomotives.com', 3),
        ('Secondary Storage', 'Additional storage for overflow inventory', '321 Storage Lane, Victoria, BC', 'Lisa Brown', '(250) 555-0104', 'storage@gtautomotives.com', 4)
    ");
    echo "✓ Inserted default locations\n";
    
    // Add location_id column to tires table if it doesn't exist
    try {
        $conn->exec("ALTER TABLE tires ADD COLUMN location_id INTEGER DEFAULT 1");
        echo "✓ Added location_id column to tires table\n";
        
        // Update existing tires to use default location (Main Warehouse)
        $conn->exec("UPDATE tires SET location_id = 1 WHERE location_id IS NULL");
        echo "✓ Updated existing tires with default location\n";
    } catch (Exception $e) {
        echo "Location column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Add location_id column to services table if it doesn't exist
    try {
        $conn->exec("ALTER TABLE services ADD COLUMN location_id INTEGER DEFAULT 1");
        echo "✓ Added location_id column to services table\n";
        
        // Update existing services to use default location
        $conn->exec("UPDATE services SET location_id = 1 WHERE location_id IS NULL");
        echo "✓ Updated existing services with default location\n";
    } catch (Exception $e) {
        echo "Services location column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Create indexes for better performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_tires_location_id ON tires(location_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_services_location_id ON services(location_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_locations_active ON locations(is_active)");
    
    echo "✓ Created indexes for better performance\n";
    
    // Verify the structure
    $tires_columns = $conn->query("PRAGMA table_info(tires)");
    $has_location = false;
    while ($column = $tires_columns->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'location_id') {
            $has_location = true;
            break;
        }
    }
    
    if ($has_location) {
        echo "✓ Location functionality is ready for use.\n";
    } else {
        echo "✗ Location column not found in tires table.\n";
    }
    
    echo "\n✓ Locations schema updated successfully!\n";
    echo "The inventory system now supports location management.\n";
    
} catch (Exception $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?> 