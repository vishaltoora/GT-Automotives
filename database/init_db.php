<?php
// Initialize the MySQL database
require_once __DIR__ . '/../includes/db_connect.php';

// Read and execute schema
$schema_path = __DIR__ . '/schema.sql';
$schema = file_get_contents($schema_path);

// Split the schema into individual statements
$statements = array_filter(array_map('trim', explode(';', $schema)));

// Execute each statement
foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $result = $conn->query($statement);
            if (!$result) {
                echo "Error executing statement: " . $conn->error . "\n";
                echo "Statement: " . $statement . "\n";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "MySQL Database initialized successfully!\n";
echo "Sample tire data has been inserted.\n";
echo "Admin user created (username: admin, password: admin123)\n";

// Close the connection
$conn->close();
?> 
