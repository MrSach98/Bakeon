@include('userheader')



<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="h4 fw-bold mb-4">Checkout</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('checkout.place') }}" id="checkoutForm">
                @csrf

                <!-- STEP 1: Login Details -->
                <div class="checkout-step-card">
                    <div class="checkout-step-header active" data-step="1">
                        <div class="checkout-step-icon"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Step 1</h6>
                            <small class="text-muted">Login Details</small>
                        </div>
                        <div class="checkout-step-summary d-none" id="step1Summary"></div>
                        <a href="javascript:void(0)" class="edit-step-link d-none ms-2" data-step="1">Edit</a>
                    </div>
                    <div class="checkout-step-body show" id="stepBody1">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Name</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $user->name ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Phone</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $user->phone ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Email</label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $user->email ?? '') }}">
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger mt-3 next-step-btn" data-current="1" data-next="2">Continue</button>
                    </div>
                </div>

                <!-- STEP 2: Delivery Address -->
                <div class="checkout-step-card">
                    <div class="checkout-step-header locked" data-step="2">
                        <div class="checkout-step-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Step 2</h6>
                            <small class="text-muted">Delivery Address</small>
                        </div>
                        <div class="checkout-step-summary d-none" id="step2Summary"></div>
                        <a href="javascript:void(0)" class="edit-step-link d-none ms-2" data-step="2">Edit</a>
                    </div>
                    <div class="checkout-step-body" id="stepBody2">
                        <h6 class="fw-bold mb-1">Let us know where to deliver</h6>
                        <p class="small text-muted mb-3">A detailed address will help us deliver the parcel smoothly</p>
                        <!-- Saved Addresses -->
                        @if ($savedAddresses->count())
                            <div class="mb-4">
                                <label class="form-label small fw-bold d-block">Choose a Saved Address</label>
                                <div class="row g-2">
                                    @foreach ($savedAddresses as $addr)
                                        <div class="col-md-6">
                                            <div class="saved-address-card border rounded-3 p-3 {{ $loop->first && $addr->is_default ? 'active' : '' }}"
                                                data-address='@json($addr)'>
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <span class="badge bg-light text-dark border">{{ ucfirst($addr->address_type) }}</span>
                                                    @if ($addr->is_default)
                                                        <span class="badge bg-danger">Default</span>
                                                    @endif
                                                </div>
                                                <div class="fw-semibold mt-2">{{ $addr->receiver_name }}</div>
                                                <div class="small text-muted">{{ $addr->receiver_phone }}</div>
                                                <div class="small text-muted mt-1">
                                                    {{ $addr->address_line }}, {{ $addr->area_locality }}, {{ $addr->city }} - {{ $addr->pincode }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="col-md-6">
                                        <div class="saved-address-card add-new-card border rounded-3 p-3 d-flex align-items-center justify-content-center text-center" id="addNewAddressCard">
                                            <div>
                                                <i class="fa-solid fa-plus fa-lg text-danger mb-2"></i>
                                                <div class="small fw-semibold">Use a New Address</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Receiver Name</label>
                                <input type="text" name="receiver_name" class="form-control" value="{{ old('receiver_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Receiver Number</label>
                                <input type="text" name="receiver_phone" class="form-control" value="{{ old('receiver_phone') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Apartment / House No. / Floor</label>
                                <input type="text" name="address_line" class="form-control" value="{{ old('address_line') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Area / Locality</label>
                                <input type="text" name="area_locality" class="form-control" value="{{ old('area_locality') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">PinCode</label>
                                <input type="text" name="pincode" id="checkoutPincode" class="form-control" maxlength="10" value="{{ old('pincode', session('selected_pincode')) }}" required>
                                <div id="pincodeCheckResult" class="small mt-1"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Delivery City</label>
                                <input type="text" name="city" id="checkoutCity" class="form-control" value="{{ old('city') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Alternate Phone Number</label>
                                <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small d-block">Address Type</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="addr-type-btn active" data-value="home">Home</button>
                                    <button type="button" class="addr-type-btn" data-value="office">Office</button>
                                    <button type="button" class="addr-type-btn" data-value="others">Others</button>
                                </div>
                                <input type="hidden" name="address_type" id="addressTypeInput" value="home">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="save_this_address" id="saveThisAddressCheck" class="form-check-input" value="1">
                                    <label class="form-check-label small" for="saveThisAddressCheck">Save this address for future orders</label>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger mt-3 next-step-btn" data-current="2" data-next="3">Continue</button>
                    </div>
                </div>

                <!-- STEP 3: Delivery Date & Time -->
                <div class="checkout-step-card">
                    <div class="checkout-step-header locked" data-step="3">
                        <div class="checkout-step-icon"><i class="fa-solid fa-clock"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Step 3</h6>
                            <small class="text-muted">Delivery Date &amp; Time</small>
                        </div>
                        <div class="checkout-step-summary d-none" id="step3Summary"></div>
                        <a href="javascript:void(0)" class="edit-step-link d-none ms-2" data-step="3">Edit</a>
                    </div>
                    <div class="checkout-step-body" id="stepBody3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Delivery Date</label>
                                <input type="date" name="delivery_date" id="deliveryDateInput" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('delivery_date', now()->addDay()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Delivery Option</label>
                                <select name="delivery_option_id" id="deliveryOptionSelect" class="form-select" required>
                                    @foreach ($deliveryOptions as $option)
                                        <option value="{{ $option->id }}" data-slug="{{ $option->slug }}" data-charge="{{ $option->extra_charge }}">
                                            {{ $option->name }} @if($option->extra_charge > 0) (+₹{{ number_format($option->extra_charge, 0) }}) @else (Free) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3" id="timeSlotWrap">
                            <label class="form-label small d-block">Delivery Time Slot</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach (['9 AM - 12 PM', '12 PM - 3 PM', '3 PM - 6 PM', '6 PM - 9 PM', '8 PM - 10 PM'] as $slot)
                                    <button type="button" class="slot-btn" data-slot="{{ $slot }}">{{ $slot }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="delivery_time_slot" id="deliveryTimeSlotInput">
                        </div>

                        <div class="mt-3">
                            <label class="form-label small">Cake Message (optional)</label>
                            <input type="text" name="cake_message" class="form-control" maxlength="250" value="{{ old('cake_message') }}">
                        </div>

                        <button type="button" class="btn btn-danger mt-3 next-step-btn" data-current="3" data-next="4">Continue</button>
                    </div>
                </div>

                <!-- STEP 4: Review Order -->
                <div class="checkout-step-card">
                    <div class="checkout-step-header locked" data-step="4">
                        <div class="checkout-step-icon"><i class="fa-solid fa-crown"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Step 4</h6>
                            <small class="text-muted">Review Order</small>
                        </div>
                    </div>
                    <div class="checkout-step-body" id="stepBody4">
                        @foreach ($lineItems as $item)
                            <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                                <img src="{{ $item['image'] ?? 'https://placehold.co/70x70/FFF8F0/D8232A?text=Item' }}" width="60" height="60" class="rounded" style="object-fit:cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    @if ($item['weight_label'])
                                        <small class="text-muted">Weight: {{ $item['weight_label'] }}</small>
                                    @endif
                                    <div class="small text-muted">Qty: {{ $item['quantity'] }}</div>
                                </div>
                                <div class="fw-bold text-danger">₹{{ number_format($item['line_total'], 0) }}</div>
                            </div>
                        @endforeach

                        <h6 class="fw-bold mt-3">Payment Method</h6>
                        <div class="form-check mb-2">
                            <input type="radio" name="payment_method" value="cod" class="form-check-input" id="pmCod" checked>
                            <label class="form-check-label" for="pmCod">Cash on Delivery</label>
                        </div>
                        @if ($paymentEnabled ?? false)
                        <div class="form-check">
                            <input type="radio" name="payment_method" value="online" class="form-check-input" id="pmOnline">
                            <label class="form-check-label" for="pmOnline">Pay Online</label>
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="order-summary-box">
                <h6 class="fw-bold mb-3">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span id="summarySubtotal">₹{{ number_format($subtotal, 0) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" id="summaryDeliveryLabel">Delivery Charge</span>
                    <span id="summaryDeliveryCharge">₹0</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success d-none" id="discountRow">
                    <span>Discount</span>
                    <span id="summaryDiscount">-₹0</span>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" id="couponInput" class="form-control form-control-sm" placeholder="Enter Coupon Code">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="applyCouponBtn">APPLY</button>
                    </div>
                    <div id="couponResult" class="small mt-1"></div>
                </div>

                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5 text-danger mb-3">
                    <span>Grand Total</span>
                    <span id="summaryGrandTotal">₹{{ number_format($subtotal, 0) }}</span>
                </div>

                <button type="submit" form="checkoutForm" class="btn btn-danger w-100 fw-bold" id="placeOrderBtn">
                    Proceed to Payment
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
$(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const subtotal = {{ $subtotal }};
    let currentDiscount = 0;

    // ---------- Step lock/summary helpers ----------
    function lockStepAndShowSummary(step) {
        const $header = $('.checkout-step-header[data-step="' + step + '"]');
        $header.removeClass('active').addClass('locked');
        $header.find('.checkout-step-summary').removeClass('d-none');
        $header.find('.edit-step-link').removeClass('d-none');
        $('#stepBody' + step).removeClass('show');
    }

    function buildStepSummary(step) {
        if (step === 1) {
            const name = $('input[name="customer_name"]').val();
            const phone = $('input[name="customer_phone"]').val();
            const email = $('input[name="customer_email"]').val();
            $('#step1Summary').html(`<div>${name}</div><div>${phone}</div><div class="text-muted">${email || ''}</div>`);
        }

        if (step === 2) {
            const name = $('input[name="receiver_name"]').val();
            const phone = $('input[name="receiver_phone"]').val();
            const addr = $('input[name="address_line"]').val();
            const area = $('input[name="area_locality"]').val();
            const pin = $('input[name="pincode"]').val();
            $('#step2Summary').html(`<div>${name} &middot; ${phone}</div><div class="text-muted">${addr}, ${area}, ${pin}</div>`);
        }

        if (step === 3) {
            const date = $('#deliveryDateInput').val();
            const optionLabel = $('#deliveryOptionSelect option:selected').text().split('(')[0].trim();
            const slot = $('#deliveryTimeSlotInput').val();
            $('#step3Summary').html(`<div>${date}</div><div class="text-muted">${optionLabel}${slot ? ' &middot; ' + slot : ''}</div>`);
        }
    }

    // ---------- Step navigation: Continue button ----------
    $('.next-step-btn').on('click', function () {
        const current = $(this).data('current');
        const next = $(this).data('next');

        const $currentBody = $('#stepBody' + current);
        let valid = true;
        $currentBody.find('[required]').each(function () {
            if (!$(this).val()) { valid = false; $(this).addClass('is-invalid'); }
            else { $(this).removeClass('is-invalid'); }
        });
        if (!valid) { alert('Please fill all required fields.'); return; }

        buildStepSummary(current);
        lockStepAndShowSummary(current);

        $('#stepBody' + next).addClass('show');
        $('.checkout-step-header[data-step="' + next + '"]').removeClass('locked').addClass('active');

        updateDeliveryCharge();
    });

    // ---------- Edit link: reopen a completed step ----------
    $('.edit-step-link').on('click', function (e) {
        e.stopPropagation();
        const step = $(this).data('step');

        $('.checkout-step-header.active').each(function () {
            const activeStep = $(this).data('step');
            $('#stepBody' + activeStep).removeClass('show');
            $(this).removeClass('active');
        });

        $('#stepBody' + step).addClass('show');
        const $header = $(this).closest('.checkout-step-header');
        $header.removeClass('locked').addClass('active');
        $header.find('.checkout-step-summary').addClass('d-none');
        $header.find('.edit-step-link').addClass('d-none');
    });

    // ---------- Address type buttons ----------
    $('.addr-type-btn').on('click', function () {
        $('.addr-type-btn').removeClass('active');
        $(this).addClass('active');
        $('#addressTypeInput').val($(this).data('value'));
    });

    // ---------- Time slot buttons ----------
    $('.slot-btn').on('click', function () {
        $('.slot-btn').removeClass('active');
        $(this).addClass('active');
        $('#deliveryTimeSlotInput').val($(this).data('slot'));
    });

    // ---------- Pincode check on Step 2 ----------
    $('#checkoutPincode').on('blur', function () {
        const pincode = $(this).val().trim();
        if (!pincode) return;

        fetch(`/check-pincode/${pincode}`)
            .then(res => res.json())
            .then(data => {
                if (data.serviceable) {
                    $('#pincodeCheckResult').html('<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Awesome, we deliver to this location</span>');
                    $('#checkoutCity').val(data.city);
                } else {
                    $('#pincodeCheckResult').html('<span class="text-danger">Sorry, we don\'t deliver to this pincode.</span>');
                }
            });
    });

    // ---------- Delivery option change -> display update only (real charge recalculated server-side) ----------
    function updateDeliveryCharge() {
        const selectedOption = $('#deliveryOptionSelect option:selected');
        const charge = parseFloat(selectedOption.data('charge')) || 0;
        const label = selectedOption.text().split('(')[0].trim();

        $('#summaryDeliveryLabel').text(label);
        $('#summaryDeliveryCharge').text(charge > 0 ? '₹' + charge.toLocaleString('en-IN') : 'FREE');

        updateGrandTotal(charge);
    }

    $('#deliveryOptionSelect').on('change', updateDeliveryCharge);

    function updateGrandTotal(deliveryCharge) {
        const total = subtotal + deliveryCharge - currentDiscount;
        $('#summaryGrandTotal').text('₹' + total.toLocaleString('en-IN'));
    }

    // ---------- Coupon apply ----------
    $('#applyCouponBtn').on('click', function () {
        const code = $('#couponInput').val().trim();
        if (!code) return;

        $.ajax({
            url: "{{ route('checkout.apply-coupon') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ coupon_code: code }),
            success: function (data) {
                if (data.success) {
                    currentDiscount = parseFloat(data.discount);
                    $('#couponResult').html('<span class="text-success">' + data.message + '</span>');
                    $('#discountRow').removeClass('d-none');
                    $('#summaryDiscount').text('-₹' + currentDiscount.toLocaleString('en-IN'));

                    const charge = parseFloat($('#deliveryOptionSelect option:selected').data('charge')) || 0;
                    updateGrandTotal(charge);
                } else {
                    $('#couponResult').html('<span class="text-danger">' + data.message + '</span>');
                }
            },
            error: function (xhr) {
                $('#couponResult').html('<span class="text-danger">' + (xhr.responseJSON?.message || 'Something went wrong.') + '</span>');
            }
        });
    });

    // ---------- Form submit: COD goes through normally, Online triggers Razorpay ----------
    $('#checkoutForm').on('submit', function (e) {
        const paymentMethod = $('input[name="payment_method"]:checked').val();

        if (paymentMethod === 'online') {
            e.preventDefault();
            startRazorpayPayment();
        } else {
            $('#placeOrderBtn').prop('disabled', true).text('Placing Order...');
        }
    });

    // ---------- Razorpay: Step 1 - create order server-side (amount never comes from client) ----------
    function startRazorpayPayment() {
        $('#placeOrderBtn').prop('disabled', true).text('Processing...');

        const pincode = $('input[name="pincode"]').val();
        const deliveryOptionId = $('#deliveryOptionSelect').val();

        $.ajax({
            url: "{{ route('razorpay.create-order') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ pincode: pincode, delivery_option_id: deliveryOptionId }),
            success: function (data) {
                if (!data.success) {
                    alert(data.message);
                    $('#placeOrderBtn').prop('disabled', false).text('Proceed to Payment');
                    return;
                }

                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: "{{ $siteSettings->store_name ?? 'Sweet Bakes' }}",
                    description: "Order Payment",
                    order_id: data.razorpay_order_id,
                    handler: function (response) {
                        submitOrderAfterPayment(response);
                    },
                    modal: {
                        ondismiss: function () {
                            $('#placeOrderBtn').prop('disabled', false).text('Proceed to Payment');
                        }
                    },
                    prefill: {
                        name: $('input[name="customer_name"]').val(),
                        contact: $('input[name="customer_phone"]').val(),
                        email: $('input[name="customer_email"]').val(),
                    },
                    theme: { color: "#d8232a" }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Something went wrong.');
                $('#placeOrderBtn').prop('disabled', false).text('Proceed to Payment');
            }
        });
    }

    // ---------- Razorpay: Step 2 - verify signature + place order ----------
    function submitOrderAfterPayment(razorpayResponse) {
        const formData = {
            razorpay_order_id: razorpayResponse.razorpay_order_id,
            razorpay_payment_id: razorpayResponse.razorpay_payment_id,
            razorpay_signature: razorpayResponse.razorpay_signature,

            customer_name: $('input[name="customer_name"]').val(),
            customer_phone: $('input[name="customer_phone"]').val(),
            customer_email: $('input[name="customer_email"]').val(),
            receiver_name: $('input[name="receiver_name"]').val(),
            receiver_phone: $('input[name="receiver_phone"]').val(),
            alternate_phone: $('input[name="alternate_phone"]').val(),
            address_line: $('input[name="address_line"]').val(),
            area_locality: $('input[name="area_locality"]').val(),
            pincode: $('input[name="pincode"]').val(),
            city: $('input[name="city"]').val(),
            address_type: $('#addressTypeInput').val(),
            delivery_date: $('#deliveryDateInput').val(),
            delivery_time_slot: $('#deliveryTimeSlotInput').val(),
            delivery_option_id: $('#deliveryOptionSelect').val(),
            cake_message: $('input[name="cake_message"]').val(),
        };

        $.ajax({
            url: "{{ route('razorpay.verify') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function (data) {
                if (data.success) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message);
                    $('#placeOrderBtn').prop('disabled', false).text('Proceed to Payment');
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Payment verification failed. Please contact support.');
                $('#placeOrderBtn').prop('disabled', false).text('Proceed to Payment');
            }
        });
    }
    // ---------- Saved Address selection ----------
$('.saved-address-card:not(.add-new-card)').on('click', function () {
    $('.saved-address-card').removeClass('active');
    $(this).addClass('active');

    const addr = $(this).data('address');

    $('input[name="receiver_name"]').val(addr.receiver_name);
    $('input[name="receiver_phone"]').val(addr.receiver_phone);
    $('input[name="alternate_phone"]').val(addr.alternate_phone || '');
    $('input[name="address_line"]').val(addr.address_line);
    $('input[name="area_locality"]').val(addr.area_locality);
    $('#checkoutPincode').val(addr.pincode);
    $('#checkoutCity').val(addr.city);

    $('.addr-type-btn').removeClass('active');
    $('.addr-type-btn[data-value="' + addr.address_type + '"]').addClass('active');
    $('#addressTypeInput').val(addr.address_type);

    // Ye ek saved address hai, dobara save karne ki zaroorat nahi
    $('#saveThisAddressCheck').prop('checked', false).closest('.col-12').hide();

    // Pincode ko auto-verify bhi kar do
    $('#checkoutPincode').trigger('blur');
});

$('#addNewAddressCard').on('click', function () {
    $('.saved-address-card').removeClass('active');
    $(this).addClass('active');

    $('#addressFormFields input[type="text"], #addressFormFields input[type="tel"]').val('');
    $('#checkoutPincode, #checkoutCity').val('');
    $('.addr-type-btn').removeClass('active');
    $('.addr-type-btn[data-value="home"]').addClass('active');
    $('#addressTypeInput').val('home');

    $('#saveThisAddressCheck').closest('.col-12').show();
});

// Agar sirf ek hi saved address hai aur wo default hai, to page load pe hi auto-select kar do
@if ($savedAddresses->count() && $savedAddresses->first()->is_default)
    $('.saved-address-card:not(.add-new-card)').first().trigger('click');
@endif

    updateDeliveryCharge();
});
</script>
@endpush

@include('userfooter')