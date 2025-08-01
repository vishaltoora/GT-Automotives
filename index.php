<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GT Automotives - Premium Tire Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Enhanced Homepage Styles */
        .hero-section {
            background: white;
            color: #333;
            padding: 4rem 0;
            margin-top: 60px;
            min-height: 80vh;
            display: flex;
            align-items: center;
        }

        .hero-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero-content {
            animation: fadeInUp 1s ease-out;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: bold;
            color: #333;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
            color: #666;
        }

        .hero-image {
            text-align: center;
        }

        .hero-image img {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero {
            background: #243c55;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-hero:hover {
            background: #1a2d3f;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-hero {
            background: transparent;
            border: 2px solid #243c55;
            color: #243c55;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-outline-hero:hover {
            background: #243c55;
            color: white;
            transform: translateY(-2px);
        }

        .stats-section {
            background: #243c55;
            color: white;
            padding: 4rem 0;
            margin: 2rem 0;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .stats-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
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

        .features-section {
            padding: 4rem 0;
            background: #f8f9fa;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .features-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 3rem;
            color: #243c55;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .services-section {
            padding: 4rem 0;
            background: white;
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .services-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: #243c55;
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

        .service-card h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .service-card p {
            opacity: 0.9;
            line-height: 1.6;
        }

        .mobile-services-section {
            background: #243c55;
            color: white;
            padding: 4rem 0;
        }

        .mobile-services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .mobile-services-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
        }

        .mobile-services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .mobile-service-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .mobile-service-card:hover {
            transform: translateY(-5px);
        }

        .mobile-service-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .mobile-service-card h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .mobile-service-card p {
            opacity: 0.9;
            line-height: 1.6;
        }

        .mechanical-services-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }

        .mechanical-services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .mechanical-services-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .mechanical-services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .mechanical-service-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .mechanical-service-card:hover {
            transform: translateY(-5px);
        }

        .mechanical-service-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            color: #243c55;
        }

        .mechanical-service-card h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
        }

        .mechanical-service-card p {
            color: #666;
            line-height: 1.6;
        }

        .testimonials-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }

        .testimonials-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .testimonials-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
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
            color: #666;
        }

        .testimonial-author {
            font-weight: bold;
            color: #007bff;
        }

        .brands-section {
            padding: 4rem 0;
            background: white;
        }

        .brands-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .brands-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .brands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .brand-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .brand-card:hover {
            transform: translateY(-5px);
        }

        .emoji-brand {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .brand-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .news-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }

        .news-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .news-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            color: #333;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
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
            background: #243c55;
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

        .news-title-inner {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }

        .news-excerpt {
            color: #666;
            line-height: 1.6;
        }

        .cta-section {
            background: #243c55;
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .cta-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .cta-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-container,
            .stats-container,
            .features-container,
            .services-container,
            .mobile-services-container,
            .mechanical-services-container,
            .testimonials-container,
            .brands-container,
            .news-container,
            .cta-container {
                padding: 0 1rem;
            }
        }

        @media (max-width: 991px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .stats-title,
            .features-title,
            .services-title,
            .mobile-services-title,
            .mechanical-services-title,
            .testimonials-title,
            .brands-title,
            .news-title,
            .cta-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid,
            .services-grid,
            .mobile-services-grid,
            .mechanical-services-grid,
            .testimonials-grid,
            .brands-grid,
            .news-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 767px) {
            .hero-section {
                padding: 2rem 0;
                min-height: 60vh;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-hero,
            .btn-outline-hero {
                width: 100%;
                max-width: 250px;
                text-align: center;
            }

            .stats-grid,
            .features-grid,
            .services-grid,
            .mobile-services-grid,
            .mechanical-services-grid,
            .testimonials-grid,
            .brands-grid,
            .news-grid {
                grid-template-columns: 1fr;
            }

            .stats-title,
            .features-title,
            .services-title,
            .mobile-services-title,
            .mechanical-services-title,
            .testimonials-title,
            .brands-title,
            .news-title,
            .cta-title {
                font-size: 1.8rem;
            }

            .stat-card,
            .feature-card,
            .service-card,
            .mobile-service-card,
            .mechanical-service-card,
            .testimonial-card,
            .brand-card,
            .news-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 575px) {
            .hero-content h1 {
                font-size: 1.8rem;
            }

            .hero-content p {
                font-size: 0.9rem;
            }

            .stats-title,
            .features-title,
            .services-title,
            .mobile-services-title,
            .mechanical-services-title,
            .testimonials-title,
            .brands-title,
            .news-title,
            .cta-title {
                font-size: 1.5rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }

            .service-card i,
            .mobile-service-card i,
            .mechanical-service-card i,
            .feature-card i {
                font-size: 2.5rem;
            }
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
    </style>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Professional Tire & Auto Services</h1>
                <p>Expert tire installation, mobile services, and mechanical repairs by certified technicians at competitive prices</p>
                <div class="cta-buttons">
                    <a href="products.php" class="btn-hero">Browse Tires</a>
                    <a href="contact.php" class="btn-outline-hero">Get Quote</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Professional Tire Service">
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stats-container">
            <h2 class="stats-title">Why Customers Trust Us</h2>
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
    <section class="features-section">
        <div class="features-container">
            <h2 class="features-title">Why Choose Us</h2>
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
    <section class="services-section">
        <div class="services-container">
            <h2 class="services-title">Our Premium Services</h2>
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
    <section class="mobile-services-section">
        <div class="mobile-services-container">
            <h2 class="mobile-services-title">Mobile Tire Services</h2>
            <div class="mobile-services-grid">
                <div class="mobile-service-card">
                    <i class="fas fa-home"></i>
                    <h3>At Your Doorstep</h3>
                    <p>Professional tire installation and repair services at your home or office location</p>
                </div>
                <div class="mobile-service-card">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Emergency Response</h3>
                    <p>24/7 emergency mobile tire service for roadside assistance and breakdowns</p>
                </div>
                <div class="mobile-service-card">
                    <i class="fas fa-clock"></i>
                    <h3>Same Day Service</h3>
                    <p>Quick response times with same-day mobile tire installation and repair</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mechanical Services Section -->
    <section class="mechanical-services-section">
        <div class="mechanical-services-container">
            <h2 class="mechanical-services-title">Complete Automotive Services</h2>
            <div class="mechanical-services-grid">
                <div class="mechanical-service-card">
                    <i class="fas fa-oil-can"></i>
                    <h3>Oil Changes</h3>
                    <p>Professional oil change services with quality filters and lubricants</p>
                </div>
                <div class="mechanical-service-card">
                    <i class="fas fa-cogs"></i>
                    <h3>Engine Repair</h3>
                    <p>Complete engine diagnostics and repair services by certified mechanics</p>
                </div>
                <div class="mechanical-service-card">
                    <i class="fas fa-bolt"></i>
                    <h3>Electrical Systems</h3>
                    <p>Battery, alternator, and electrical system diagnostics and repair</p>
                </div>
                <div class="mechanical-service-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Brake Service</h3>
                    <p>Complete brake system inspection, repair, and replacement services</p>
                </div>
                <div class="mechanical-service-card">
                    <i class="fas fa-thermometer-half"></i>
                    <h3>AC & Heating</h3>
                    <p>Air conditioning and heating system maintenance and repair</p>
                </div>
                <div class="mechanical-service-card">
                    <i class="fas fa-car-battery"></i>
                    <h3>Battery Service</h3>
                    <p>Battery testing, replacement, and jump-start services</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="testimonials-container">
            <h2 class="testimonials-title">What Our Customers Say</h2>
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
    <section class="brands-section">
        <div class="brands-container">
            <h2 class="brands-title">Premium Tire Brands</h2>
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
        <div class="news-container">
            <h2 class="news-title">Latest News & Updates</h2>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 12, 2024</div>
                        <div class="news-title-inner">Mobile Service Expansion</div>
                        <div class="news-excerpt">We've expanded our mobile tire service to cover more areas. Call for availability!</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 10, 2024</div>
                        <div class="news-title-inner">New Equipment Arrival</div>
                        <div class="news-excerpt">We've upgraded our alignment equipment for even more precise service.</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">December 1, 2024</div>
                        <div class="news-title-inner">Mechanical Services</div>
                        <div class="news-excerpt">Now offering complete automotive mechanical services including engine repair and diagnostics.</div>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-image">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="news-content">
                        <div class="news-date">November 28, 2024</div>
                        <div class="news-title-inner">24/7 Emergency Service</div>
                        <div class="news-excerpt">Emergency mobile tire service now available 24/7 for roadside assistance.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2 class="cta-title">Ready for Professional Auto Service?</h2>
            <p class="cta-description">Get a free quote today for tires, mobile service, or mechanical repairs!</p>
            <div class="cta-buttons">
                <a href="products.php" class="btn-hero">Shop Tires</a>
                <a href="contact.php" class="btn-outline-hero">Contact Us</a>
            </div>
        </div>
    </section>

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