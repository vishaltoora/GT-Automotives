<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GT Automotives - Premium Tire Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for enhanced homepage */
        .hero-animation {
            animation: fadeInUp 1s ease-out;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin: 2rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .testimonials {
            background: #f8f9fa;
            padding: 4rem 0;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
        
        .testimonial-card::before {
            content: '"';
            font-size: 4rem;
            color: #007bff;
            position: absolute;
            top: -10px;
            left: 20px;
            font-family: serif;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .testimonial-author {
            font-weight: bold;
            color: #007bff;
        }
        
        .services-highlight {
            padding: 4rem 0;
            background: white;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .service-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .service-card:hover::before {
            left: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .service-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .news-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
        }
        
        .news-image {
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .news-content {
            padding: 1.5rem;
        }
        
        .news-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .news-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .news-excerpt {
            color: #666;
            line-height: 1.6;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: white;
            color: #667eea;
        }
        
        .brand-showcase {
            padding: 4rem 0;
            background: white;
        }
        
        .brand-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .brand-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .brand-item:hover {
            background: #f8f9fa;
            transform: scale(1.05);
        }
        
        .brand-emoji {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .brand-name {
            font-weight: bold;
            color: #333;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-count {
            animation: countUp 0.8s ease-out;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .news-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">GT Automotives</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0; margin-top: 60px;">
        <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
            <div class="hero-content hero-animation">
                <h1 style="font-size: 3rem; margin-bottom: 1.5rem; line-height: 1.2;">Professional Tire & Auto Services</h1>
                <p style="font-size: 1.2rem; margin-bottom: 2rem; line-height: 1.6; opacity: 0.9;">Expert tire installation, mobile services, and mechanical repairs by certified technicians at competitive prices</p>
                <div class="cta-buttons">
                    <a href="products.php" class="btn" style="background: white; color: #667eea; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; margin-right: 1rem; display: inline-block; transition: all 0.3s ease;">Browse Tires</a>
                    <a href="contact.php" class="btn-outline" style="background: transparent; border: 2px solid white; color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; transition: all 0.3s ease;">Get Quote</a>
                </div>
            </div>
            <div class="hero-image" style="text-align: center;">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Professional Tire Service" style="width: 100%; max-width: 500px; height: auto; border-radius: 15px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Why Customers Trust Us</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number" data-target="500">0</span>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number" data-target="7500">0</span>
                    <div class="stat-label">Tires Installed</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number" data-target="10">0</span>
                    <div class="stat-label">Years Experience</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number" data-target="100">0</span>
                    <div class="stat-label">% Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Choose Us</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-tools"></i>
                    <h3>Expert Installation</h3>
                    <p>Professional tire installation by certified technicians with years of experience</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <h3>Mobile Service</h3>
                    <p>Convenient mobile tire installation at your doorstep for emergency situations</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Quality Guarantee</h3>
                    <p>100% satisfaction guarantee on all our services and products</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Highlight Section -->
    <section class="services-highlight">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Our Premium Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-cog"></i>
                    <h3>Tire Installation</h3>
                    <p>Professional mounting and balancing with precision equipment</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Performance Tuning</h3>
                    <p>Custom tire solutions for performance vehicles</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-car"></i>
                    <h3>Emergency Service</h3>
                    <p>24/7 emergency tire replacement and repair</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile Services Section -->
    <section class="mobile-services" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Mobile Tire Services</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
                <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 15px; text-align: center; backdrop-filter: blur(10px);">
                    <i class="fas fa-home" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <h3>At Your Doorstep</h3>
                    <p>Professional tire installation and repair services at your home or office location</p>
                </div>
                <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 15px; text-align: center; backdrop-filter: blur(10px);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <h3>Emergency Response</h3>
                    <p>24/7 emergency mobile tire service for roadside assistance and breakdowns</p>
                </div>
                <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 15px; text-align: center; backdrop-filter: blur(10px);">
                    <i class="fas fa-clock" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <h3>Same Day Service</h3>
                    <p>Quick response times with same-day mobile tire installation and repair</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mechanical Services Section -->
    <section class="mechanical-services" style="background: #f8f9fa; padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Complete Automotive Services</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-oil-can" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>Oil Changes</h3>
                    <p>Professional oil change services with quality filters and lubricants</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-cogs" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>Engine Repair</h3>
                    <p>Complete engine diagnostics and repair services by certified mechanics</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-bolt" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>Electrical Systems</h3>
                    <p>Battery, alternator, and electrical system diagnostics and repair</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-tachometer-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>Brake Service</h3>
                    <p>Complete brake system inspection, repair, and replacement services</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-thermometer-half" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>AC & Heating</h3>
                    <p>Air conditioning and heating system maintenance and repair</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-car-battery" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #667eea;"></i>
                    <h3>Battery Service</h3>
                    <p>Battery testing, replacement, and jump-start services</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "GT Automotives provided exceptional service when I needed new tires for my SUV. The staff was professional and the work was completed quickly."
                    </div>
                    <div class="testimonial-author">- Sarah Johnson</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "Best tire shop in town! They have a great selection of brands and the installation was perfect. Highly recommend!"
                    </div>
                    <div class="testimonial-author">- Mike Chen</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "I've been coming here for years. The quality of service and attention to detail is unmatched. Great prices too!"
                    </div>
                    <div class="testimonial-author">- David Rodriguez</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "Their mobile service saved me when I had a flat tire on the highway. They came to my location and fixed everything quickly!"
                    </div>
                    <div class="testimonial-author">- Lisa Thompson</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "Not just tires - they fixed my engine problem when other shops couldn't figure it out. True professionals!"
                    </div>
                    <div class="testimonial-author">- Robert Wilson</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        "The mobile tire service is a game-changer. They came to my office and changed my tires during lunch break!"
                    </div>
                    <div class="testimonial-author">- Jennifer Davis</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Brands Section -->
    <section class="popular-brands">
        <div class="container">
            <h2>Premium Tire Brands</h2>
            <div class="brands-grid">
                <div class="brand-card">
                    <div class="emoji-brand">
                        <span class="big-tire-emoji">🛞</span>
                        <span class="brand-name">MICHELIN</span>
                    </div>
                </div>
                <div class="brand-card">
                    <div class="emoji-brand">
                        <span class="big-tire-emoji">🛞</span>
                        <span class="brand-name">BRIDGESTONE</span>
                    </div>
                </div>
                <div class="brand-card">
                    <div class="emoji-brand">
                        <span class="big-tire-emoji">🛞</span>
                        <span class="brand-name">GOODYEAR</span>
                    </div>
                </div>
                <div class="brand-card">
                    <div class="emoji-brand">
                        <span class="big-tire-emoji">🛞</span>
                        <span class="brand-name">CONTINENTAL</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News & Updates Section -->
    <section class="news-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Latest News & Updates</h2>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 12, 2024</div>
                        <div class="news-title">Mobile Service Expansion</div>
                        <div class="news-excerpt">We've expanded our mobile tire service to cover more areas. Call for availability!</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 10, 2024</div>
                        <div class="news-title">New Equipment Arrival</div>
                        <div class="news-excerpt">We've upgraded our alignment equipment for even more precise service.</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 1, 2024</div>
                        <div class="news-title">Mechanical Services</div>
                        <div class="news-excerpt">Now offering complete automotive mechanical services including engine repair and diagnostics.</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">November 28, 2024</div>
                        <div class="news-title">24/7 Emergency Service</div>
                        <div class="news-excerpt">Emergency mobile tire service now available 24/7 for roadside assistance.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready for Professional Auto Service?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">Get a free quote today for tires, mobile service, or mechanical repairs!</p>
            <div class="cta-buttons">
                <a href="products.php" class="btn">Shop Tires</a>
                <a href="contact.php" class="btn-outline">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> (250) 986-9191</li>
                    <li><i class="fas fa-envelope"></i> gt-automotives@outlook.com</li>
                    <li><i class="fas fa-user"></i> Contact: Johny</li>
                </ul>
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
        // Animated counter for statistics
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 20);
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const target = parseInt(stat.getAttribute('data-target'));
                        animateCounter(stat, target);
                        stat.classList.add('animate-count');
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe stats section
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        }

        // Add hover effects to service cards
        document.addEventListener('DOMContentLoaded', function() {
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html> 