@include('userheader')



<div class="container-fluid px-4 px-lg-5 py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ url('/category/' . $product->category->slug) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Gallery with Zoom -->
        <div class="col-lg-6">
            <div class="row g-0 position-relative">
                <div class="col-12 col-xl-7">
                    <div class="product-main-img-box mb-3 text-center" id="zoomImageBox">
                        <img id="mainProductImage"
                             src="{{ $product->images->first() ? asset($product->images->first()->image_path) : 'https://placehold.co/600x600/FFF8F0/D8232A?text=Bakingo+Cake' }}"
                             alt="{{ $product->name }}" class="img-fluid" style="max-height: 500px; object-fit: cover; width: 100%; cursor: crosshair;">

                        @if ($product->egg_type === 'eggless')
                            <span class="badge position-absolute top-0 start-0 m-3" style="background: #008a00; font-size: 0.85rem;">
                                <i class="fa-solid fa-leaf me-1"></i> 100% Eggless
                            </span>
                        @endif

                        <button type="button" class="btn btn-light rounded-circle position-absolute top-0 end-0 m-3 shadow-sm btn-wishlist-toggle"
                                style="width:40px; height:40px;" data-product-id="{{ $product->id }}">
                            <i class="fa-regular fa-heart"></i>
                        </button>

                        <div id="zoomLens" class="zoom-lens"></div>
                    </div>

                    @if ($product->images->count() > 1)
                        <div class="d-flex gap-2 overflow-auto pb-2">
                            @foreach ($product->images as $key => $img)
                                <img src="{{ asset($img->image_path) }}"
                                     class="thumb-img {{ $key === 0 ? 'active-thumb' : '' }}"
                                     width="70" height="70" style="object-fit: cover;"
                                     onclick="changeMainImage(this, '{{ asset($img->image_path) }}')">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-xl-5 d-none d-xl-block">
                    <div id="zoomResultPane" class="zoom-result-pane"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Specs & Ordering -->
        <div class="col-lg-6">
            <h1 class="h3 fw-bold text-dark mb-1">{{ $product->name }}</h1>

            <!-- Description right under the name -->
            @if ($product->short_description || $product->description)
                <div class="mb-2">
                    <p class="text-muted small mb-0 desc-clamp" id="descText">
                        {{ $product->short_description ?: $product->description }}
                    </p>
                    <a class="read-more-link small" id="readMoreBtn">Read more</a>
                    <a class="read-less-link small d-none" id="readLessBtn">Read less</a>
                </div>
            @endif

            <!-- Rating summary (static placeholder until Reviews module exists) -->
            <div class="mb-2">
                <span class="fw-semibold">4.8</span>
                <span style="color:#ffb800;">★★★★★</span>
                <span class="text-muted small">(Be the first to review)</span>
            </div>

            <!-- Pricing Box -->
            <div class="d-flex align-items-baseline gap-2 mb-3">
                <span class="fs-2 fw-extrabold text-danger" id="displayPrice">
                    ₹{{ number_format($defaultVariant ? ($defaultVariant->discount_price ?? $defaultVariant->price) : $product->base_price, 0) }}
                </span>
                @if ($defaultVariant && $defaultVariant->discount_price)
                    <span class="text-muted text-decoration-line-through fs-6" id="strikePrice">
                        ₹{{ number_format($defaultVariant->price, 0) }}
                    </span>
                    <span class="badge bg-success small">OFF</span>
                @endif
                <span class="small text-muted ms-1">(Inclusive of all taxes)</span>
            </div>

            <hr class="my-3">

            <!-- Weight Selection -->
            @if ($variantsByWeight->count())
            <div class="mb-4 position-relative">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="fw-bold small text-uppercase text-secondary mb-0">1. Select Weight</label>
                    <span class="small fw-semibold serving-info-link" id="servingInfoToggle" style="color:var(--brand-red);">
                        Serving Information
                    </span>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($variantsByWeight as $weightId => $variants)
                        @php
                            $firstVar = $variants->first();
                            $weight = $firstVar->weight;
                            $hasBothEggTypes = $variants->pluck('egg_type')->unique()->count() > 1;
                        @endphp
                        <div class="variant-card weight-option {{ $loop->first ? 'active' : '' }}"
                             data-weight-id="{{ $weightId }}"
                             data-variant-id="{{ $firstVar->id }}"
                             data-price="{{ $firstVar->discount_price ?? $firstVar->price }}"
                             data-original-price="{{ $firstVar->price }}"
                             data-has-discount="{{ $firstVar->discount_price ? 'true' : 'false' }}"
                             data-has-both-egg-types="{{ $hasBothEggTypes ? 'true' : 'false' }}"
                             data-eggless-variant-id="{{ $variants->firstWhere('egg_type', 'eggless')->id ?? '' }}"
                             data-eggless-price="{{ $variants->firstWhere('egg_type', 'eggless')->discount_price ?? $variants->firstWhere('egg_type', 'eggless')->price ?? '' }}"
                             data-egg-variant-id="{{ $variants->firstWhere('egg_type', 'egg')->id ?? '' }}"
                             data-egg-price="{{ $variants->firstWhere('egg_type', 'egg')->discount_price ?? $variants->firstWhere('egg_type', 'egg')->price ?? '' }}">
                            <div class="fw-bold fs-6">{{ $weight->label }}</div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Serves {{ $weight->serves_range ?: '—' }}</small>
                        </div>
                    @endforeach
                </div>

                <div class="serving-popover" id="servingPopover">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Serving Information</strong>
                        <button type="button" class="btn-close btn-close-sm" id="closeServingPopover"></button>
                    </div>
                    @foreach ($servingInfo as $w)
                        <div class="serving-row">
                            <span>{{ $w->label }}</span>
                            <span class="text-muted">{{ $w->serves_range ?: '—' }} People</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Egg Preference -->
            <div class="mb-4" id="eggPreferenceWrap" style="display:none;">
                <label class="fw-bold small text-uppercase text-secondary d-block mb-2">Egg Preference</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm egg-toggle-btn active" data-egg-type="eggless">
                        <i class="fa-solid fa-leaf me-1"></i> Eggless
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm egg-toggle-btn" data-egg-type="egg">
                        With Egg
                    </button>
                </div>
            </div>

            <!-- Flavor Selection -->
            @if ($product->flavors->count())
            <div class="mb-4">
                <label class="fw-bold small text-uppercase text-secondary d-block mb-2">2. Choose Flavour</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($product->flavors as $flavor)
                        <button type="button" class="btn btn-outline-dark btn-sm flavor-option {{ $flavor->pivot->is_default ? 'active' : '' }}"
                                data-flavor-id="{{ $flavor->id }}">
                            {{ $flavor->name }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Custom Messages & Photo -->
            @if ($product->is_message_enabled || $product->is_photo_cake)
            <div class="p-3 bg-light rounded-3 mb-4 border">
                @if ($product->is_message_enabled)
                <div class="mb-2">
                    <label for="cakeMessage" class="fw-bold small text-secondary mb-1">Message on Cake (Optional)</label>
                    <input type="text" id="cakeMessage" class="form-control form-control-sm"
                           maxlength="{{ $product->message_char_limit ?? 30 }}"
                           placeholder="e.g. Happy Birthday Rahul!">
                    <div class="text-end small text-muted" style="font-size:0.75rem;">
                        <span id="charCount">0</span>/{{ $product->message_char_limit ?? 30 }}
                    </div>
                </div>
                @endif

                @if ($product->is_photo_cake)
                <div>
                    <label for="cakePhoto" class="fw-bold small text-secondary mb-1">Upload Photo for Cake</label>
                    <input type="file" id="cakePhoto" class="form-control form-control-sm" accept="image/*">
                </div>
                @endif
            </div>
            @endif

            <!-- Delivery Availability Box -->
            <div class="delivery-box p-3 mb-4">
                <div class="fw-bold text-dark mb-2"><i class="fa-solid fa-truck-fast me-2 text-danger"></i>Check Delivery Availability</div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" id="deliveryPincode" class="form-control form-control-sm"
                               placeholder="Enter Pincode" maxlength="6" value="{{ session('selected_pincode') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="date" id="deliveryDate" class="form-control form-control-sm"
                               min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" id="checkDeliveryBtn">Check</button>
                    </div>
                </div>
                <div id="deliveryResult" class="mt-2"></div>
                @if ($product->deliveryOptions->count())
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @foreach ($product->deliveryOptions as $option)
                        <span class="badge bg-white text-danger border border-danger fw-normal">{{ $option->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            @if ($product->sku)
            <div class="mb-3">
                <small class="text-muted d-block">SKU Number</small>
                <small class="text-dark">{{ $product->sku }}</small>
            </div>
            @endif

            <div class="mb-3">
                <i class="fa-regular fa-clock me-1 text-danger"></i>
                <span class="small">Earliest Delivery: <strong>Tomorrow</strong></span>
            </div>

            <!-- Desktop Add to Cart Buttons -->
            <div class="d-none d-md-flex gap-2 mt-4">
                <div class="input-group" style="width: 120px;">
                    <button class="btn btn-outline-secondary" type="button" id="qtyMinus">-</button>
                    <input type="text" class="form-control text-center" id="qtyInput" value="1" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="qtyPlus">+</button>
                </div>
                <button type="button" class="btn btn-danger btn-lg flex-grow-1 fw-bold text-uppercase" id="addToCartBtn">
                    <i class="fa-solid fa-cart-shopping me-2"></i> Add To Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Full Description -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold text-dark border-bottom pb-2">Product Description</h5>
                    <p class="text-muted mt-3" style="line-height: 1.7;">{{ $product->description }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ratings & Reviews (static placeholder — Reviews module not built yet) -->
    <div class="row mt-5">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3">Ratings &amp; Reviews</h5>
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="fs-3 fw-bold">4.8/5</span>
                <span style="color:#ffb800; font-size:1.2rem;">★★★★★</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="review-card">
                        <div style="color:#ffb800;">★★★★★</div>
                        <p class="small mt-2 mb-2">"Loved the taste and freshness. Delivered right on time!"</p>
                        <small class="text-muted fw-semibold">Sample Customer</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="review-card">
                        <div style="color:#ffb800;">★★★★★</div>
                        <p class="small mt-2 mb-2">"Beautifully decorated, exactly like the photos."</p>
                        <small class="text-muted fw-semibold">Sample Customer</small>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block mt-2">* Sample reviews shown as placeholder — real customer reviews will appear here once available.</small>
        </div>
    </div>

    <!-- Recently Viewed -->
    @if ($recentlyViewed->count())
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0">Recently Viewed</h4>
            <a href="{{ url('/') }}" class="small fw-semibold text-decoration-none text-danger">VIEW ALL</a>
        </div>
        <div class="product-carousel-wrap">
            <button type="button" class="carousel-arrow carousel-arrow-left" data-target="recentlyViewedTrack">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="related-scroll" id="recentlyViewedTrack">
                @foreach ($recentlyViewed as $rv)
                    <div class="related-card">
                        @include('partials.product-card', ['product' => $rv])
                    </div>
                @endforeach
            </div>
            <button type="button" class="carousel-arrow carousel-arrow-right" data-target="recentlyViewedTrack">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- You May Also Like -->
    @if ($relatedProducts->count())
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0">You May Also Like</h4>
            <a href="{{ url('/category/' . ($product->category->slug ?? '')) }}" class="small fw-semibold text-decoration-none text-danger">VIEW ALL</a>
        </div>
        <div class="product-carousel-wrap">
            <button type="button" class="carousel-arrow carousel-arrow-left" data-target="relatedProductsTrack">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="related-scroll" id="relatedProductsTrack">
                @foreach ($relatedProducts as $related)
                    <div class="related-card">
                        @include('partials.product-card', ['product' => $related])
                    </div>
                @endforeach
            </div>
            <button type="button" class="carousel-arrow carousel-arrow-right" data-target="relatedProductsTrack">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
    @endif
</div>

<!-- Mobile Fixed Bottom Action Bar -->
<div class="mobile-sticky-bar d-flex align-items-center justify-content-between d-md-none">
    <div>
        <small class="text-muted d-block" style="font-size:0.7rem;">Total Price</small>
        <span class="fw-bold fs-5 text-danger" id="mobileDisplayPrice">₹{{ number_format($defaultVariant ? ($defaultVariant->discount_price ?? $defaultVariant->price) : $product->base_price, 0) }}</span>
    </div>
    <button type="button" class="btn btn-danger px-4 fw-bold" id="mobileAddToCartBtn">
        Add To Cart
    </button>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedVariantId = {{ $defaultVariant->id ?? 'null' }};
    let selectedBasePrice = {{ $defaultVariant ? ($defaultVariant->discount_price ?? $defaultVariant->price) : $product->base_price }};
    let selectedFlavorId = {{ $product->flavors->where('pivot.is_default', true)->first()->id ?? 'null' }};
    let quantity = 1;
    let currentEggType = 'eggless';

    // ---------- Gallery: thumbnail click ----------
    window.changeMainImage = function (element, imageSrc) {
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = imageSrc;
        document.querySelectorAll('.thumb-img').forEach(el => el.classList.remove('active-thumb'));
        element.classList.add('active-thumb');
    };

    // ---------- Zoom lens ----------
    const zoomImageBox = document.getElementById('zoomImageBox');
    const mainImg = document.getElementById('mainProductImage');
    const zoomLens = document.getElementById('zoomLens');
    const zoomResultPane = document.getElementById('zoomResultPane');

    if (zoomImageBox && mainImg && zoomLens && zoomResultPane) {
        function initZoom() {
            zoomResultPane.style.backgroundImage = `url('${mainImg.src}')`;
        }
        initZoom();

        const imgObserver = new MutationObserver(initZoom);
        imgObserver.observe(mainImg, { attributes: true, attributeFilter: ['src'] });

        zoomImageBox.addEventListener('mouseenter', function () {
            if (window.innerWidth < 1200) return;
            zoomLens.style.display = 'block';
            zoomResultPane.style.display = 'block';
        });

        zoomImageBox.addEventListener('mouseleave', function () {
            zoomLens.style.display = 'none';
            zoomResultPane.style.display = 'none';
        });

        zoomImageBox.addEventListener('mousemove', function (e) {
            if (window.innerWidth < 1200) return;

            const rect = mainImg.getBoundingClientRect();
            const lensSize = 150;

            let x = e.clientX - rect.left - lensSize / 2;
            let y = e.clientY - rect.top - lensSize / 2;

            x = Math.max(0, Math.min(x, rect.width - lensSize));
            y = Math.max(0, Math.min(y, rect.height - lensSize));

            zoomLens.style.left = x + 'px';
            zoomLens.style.top = y + 'px';

            const ratioX = zoomResultPane.offsetWidth / lensSize;
            const ratioY = zoomResultPane.offsetHeight / lensSize;

            zoomResultPane.style.backgroundSize = (rect.width * ratioX) + 'px ' + (rect.height * ratioY) + 'px';
            zoomResultPane.style.backgroundPosition = `-${x * ratioX}px -${y * ratioY}px`;
        });
    }

    // ---------- Weight selection ----------
    const weightOptions = document.querySelectorAll('.weight-option');
    weightOptions.forEach(card => {
        card.addEventListener('click', function () {
            weightOptions.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const hasBothEggTypes = this.dataset.hasBothEggTypes === 'true';
            const eggPreferenceWrap = document.getElementById('eggPreferenceWrap');

            if (hasBothEggTypes) {
                if (eggPreferenceWrap) eggPreferenceWrap.style.display = 'block';
                currentEggType = 'eggless';
                document.querySelectorAll('.egg-toggle-btn').forEach(b => b.classList.remove('active'));
                const egglessBtn = document.querySelector('.egg-toggle-btn[data-egg-type="eggless"]');
                if (egglessBtn) egglessBtn.classList.add('active');
                selectedVariantId = this.dataset.egglessVariantId;
                selectedBasePrice = parseFloat(this.dataset.egglessPrice);
            } else {
                if (eggPreferenceWrap) eggPreferenceWrap.style.display = 'none';
                selectedVariantId = this.dataset.variantId;
                selectedBasePrice = parseFloat(this.dataset.price);
            }

            const originalPrice = parseFloat(this.dataset.originalPrice);
            const hasDiscount = this.dataset.hasDiscount === 'true';
            const strikePriceEl = document.getElementById('strikePrice');
            if (strikePriceEl) {
                strikePriceEl.style.display = hasDiscount ? 'inline' : 'none';
                if (hasDiscount) strikePriceEl.textContent = '₹' + originalPrice.toLocaleString('en-IN');
            }

            updateTotalPrice();
        });
    });

    // ---------- Egg preference toggle ----------
    document.querySelectorAll('.egg-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.egg-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentEggType = this.dataset.eggType;

            const activeWeightCard = document.querySelector('.weight-option.active');
            if (!activeWeightCard) return;

            if (currentEggType === 'egg') {
                selectedVariantId = activeWeightCard.dataset.eggVariantId;
                selectedBasePrice = parseFloat(activeWeightCard.dataset.eggPrice);
            } else {
                selectedVariantId = activeWeightCard.dataset.egglessVariantId;
                selectedBasePrice = parseFloat(activeWeightCard.dataset.egglessPrice);
            }
            updateTotalPrice();
        });
    });

    // ---------- Flavor selection ----------
    document.querySelectorAll('.flavor-option').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.flavor-option').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedFlavorId = this.dataset.flavorId;
        });
    });

    // ---------- Price display ----------
    function updateTotalPrice() {
        const finalPrice = selectedBasePrice * quantity;
        const formatted = '₹' + finalPrice.toLocaleString('en-IN');
        const displayEl = document.getElementById('displayPrice');
        const mobileEl = document.getElementById('mobileDisplayPrice');
        if (displayEl) displayEl.textContent = formatted;
        if (mobileEl) mobileEl.textContent = formatted;
    }

    // ---------- Quantity ----------
    const qtyPlus = document.getElementById('qtyPlus');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyInput = document.getElementById('qtyInput');

    if (qtyPlus) qtyPlus.addEventListener('click', () => {
        quantity++;
        if (qtyInput) qtyInput.value = quantity;
        updateTotalPrice();
    });
    if (qtyMinus) qtyMinus.addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            if (qtyInput) qtyInput.value = quantity;
            updateTotalPrice();
        }
    });

    // ---------- Message character count ----------
    const msgInput = document.getElementById('cakeMessage');
    if (msgInput) {
        msgInput.addEventListener('input', function () {
            const counter = document.getElementById('charCount');
            if (counter) counter.textContent = this.value.length;
        });
    }

    // ---------- Read more / Read less ----------
    const descText = document.getElementById('descText');
    const readMoreBtn = document.getElementById('readMoreBtn');
    const readLessBtn = document.getElementById('readLessBtn');
    if (descText && readMoreBtn && readLessBtn) {
        readMoreBtn.addEventListener('click', () => {
            descText.classList.remove('desc-clamp');
            readMoreBtn.classList.add('d-none');
            readLessBtn.classList.remove('d-none');
        });
        readLessBtn.addEventListener('click', () => {
            descText.classList.add('desc-clamp');
            readLessBtn.classList.add('d-none');
            readMoreBtn.classList.remove('d-none');
        });
    }

    // ---------- Serving info popover ----------
    const servingToggle = document.getElementById('servingInfoToggle');
    const servingPopover = document.getElementById('servingPopover');
    if (servingToggle && servingPopover) {
        servingToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            servingPopover.classList.toggle('show');
        });

        const closeBtn = document.getElementById('closeServingPopover');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => servingPopover.classList.remove('show'));
        }

        document.addEventListener('click', function (e) {
            if (!servingPopover.contains(e.target) && e.target !== servingToggle) {
                servingPopover.classList.remove('show');
            }
        });
    }

    // ---------- Delivery pincode check ----------
    const checkDeliveryBtn = document.getElementById('checkDeliveryBtn');
    if (checkDeliveryBtn) {
        checkDeliveryBtn.addEventListener('click', function () {
            const pincodeInput = document.getElementById('deliveryPincode');
            const resultBox = document.getElementById('deliveryResult');
            const pincode = pincodeInput ? pincodeInput.value.trim() : '';
            if (!pincode || !resultBox) return;

            fetch(`/check-pincode/${pincode}`)
                .then(res => res.json())
                .then(data => {
                    if (data.serviceable) {
                        resultBox.innerHTML = `<small class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Awesome, we deliver to ${data.city}!</small>`;
                    } else {
                        resultBox.innerHTML = `<small class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Sorry, we don't deliver here yet.</small>`;
                    }
                });
        });
    }

    // ---------- Product carousels ----------
    document.querySelectorAll('.carousel-arrow').forEach(btn => {
        btn.addEventListener('click', function () {
            const track = document.getElementById(this.dataset.target);
            if (!track) return;

            const card = track.querySelector('.related-card');
            if (!card) return;

            const scrollAmount = card.offsetWidth + 16;
            const direction = this.classList.contains('carousel-arrow-left') ? -1 : 1;

            track.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });
        });
    });

    // ---------- Add to Cart ----------
    function submitAddToCart() {
        const pincodeInput = document.getElementById('deliveryPincode');
        const pincode = pincodeInput ? pincodeInput.value.trim() : '';
        if (!pincode) {
            alert('Please enter your delivery pincode first!');
            if (pincodeInput) pincodeInput.focus();
            return;
        }

        const dateInput = document.getElementById('deliveryDate');

        const payload = {
            product_id: {{ $product->id }},
            variant_id: selectedVariantId,
            flavor_id: selectedFlavorId,
            quantity: quantity,
            delivery_date: dateInput ? dateInput.value : null,
            pincode: pincode,
            cake_message: msgInput ? msgInput.value : null
        };

        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const cartCountEl = document.getElementById('cartCount');
                if (cartCountEl) cartCountEl.textContent = data.cart_count;
                alert('Product added to cart successfully!');
            } else {
                alert(data.message || 'Error adding product to cart.');
            }
        })
        .catch(() => alert('Something went wrong. Please try again.'));
    }

    const addToCartBtn = document.getElementById('addToCartBtn');
    const mobileAddToCartBtn = document.getElementById('mobileAddToCartBtn');
    if (addToCartBtn) addToCartBtn.addEventListener('click', submitAddToCart);
    if (mobileAddToCartBtn) mobileAddToCartBtn.addEventListener('click', submitAddToCart);
});
</script>

@include('userfooter')