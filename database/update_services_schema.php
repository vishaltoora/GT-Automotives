<?php
// Update database schema to include services
require_once __DIR__ . '/../includes/db_connect.php';

try {
    // Create services table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT NOT NULL DEFAULT 'general',
            duration_minutes INTEGER DEFAULT 60,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create service_categories table for better organization
    $conn->exec("
        CREATE TABLE IF NOT EXISTS service_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            icon TEXT DEFAULT 'fas fa-tools',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default service categories
    $conn->exec("
        INSERT OR IGNORE INTO service_categories (name, description, icon, sort_order) VALUES
        ('Oil & Lubrication', 'Oil changes, fluid checks, and lubrication services', 'fas fa-oil-can', 1),
        ('Tire Services', 'Tire mounting, balancing, rotation, and repair', 'fas fa-tire', 2),
        ('Brake Services', 'Brake inspection, replacement, and repair', 'fas fa-brake-system', 3),
        ('Engine Services', 'Engine diagnostics, tune-ups, and repairs', 'fas fa-engine', 4),
        ('Electrical Services', 'Battery, alternator, and electrical system work', 'fas fa-bolt', 5),
        ('Suspension & Steering', 'Shocks, struts, alignment, and steering repairs', 'fas fa-car-side', 6),
        ('Air Conditioning', 'AC system inspection, repair, and maintenance', 'fas fa-snowflake', 7),
        ('General Maintenance', 'General inspection, maintenance, and repairs', 'fas fa-tools', 8)
    ");
    
    // Insert sample services
    $conn->exec("
        INSERT OR IGNORE INTO services (name, description, price, category, duration_minutes) VALUES
        ('Oil Change', 'Conventional oil change with filter replacement', 45.00, 'Oil & Lubrication', 30),
        ('Synthetic Oil Change', 'Full synthetic oil change with premium filter', 65.00, 'Oil & Lubrication', 30),
        ('Tire Mount & Balance', 'Mount and balance 4 tires', 80.00, 'Tire Services', 60),
        ('Tire Rotation', 'Rotate tires and check alignment', 35.00, 'Tire Services', 30),
        ('Brake Pad Replacement', 'Replace front or rear brake pads', 120.00, 'Brake Services', 90),
        ('Brake Rotor Replacement', 'Replace brake rotors and pads', 200.00, 'Brake Services', 120),
        ('Battery Replacement', 'Replace car battery with testing', 85.00, 'Electrical Services', 30),
        ('Alternator Replacement', 'Replace alternator with testing', 250.00, 'Electrical Services', 120),
        ('Air Filter Replacement', 'Replace engine air filter', 25.00, 'Engine Services', 15),
        ('Cabin Air Filter Replacement', 'Replace cabin air filter', 35.00, 'Engine Services', 15),
        ('Wheel Alignment', 'Four-wheel alignment check and adjustment', 75.00, 'Suspension & Steering', 60),
        ('Shock/Strut Replacement', 'Replace shocks or struts (per pair)', 300.00, 'Suspension & Steering', 120),
        ('AC Recharge', 'Recharge air conditioning system', 120.00, 'Air Conditioning', 45),
        ('AC System Diagnosis', 'Diagnose AC system issues', 75.00, 'Air Conditioning', 30),
        ('Multi-Point Inspection', 'Comprehensive vehicle inspection', 50.00, 'General Maintenance', 45),
        ('Fluid Level Check', 'Check and top off all fluid levels', 25.00, 'General Maintenance', 15)
    ");
    
    // Update sale_items table to support services
    try {
        $conn->exec("ALTER TABLE sale_items ADD COLUMN service_id INTEGER DEFAULT NULL");
    } catch (Exception $e) {
        // Column might already exist, ignore error
    }
    
    // Add foreign key constraint for services in sale_items
    $conn->exec("
        CREATE INDEX IF NOT EXISTS idx_sale_items_service_id ON sale_items(service_id)
    ");
    
    echo "Services schema updated successfully!\n";
    echo "Added services table with sample data.\n";
    echo "Updated sale_items table to support services.\n";
    
} catch (Exception $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?> 