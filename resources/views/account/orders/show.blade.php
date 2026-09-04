@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <nav class="mb-3">
        <small class="text-muted">
            <a href="{{ route('account.orders.index') }}" class="text-decoration-none text-muted">My Orders</a> /
            <span class="text-dark fw-semibold">{{ $order->order_number }}</span>
        </small>
    </nav>

    <div class="row">
        @include('account.partials.sidebar')

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Order #{{ $order->order_number }}</h5>
                            <small class="text-muted">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</small>
                        </div>
                        @php
                            $colors = ['pending' => 'warning text-dark', 'confirmed' => 'info text-dark', 'preparing' => 'primary', 'out_for_delivery' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
                        @endphp
                        <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }} fs-6">{{ \App\Models\Order::STATUS_FLOW[$order->status] ?? $order->status }}</span>
                    </div>

                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                <small class="text-muted">
                                    {{ $item->weight_label }}
                                    @if ($item->egg_type) &middot; {{ ucfirst($item->egg_type) }} @endif
                                    &middot; Qty: {{ $item->quantity }}
                                </small>
                            </div>
                            <div class="fw-bold">₹{{ number_format($item->total_price, 2) }}</div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Delivery Charge</span><span>₹{{ number_format($order->delivery_charge, 2) }}</span></div>
                        @if ($order->discount_amount > 0)
                            <div class="d-flex justify-content-between text-success"><span>Discount</span><span>-₹{{ number_format($order->discount_amount, 2) }}</span></div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span>₹{{ number_format($order->total_amount, 2) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Delivery Details</h6>
                    <p class="mb-1"><strong>{{ $order->receiver_name }}</strong> &middot; {{ $order->receiver_phone }}</p>
                    <p class="mb-1 text-muted">{{ $order->delivery_address }}</p>
                    <p class="mb-0 text-muted">
                        Delivery Date: {{ $order->delivery_date?->format('d M Y') }}
                        @if ($order->delivery_time_slot) ({{ $order->delivery_time_slot }}) @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('userfooter')