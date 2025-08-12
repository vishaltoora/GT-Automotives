<?php
// Script to fix all path issues in admin files
echo "<h1>Admin Path Fix Script</h1>";

// Set base path
$base_path = dirname(__DIR__);
echo "Base path: $base_path<br>";

// Files to fix with their current problematic paths and correct replacements
$files_to_fix = [
    'index.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'create_sale.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\'',
        'includes/header.php' => '$base_path . \'/admin/includes/header.php\'',
        'includes/footer.php' => '$base_path . \'/admin/includes/footer.php\''
    ],
    'sales.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'products.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'users.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'services.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'locations.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'brands.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'sizes.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ],
    'inventory.php' => [
        '../includes/db_connect.php' => '$base_path . \'/includes/db_connect.php\'',
        '../includes/auth.php' => '$base_path . \'/includes/auth.php\''
    ]
];

$total_files = 0;
$fixed_files = 0;
$errors = [];

echo "<h2>Fixing Files...</h2>";

foreach ($files_to_fix as $filename => $replacements) {
    $filepath = __DIR__ . '/' . $filename;
    
    if (!file_exists($filepath)) {
        echo "⚠️ File $filename not found, skipping...<br>";
        continue;
    }
    
    $total_files++;
    echo "Processing $filename...<br>";
    
    try {
        // Read file content
        $content = file_get_contents($filepath);
        $original_content = $content;
        
        // Add base_path declaration if not present
        if (strpos($content, '$base_path = dirname(__DIR__);') === false) {
            // Find the first PHP tag and add base_path after it
            $content = preg_replace(
                '/(<\?php\s*)/',
                '$1' . "\n" . '// Set base path for includes' . "\n" . '$base_path = dirname(__DIR__);' . "\n\n",
                $content,
                1
            );
        }
        
        // Apply replacements
        foreach ($replacements as $old_path => $new_path) {
            $content = str_replace($old_path, $new_path, $content);
        }
        
        // Write back to file if content changed
        if ($content !== $original_content) {
            if (file_put_contents($filepath, $content)) {
                echo "✅ $filename fixed successfully<br>";
                $fixed_files++;
            } else {
                echo "❌ $filename: Failed to write file<br>";
                $errors[] = "Failed to write $filename";
            }
        } else {
            echo "ℹ️ $filename: No changes needed<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ $filename: Error - " . $e->getMessage() . "<br>";
        $errors[] = "Error processing $filename: " . $e->getMessage();
    }
}

echo "<h2>Summary</h2>";
echo "Total files processed: $total_files<br>";
echo "Files fixed: $fixed_files<br>";
echo "Errors: " . count($errors) . "<br>";

if (!empty($errors)) {
    echo "<h3>Errors:</h3>";
    foreach ($errors as $error) {
        echo "• $error<br>";
    }
}

echo "<hr>";
echo "<p><strong>Path fix completed.</strong></p>";
echo "<p>Next steps:</p>";
echo "<ol>";
echo "<li><a href='test_paths.php'>Test the path resolution</a></li>";
echo "<li><a href='production_test.php'>Run production test again</a></li>";
echo "<li><a href='create_sale.php'>Test the create sale page</a></li>";
echo "</ol>";

echo "<p><strong>Note:</strong> All admin files now use the robust base_path approach that works in both development and production environments.</p>";
?> 