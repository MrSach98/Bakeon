<div class="col-lg-3 mb-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                    {{ strtoupper(substr($accountUser->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold">{{ $accountUser->name }}</div>
                    <small class="text-muted">{{ $accountUser->email }}</small>
                </div>
            </div>

            <ul class="list-unstyled mb-0">
                <li>
                    <a href="{{ route('account.orders.index') }}" class="d-block py-2 text-decoration-none {{ request()->routeIs('account.orders.*') ? 'text-danger fw-bold' : 'text-dark' }}">
                        <i class="fa-solid fa-box me-2"></i> My Orders
                    </a>
                </li>
                <li>
                    <a href="{{ url('/cart') }}" class="d-block py-2 text-decoration-none text-dark">
                        <i class="fa-solid fa-cart-shopping me-2"></i> My Cart
                        @if (($cartCount ?? 0) > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('wishlist.index') }}" class="d-block py-2 text-decoration-none {{ request()->routeIs('wishlist.*') ? 'text-danger fw-bold' : 'text-dark' }}">
                        <i class="fa-regular fa-heart me-2"></i> My Favourites
                        @if (($wishlistCount ?? 0) > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('account.addresses.index') }}" class="d-block py-2 text-decoration-none {{ request()->routeIs('account.addresses.*') ? 'text-danger fw-bold' : 'text-dark' }}">
                        <i class="fa-solid fa-location-dot me-2"></i> Manage Address
                    </a>
                </li>
                <li>
                    <a href="{{ route('account.profile.edit') }}" class="d-block py-2 text-decoration-none {{ request()->routeIs('account.profile.*') ? 'text-danger fw-bold' : 'text-dark' }}">
                        <i class="fa-solid fa-user-pen me-2"></i> Profile Settings
                    </a>
                </li>
                <li>
                    <a href="{{ url('/track-order') }}" class="d-block py-2 text-decoration-none text-dark">
                        <i class="fa-solid fa-truck-fast me-2"></i> Track Order
                    </a>
                </li>
                <li>
                    <a href="{{ route('account.reviews.index') }}" class="d-block py-2 text-decoration-none {{ request()->routeIs('account.reviews.*') ? 'text-danger fw-bold' : 'text-dark' }}">
                        <i class="fa-regular fa-comment-dots me-2"></i> My Reviews
                    </a>
                </li>
                @if ($siteSettings->contact_phone ?? null)
                <li>
                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $siteSettings->contact_phone) }}" target="_blank" class="d-block py-2 text-decoration-none text-dark">
                        <i class="fa-brands fa-whatsapp me-2"></i> WhatsApp
                    </a>
                </li>
                @endif
                <li class="pt-2 border-top mt-2">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-decoration-none text-danger p-0 py-2">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>