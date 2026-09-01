@include('userheader')

<!-- Hero Banner Slider -->
@if ($heroBanners->count())
<div id="heroCarousel" class="carousel slide hero-banner" data-bs-ride="carousel">
    <div class="carousel-inner">
        @foreach ($heroBanners as $index => $banner)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <img src="{{ asset($banner->image) }}" alt="{{ $banner->title }}">
                @if ($banner->title)
                    <div class="hero-caption">
                        <h2 class="font-display mb-2">{{ $banner->title }}</h2>
                        @if ($banner->subtitle)<p class="mb-3">{{ $banner->subtitle }}</p>@endif
                        @if ($banner->button_text)
                            <a href="{{ $banner->link_url ?? '#' }}" class="btn" style="background:var(--brand-maroon); color:#fff;">{{ $banner->button_text }}</a>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @if ($heroBanners->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        <div class="carousel-indicators">
            @foreach ($heroBanners as $index => $banner)
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
            @endforeach
        </div>
    @endif
</div>
@endif

<!-- Menu / What will you wish for -->
@if ($menuChips->count())
<section class="py-4" style="background:var(--brand-cream);">
    <div class="container">
        <div class="text-center mb-3">
            <h6 class="text-uppercase fw-bold" style="color:var(--brand-maroon); letter-spacing:1px;">Menu</h6>
            <h3 class="section-title" style="font-size:1.3rem;">What will you wish for?</h3>
        </div>
        <div class="d-flex overflow-auto gap-3 pb-2" style="scrollbar-width:none;">
            @foreach ($menuChips as $chip)
                <a href="{{ url('/category/' . $chip->slug) }}" class="text-center text-decoration-none flex-shrink-0" style="width:110px;">
                    <div class="rounded-3 overflow-hidden mb-2" style="height:110px; background:#f7f2ec;">
                        @if ($chip->image)
                            <img src="{{ asset($chip->image) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <img src="https://placehold.co/150x150/FFF8F0/A31E42?text={{ urlencode($chip->name) }}" style="width:100%; height:100%; object-fit:cover;">
                        @endif
                    </div>
                    <small class="fw-semibold text-dark text-uppercase" style="font-size:0.72rem;">{{ $chip->name }}</small>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Bestsellers -->
@if ($bestsellers->count())
<section class="py-5" style="background:#fdf9f4;">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Bestseller Cakes</h2>
            <a href="{{ url('/bestsellers') }}" class="btn btn-outline-dark btn-sm">View All</a>
        </div>
        <div class="row g-4">
            @foreach ($bestsellers as $product)
                @php
                    $variant = $product->defaultVariant();
                    $displayPrice = $variant ? ($variant->discount_price ?? $variant->price) : ($product->discount_price ?? $product->base_price);
                    $oldPrice = $variant ? ($variant->discount_price ? $variant->price : null) : ($product->discount_price ? $product->base_price : null);
                @endphp
                <div class="col-6 col-md-3">
                    <div class="product-card position-relative">
                        <span class="badge badge-bestseller">Bestseller</span>
                        <div class="img-wrap">
                            <a href="{{ url('/product/' . $product->slug) }}">
                                @if ($product->primaryImage)
                                    <img src="{{ asset($product->primaryImage->image_path) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://placehold.co/300x300/FFF8F0/A31E42?text={{ urlencode($product->name) }}" alt="{{ $product->name }}">
                                @endif
                            </a>
                        </div>
                        <div class="p-3 text-center">
                            <h6 class="fw-semibold text-truncate mb-1">
                                <a href="{{ url('/product/' . $product->slug) }}" class="text-dark">{{ $product->name }}</a>
                            </h6>
                            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                @if ($oldPrice)
                                    <span class="price-new">₹{{ number_format($displayPrice, 2) }}</span>
                                    <span class="price-old">₹{{ number_format($oldPrice, 2) }}</span>
                                @else
                                    <span class="price-new">₹{{ number_format($displayPrice, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ url('/product/' . $product->slug) }}" class="btn btn-add btn-sm px-3">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Promo Strip Banner -->
@if ($promoStripBanner)
<section class="py-3">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <a href="{{ $promoStripBanner->link_url ?? '#' }}" class="d-block">
            <img src="{{ asset($promoStripBanner->image) }}" alt="{{ $promoStripBanner->title ?? 'Offer' }}"
                 class="w-100 rounded-3" style="max-height:160px; object-fit:cover;">
        </a>
    </div>
</section>
@endif

<!-- Our Promise -->
<section class="py-5" style="background:var(--brand-cream);">
    <div class="container text-center">
        <h6 class="text-uppercase fw-bold" style="color:var(--brand-maroon);">Our Promise</h6>
        <h3 class="section-title mb-4" style="font-size:1.4rem;">There's no secret spell—only honest, hard work!</h3>
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <i class="fa-solid fa-truck fa-2x mb-2" style="color:var(--brand-maroon);"></i>
                <h6 class="fw-bold">ON-TIME DELIVERY</h6>
                <small class="text-muted">Because no one likes late surprises.</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="fa-solid fa-cake-candles fa-2x mb-2" style="color:var(--brand-maroon);"></i>
                <h6 class="fw-bold">500+ DESIGNS</h6>
                <small class="text-muted">Wishes come in all shapes and sizes.</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="fa-solid fa-gift fa-2x mb-2" style="color:var(--brand-maroon);"></i>
                <h6 class="fw-bold">THOUSANDS OF ORDERS</h6>
                <small class="text-muted">You can close your eyes and trust us.</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="fa-solid fa-bread-slice fa-2x mb-2" style="color:var(--brand-maroon);"></i>
                <h6 class="fw-bold">BAKED FRESH</h6>
                <small class="text-muted">Spreading smiles, one slice at a time.</small>
            </div>
        </div>
    </div>
</section>

<!-- Featured -->
@if ($featured->count())
<section class="py-5">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Handpicked For You</h2>
            <a href="{{ url('/products') }}" class="btn btn-outline-dark btn-sm">View All</a>
        </div>
        <div class="row g-4">
            @foreach ($featured as $product)
                @php
                    $variant = $product->defaultVariant();
                    $displayPrice = $variant ? ($variant->discount_price ?? $variant->price) : ($product->discount_price ?? $product->base_price);
                    $oldPrice = $variant ? ($variant->discount_price ? $variant->price : null) : ($product->discount_price ? $product->base_price : null);
                @endphp
                <div class="col-6 col-md-3">
                    <div class="product-card position-relative">
                        <div class="img-wrap">
                            <a href="{{ url('/product/' . $product->slug) }}">
                                @if ($product->primaryImage)
                                    <img src="{{ asset($product->primaryImage->image_path) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://placehold.co/300x300/FFF8F0/A31E42?text={{ urlencode($product->name) }}" alt="{{ $product->name }}">
                                @endif
                            </a>
                        </div>
                        <div class="p-3 text-center">
                            <h6 class="fw-semibold text-truncate mb-1">
                                <a href="{{ url('/product/' . $product->slug) }}" class="text-dark">{{ $product->name }}</a>
                            </h6>
                            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                @if ($oldPrice)
                                    <span class="price-new">₹{{ number_format($displayPrice, 2) }}</span>
                                    <span class="price-old">₹{{ number_format($oldPrice, 2) }}</span>
                                @else
                                    <span class="price-new">₹{{ number_format($displayPrice, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ url('/product/' . $product->slug) }}" class="btn btn-add btn-sm px-3">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Occasion Reminder Banner -->
@if ($occasionReminderBanner)
<section class="py-4">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <div class="occasion-reminder-card text-center p-4 p-md-5 position-relative">
            @if ($occasionReminderBanner->title)
                <h2 class="font-display mb-2" style="color:var(--brand-maroon); font-size:2rem;">{{ $occasionReminderBanner->title }}</h2>
            @endif
            @if ($occasionReminderBanner->subtitle)
                <p class="mb-4 text-dark">{{ $occasionReminderBanner->subtitle }}</p>
            @endif
            @if ($occasionReminderBanner->image)
                <img src="{{ asset($occasionReminderBanner->image) }}" alt="{{ $occasionReminderBanner->title }}" class="mx-auto d-block mb-3" style="max-height:120px;">
            @endif
            @if ($occasionReminderBanner->button_text)
                <a href="{{ $occasionReminderBanner->link_url ?? '#' }}" class="btn px-4 py-2" style="background:var(--brand-maroon); color:#fff; border-radius:30px;">
                    {{ $occasionReminderBanner->button_text }}
                </a>
            @endif
        </div>
    </div>
</section>
@endif

<!-- App Only Deal Banner -->
@if ($appDealBanner)
<section class="py-3">
    <div class="container-fluid px-4 px-lg-5 py-4" style="max-width:700px;">
        <a href="{{ $appDealBanner->link_url ?? '#' }}" class="d-flex align-items-center justify-content-between text-decoration-none p-3 rounded-3 shadow-sm" style="background:#fff; border:1px solid #eee;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-brands fa-apple fa-lg text-dark"></i>
                <i class="fa-brands fa-google-play fa-lg text-dark"></i>
                <span class="fw-semibold text-dark">
                    {{ $appDealBanner->title ?? 'App Only Deal' }}
                    @if ($appDealBanner->subtitle)<span class="text-muted fw-normal ms-1">{{ $appDealBanner->subtitle }}</span>@endif
                </span>
            </div>
            <i class="fa-solid fa-chevron-right text-muted"></i>
        </a>
    </div>
</section>
@endif

<!-- What's In Your Heart - Instagram grid -->
@if ($instagramPosts->count())
<section class="py-5">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <div class="text-center mb-4">
            <h2 class="section-title" style="color:var(--brand-maroon);">What's In Your Heart?</h2>
            <p class="text-muted">A glimpse from our social world 💖</p>
        </div>
        <div class="instagram-scroll-wrap">
            <div class="instagram-grid">
                @foreach ($instagramPosts as $post)
                    <a href="{{ $post->permalink }}" target="_blank" class="instagram-tile">
                        <img src="{{ $post->media_url }}" alt="Instagram post">
                        <span class="instagram-icon">
                            @if (($post->media_type ?? '') === 'VIDEO')
                                <i class="fa-solid fa-circle-play"></i>
                            @else
                                <i class="fa-solid fa-bookmark"></i>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- Delivery Cities -->
@if ($deliveryCities->count())
<section class="py-4 text-center border-top">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <small class="text-muted">
            <strong>Avail {{ $siteSettings->store_name ?? 'Sweet Bakes' }} cake delivery service in:</strong>
            {{ $deliveryCities->implode(' | ') }}
        </small>
    </div>
</section>
@endif

@include('userfooter')