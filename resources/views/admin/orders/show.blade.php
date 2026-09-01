@extends('admin.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
        <small class="text-muted">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</small>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back to Orders</a>
</div>

<div class="row">
    <div class="col-lg-8">

        <!-- Order Items -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Order Items ({{ $order->items->count() }})</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Weight</th>
                            <th>Egg Type</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    {{ $item->product_name }}
                                    @if ($item->cake_message)
                                        <br><small class="text-muted">Message: "{{ $item->cake_message }}"</small>
                                    @endif
                                    @if ($item->photo_path)
                                        <br><a href="{{ asset($item->photo_path) }}" target="_blank" class="small">View Uploaded Photo</a>
                                    @endif
                                </td>
                                <td>{{ $item->weight_label ?? '—' }}</td>
                                <td>{{ $item->egg_type ? ucfirst($item->egg_type) : '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="fw-bold">₹{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Delivery Charge ({{ $order->deliveryOption->name ?? '—' }})</span><span>₹{{ number_format($order->delivery_charge, 2) }}</span></div>
                @if ($order->discount_amount > 0)
                    <div class="d-flex justify-content-between text-success">
                        <span>Discount ({{ $order->coupon_code }})</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span>₹{{ number_format($order->total_amount, 2) }}</span></div>
            </div>
        </div>

        <!-- Delivery Details -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Delivery Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Receiver Name</span>
                        {{ $order->receiver_name ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Receiver Phone</span>
                        {{ $order->receiver_phone ?? '—' }}
                    </div>
                    @if ($order->alternate_phone)
                        <div class="col-md-6 mb-2">
                            <span class="text-muted d-block small">Alternate Phone</span>
                            {{ $order->alternate_phone }}
                        </div>
                    @endif
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Address Type</span>
                        <span class="badge bg-light text-dark border">{{ ucfirst($order->address_type ?? '—') }}</span>
                    </div>
                    <div class="col-12 mb-2">
                        <span class="text-muted d-block small">Full Address</span>
                        {{ $order->delivery_address }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Pincode</span>
                        {{ $order->pincode }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">City</span>
                        {{ $order->city ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Delivery Date</span>
                        {{ $order->delivery_date?->format('d M Y') ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Delivery Time Slot</span>
                        {{ $order->delivery_time_slot ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted d-block small">Delivery Option</span>
                        {{ $order->deliveryOption->name ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Admin Notes</div>
            <div class="card-body">
                <textarea id="adminNotesInput" class="form-control" rows="3" placeholder="Internal notes about this order (not visible to customer)">{{ $order->admin_notes }}</textarea>
                <button type="button" class="btn btn-outline-dark btn-sm mt-2" id="saveNotesBtn">Save Notes</button>
            </div>
        </div>

    </div>

    <div class="col-lg-4">

        <!-- Customer Info -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Customer</div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted d-block small">Name</span>
                    {{ $order->customer_name }}
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small">Phone</span>
                    {{ $order->customer_phone }}
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small">Email</span>
                    {{ $order->customer_email ?? '—' }}
                </div>
                <div>
                    <span class="text-muted d-block small">Account Type</span>
                    @if ($order->user)
                        <span class="badge bg-info text-dark">Registered User</span>
                        <div class="small text-muted mt-1">User ID: {{ $order->user->id }}</div>
                    @else
                        <span class="badge bg-secondary">Guest Checkout</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Payment</div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted d-block small">Method</span>
                    @if ($order->payment_method === 'online')
                        <span class="badge bg-primary">Online (Razorpay)</span>
                    @else
                        <span class="badge bg-secondary">Cash on Delivery</span>
                    @endif
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small">Payment Status</span>
                    <span class="badge bg-{{ ['pending' => 'warning text-dark', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary'][$order->payment_status] ?? 'secondary' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>

                @if ($order->payment)
                    <hr>
                    <div class="mb-2">
                        <span class="text-muted d-block small">Razorpay Order ID</span>
                        <code class="small">{{ $order->payment->razorpay_order_id }}</code>
                    </div>
                    @if ($order->payment->razorpay_payment_id)
                        <div class="mb-2">
                            <span class="text-muted d-block small">Razorpay Payment ID</span>
                            <code class="small">{{ $order->payment->razorpay_payment_id }}</code>
                        </div>
                    @endif
                    <div>
                        <span class="text-muted d-block small">Amount Paid</span>
                        ₹{{ number_format($order->payment->amount, 2) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Status -->
        <div class="card mb-3">
            <div class="card-header fw-bold">Order Status</div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted d-block small">Current Status</span>
                    @php
                        $colors = ['pending' => 'warning text-dark', 'confirmed' => 'info text-dark', 'preparing' => 'primary', 'out_for_delivery' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
                    @endphp
                    <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_FLOW[$order->status] ?? $order->status }}</span>
                </div>

                @if ($order->status === 'cancelled' && $order->cancellation_reason)
                    <div class="mb-2">
                        <span class="text-muted d-block small">Cancellation Reason</span>
                        {{ $order->cancellation_reason }}
                    </div>
                @endif

                <select id="statusSelect" class="form-select mb-2">
                    @foreach (\App\Models\Order::STATUS_FLOW as $value => $label)
                        <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <div id="cancellationReasonWrap" class="mb-2 d-none">
                    <label class="form-label small">Cancellation Reason</label>
                    <textarea id="cancellationReasonInput" class="form-control form-control-sm" rows="2"></textarea>
                </div>

                <button type="button" class="btn btn-dark w-100" id="updateStatusBtn">Update Status</button>
            </div>
        </div>

        <!-- Coupon Info -->
        @if ($order->coupon)
        <div class="card mb-3">
            <div class="card-header fw-bold">Coupon Applied</div>
            <div class="card-body">
                <div class="mb-1"><strong>{{ $order->coupon_code }}</strong></div>
                <div class="small text-muted">Discount: ₹{{ number_format($order->discount_amount, 2) }}</div>
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="card">
            <div class="card-header fw-bold">Timeline</div>
            <div class="card-body">
                <div class="small text-muted">Order Placed</div>
                <div class="mb-2">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                <div class="small text-muted">Last Updated</div>
                <div>{{ $order->updated_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#statusSelect').on('change', function () {
        $('#cancellationReasonWrap').toggleClass('d-none', $(this).val() !== 'cancelled');
    });

    $('#updateStatusBtn').on('click', function () {
        const status = $('#statusSelect').val();
        const reason = $('#cancellationReasonInput').val();

        $.ajax({
            url: "{{ route('admin.orders.update-status', $order) }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { status: status, cancellation_reason: reason },
            success: function (data) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function () {
                showToast('Something went wrong.', 'danger');
            }
        });
    });

    $('#saveNotesBtn').on('click', function () {
        const notes = $('#adminNotesInput').val();

        $.ajax({
            url: "{{ route('admin.orders.update-notes', $order) }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { admin_notes: notes },
            success: function (data) {
                showToast(data.message, 'success');
            },
            error: function () {
                showToast('Something went wrong.', 'danger');
            }
        });
    });
</script>
@endpush