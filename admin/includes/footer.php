        </main>
    </div>

    <script>
    // Custom confirmation for delete actions
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-confirm');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                showCustomConfirm('Are you sure you want to delete this item? This action cannot be undone.', function(confirmed) {
                    if (confirmed) {
                        // Get the href from the button and navigate to it
                        const href = button.getAttribute('href');
                        if (href) {
                            window.location.href = href;
                        }
                    }
                });
            });
        });
    });
    </script>
</body>
</html> 