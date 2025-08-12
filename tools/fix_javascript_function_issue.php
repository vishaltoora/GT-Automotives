<?php
// Fix JavaScript Function Not Defined Error
// This script fixes the "openAddProductDialog is not defined" error

echo "🔧 Fix JavaScript Function Not Defined Error\n";
echo "==========================================\n\n";

echo "The error 'openAddProductDialog is not defined' means:\n";
echo "1. JavaScript functions are not loading properly\n";
echo "2. There might be JavaScript errors preventing execution\n";
echo "3. The script section might be missing or corrupted\n\n";

echo "Here's the complete fix for your products.php:\n";
echo "=============================================\n\n";
?>

<!-- Add this script section to your products.php BEFORE the closing </body> tag -->
<script>
// Debug logging to ensure JavaScript is loading
console.log('JavaScript loading...');
console.log('Document ready state:', document.readyState);

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Check if modal elements exist
    var addModal = document.getElementById('addProductDialog');
    var editModal = document.getElementById('editProductDialog');
    
    console.log('Add modal element:', addModal);
    console.log('Edit modal element:', editModal);
    
    if (!addModal) {
        console.error('Add Product Modal not found!');
    }
    
    if (!editModal) {
        console.error('Edit Product Modal not found!');
    }
});

// Define the modal functions globally to ensure they're available
window.openAddProductDialog = function() {
    console.log('openAddProductDialog called');
    
    var modal = document.getElementById('addProductDialog');
    if (!modal) {
        console.error('Add Product Modal not found!');
        alert('Error: Modal element not found. Please refresh the page.');
        return;
    }
    
    try {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        console.log('Modal opened successfully');
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening modal: ' + error.message);
    }
};

window.closeAddProductDialog = function() {
    console.log('closeAddProductDialog called');
    
    var modal = document.getElementById('addProductDialog');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Modal closed successfully');
    }
};

window.openEditProductDialog = function(productId) {
    console.log('openEditProductDialog called with ID:', productId);
    
    var modal = document.getElementById('editProductDialog');
    if (!modal) {
        console.error('Edit Product Modal not found!');
        alert('Error: Edit modal element not found. Please refresh the page.');
        return;
    }
    
    try {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        console.log('Edit modal opened successfully');
        
        // Fetch product data for editing
        fetch('get_product_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => {
            console.log('Fetch response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Product data received:', data);
            if (data.success) {
                document.getElementById('edit_product_id').value = productId;
                document.getElementById('edit_brand_id').value = data.brand_id;
                document.getElementById('edit_location_id').value = data.location_id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_size').value = data.size;
                document.getElementById('edit_price').value = data.price;
                document.getElementById('edit_stock_quantity').value = data.stock_quantity;
                document.getElementById('edit_condition').value = data.condition;
                document.getElementById('edit_description').value = data.description;
                console.log('Form fields populated successfully');
            } else {
                console.error('Error fetching product data:', data.message);
                alert('Error fetching product data for editing: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while fetching product data for editing');
        });
        
    } catch (error) {
        console.error('Error opening edit modal:', error);
        alert('Error opening edit modal: ' + error.message);
    }
};

window.closeEditProductDialog = function() {
    console.log('closeEditProductDialog called');
    
    var modal = document.getElementById('editProductDialog');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Edit modal closed successfully');
    }
};

// Enhanced click outside handler
window.onclick = function(event) {
    console.log('Window click event:', event.target);
    
    var addModal = document.getElementById('addProductDialog');
    if (event.target == addModal) {
        console.log('Clicking outside add modal');
        closeAddProductDialog();
    }
    
    var editModal = document.getElementById('editProductDialog');
    if (event.target == editModal) {
        console.log('Clicking outside edit modal');
        closeEditProductDialog();
    }
};

// Enhanced form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    var addForm = document.getElementById('addProductForm');
    var editForm = document.getElementById('editProductForm');
    
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            console.log('Add form submitted');
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add_product_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Add product response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Add product data:', data);
                if (data.success) {
                    alert('Product added successfully!');
                    closeAddProductDialog();
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to add product'));
                }
            })
            .catch(error => {
                console.error('Add product error:', error);
                alert('An error occurred while adding the product');
            });
        });
    } else {
        console.error('Add Product Form not found!');
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            console.log('Edit form submitted');
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('edit_product_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Edit product response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Edit product data:', data);
                if (data.success) {
                    alert('Product updated successfully!');
                    closeEditProductDialog();
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update product'));
                }
            })
            .catch(error => {
                console.error('Edit product error:', error);
                alert('An error occurred while updating the product');
            });
        });
    } else {
        console.error('Edit Product Form not found!');
    }
});

// Test modal functionality
window.testModal = function() {
    console.log('Testing modal functionality...');
    openAddProductDialog();
};

console.log('All modal functions defined successfully');
</script>

<?php
echo "\n🔧 CSS Fixes:\n";
echo "=============\n";
echo "Add these CSS rules to ensure modals display properly:\n";
echo "====================================================\n";
?>

<style>
/* Enhanced Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999; /* Higher z-index */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    overflow: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.modal-header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

.modal-body {
    padding: 20px;
}

/* Ensure buttons are clickable */
.btn {
    cursor: pointer;
    user-select: none;
}

/* Debug styles */
.modal-debug {
    border: 2px solid red;
    background-color: yellow;
    padding: 10px;
    margin: 10px 0;
}
</style>

<?php
echo "\n🎯 Implementation Steps:\n";
echo "=======================\n";
echo "1. Replace the entire <script> section in your products.php with the code above\n";
echo "2. Add the CSS styles above to your products.php\n";
echo "3. Make sure this script is placed BEFORE the closing </body> tag\n";
echo "4. Test the modal functionality\n";
echo "5. Check browser console for any remaining errors\n\n";

echo "🔍 Key Changes Made:\n";
echo "===================\n";
echo "1. Functions are now defined on window object (global scope)\n";
echo "2. Added extensive error handling and logging\n";
echo "3. Enhanced CSS with higher z-index\n";
echo "4. Added DOM ready event listeners\n";
echo "5. Improved error messages for debugging\n\n";

echo "📞 If Still Not Working:\n";
echo "=======================\n";
echo "1. Check if there are any JavaScript errors before this script\n";
echo "2. Verify the script is placed in the correct location\n";
echo "3. Check for any conflicting JavaScript libraries\n";
echo "4. Ensure the modal HTML elements exist in the page\n";
echo "5. Test with a simple alert to verify JavaScript is working\n\n";

echo "🎉 This should fix the 'function not defined' error!\n";
?> 