<nav class="bb-navbar">
    <div class="container-bb bb-navbar-inner">

        <a href="{{ route('home') }}" class="bb-logo">
            <span class="mark"><i class="bi bi-egg-fried"></i></span>
            <span>
                BENBEN CO.
                <small>BAKERY · ANTAPANI</small>
            </span>
        </a>

        <ul class="bb-nav-links">
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
            <li><a href="{{ route('products') }}" class="{{ request()->routeIs('products', 'product.detail') ? 'active' : '' }}">Products</a></li>
            <li><a href="{{ route('home') }}#gallery">Gallery</a></li>
            <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
        </ul>

        <div class="bb-nav-actions">
            @auth
                @if(auth()->user()->role === 'customer')
                    <a href="{{ route('customer.dashboard') }}" class="bb-nav-account-link" aria-label="Dashboard pelanggan"><i class="bi bi-person"></i><span>Akun Saya</span></a>
                @endif
            @else
                <a href="{{ route('login') }}" class="bb-nav-account-link" aria-label="Masuk sebagai pelanggan"><i class="bi bi-person"></i><span>Masuk</span></a>
            @endauth
            <a href="{{ route('cart') }}" class="btn-icon-circle bb-cart-btn" aria-label="Keranjang">
                <i class="bi bi-basket3"></i>
                <span class="bb-cart-badge">{{ collect(session('cart', []))->sum('qty') }}</span>
            </a>
            <x-theme-toggle />
            <a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm d-none d-md-inline-flex">
                Lihat Menu
            </a>
            <button class="bb-burger" type="button" aria-label="Menu" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

    <div class="bb-mobile-menu">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('about') }}">About the Bakery</a>
        <a href="{{ route('products') }}">Products</a>
        <a href="{{ route('home') }}#gallery">Gallery</a>
        <a href="{{ route('contact') }}">Contact</a>
        @auth
            @if(auth()->user()->role === 'customer')
                <a href="{{ route('customer.dashboard') }}">Dashboard pelanggan</a>
            @endif
        @else
            <a href="{{ route('login') }}">Masuk untuk memesan</a>
        @endauth
        <a href="{{ route('products') }}">Lihat Menu</a>
    </div>
</nav>
