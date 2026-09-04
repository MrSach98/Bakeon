<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | Sweet Bakes</title>

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-chocolate: #4A2E2B;
            --secondary-cream: #FFF8F0;
            --accent-gold: #D4A373;
            --text-dark: #2B2B2B;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8F9FA;
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--primary-chocolate);
            color: #FFFFFF;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-brand {
            padding: 20px;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-gold);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            padding: 15px 0;
            list-style: none;
            margin: 0;
        }

        .sidebar-menu .nav-link {
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .sidebar-menu .nav-link:hover, 
        .sidebar-menu .nav-link.active {
            color: #FFFFFF;
            background-color: rgba(212, 163, 115, 0.2);
            border-left: 4px solid var(--accent-gold);
        }

        /* Main Content Styling */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            height: 60px;
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2D7CD;
            padding: 0 25px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .content-body {
            padding: 25px;
            flex: 1;
        }
        .product-image-thumb-wrap {
    width: 140px;
    height: 140px;
    position: relative;
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    display: inline-block;
}
.product-image-thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-image-thumb-wrap .remove-image-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    padding: 0;
    line-height: 1;
    font-size: 0.75rem;
}
.sidebar-menu .nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: #ddd;
    cursor: pointer;
}
.sidebar-menu .nav-link:hover,
.sidebar-menu .nav-link.active {
    background: #3a2f2f;
    color: #fff;
}
.sidebar-menu .submenu {
    background: #171212;
}
.sidebar-menu .submenu-link {
    padding: 8px 16px 8px 44px !important;
    font-size: 0.9rem;
}
.sidebar-menu .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
    transition: transform 0.2s;
}
.sidebar-menu .nav-link[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}
    </style>
</head>
<body>

    <div class="sidebar">
        <h5 class="text-white p-3 mb-0">🎂 Sweet Bakes</h5>

        <ul class="sidebar-menu list-unstyled mb-0">

            <li>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </li>

            <!-- Catalog group -->
            <li>
                <a href="javascript:void(0)" class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs(['admin.categories.*','admin.flavors.*','admin.weights.*','admin.addons.*','admin.delivery-options.*','admin.occasions.*','admin.products.*']) ? 'active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#catalogSubmenu">
                    <span><i class="fa-solid fa-cake-candles"></i> Catalog</span>
                    <i class="fa-solid fa-chevron-down small"></i>
                </a>
                <div class="collapse {{ request()->routeIs(['admin.categories.*','admin.flavors.*','admin.weights.*','admin.addons.*','admin.delivery-options.*','admin.occasions.*','admin.products.*']) ? 'show' : '' }}" id="catalogSubmenu">
                    <ul class="list-unstyled submenu">
                        <li><a href="{{ route('admin.products.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Products</a></li>
                        <li><a href="{{ route('admin.categories.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categories</a></li>
                        <li><a href="{{ route('admin.flavors.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.flavors.*') ? 'active' : '' }}">Flavors</a></li>
                        <li><a href="{{ route('admin.weights.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.weights.*') ? 'active' : '' }}">Weights</a></li>
                        <li><a href="{{ route('admin.addons.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.addons.*') ? 'active' : '' }}">Addons</a></li>
                        <li><a href="{{ route('admin.delivery-options.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.delivery-options.*') ? 'active' : '' }}">Delivery Options</a></li>
                        <li><a href="{{ route('admin.occasions.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.occasions.*') ? 'active' : '' }}">Occasions</a></li>
                    </ul>
                </div>
            </li>

            <!-- Orders group -->
            <li>
                <a href="javascript:void(0)" class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#ordersSubmenu">
                    <span><i class="fa-solid fa-receipt"></i> Orders</span>
                    <i class="fa-solid fa-chevron-down small"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.orders.*') ? 'show' : '' }}" id="ordersSubmenu">
                    <ul class="list-unstyled submenu">
                        <li><a href="{{ route('admin.orders.index') }}" class="nav-link submenu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">All Orders</a></li>
                        <li><a href="{{ route('admin.orders.online') }}" class="nav-link submenu-link {{ request()->routeIs('admin.orders.online') ? 'active' : '' }}">
                            <i class="fa-solid fa-credit-card"></i> Online Orders
                        </a></li>
                        <li><a href="{{ route('admin.orders.cod') }}" class="nav-link submenu-link {{ request()->routeIs('admin.orders.cod') ? 'active' : '' }}">
                            <i class="fa-solid fa-money-bill-wave"></i> COD Orders
                        </a></li>
                    </ul>
                </div>
            </li>

            <li>
                <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-star"></i> Reviews
                </a>
            </li>

            <li>
                <a href="{{ route('admin.pincodes.index') }}" class="nav-link {{ request()->routeIs('admin.pincodes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-location-dot"></i> Serviceable Pincodes
                </a>
            </li>

            <li>
                <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tag"></i> Coupons
                </a>
            </li>

            <li>
                <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i> Banners
                </a>
            </li>

            <li>
                <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i> Site Settings
                </a>
            </li>
            <li>
                <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-file-lines"></i> Content Pages
                </a>
            </li>

        </ul>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Navbar -->
        <nav class="top-navbar">
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user me-1"></i> {{ auth()->user()->name ?? 'Admin' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Dynamic Content -->
        <main class="content-body">
            <!-- @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif -->

            @yield('content')
        </main>
    </div>
    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="appToastBody">
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close">
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('appToast');
            const toastBody = document.getElementById('appToastBody');

            toastEl.className = 'toast align-items-center text-white border-0 bg-' + (type === 'danger' ? 'danger' : 'success');
            toastBody.textContent = message;

            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }

        // Agar Laravel session me success/error message hai to page load pe turant toast dikhao
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success')), 'success'));
        @endif
        @if (session('error'))
            document.addEventListener('DOMContentLoaded', () => showToast(@json(session('error')), 'danger'));
        @endif
    </script>
    @stack('scripts')
</body>
</html>