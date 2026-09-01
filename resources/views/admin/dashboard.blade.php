@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Welcome, {{ auth()->user()->name }} 👋</h4>
    <small class="text-muted">Here's what's happening in your store</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Products</div>
                        <h3 class="mb-0">{{ $stats['total_products'] }}</h3>
                    </div>
                    <i class="fa-solid fa-cake-candles fa-2x text-warning"></i>
                </div>
                <div class="small text-muted mt-2">
                    {{ $stats['active_products'] }} active · {{ $stats['draft_products'] }} draft
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Categories</div>
                        <h3 class="mb-0">{{ $stats['total_categories'] }}</h3>
                    </div>
                    <i class="fa-solid fa-layer-group fa-2x text-primary"></i>
                </div>
                <div class="small text-muted mt-2">
                    {{ $stats['total_subcategories'] }} sub/child categories
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Serviceable Pincodes</div>
                        <h3 class="mb-0">{{ $stats['total_pincodes'] }}</h3>
                    </div>
                    <i class="fa-solid fa-location-dot fa-2x text-success"></i>
                </div>
                <div class="small text-muted mt-2">
                    {{ $stats['active_pincodes'] }} active
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Coupons</div>
                        <h3 class="mb-0">{{ $stats['active_coupons'] }}</h3>
                    </div>
                    <i class="fa-solid fa-tag fa-2x text-danger"></i>
                </div>
                <div class="small text-muted mt-2">
                    {{ $stats['total_coupons'] }} total created
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['total_flavors'] }}</h5>
                <small class="text-muted">Flavors</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['total_weights'] }}</h5>
                <small class="text-muted">Weights</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['total_addons'] }}</h5>
                <small class="text-muted">Addons</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['total_occasions'] }}</h5>
                <small class="text-muted">Occasions</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['total_delivery_options'] }}</h5>
                <small class="text-muted">Delivery Options</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <h5 class="mb-0">{{ $stats['featured_products'] }} / {{ $stats['bestseller_products'] }}</h5>
                <small class="text-muted">Featured / Bestseller</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        Recently Added Products
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-dark">View All</a>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '—' }}</td>
                        <td>₹{{ number_format($product->base_price, 2) }}</td>
                        <td>
                            @if ($product->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif ($product->status === 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection