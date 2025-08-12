<?php
// Test script to verify path resolution
echo "<h1>Path Resolution Test</h1>";

// Test the base_path approach
$base_path = dirname(__DIR__);
echo "<h2>Base Path Resolution</h2>";
echo "Current file: " . __FILE__ . "<br>";
echo "Base path: " . $base_path . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test if the include files exist
echo "<h2>File Existence Test</h2>";

$files_to_test = [
    'db_connect.php' => $base_path . '/includes/db_connect.php',
    'auth.php' => $base_path . '/includes/auth.php',
    'header.php' => $base_path . '/admin/includes/header.php',
    'footer.php' => $base_path . '/admin/includes/footer.php'
];

foreach ($files_to_test as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name: EXISTS at $path<br>";
    } else {
        echo "❌ $name: MISSING at $path<br>";
    }
}

// Test if we can include the files
echo "<h2>Include Test</h2>";

try {
    require_once $base_path . '/includes/db_connect.php';
    echo "✅ db_connect.php included successfully<br>";
    
    if (isset($conn)) {
        echo "✅ Database connection object created<br>";
        echo "Database host: " . ($conn->host_info ?? 'Unknown') . "<br>";
    } else {
        echo "❌ Database connection object not created<br>";
    }
} catch (Exception $e) {
    echo "❌ Error including db_connect.php: " . $e->getMessage() . "<br>";
}

try {
    require_once $base_path . '/includes/auth.php';
    echo "✅ auth.php included successfully<br>";
    
    if (function_exists('isLoggedIn')) {
        echo "✅ Auth functions loaded<br>";
    } else {
        echo "❌ Auth functions not loaded<br>";
    }
} catch (Exception $e) {
    echo "❌ Error including auth.php: " . $e->getMessage() . "<br>";
}

// Test header and footer includes
echo "<h2>Header/Footer Test</h2>";

try {
    ob_start();
    include_once $base_path . '/admin/includes/header.php';
    $header_content = ob_get_clean();
    echo "✅ header.php included successfully<br>";
    echo "Header content length: " . strlen($header_content) . " characters<br>";
} catch (Exception $e) {
    echo "❌ Error including header.php: " . $e->getMessage() . "<br>";
}

try {
    ob_start();
    include_once $base_path . '/admin/includes/footer.php';
    $footer_content = ob_get_clean();
    echo "✅ footer.php included successfully<br>";
    echo "Footer content length: " . strlen($footer_content) . " characters<br>";
} catch (Exception $e) {
    echo "❌ Error including footer.php: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "If all tests pass, the create_sale.php page should work correctly.<br>";
echo "<a href='create_sale.php'>Test Create Sale Page</a>";
?> 