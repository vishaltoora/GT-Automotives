<?php
// Update database schema to use separate GST and PST fields
require_once __DIR__ . '/../includes/db_connect.php';

try {
    echo "Updating sales table to use separate GST and PST fields...\n";
    
    // Check if the old tax_rate column exists
    $check_old_tax = $conn->query("PRAGMA table_info(sales)");
    $has_old_tax = false;
    while ($column = $check_old_tax->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'tax_rate') {
            $has_old_tax = true;
            break;
        }
    }
    
    if ($has_old_tax) {
        echo "Found old tax structure. Migrating data...\n";
        
        // Get existing sales with old tax structure
        $old_sales = $conn->query("SELECT id, tax_rate, tax_amount FROM sales");
        
        // Add new columns
        $conn->exec("ALTER TABLE sales ADD COLUMN gst_rate REAL DEFAULT 0.05");
        $conn->exec("ALTER TABLE sales ADD COLUMN gst_amount REAL DEFAULT 0");
        $conn->exec("ALTER TABLE sales ADD COLUMN pst_rate REAL DEFAULT 0.07");
        $conn->exec("ALTER TABLE sales ADD COLUMN pst_amount REAL DEFAULT 0");
        
        // Migrate existing data
        while ($sale = $old_sales->fetchArray(SQLITE3_ASSOC)) {
            $old_tax_rate = $sale['tax_rate'] ?? 0.08;
            $old_tax_amount = $sale['tax_amount'] ?? 0;
            
            // Split the old tax into GST (5%) and PST (remaining)
            $gst_rate = 0.05;
            $pst_rate = $old_tax_rate - $gst_rate;
            
            if ($pst_rate < 0) {
                $pst_rate = 0;
            }
            
            $gst_amount = $old_tax_amount * ($gst_rate / $old_tax_rate);
            $pst_amount = $old_tax_amount * ($pst_rate / $old_tax_rate);
            
            $update_stmt = $conn->prepare("
                UPDATE sales SET 
                    gst_rate = ?, 
                    gst_amount = ?, 
                    pst_rate = ?, 
                    pst_amount = ?
                WHERE id = ?
            ");
            
            $update_stmt->bindValue(1, $gst_rate, SQLITE3_FLOAT);
            $update_stmt->bindValue(2, $gst_amount, SQLITE3_FLOAT);
            $update_stmt->bindValue(3, $pst_rate, SQLITE3_FLOAT);
            $update_stmt->bindValue(4, $pst_amount, SQLITE3_FLOAT);
            $update_stmt->bindValue(5, $sale['id'], SQLITE3_INTEGER);
            $update_stmt->execute();
        }
        
        echo "Data migration completed.\n";
    } else {
        echo "Sales table already has new tax structure.\n";
    }
    
    // Ensure the table has the correct structure
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sales_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_number TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL,
            customer_email TEXT,
            customer_phone TEXT,
            customer_address TEXT,
            subtotal REAL NOT NULL DEFAULT 0,
            gst_rate REAL NOT NULL DEFAULT 0.05,
            gst_amount REAL NOT NULL DEFAULT 0,
            pst_rate REAL NOT NULL DEFAULT 0.07,
            pst_amount REAL NOT NULL DEFAULT 0,
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
    
    echo "Sales table structure updated successfully!\n";
    
    // Verify the structure
    $columns = $conn->query("PRAGMA table_info(sales)");
    $has_gst = false;
    $has_pst = false;
    
    while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'gst_rate') $has_gst = true;
        if ($column['name'] === 'pst_rate') $has_pst = true;
    }
    
    if ($has_gst && $has_pst) {
        echo "✓ GST and PST columns are ready for use.\n";
    } else {
        echo "✗ GST and PST columns not found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 