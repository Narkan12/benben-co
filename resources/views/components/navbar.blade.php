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
                    <a href="{{ route('customer.dashboard') }}" class="btn-icon-circle" aria-label="Akun pelanggan"><i class="bi bi-person"></i></a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-icon-circle" aria-label="Login pelanggan"><i class="bi bi-person"></i></a>
            @endauth
            <a href="{{ route('cart') }}" class="btn-icon-circle bb-cart-btn" aria-label="Keranjang">
                <i class="bi bi-basket3"></i>
                <span class="bb-cart-badge">{{ collect(session('cart', []))->sum('qty') }}</span>
            </a>
            <a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm d-none d-md-inline-flex">
                Order Now
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
        <a href="{{ route('products') }}">Order Now</a>
    </div>
</nav>
