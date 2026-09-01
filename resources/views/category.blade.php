@include('userheader')


<div class="container-fluid px-4 px-lg-5 py-4">
    <!-- Breadcrumb -->
    <nav class="mb-3">
        <small class="text-muted">
            <a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a> /
            <span class="text-dark fw-semibold">{{ $category->name }}</span>
        </small>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #2B2B2B; font-size: 2rem;">
                {{ $category->name }}
            </h1>
            <p class="text-muted small mb-0">{{ $products->total() }} delicious cakes found</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex align-items-center gap-2">
            <label for="sortSelect" class="small text-muted fw-semibold text-nowrap">Sort By:</label>
            <select class="form-select form-select-sm border-0 bg-light rounded-3 px-3 py-2 shadow-sm" style="max-width: 200px; font-size: 0.85rem;" id="sortSelect">
                <option value="">Relevance</option>
                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="bestseller" {{ request('sort') === 'bestseller' ? 'selected' : '' }}>Bestsellers</option>
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px; background-color: #FFF9F5;">
                <div class="card-body p-4">
                    <form method="GET" action="{{ url()->current() }}" id="filterForm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase tracking-wide" style="color: #800020; font-size: 0.85rem;">Filters</h6>
                            <a href="{{ url()->current() }}" class="text-decoration-none text-muted small">Clear All</a>
                        </div>

                        <hr class="my-3 opacity-25">

                        @if ($availableFlavors->count())
                            <div class="mb-4">
                                <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Flavour</h6>
                                @foreach ($availableFlavors as $flavor)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="flavor[]" value="{{ $flavor->id }}" class="form-check-input filter-input"
                                               id="flavor{{ $flavor->id }}" {{ in_array($flavor->id, (array) request('flavor')) ? 'checked' : '' }}>
                                        <label class="form-check-label small text-secondary" for="flavor{{ $flavor->id }}">{{ $flavor->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-4">
                            <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Egg Preference</h6>
                            @foreach (['eggless' => 'Eggless Only', 'egg' => 'With Egg'] as $val => $label)
                                <div class="form-check mb-2">
                                    <input type="radio" name="egg_type" value="{{ $val }}" class="form-check-input filter-input"
                                           id="egg{{ $val }}" {{ request('egg_type') === $val ? 'checked' : '' }}>
                                    <label class="form-check-label small text-secondary" for="egg{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        @if ($availableOccasions->count())
                            <div class="mb-4">
                                <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Occasion</h6>
                                @foreach ($availableOccasions as $occasion)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="occasion[]" value="{{ $occasion->id }}" class="form-check-input filter-input"
                                               id="occasion{{ $occasion->id }}" {{ in_array($occasion->id, (array) request('occasion')) ? 'checked' : '' }}>
                                        <label class="form-check-label small text-secondary" for="occasion{{ $occasion->id }}">{{ $occasion->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-2">
                            <h6 class="small text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem;">Price Range (₹)</h6>
                            <div class="d-flex gap-2">
                                <input type="number" name="min_price" class="form-control form-control-sm border-0 bg-white filter-input shadow-sm" placeholder="Min" value="{{ request('min_price') }}">
                                <input type="number" name="max_price" class="form-control form-control-sm border-0 bg-white filter-input shadow-sm" placeholder="Max" value="{{ request('max_price') }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            @if ($products->count())
                <div class="row g-4">
                    @foreach ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-5 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5 my-5 bg-light rounded-4">
                    <i class="fa-solid fa-cake-candles fa-3x mb-3" style="color: #800020;"></i>
                    <h5 class="fw-bold">No cakes available</h5>
                    <p class="text-muted small">Try adjusting your filters or search for another category.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('userfooter')

@push('scripts')
<script>
    document.querySelectorAll('.filter-input').forEach(input => {
        input.addEventListener('change', () => document.getElementById('filterForm').submit());
    });

    document.getElementById('sortSelect').addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', this.value);
        window.location.href = url.toString();
    });
</script>
@endpush