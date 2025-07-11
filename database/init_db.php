<?php
// Initialize the SQLite database
$db_path = __DIR__ . '/gt_automotives.db';
$schema_path = __DIR__ . '/schema.sql';

// Create database connection
$conn = new SQLite3($db_path);
$conn->enableExceptions(true);

// Read and execute schema
$schema = file_get_contents($schema_path);
$conn->exec($schema);

echo "Database initialized successfully!\n";
echo "Sample tire data has been inserted.\n";
echo "Admin user created (username: admin, password: admin123)\n";
?> 