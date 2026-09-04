@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-5">
    <div style="max-width:600px; margin:0 auto;">
        <h1 class="mb-4 text-center" style="font-size:1.6rem;">Track Your Order</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('track-order.search') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Order Number</label>
                        <input type="text" name="order_number" class="form-control" placeholder="e.g. SB260830ABCDE" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Track Order</button>
                </form>
            </div>
        </div>

        @if ($order)
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold">Order #{{ $order->order_number }}</h5>
                    @php
                        $colors = ['pending' => 'warning text-dark', 'confirmed' => 'info text-dark', 'preparing' => 'primary', 'out_for_delivery' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
                    @endphp
                    <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }} fs-6 mb-3">
                        {{ \App\Models\Order::STATUS_FLOW[$order->status] ?? $order->status }}
                    </span>

                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $item->product_name }} ({{ $item->weight_label }}) &times; {{ $item->quantity }}</span>
                            <span class="fw-bold">₹{{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between fw-bold fs-5 mt-3">
                        <span>Total</span>
                        <span>₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>

                    <hr>
                    <p class="small text-muted mb-0">
                        Delivery Date: {{ $order->delivery_date?->format('d M Y') }}
                        @if ($order->delivery_time_slot) ({{ $order->delivery_time_slot }}) @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

@include('userfooter')