@include('userheader')

<div class="container-fluid px-4 px-lg-5 py-4">
    <h1 class="mb-4" style="font-size:1.6rem;">My Wishlist</h1>

    @if ($products->count())
        <div class="row g-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fa-regular fa-heart fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-3">Your wishlist is empty.</p>
            <a href="{{ url('/') }}" class="btn btn-danger px-4">Start Shopping</a>
        </div>
    @endif
</div>

@include('userfooter')