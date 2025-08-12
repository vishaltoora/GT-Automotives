@extends('layouts.app')

@section('title', 'Contact Us - GT Automotives')

@push('styles')
<style>
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        margin-top: 2rem;
    }

    .contact-info {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .contact-info h2 {
        color: #333;
        margin-bottom: 2rem;
        font-size: 1.8rem;
        border-bottom: 3px solid #243c55;
        padding-bottom: 0.5rem;
    }

    .contact-person-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 8px;
        border-left: 4px solid #243c55;
    }

    .contact-person-section h3 {
        color: #243c55;
        margin-bottom: 1rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .contact-details {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .contact-details li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: #555;
    }

    .contact-details i {
        color: #243c55;
        width: 20px;
        text-align: center;
    }

    .business-hours {
        margin-top: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }

    .business-hours h3 {
        color: #28a745;
        margin-bottom: 1rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .business-hours ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .business-hours li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: #555;
    }

    .business-hours i {
        color: #28a745;
        width: 20px;
        text-align: center;
    }

    .contact-form {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .contact-form h2 {
        color: #333;
        margin-bottom: 2rem;
        font-size: 1.8rem;
        border-bottom: 3px solid #243c55;
        padding-bottom: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: #333;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #243c55;
        box-shadow: 0 0 0 3px rgba(36, 60, 85, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .btn {
        background: #243c55;
        color: white;
        padding: 1rem 2rem;
        border: none;
        border-radius: 6px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn:hover {
        background: #1a2d3f;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .form-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .map-section {
        margin-top: 3rem;
        text-align: center;
    }

    .map-section h3 {
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }

    .map-placeholder {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 3rem;
        color: #6c757d;
    }

    .map-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }

    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .contact-container {
            padding: 1rem;
        }

        .contact-info,
        .contact-form {
            padding: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="contact-container">
    <h1>Contact Us</h1>
    <p class="lead">Get in touch with us for all your tire and automotive needs.</p>

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
                <h3><i class="far fa-clock"></i> Business Hours</h3>
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
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                        <option value="Product Information" {{ old('subject') == 'Product Information' ? 'selected' : '' }}>Product Information</option>
                        <option value="Service Request" {{ old('subject') == 'Service Request' ? 'selected' : '' }}>Service Request</option>
                        <option value="Quote Request" {{ old('subject') == 'Quote Request' ? 'selected' : '' }}>Quote Request</option>
                        <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('subject')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Map Section -->
    <div class="map-section">
        <h3>Find Us</h3>
        <div class="map-placeholder">
            <i class="fas fa-map-marker-alt"></i>
            <p>Map integration coming soon...</p>
            <p>We're located in your local area. Call us for directions!</p>
        </div>
    </div>
</div>
@endsection 