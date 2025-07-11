<?php
// Update database schema to include sales tables
require_once __DIR__ . '/../includes/db_connect.php';

try {
    // Create sales table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_number TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL,
            customer_email TEXT,
            customer_phone TEXT,
            customer_address TEXT,
            subtotal REAL NOT NULL DEFAULT 0,
            tax_rate REAL NOT NULL DEFAULT 0.08,
            tax_amount REAL NOT NULL DEFAULT 0,
            total_amount REAL NOT NULL DEFAULT 0,
            payment_method TEXT DEFAULT 'cash_with_invoice',
            payment_status TEXT DEFAULT 'pending' CHECK(payment_status IN ('pending', 'paid', 'cancelled')),
            notes TEXT,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");
    
    // Create sale_items table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sale_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            tire_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            unit_price REAL NOT NULL,
            total_price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
        )
    ");
    
    echo "Sales tables created successfully!\n";
    
    // Check if tables exist
    $sales_check = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sales'");
    $sale_items_check = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sale_items'");
    
    if ($sales_check->fetchArray() && $sale_items_check->fetchArray()) {
        echo "Both sales and sale_items tables are ready for use.\n";
    } else {
        echo "Error: Tables were not created properly.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 