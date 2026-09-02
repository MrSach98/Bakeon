@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <nav class="mb-3">
        <small class="text-muted">
            <a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a> /
            <span class="text-dark fw-semibold">Search Results</span>
        </small>
    </nav>

    <h1 class="mb-1" style="font-size:1.6rem;">
        @if ($query)
            Search results for "{{ $query }}"
        @else
            Search
        @endif
    </h1>

    @if ($query)
        <p class="text-muted mb-4">{{ $products->total() }} cakes found</p>
    @endif

    @if ($query === '')
        <div class="text-center py-5">
            <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
            <p class="text-muted">Type something in the search bar to find cakes, cookies, and more.</p>
        </div>
    @elseif ($products->count())
        <div class="row g-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fa-solid fa-cake-candles fa-3x text-muted mb-3"></i>
            <p class="text-muted">No cakes found matching "{{ $query }}". Try a different search.</p>
        </div>
    @endif
</div>

@include('userfooter')