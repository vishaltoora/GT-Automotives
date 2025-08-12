@extends('layouts.app')

@section('title', 'Products - GT Automotives')

@push('styles')
<style>
    /* Enhanced filter styles */
    .filters {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .filters h3 {
        margin-bottom: 1rem;
        color: #333;
        font-size: 1.2rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: bold;
        margin-bottom: 0.5rem;
        color: #555;
    }

    .filter-group select,
    .filter-group input {
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .filter-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: #243c55;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .btn-filter:hover {
        background: #1a2d3f;
    }

    .btn-clear {
        background: #6c757d;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .btn-clear:hover {
        background: #5a6268;
    }

    /* Products grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f8f9fa;
    }

    .product-content {
        padding: 1.5rem;
    }

    .product-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .brand-logo {
        width: 30px;
        height: 30px;
        object-fit: contain;
    }

    .brand-name {
        font-size: 0.9rem;
        color: #666;
        font-weight: bold;
    }

    .product-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        color: #333;
        line-height: 1.3;
    }

    .product-description {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .spec-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .spec-label {
        font-weight: bold;
        color: #555;
    }

    .spec-value {
        color: #333;
    }

    .product-price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #243c55;
        margin-bottom: 1rem;
    }

    .product-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-primary {
        background: #243c55;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .btn-primary:hover {
        background: #1a2d3f;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    /* No results */
    .no-results {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .no-results i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #ddd;
    }

    /* Loading state */
    .loading {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #243c55;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }

        .products-grid {
            grid-template-columns: 1fr;
        }

        .product-specs {
            grid-template-columns: 1fr;
        }

        .product-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <h1>Our Products</h1>
    <p class="lead">Discover our comprehensive selection of premium tires for all vehicle types.</p>

    <!-- Filters -->
    <div class="filters">
        <h3>Filter Products</h3>
        <form action="{{ route('products') }}" method="GET" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="brand">Brand</label>
                    <select name="brand" id="brand">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->name }}" {{ request('brand') == $brand->name ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="size">Size</label>
                    <select name="size" id="size">
                        <option value="">All Sizes</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->name }}" {{ request('size') == $size->name ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search products...">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('products') }}" class="btn-clear">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="products-grid">
            @foreach($products as $product)
                <div class="product-card">
                    <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x200/f8f9fa/666?text=Tire+Image' }}" 
                         alt="{{ $product->name }}" 
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/300x200/f8f9fa/666?text=Tire+Image'">
                    
                    <div class="product-content">
                        <div class="product-brand">
                            @if($product->brand && isset($brandsLookup[$product->brand]) && $brandsLookup[$product->brand]->logo_url)
                                <img src="{{ $brandsLookup[$product->brand]->logo_url }}" alt="{{ $brandsLookup[$product->brand]->name }}" class="brand-logo">
                            @endif
                            <span class="brand-name">{{ $product->brand ?? 'Unknown Brand' }}</span>
                        </div>
                        
                        <h3 class="product-title">{{ $product->name }}</h3>
                        <p class="product-description">{{ $product->description }}</p>
                        
                        <div class="product-specs">
                            <div class="spec-item">
                                <span class="spec-label">Size:</span>
                                <span class="spec-value">{{ $product->size ?? 'N/A' }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Type:</span>
                                <span class="spec-value">{{ $product->type ?? 'N/A' }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Season:</span>
                                <span class="spec-value">{{ $product->season ?? 'N/A' }}</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Rating:</span>
                                <span class="spec-value">{{ $product->rating ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            ${{ number_format($product->price, 2) }}
                        </div>
                        
                        <div class="product-actions">
                            <a href="{{ route('contact') }}" class="btn-primary">
                                <i class="fas fa-phone"></i> Inquire
                            </a>
                            <a href="{{ route('contact') }}" class="btn-secondary">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if($products->hasPages())
            <div class="pagination-wrapper">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>No products found</h3>
            <p>Try adjusting your filters or search terms.</p>
            <a href="{{ route('products') }}" class="btn-primary">Clear All Filters</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterForm = document.getElementById('filterForm');
    const filterInputs = filterForm.querySelectorAll('select, input[type="text"]');
    
    filterInputs.forEach(input => {
        if (input.type === 'select-one') {
            input.addEventListener('change', () => filterForm.submit());
        } else if (input.type === 'text') {
            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => filterForm.submit(), 500);
            });
        }
    });
});
</script>
@endpush 