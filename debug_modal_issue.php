<?php
// Debug Modal/Popover Issue
// This script will help identify why modals are not opening in production

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Debug Modal/Popover Issue\n";
echo "============================\n\n";

// Test 1: Check if we can access the products page
echo "1. Testing Products Page Access:\n";
try {
    $products_url = "https://www.gt-automotives.com/admin/products.php";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Modal-Debug/1.0'
        ]
    ]);
    
    $response = @file_get_contents($products_url, false, $context);
    if ($response !== false) {
        echo "✅ Products page is accessible\n";
        
        // Check for modal elements
        if (strpos($response, 'addProductDialog') !== false) {
            echo "✅ Modal elements found in HTML\n";
        } else {
            echo "❌ Modal elements missing from HTML\n";
        }
        
        // Check for JavaScript functions
        if (strpos($response, 'openAddProductDialog') !== false) {
            echo "✅ JavaScript functions found\n";
        } else {
            echo "❌ JavaScript functions missing\n";
        }
        
        // Check for CSS styles
        if (strpos($response, '.modal') !== false) {
            echo "✅ Modal CSS styles found\n";
        } else {
            echo "❌ Modal CSS styles missing\n";
        }
        
    } else {
        echo "❌ Products page is not accessible\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing products page: " . $e->getMessage() . "\n";
}

// Test 2: Check for JavaScript errors
echo "\n2. JavaScript Error Check:\n";
echo "To check for JavaScript errors:\n";
echo "1. Open browser developer tools (F12)\n";
echo "2. Go to Console tab\n";
echo "3. Visit: https://www.gt-automotives.com/admin/products.php\n";
echo "4. Click 'Add Product' button\n";
echo "5. Check for any red error messages\n\n";

// Test 3: Check for missing dependencies
echo "3. Dependencies Check:\n";
echo "Common issues that prevent modals from working:\n";
echo "- Missing jQuery library\n";
echo "- JavaScript errors in console\n";
echo "- CSS conflicts\n";
echo "- Modal elements not in DOM\n";
echo "- JavaScript not loading properly\n\n";

// Test 4: Create a simple test modal
echo "4. Simple Modal Test:\n";
echo "Add this test code to your products.php page to test:\n";
echo "==================================================\n";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modal Test</title>
    <style>
        .test-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .test-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }
        .test-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .test-close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <button onclick="openTestModal()">Test Modal</button>
    
    <div id="testModal" class="test-modal">
        <div class="test-modal-content">
            <span class="test-close" onclick="closeTestModal()">&times;</span>
            <h2>Test Modal</h2>
            <p>If you can see this, modals are working!</p>
        </div>
    </div>
    
    <script>
        function openTestModal() {
            document.getElementById('testModal').style.display = 'block';
        }
        
        function closeTestModal() {
            document.getElementById('testModal').style.display = 'none';
        }
        
        // Close when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('testModal');
            if (event.target == modal) {
                closeTestModal();
            }
        }
    </script>
</body>
</html>

<?php
echo "\n5. Troubleshooting Steps:\n";
echo "=========================\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Verify jQuery is loaded (if required)\n";
echo "3. Check if modal elements exist in DOM\n";
echo "4. Test with the simple modal above\n";
echo "5. Check for CSS conflicts\n";
echo "6. Verify JavaScript functions are being called\n\n";

echo "6. Quick Fixes to Try:\n";
echo "=====================\n";
echo "1. Add this to your products.php head section:\n";
echo "   <script>console.log('JavaScript loaded');</script>\n\n";
echo "2. Add this to test modal opening:\n";
echo "   <script>console.log('openAddProductDialog called');</script>\n\n";
echo "3. Check if modal element exists:\n";
echo "   <script>console.log('Modal element:', document.getElementById('addProductDialog'));</script>\n\n";

echo "🎯 Next Steps:\n";
echo "==============\n";
echo "1. Visit: https://www.gt-automotives.com/admin/products.php\n";
echo "2. Open browser developer tools (F12)\n";
echo "3. Go to Console tab\n";
echo "4. Click 'Add Product' button\n";
echo "5. Check for any error messages\n";
echo "6. Share any error messages you see\n\n";

echo "🎉 Debug complete!\n";
?> 