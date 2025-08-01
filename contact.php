<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GT Automotives</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">GT Automotives</a>
            <button class="mobile-nav-toggle" id="mobile-nav-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-links" id="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="products.php"><i class="fas fa-tire"></i> Products</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="admin/login.php"><i class="fas fa-user-shield"></i> Admin</a>
            </div>
        </div>
    </nav>

    <div class="contact-container">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                
                <!-- First Contact Person -->
                <div class="contact-person-section">
                    <h3><i class="fas fa-user"></i> Johny</h3>
                    <ul class="contact-details">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>Email: gt-automotives@outlook.com</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>Phone: (250) 986-9191</span>
                        </li>
                    </ul>
                </div>

                <!-- Second Contact Person -->
                <div class="contact-person-section">
                    <h3><i class="fas fa-user"></i> Harjinder Gill</h3>
                    <ul class="contact-details">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>Email: gt-automotives@outlook.com</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>Phone: (250) 565-1571</span>
                        </li>
                    </ul>
                </div>

                <div class="business-hours">
                    <h3>Business Hours</h3>
                    <ul>
                        <li>
                            <i class="far fa-clock"></i>
                            <span>Monday - Friday: 8:00 AM - 6:00 PM</span>
                        </li>
                        <li>
                            <i class="far fa-clock"></i>
                            <span>Saturday - Sunday: 9:00 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h2>Send Us a Message</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                
                <!-- First Contact Person -->
                <div class="footer-contact-person">
                    <h4><i class="fas fa-user"></i> Johny</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> (250) 986-9191</li>
                        <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    </ul>
                </div>
                
                <!-- Second Contact Person -->
                <div class="footer-contact-person">
                    <h4><i class="fas fa-user"></i> Harjinder Gill</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> (250) 565-1571</li>
                        <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-section">
                <h3>Business Hours</h3>
                <ul>
                    <li>Monday - Friday: 8:00 AM - 6:00 PM</li>
                    <li>Saturday - Sunday: 9:00 AM - 5:00 PM</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Navigation Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNavToggle = document.getElementById('mobile-nav-toggle');
            const navLinks = document.getElementById('nav-links');
            
            if (mobileNavToggle && navLinks) {
                mobileNavToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    
                    // Change icon based on state
                    const icon = this.querySelector('i');
                    if (navLinks.classList.contains('active')) {
                        icon.className = 'fas fa-times';
                    } else {
                        icon.className = 'fas fa-bars';
                    }
                });
                
                // Close mobile menu when clicking on a link
                const navLinksItems = navLinks.querySelectorAll('a');
                navLinksItems.forEach(link => {
                    link.addEventListener('click', function() {
                        navLinks.classList.remove('active');
                        const icon = mobileNavToggle.querySelector('i');
                        icon.className = 'fas fa-bars';
                    });
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileNavToggle.contains(event.target) && !navLinks.contains(event.target)) {
                        navLinks.classList.remove('active');
                        const icon = mobileNavToggle.querySelector('i');
                        icon.className = 'fas fa-bars';
                    }
                });
            }
        });
    </script>
</body>
</html> 