@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-5">
    <div class="confirmation-box text-center">
        <div class="success-icon mb-3"><i class="fa-solid fa-circle-check"></i></div>
        <h1 class="h4 fw-bold mb-2">Order Placed Successfully!</h1>
        <p class="text-muted">Your order number is <strong>{{ $order->order_number }}</strong></p>

        @if ($order->payment_method === 'online')
            <span class="badge bg-success mb-3">Payment Received</span>
        @else
            <span class="badge bg-warning text-dark mb-3">Cash on Delivery</span>
        @endif
    </div>

    <div class="card confirmation-box mt-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Order Details</h6>

            @foreach ($order->items as $item)
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ $item->product_name }}</div>
                        @if ($item->weight_label)
                            <small class="text-muted">{{ $item->weight_label }} @if($item->egg_type) &middot; {{ ucfirst($item->egg_type) }} @endif</small>
                        @endif
                        <div class="small text-muted">Qty: {{ $item->quantity }}</div>
                    </div>
                    <div class="fw-bold text-danger">₹{{ number_format($item->total_price, 0) }}</div>
                </div>
            @endforeach

            <div class="d-flex justify-content-between mt-3">
                <span class="text-muted">Subtotal</span>
                <span>₹{{ number_format($order->subtotal, 0) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Delivery Charge</span>
                <span>₹{{ number_format($order->delivery_charge, 0) }}</span>
            </div>
            @if ($order->discount_amount > 0)
                <div class="d-flex justify-content-between text-success">
                    <span>Discount ({{ $order->coupon_code }})</span>
                    <span>-₹{{ number_format($order->discount_amount, 0) }}</span>
                </div>
            @endif
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total</span>
                <span>₹{{ number_format($order->total_amount, 0) }}</span>
            </div>
        </div>
    </div>

    <div class="card confirmation-box mt-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Delivery Details</h6>
            <div class="row small">
                <div class="col-6 mb-2">
                    <span class="text-muted d-block">Receiver</span>
                    {{ $order->receiver_name }} &middot; {{ $order->receiver_phone }}
                </div>
                <div class="col-6 mb-2">
                    <span class="text-muted d-block">Delivery Date</span>
                    {{ $order->delivery_date?->format('d M Y') }}
                    @if ($order->delivery_time_slot) &middot; {{ $order->delivery_time_slot }} @endif
                </div>
                <div class="col-12">
                    <span class="text-muted d-block">Address</span>
                    {{ $order->delivery_address }}
                </div>
            </div>
        </div>
    </div>

    <div class="confirmation-box text-center mt-4">
        <a href="{{ url('/') }}" class="btn btn-danger px-4 me-2">Continue Shopping</a>
        <a href="{{ url('/account/orders') }}" class="btn btn-outline-secondary px-4">View My Orders</a>
    </div>
</div>

@include('userfooter')