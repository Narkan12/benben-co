<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — Bakery BenBen Co.')</title>
    <script src="{{ asset('js/theme.js') }}"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">

    @stack('styles')
</head>
<body class="bg-light-cream">

    <div class="bb-dash bb-admin-shell">

        {{-- SIDEBAR --}}
        <aside class="bb-dash-sidebar dark" id="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="bb-admin-logo">
                <span class="mark"><i class="bi bi-egg-fried"></i></span>
                <span>
                    BENBEN CO.
                    <small class="d-block">BAKERY · ADMIN PANEL</small>
                </span>
            </a>

            <div class="bb-admin-sidebar-scroll">
                <span class="bb-admin-nav-caption">WORKSPACE</span>
                <ul class="bb-dash-nav">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <span class="bb-admin-nav-caption">PENGELOLAAN</span>
                <nav class="bb-admin-menu-groups" aria-label="Menu pengelolaan">
                    <details class="bb-admin-menu-group" {{ request()->routeIs('admin.products*', 'admin.categories*', 'admin.inventory*', 'admin.customers*', 'admin.ratings*', 'admin.promotions*', 'admin.company-profile*') ? 'open' : '' }}>
                        <summary>
                            <span class="bb-admin-group-title"><i class="bi bi-grid"></i> Data Master</span>
                            <i class="bi bi-chevron-down bb-admin-group-chevron" aria-hidden="true"></i>
                        </summary>
                        <ul class="bb-dash-nav bb-admin-subnav">
                            <li><a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}"><i class="bi bi-basket"></i><span>Produk</span></a></li>
                            <li><a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Kategori</span></a></li>
                            <li><a href="{{ route('admin.inventory') }}" class="{{ request()->routeIs('admin.inventory*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i><span>Stok</span></a></li>
                            <li><a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers*') ? 'active' : '' }}"><i class="bi bi-people"></i><span>Pelanggan</span></a></li>
                            <li><a href="{{ route('admin.ratings') }}" class="{{ request()->routeIs('admin.ratings*') ? 'active' : '' }}"><i class="bi bi-star"></i><span>Rating</span></a></li>
                            <li><a href="{{ route('admin.promotions') }}" class="{{ request()->routeIs('admin.promotions*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i><span>Promo</span></a></li>
                            @if(auth()->user()->role === 'admin')
                                <li><a href="{{ route('admin.company-profile') }}" class="{{ request()->routeIs('admin.company-profile*') ? 'active' : '' }}"><i class="bi bi-images"></i><span>Company Profile</span></a></li>
                            @endif
                        </ul>
                    </details>

                    <details class="bb-admin-menu-group" {{ request()->routeIs('admin.pos*', 'admin.orders*', 'admin.payments*', 'admin.reports*') ? 'open' : '' }}>
                        <summary>
                            <span class="bb-admin-group-title"><i class="bi bi-receipt"></i> Transaksi</span>
                            <i class="bi bi-chevron-down bb-admin-group-chevron" aria-hidden="true"></i>
                        </summary>
                        <ul class="bb-dash-nav bb-admin-subnav">
                            <li><a href="{{ route('admin.pos') }}" class="{{ request()->routeIs('admin.pos*') ? 'active' : '' }}"><i class="bi bi-shop-window"></i><span>Kasir POS</span></a></li>
                            <li><a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}"><i class="bi bi-bag-check"></i><span>Pesanan</span></a></li>
                            <li><a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments*') ? 'active' : '' }}"><i class="bi bi-credit-card"></i><span>Pembayaran</span></a></li>
                            <li>
                                <details class="bb-admin-menu-subgroup" {{ request()->routeIs('admin.reports*') ? 'open' : '' }}>
                                    <summary class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                        <span><i class="bi bi-graph-up"></i><span>Laporan</span></span>
                                        <i class="bi bi-chevron-down bb-admin-group-chevron" aria-hidden="true"></i>
                                    </summary>
                                    <ul class="bb-dash-nav bb-admin-subnav bb-admin-report-subnav">
                                        <li><a href="{{ route('admin.reports.transactions') }}" class="{{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}"><i class="bi bi-currency-exchange"></i><span>Transaksi</span></a></li>
                                        <li><a href="{{ route('admin.reports.sales') }}" class="{{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i><span>Penjualan</span></a></li>
                                        <li><a href="{{ route('admin.reports.stock') }}" class="{{ request()->routeIs('admin.reports.stock') ? 'active' : '' }}"><i class="bi bi-box-seam"></i><span>Stok Barang</span></a></li>
                                        <li><a href="{{ route('admin.reports.products') }}" class="{{ request()->routeIs('admin.reports.products') ? 'active' : '' }}"><i class="bi bi-basket3"></i><span>Produk</span></a></li>
                                        <li><a href="{{ route('admin.reports.finance') }}" class="{{ request()->routeIs('admin.reports.finance') ? 'active' : '' }}"><i class="bi bi-wallet2"></i><span>Keuangan</span></a></li>
                                    </ul>
                                </details>
                            </li>
                        </ul>
                    </details>
                </nav>

                @if(auth()->user()->role === 'admin')
                    <span class="bb-admin-nav-caption bb-admin-content-caption">KONTEN WEBSITE</span>
                    <details class="bb-admin-menu-group bb-admin-content-group" {{ request()->routeIs('admin.pages*') ? 'open' : '' }}>
                        <summary>
                            <span class="bb-admin-group-title"><i class="bi bi-window-stack"></i> Manajemen Halaman</span>
                            <i class="bi bi-chevron-down bb-admin-group-chevron" aria-hidden="true"></i>
                        </summary>
                        <ul class="bb-dash-nav bb-admin-subnav">
                            <li><a href="{{ route('admin.pages.edit', 'home') }}" class="{{ request()->routeIs('admin.pages*') && request()->route('pageKey') === 'home' ? 'active' : '' }}"><i class="bi bi-house"></i><span>Beranda</span></a></li>
                            <li><a href="{{ route('admin.pages.edit', 'about') }}" class="{{ request()->routeIs('admin.pages*') && request()->route('pageKey') === 'about' ? 'active' : '' }}"><i class="bi bi-info-circle"></i><span>Tentang Kami</span></a></li>
                            <li><a href="{{ route('admin.pages.edit', 'favorites') }}" class="{{ request()->routeIs('admin.pages*') && request()->route('pageKey') === 'favorites' ? 'active' : '' }}"><i class="bi bi-star"></i><span>Menu Favorit</span></a></li>
                            <li><a href="{{ route('admin.pages.edit', 'promotions') }}" class="{{ request()->routeIs('admin.pages*') && request()->route('pageKey') === 'promotions' ? 'active' : '' }}"><i class="bi bi-megaphone"></i><span>Promo</span></a></li>
                            <li><a href="{{ route('admin.pages.edit', 'contact') }}" class="{{ request()->routeIs('admin.pages*') && request()->route('pageKey') === 'contact' ? 'active' : '' }}"><i class="bi bi-envelope"></i><span>Kontak</span></a></li>
                        </ul>
                    </details>
                @endif

                <ul class="bb-dash-nav bb-admin-secondary-nav">
                    <li><a href="{{ route('admin.history') }}" class="{{ request()->routeIs('admin.history*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i><span>Riwayat Aktivitas</span></a></li>
                    <li><a href="{{ route('admin.sessions') }}" class="{{ request()->routeIs('admin.sessions*') ? 'active' : '' }}"><i class="bi bi-shield-check"></i><span>Monitoring Sesi</span></a></li>
                    @if(auth()->user()->role === 'admin')
                        <li><a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i><span>Kelola Pengguna</span></a></li>
                    @endif
                    <li><a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}"><i class="bi bi-sliders"></i><span>Pengaturan</span></a></li>
                </ul>
            </div>

            <div class="bb-admin-sidebar-bottom">
                <a href="{{ route('home') }}" class="bb-admin-store-link"><i class="bi bi-arrow-up-right-square"></i><span>Lihat website</span></a>
                <button type="button" class="bb-admin-logout" data-bs-toggle="modal" data-bs-target="#logout-confirm-modal"><i class="bi bi-box-arrow-left"></i><span>Keluar dari akun</span></button>
            </div>
        </aside>

        <button type="button" class="bb-admin-sidebar-backdrop" data-admin-sidebar-close aria-label="Tutup menu"></button>

        {{-- MAIN --}}
        <div class="bb-admin-workspace">
            <header class="bb-admin-topbar">
                <button type="button" class="bb-admin-menu-toggle" data-admin-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Buka menu navigasi">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <div class="bb-admin-topbar-title">
                    <span>WORKSPACE <span aria-hidden="true">/</span> BENBEN CO.</span>
                    <strong>{{ ucfirst(auth()->user()->role) }} Panel</strong>
                </div>
                <a href="{{ route('admin.settings') }}" class="bb-dashboard-profile ms-auto" aria-label="Pengaturan akun {{ auth()->user()->nama }}">
                    <span class="bb-dashboard-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->nama, 0, 1) }}</span>
                    <span class="bb-dashboard-profile-copy"><strong>{{ auth()->user()->nama }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </a>
                <x-theme-toggle />
            </header>
            <div class="bb-dash-main">
                @if (session('success') || session('error'))
                    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;">
                        <div class="toast bb-toast {{ session('error') ? 'is-error' : '' }}" role="status"
                             aria-live="polite" aria-atomic="true" data-admin-toast>
                            <div class="toast-header">
                                <strong class="me-auto">{{ session('error') ? 'Perlu diperiksa' : 'BENBEN CO. Bakery' }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button>
                            </div>
                            <div class="toast-body">{{ session('success') ?? session('error') }}</div>
                        </div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;" data-order-toast-container aria-live="polite"></div>
    <x-logout-modal :action="route('admin.logout')" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    <script src="{{ asset('js/form-inputs.js') }}"></script>
    <script>
        const sidebar = document.querySelector('.bb-admin-shell .bb-dash-sidebar');
        const sidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
        const sidebarClose = document.querySelector('[data-admin-sidebar-close]');

        if (sidebar) {
            sidebar.id = 'admin-sidebar';
        }

        function setAdminSidebarOpen(isOpen) {
            document.body.classList.toggle('bb-admin-menu-open', isOpen);
            sidebarToggle?.setAttribute('aria-expanded', String(isOpen));
        }

        const sidebarScrollArea = sidebar?.querySelector('.bb-admin-sidebar-scroll');
        const sidebarScrollKey = 'benben-admin-sidebar-scroll';
        const savedSidebarScroll = sessionStorage.getItem(sidebarScrollKey);
        if (sidebarScrollArea) {
            requestAnimationFrame(() => {
                if (savedSidebarScroll !== null) {
                    sidebarScrollArea.scrollTop = Number(savedSidebarScroll) || 0;
                }
                const activeItem = sidebar.querySelector('a.active, summary.active');
                if (!activeItem) return;
                const areaBounds = sidebarScrollArea.getBoundingClientRect();
                const activeBounds = activeItem.getBoundingClientRect();
                if (activeBounds.top < areaBounds.top) {
                    sidebarScrollArea.scrollTop -= areaBounds.top - activeBounds.top;
                } else if (activeBounds.bottom > areaBounds.bottom) {
                    sidebarScrollArea.scrollTop += activeBounds.bottom - areaBounds.bottom;
                }
            });
        }
        const saveSidebarScroll = () => {
            if (sidebarScrollArea) {
                sessionStorage.setItem(sidebarScrollKey, String(sidebarScrollArea.scrollTop));
            }
        };
        window.addEventListener('pagehide', saveSidebarScroll);

        sidebarToggle?.addEventListener('click', () => {
            setAdminSidebarOpen(!document.body.classList.contains('bb-admin-menu-open'));
        });
        sidebarClose?.addEventListener('click', () => setAdminSidebarOpen(false));
        sidebar?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                const target = new URL(link.href, window.location.href);
                if (target.origin === window.location.origin && !link.hasAttribute('download') && (!link.target || link.target === '_self')) {
                    saveSidebarScroll();
                }
                setAdminSidebarOpen(false);
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setAdminSidebarOpen(false);
            }
        });

        document.querySelectorAll('[data-admin-toast]').forEach((toast) => {
            new bootstrap.Toast(toast, { delay: 4500 }).show();
        });
        document.querySelectorAll('[data-loading-button]').forEach((button) => {
            button.form?.addEventListener('submit', () => {
                button.disabled = true;
                button.dataset.originalText = button.textContent.trim();
                button.textContent = 'Memproses...';
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
