<?php
/**
 * Update locations table - Remove contact_person, contact_phone, contact_email, is_active, and sort_order columns
 */

require_once __DIR__ . '/../includes/db_connect.php';

echo "Starting locations table update...\n";

try {
    // Begin transaction
    $conn->exec("BEGIN TRANSACTION");
    
    // Create new table structure without the columns to be removed
    $createNewTable = "
    CREATE TABLE locations_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createNewTable);
    echo "Created new locations table structure...\n";
    
    // Copy data from old table to new table (excluding removed columns)
    $copyData = "
    INSERT INTO locations_new (id, name, description, address, created_at, updated_at)
    SELECT id, name, description, address, created_at, updated_at
    FROM locations";
    
    $conn->exec($copyData);
    echo "Copied data to new table structure...\n";
    
    // Drop the old table
    $conn->exec("DROP TABLE locations");
    echo "Dropped old locations table...\n";
    
    // Rename new table to original name
    $conn->exec("ALTER TABLE locations_new RENAME TO locations");
    echo "Renamed new table to 'locations'...\n";
    
    // Commit transaction
    $conn->exec("COMMIT");
    
    echo "Successfully updated locations table!\n";
    echo "Removed columns: contact_person, contact_phone, contact_email, is_active, sort_order\n";
    echo "Remaining columns: id, name, description, address, created_at, updated_at\n";
    
} catch (Exception $e) {
    $conn->exec("ROLLBACK");
    echo "Error updating locations table: " . $e->getMessage() . "\n";
    exit(1);
}
?> 