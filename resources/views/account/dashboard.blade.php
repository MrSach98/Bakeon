@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Account</h1>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-1">Welcome back, {{ $accountUser->name }}! 👋</h5>
                    <p class="text-muted mb-0">Here's a quick overview of your account.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-box fa-lg text-danger mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['total_orders'] }}</h4>
                            <small class="text-muted">Total Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-truck-fast fa-lg text-warning mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['pending_orders'] }}</h4>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-circle-check fa-lg text-success mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['delivered_orders'] }}</h4>
                            <small class="text-muted">Delivered</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-regular fa-heart fa-lg text-danger mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['wishlist_count'] }}</h4>
                            <small class="text-muted">Wishlist Items</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-cart-shopping fa-lg text-primary mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['cart_count'] }}</h4>
                            <small class="text-muted">Items in Cart</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 text-center">
                        <div class="card-body">
                            <i class="fa-regular fa-star fa-lg text-warning mb-2"></i>
                            <h4 class="fw-bold mb-0">{{ $stats['reviews_count'] }}</h4>
                            <small class="text-muted">Reviews Written</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Recent Orders</h6>
                        <a href="{{ route('account.orders.index') }}" class="small fw-semibold text-decoration-none text-danger">View All</a>
                    </div>

                    @if ($recentOrders->count())
                        @foreach ($recentOrders as $order)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <a href="{{ route('account.orders.show', $order) }}" class="fw-semibold text-decoration-none text-dark">#{{ $order->order_number }}</a>
                                    <div class="small text-muted">{{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} item(s)</div>
                                </div>
                                <div class="text-end">
                                    @php
                                        $colors = ['pending' => 'warning text-dark', 'confirmed' => 'info text-dark', 'preparing' => 'primary', 'out_for_delivery' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_FLOW[$order->status] ?? $order->status }}</span>
                                    <div class="fw-bold text-danger small mt-1">₹{{ number_format($order->total_amount, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3 mb-0">No orders yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('userfooter')