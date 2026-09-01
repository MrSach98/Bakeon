@include('userheader')


<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="h4 fw-bold mb-4">Your Cart</h1>

    <div id="cartAlertBox"></div>

    <div id="cartWrapper">
        @if (count($lineItems))
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="bg-white rounded-3 border" id="cartItemsContainer">
                        @foreach ($lineItems as $item)
                            <div class="cart-item-row" data-item-id="{{ $item['item_id'] }}">
                                <img src="{{ $item['image'] ?? 'https://placehold.co/90x90/FFF8F0/D8232A?text=Item' }}" width="80" height="80">
                                <div class="flex-grow-1">
                                    @if (!$item['is_addon'])
                                        <a href="{{ url('/' . $item['slug']) }}" class="text-decoration-none text-dark fw-semibold d-block">
                                            {{ $item['name'] }}
                                        </a>
                                        <small class="text-muted">
                                            {{ $item['weight_label'] }}
                                            @if ($item['egg_type']) &middot; {{ ucfirst($item['egg_type']) }} @endif
                                        </small>
                                    @else
                                        <span class="fw-semibold d-block">{{ $item['name'] }}</span>
                                        <small class="text-muted">Add-on</small>
                                    @endif
                                    <div class="fw-bold text-danger mt-1 line-total-display">₹{{ number_format($item['line_total'], 0) }}</div>
                                </div>
                                <div class="text-center">
                                    <small class="text-muted d-block mb-1">Qty:</small>
                                    <div class="qty-box">
                                        <button type="button" class="qty-minus">−</button>
                                        <input type="text" class="qty-input" value="{{ $item['quantity'] }}" data-max="{{ $item['max_qty'] }}" readonly>
                                        <button type="button" class="qty-plus">+</button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-item ms-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if ($crossSellAddons->count())
                        <div class="bg-white rounded-3 border p-3 mt-4">
                            <h6 class="fw-bold mb-3">Treat Yourself <span class="text-danger">More</span> With</h6>
                            <div class="addon-cross-scroll">
                                @foreach ($crossSellAddons as $addon)
                                    <div class="addon-cross-card">
                                        @if ($addon->image)
                                            <img src="{{ asset($addon->image) }}" width="100" height="100" class="rounded mb-2" style="object-fit:cover;">
                                        @else
                                            <div class="rounded mb-2 d-flex align-items-center justify-content-center bg-light" style="width:100px; height:100px; margin:0 auto;">
                                                <i class="fa-solid fa-gift text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="small fw-semibold text-truncate">{{ $addon->name }}</div>
                                        <div class="text-danger small mb-2">₹{{ number_format($addon->price, 0) }}</div>
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 add-addon-btn" data-addon-id="{{ $addon->id }}">
                                            Add to Cart
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="order-summary-box">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Sub Total</span>
                            <span class="fw-semibold" id="cartSubtotal">₹{{ number_format($subtotal, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Charges</span>
                            <span class="fw-semibold" id="deliveryChargeDisplay">
                                {{ $deliveryCharge > 0 ? '₹' . number_format($deliveryCharge, 0) : 'FREE' }}
                            </span>
                        </div>

                        @if ($amountToFreeDelivery > 0)
                            <div class="free-delivery-note mb-2" id="freeDeliveryNote">
                                Add products worth ₹{{ number_format($amountToFreeDelivery, 0) }} to get free delivery
                            </div>
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Grand Total</span>
                            <span class="fw-bold fs-5 text-danger" id="grandTotalDisplay">₹{{ number_format($grandTotal, 0) }}</span>
                        </div>

                        @auth
                            <a href="{{ url('/checkout') }}" class="btn btn-danger w-100 fw-bold mb-2">
                                <i class="fa-solid fa-cart-shopping me-1"></i> Place Order
                            </a>
                        @else
                            <button type="button" class="btn btn-danger w-100 fw-bold mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fa-solid fa-cart-shopping me-1"></i> Place Order
                            </button>
                        @endauth
                        <a href="{{ url('/') }}" class="btn btn-link w-100 text-decoration-none">Continue Shopping</a>

                        <p class="text-center small text-muted mt-3 mb-3">
                            Have a Coupon Code? You can apply the discount coupon in the Checkout process
                        </p>

                        <div class="d-flex justify-content-around text-center trust-badge">
                            <div>
                                <i class="fa-solid fa-truck d-block mb-1"></i>
                                <small class="d-block text-muted">On Time<br>Delivery</small>
                            </div>
                            <div>
                                <i class="fa-solid fa-leaf d-block mb-1"></i>
                                <small class="d-block text-muted">100% Fresh<br>&amp; Hygienic</small>
                            </div>
                            <div>
                                <i class="fa-solid fa-certificate d-block mb-1"></i>
                                <small class="d-block text-muted">FSSAI<br>Approved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5" id="emptyCartMessage">
                <i class="fa-solid fa-cart-shopping fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-3">Your cart is empty.</p>
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Start Shopping</a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(function () {

   @if (session('open_login_modal'))
        $('#loginModal').modal('show');
    @endif
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function showAlert(message, type = 'danger') {
        $('#cartAlertBox').html(
            `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`
        );
    }

    $(document).on('click', '.add-addon-btn', function () {
        const $btn = $(this);
        const addonId = $btn.data('addon-id');

        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: "{{ route('cart.add-addon') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ addon_id: addonId, quantity: 1 }),
            success: function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    $btn.prop('disabled', false).text('Add to Cart');
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message || 'Something went wrong.';
                alert(message);
                $btn.prop('disabled', false).text('Add to Cart');
            }
        });
    });

    function updateQuantity(itemId, newQty, $row) {
        const maxQty = parseInt($row.find('.qty-input').data('max'), 10);
        if (newQty < 1 || newQty > maxQty) return;

        $.ajax({
            url: "{{ route('cart.update-quantity') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ item_id: itemId, quantity: newQty }),
            success: function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    showAlert(data.message);
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message || 'Something went wrong.';
                showAlert(message);
            }
        });
    }

    $(document).on('click', '.qty-plus', function () {
        const $row = $(this).closest('.cart-item-row');
        const itemId = $row.data('item-id');
        const currentQty = parseInt($row.find('.qty-input').val(), 10);
        updateQuantity(itemId, currentQty + 1, $row);
    });

    $(document).on('click', '.qty-minus', function () {
        const $row = $(this).closest('.cart-item-row');
        const itemId = $row.data('item-id');
        const currentQty = parseInt($row.find('.qty-input').val(), 10);
        if (currentQty > 1) updateQuantity(itemId, currentQty - 1, $row);
    });

    $(document).on('click', '.remove-item', function () {
        if (!confirm('Remove this item from cart?')) return;

        const $row = $(this).closest('.cart-item-row');
        const itemId = $row.data('item-id');

        $.ajax({
            url: "{{ route('cart.remove') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ item_id: itemId }),
            success: function (data) {
                if (data.success) location.reload();
            },
            error: function () {
                showAlert('Something went wrong removing this item.');
            }
        });
    });
});
</script>
@endpush

@include('userfooter')