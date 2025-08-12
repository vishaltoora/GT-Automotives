@extends('layouts.app')

@section('title', 'GT Automotives - Premium Tire Shop')

@push('styles')
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

    .features-section {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 3rem;
        color: #243c55;
        margin-bottom: 1rem;
    }

    .feature-card h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
    }

    .brands-section {
        padding: 4rem 0;
        background: white;
    }

    .brands-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        text-align: center;
    }

    .brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .brand-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .brand-logo {
        width: 80px;
        height: 80px;
        object-fit: contain;
        filter: grayscale(100%);
        transition: filter 0.3s ease;
    }

    .brand-item:hover .brand-logo {
        filter: grayscale(0%);
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

    @media (max-width: 768px) {
        .hero-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 2.5rem;
        }

        .cta-buttons {
            justify-content: center;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1>Premium Tires for Your Vehicle</h1>
            <p>Experience the difference with our selection of high-quality tires from world-renowned brands. We offer competitive prices and expert installation services.</p>
            <div class="cta-buttons">
                <a href="{{ route('products') }}" class="btn-hero">View Products</a>
                <a href="{{ route('contact') }}" class="btn-outline-hero">Contact Us</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/hero-tires.jpg') }}" alt="Premium Tires" onerror="this.src='https://via.placeholder.com/500x400/243c55/ffffff?text=Premium+Tires'">
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-tire"></i>
            </div>
            <h3>Premium Quality</h3>
            <p>We carry only the finest tires from trusted manufacturers like Michelin, Bridgestone, and Goodyear.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h3>Expert Installation</h3>
            <p>Our certified technicians ensure proper installation and balancing for optimal performance and safety.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3>Competitive Pricing</h3>
            <p>Get the best value for your money with our competitive prices and special offers.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3>Quick Service</h3>
            <p>Fast turnaround times so you can get back on the road with confidence.</p>
        </div>
    </div>
</section>

<!-- Brands Section -->
<section class="brands-section">
    <div class="brands-container">
        <h2>Trusted Brands We Carry</h2>
        <div class="brands-grid">
            @forelse($brands as $brand)
                <div class="brand-item">
                    @if($brand->logo_url)
                        <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="brand-logo">
                    @else
                        <div class="brand-logo-placeholder">
                            <i class="fas fa-tire"></i>
                        </div>
                    @endif
                    <span class="brand-name">{{ $brand->name }}</span>
                </div>
            @empty
                <div class="brand-item">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Michelin.svg/200px-Michelin.svg.png" alt="Michelin" class="brand-logo">
                    <span class="brand-name">Michelin</span>
                </div>
                <div class="brand-item">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bridgestone_logo.svg/200px-Bridgestone_logo.svg.png" alt="Bridgestone" class="brand-logo">
                    <span class="brand-name">Bridgestone</span>
                </div>
                <div class="brand-item">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Goodyear_logo.svg/200px-Goodyear_logo.svg.png" alt="Goodyear" class="brand-logo">
                    <span class="brand-name">Goodyear</span>
                </div>
                <div class="brand-item">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/200px-Continental_AG_logo.svg.png" alt="Continental" class="brand-logo">
                    <span class="brand-name">Continental</span>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection 