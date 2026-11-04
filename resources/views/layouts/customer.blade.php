<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Customer — Bakery BenBen Co.')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">
    @stack('styles')
</head>
<body class="bg-light-cream">
    <div class="bb-dash bb-admin-shell bb-customer-shell">
        <aside class="bb-dash-sidebar dark" id="customer-sidebar">
            <a href="{{ route('customer.dashboard') }}" class="bb-admin-logo">
                <span class="mark"><i class="bi bi-egg-fried"></i></span>
                <span>BENBEN CO.<small class="d-block">CUSTOMER AREA</small></span>
            </a>
            <div class="bb-admin-sidebar-scroll">
                <span class="bb-admin-nav-caption">AKUN SAYA</span>
                <ul class="bb-dash-nav">
                    <li><a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a></li>
                    <li><a href="{{ route('customer.orders') }}" class="{{ request()->routeIs('customer.orders', 'customer.order.detail') ? 'active' : '' }}"><i class="bi bi-bag-check"></i><span>Pesanan Saya</span></a></li>
                    <li><a href="{{ route('customer.profile') }}" class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}"><i class="bi bi-person"></i><span>Profil</span></a></li>
                </ul>
                <span class="bb-admin-nav-caption">BELANJA</span>
                <ul class="bb-dash-nav">
                    <li><a href="{{ route('products') }}"><i class="bi bi-shop"></i><span>Katalog Produk</span></a></li>
                    <li><a href="{{ route('cart') }}"><i class="bi bi-basket3"></i><span>Keranjang</span><span class="bb-customer-cart-count">{{ collect(session('cart', []))->sum('qty') }}</span></a></li>
                </ul>
            </div>
            <div class="bb-admin-sidebar-bottom">
                <a href="{{ route('home') }}" class="bb-admin-store-link"><i class="bi bi-arrow-up-right-square"></i><span>Kembali ke website</span></a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bb-admin-logout"><i class="bi bi-box-arrow-left"></i><span>Keluar dari akun</span></button>
                </form>
            </div>
        </aside>
        <button type="button" class="bb-admin-sidebar-backdrop" data-customer-sidebar-close aria-label="Tutup menu"></button>
        <div class="bb-admin-workspace">
            <header class="bb-admin-topbar">
                <button type="button" class="bb-admin-menu-toggle" data-customer-sidebar-toggle aria-controls="customer-sidebar" aria-expanded="false" aria-label="Buka menu navigasi"><i class="bi bi-list" aria-hidden="true"></i></button>
                <div class="bb-admin-topbar-title"><span>BENBEN CO.</span><strong>Customer Panel</strong></div>
                <a href="{{ route('products') }}" class="bb-admin-topbar-link ms-auto"><i class="bi bi-plus-lg"></i> Belanja</a>
            </header>
            <main class="bb-dash-main">
                @if (session('success') || session('error'))
                    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;">
                        <div class="toast bb-toast {{ session('error') ? 'is-error' : '' }}" role="status" aria-live="polite" data-customer-toast>
                            <div class="toast-header"><strong class="me-auto">BENBEN CO. Bakery</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button></div>
                            <div class="toast-body">{{ session('success') ?? session('error') }}</div>
                        </div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const customerSidebar = document.getElementById('customer-sidebar');
        const customerToggle = document.querySelector('[data-customer-sidebar-toggle]');
        const customerClose = document.querySelector('[data-customer-sidebar-close]');
        const setCustomerSidebarOpen = (open) => {
            document.body.classList.toggle('bb-customer-menu-open', open);
            document.body.classList.toggle('bb-admin-menu-open', open);
            customerToggle?.setAttribute('aria-expanded', String(open));
        };
        customerToggle?.addEventListener('click', () => setCustomerSidebarOpen(!document.body.classList.contains('bb-customer-menu-open')));
        customerClose?.addEventListener('click', () => setCustomerSidebarOpen(false));
        customerSidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setCustomerSidebarOpen(false)));
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setCustomerSidebarOpen(false); });
        document.querySelectorAll('[data-customer-toast]').forEach((toast) => new bootstrap.Toast(toast, { delay: 4500 }).show());
    </script>
    @stack('scripts')
</body>
</html>
