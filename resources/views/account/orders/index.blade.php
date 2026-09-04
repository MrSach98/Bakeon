@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Account</h1>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">My Orders</h5>

                    @if ($orders->count())
                        @foreach ($orders as $order)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <div class="fw-bold">Order #{{ $order->order_number }}</div>
                                        <small class="text-muted">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</small>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $colors = ['pending' => 'warning text-dark', 'confirmed' => 'info text-dark', 'preparing' => 'primary', 'out_for_delivery' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_FLOW[$order->status] ?? $order->status }}</span>
                                        <div class="fw-bold text-danger mt-1">₹{{ number_format($order->total_amount, 2) }}</div>
                                    </div>
                                </div>

                                <div class="mt-2 small text-muted">
                                    {{ $order->items->count() }} item(s) &middot;
                                    {{ $order->items->pluck('product_name')->take(2)->implode(', ') }}
                                    @if ($order->items->count() > 2) + {{ $order->items->count() - 2 }} more @endif
                                </div>

                                <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline-danger btn-sm mt-2">View Details</a>
                            </div>
                        @endforeach

                        <div class="mt-3">{{ $orders->links() }}</div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">You haven't placed any orders yet.</p>
                            <a href="{{ url('/') }}" class="btn btn-danger px-4">Start Shopping</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('userfooter')