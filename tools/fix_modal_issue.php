<?php
// Fix Modal/Popover Issue
// This script provides fixes for modal not opening in production

echo "🔧 Fix Modal/Popover Issue\n";
echo "==========================\n\n";

echo "The issue is likely one of these:\n";
echo "1. JavaScript errors preventing execution\n";
echo "2. Modal elements not found in DOM\n";
echo "3. CSS conflicts hiding the modal\n";
echo "4. JavaScript not loading properly\n\n";

echo "Here are the fixes to apply to your products.php:\n";
echo "================================================\n\n";
?>

<!-- Add this debugging code to your products.php head section -->
<script>
// Debug logging
console.log('JavaScript loaded successfully');
console.log('Document ready state:', document.readyState);

// Check if modal elements exist
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
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

// Enhanced modal functions with error handling
function openAddProductDialog() {
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
}

function closeAddProductDialog() {
    console.log('closeAddProductDialog called');
    
    var modal = document.getElementById('addProductDialog');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Modal closed successfully');
    }
}

function openEditProductDialog(productId) {
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
}

function closeEditProductDialog() {
    console.log('closeEditProductDialog called');
    
    var modal = document.getElementById('editProductDialog');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('Edit modal closed successfully');
    }
}

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
}

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
function testModal() {
    console.log('Testing modal functionality...');
    openAddProductDialog();
}
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
echo "1. Add the JavaScript code above to your products.php\n";
echo "2. Add the CSS styles above to your products.php\n";
echo "3. Test the modal functionality\n";
echo "4. Check browser console for any errors\n";
echo "5. If still not working, check for JavaScript conflicts\n\n";

echo "🔍 Debug Commands:\n";
echo "=================\n";
echo "Add this button to test modal functionality:\n";
echo "<button onclick='testModal()' class='btn btn-info'>Test Modal</button>\n\n";

echo "📞 If Still Not Working:\n";
echo "=======================\n";
echo "1. Check browser console for errors\n";
echo "2. Verify all JavaScript files are loading\n";
echo "3. Check for jQuery conflicts\n";
echo "4. Test with a simple modal first\n";
echo "5. Check server error logs\n\n";

echo "🎉 Fix complete! Apply these changes to your products.php file.\n";
?> 