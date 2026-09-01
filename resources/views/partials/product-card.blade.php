@php
    $variant = $product->defaultVariant();
    $displayPrice = $variant ? ($variant->discount_price ?? $variant->price) : ($product->discount_price ?? $product->base_price);
    $oldPrice = $variant ? ($variant->discount_price ? $variant->price : null) : ($product->discount_price ? $product->base_price : null);
    $image = $product->primaryImage ?? $product->images->first();
@endphp

<div class="col-6 col-md-4 col-lg-4">
    <div class="product-card h-100 border-0 rounded-4 overflow-hidden position-relative shadow-sm transition-all" style="background-color: #FFF9F5;">
        
        {{-- Bestseller Badge --}}
        @if ($product->is_bestseller)
            <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill text-white fw-semibold" style="background-color: #800020; z-index: 2; font-size: 0.75rem;">
                Bestseller
            </span>
        @endif

        {{-- Wishlist Button --}}
        <button type="button" class="btn btn-wishlist-toggle position-absolute top-0 end-0 m-3 rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center bg-white text-secondary" 
                style="width: 36px; height: 36px; z-index: 2;" data-product-id="{{ $product->id }}">
            <i class="fa-regular fa-heart"></i>
        </button>

        {{-- Product Image Wrap --}}
        <a href="{{ url('/' . $product->slug) }}" class="text-decoration-none d-block overflow-hidden position-relative" style="padding-top: 100%;">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3">
                @if ($image)
                    <img src="{{ asset($image->image_path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-3 product-img-hover" style="transition: transform 0.4s ease;">
                @else
                    <div class="w-100 h-100 rounded-3 d-flex align-items-center justify-content-center fw-bold" style="background: #FCEFE6; color: #800020; font-family: 'Playfair Display', serif; font-size: 1.5rem;">
                        Cake
                    </div>
                @endif
            </div>
        </a>

        {{-- Details --}}
        <div class="p-3 d-flex flex-column justify-content-between text-center">
            <div>
                <a href="{{ route('product.show', ['slug' => $product->slug]) }}" class="text-decoration-none text-dark">
                    <h6 class="fw-bold mb-1 text-truncate-2" style="font-size: 0.95rem; min-height: 2.7rem; color: #2B2B2B;">
                        {{ $product->name }}
                    </h6>
                </a>

                @if ($product->egg_type === 'eggless')
                    <div class="mb-2">
                        <span class="badge rounded-pill bg-success-subtle text-success small font-monospace" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-leaf me-1"></i> Eggless
                        </span>
                    </div>
                @elseif ($product->egg_type === 'both')
                    <div class="mb-2">
                        <span class="badge rounded-pill bg-light text-muted small" style="font-size: 0.7rem;">
                            Egg / Eggless
                        </span>
                    </div>
                @endif
            </div>

            <div class="mt-2">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    @if ($oldPrice)
                        <span class="text-muted text-decoration-line-through small" style="font-size: 0.85rem;">₹{{ number_format($oldPrice, 0) }}</span>
                    @endif
                    <span class="fw-bold" style="color: #800020; font-size: 1.1rem;">₹{{ number_format($displayPrice, 0) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>