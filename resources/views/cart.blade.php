@extends('layouts.app')

@section('title', 'Keranjang — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Keranjang</span>
            <h1 class="section-title mt-2">Keranjang Belanja Anda</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Keranjang</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-4">
                <div class="col-lg-8">
                    @php($hasItems = count($items) > 0)

                    <div data-cart-empty style="display:{{ $hasItems ? 'none' : 'block' }};text-align:center;padding:60px 20px;">
                        <i class="bi bi-basket3" style="font-size:3rem;color:var(--bb-beige);"></i>
                        <h4 class="mt-3">Keranjang Anda masih kosong</h4>
                        <p class="section-sub mx-auto">Yuk mulai belanja dan temukan bakery favoritmu.</p>
                        <a href="{{ url('/products') }}" class="btn-bb btn-bb-primary mt-2">Lihat Produk</a>
                    </div>

                    <div class="bb-panel" data-cart-table style="display:{{ $hasItems ? '' : 'none' }}">
                        @foreach($items as $item)
                            <div class="bb-cart-row" data-cart-row data-price="{{ $item['price'] }}" data-stock="{{ $item['stock'] }}">
                                <img src="{{ route('media.show', ['path' => $item['image']]) }}" alt="{{ $item['name'] }}">
                                <div class="bb-cart-name">
                                    <div style="font-weight:600;color:var(--bb-dark-choc);">{{ $item['name'] }}</div>
                                    <div class="section-sub" style="font-size:.82rem;">Rp{{ number_format($item['price'], 0, ',', '.') }} / pcs</div>
                                </div>
                                <form method="POST" action="{{ route('cart.update', $item['id_product']) }}" class="bb-cart-update-form" data-cart-update-form>
                                    @csrf
                                    <div class="bb-cart-qty">
                                        <button type="button" data-qty-minus aria-label="Kurangi jumlah">−</button>
                                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="{{ $item['stock'] }}" data-qty-input required>
                                        <button type="button" data-qty-plus aria-label="Tambah jumlah" @disabled($item['qty'] >= $item['stock'])>+</button>
                                    </div>
                                    <small class="section-sub" data-cart-stock-info aria-live="polite">
                                        Sisa stok setelah jumlah ini: {{ number_format(max(0, $item['stock'] - $item['qty']), 0, ',', '.') }} pcs
                                    </small>
                                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Simpan</button>
                                </form>
                                <div class="bb-price" data-line-total>Rp{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</div>
                                <form method="POST" action="{{ route('cart.remove', $item['id_product']) }}" data-cart-remove-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon-circle" data-cart-remove aria-label="Hapus {{ $item['name'] }} dari keranjang">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ url('/products') }}" class="btn-bb btn-bb-outline mt-4">
                        <i class="bi bi-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>

                {{-- ORDER SUMMARY --}}
                <div class="col-lg-4">
                    <div class="bb-summary-card">
                        <h5 class="bb-panel-title" style="margin-bottom:16px;">Ringkasan Pesanan</h5>
                        <div class="bb-summary-row"><span>Subtotal</span><span data-cart-subtotal>Rp0</span></div>
                        <div class="bb-summary-row"><span>Diskon</span><span data-cart-discount>Rp0</span></div>
                        <div class="bb-summary-row"><span>Ongkos Kirim</span><span data-cart-delivery>Rp0</span></div>
                        <div class="bb-summary-row total"><span>Total</span><span data-cart-total>Rp0</span></div>
                        <a href="{{ url('/checkout') }}" class="btn-bb btn-bb-primary w-100 mt-3">
                            Lanjut ke Checkout <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/cart.js') }}"></script>
@endpush
