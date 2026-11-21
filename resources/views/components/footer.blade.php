<footer class="bb-footer">
    <div class="container-bb">
        <div class="bb-footer-grid">
            <div>
                <div class="bb-logo" style="color:var(--bb-theme-text);margin-bottom:14px;">
                    <span class="mark" style="background:var(--bb-choc);"><i class="bi bi-egg-fried"></i></span>
                    <span>Bakery BenBen Co.</span>
                </div>
                <p style="opacity:.8;max-width:280px;font-size:.92rem;">
                    Manisnya momen, dibuat dengan hati — brownies, cake, dan dessert premium yang dipanggang fresh setiap hari.
                </p>
                <div class="bb-footer-social">
                    <a href="https://www.instagram.com/benbenco.id/" target="_blank" rel="noopener noreferrer" aria-label="Instagram BENBEN CO."><i class="bi bi-instagram"></i></a>
                    @if($whatsappAdminNumber)
                        <a href="https://wa.me/{{ $whatsappAdminNumber }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp BENBEN CO."><i class="bi bi-whatsapp"></i></a>
                    @endif
                </div>
            </div>

            <div>
                <h5>Navigasi</h5>
                <ul class="bb-footer-links">
                    <li><a href="{{ url('/') }}">Beranda</a></li>
                    <li><a href="{{ url('/about') }}">Tentang Kami</a></li>
                    <li><a href="{{ url('/products') }}">Produk</a></li>
                    <li><a href="{{ url('/promotions') }}">Promo</a></li>
                    <li><a href="{{ url('/contact') }}">Kontak</a></li>
                </ul>
            </div>

            <div>
                <h5>Akun</h5>
                <ul class="bb-footer-links">
                    @auth
                        @if(auth()->user()->role === 'customer')
                            <li><a href="{{ route('customer.dashboard') }}">Dashboard Saya</a></li>
                            <li><a href="{{ route('customer.orders') }}">Pesanan Saya</a></li>
                            <li>
                                <button type="button" class="bb-footer-link-button" data-bs-toggle="modal" data-bs-target="#logout-confirm-modal">Logout</button>
                            </li>
                        @else
                            <li><a href="{{ route('admin.dashboard') }}">Dashboard Admin</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}">Login Pelanggan</a></li>
                        <li><a href="{{ route('register') }}">Daftar</a></li>
                        <li><a href="{{ route('admin.login') }}">Login Admin / Staf</a></li>
                    @endauth
                    <li><a href="{{ url('/cart') }}">Keranjang</a></li>
                </ul>
            </div>

            <div>
                <h5>Hubungi Kami</h5>
                <ul class="bb-footer-links">
                    <li><i class="bi bi-geo-alt me-1"></i> Antapani, Bandung</li>
                    @if($whatsappAdminNumber)
                        <li><i class="bi bi-whatsapp me-1"></i> +{{ $whatsappAdminNumber }}</li>
                    @endif
                    <li><i class="bi bi-envelope me-1"></i> hello@benbenco.id</li>
                    <li><i class="bi bi-clock me-1"></i> Setiap hari, 08.00–20.00</li>
                </ul>
            </div>
        </div>

        <div class="bb-footer-bottom">
            <span>&copy; {{ date('Y') }} Bakery BenBen Co. Seluruh hak cipta dilindungi.</span>
            <span>Dibuat dengan <i class="bi bi-suit-heart-fill" style="color:var(--bb-choc);"></i> di Bandung</span>
        </div>
    </div>
</footer>
