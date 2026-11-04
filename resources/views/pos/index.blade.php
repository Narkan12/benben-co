@extends('layouts.admin')

@section('title', 'Kasir POS — BENBEN CO. Bakery')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Point of Sale</span>
            <h1 class="section-title" style="font-size:1.7rem;">Kasir</h1>
        </div>
        <span class="section-sub">Transaksi langsung di toko</span>
    </div>

    <div class="row g-4">
        <section class="col-lg-7" aria-label="Daftar produk">
            <div class="bb-panel">
                <label for="pos-search" class="bb-form-label">Cari produk</label>
                <input id="pos-search" type="search" class="bb-input mb-3" placeholder="Ketik nama produk...">
                <div class="row g-3" id="pos-products">
                    @forelse($products as $product)
                        <div class="col-sm-6 col-xl-4" data-pos-product
                             data-id="{{ $product->id_product }}"
                             data-name="{{ $product->nama }}"
                             data-price="{{ $product->harga }}"
                             data-stock="{{ $product->stok }}">
                            <button type="button" class="bb-panel w-100 h-100 text-start p-2" data-pos-add
                                    style="border:1px solid var(--bb-line);">
                                <img src="{{ route('media.show', ['path' => $product->gambar ?: 'images/products/placeholder.jpg']) }}"
                                     alt="{{ $product->nama }}" loading="lazy"
                                     style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px;">
                                <strong class="d-block mt-2">{{ $product->nama }}</strong>
                                <span class="section-sub">Rp{{ number_format($product->harga, 0, ',', '.') }}</span>
                                <small class="d-block" data-pos-stock-display aria-live="polite">Stok tersisa: {{ number_format($product->stok, 0, ',', '.') }} pcs</small>
                                <small class="d-block text-muted" data-pos-cart-quantity>Di keranjang: 0 pcs</small>
                            </button>
                        </div>
                    @empty
                        <p class="section-sub">Belum ada produk aktif dengan stok tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="col-lg-5" aria-label="Keranjang transaksi">
            <form method="POST" action="{{ route('admin.pos.store') }}" class="bb-panel" id="pos-form">
                @csrf
                <h2 class="bb-panel-title">Transaksi Baru</h2>
                <label for="pos-customer" class="bb-form-label">Nama pelanggan</label>
                <input id="pos-customer" name="nama" class="bb-input mb-3" value="{{ old('nama', 'Pelanggan Toko') }}" required maxlength="100">
                <label for="pos-phone" class="bb-form-label">WhatsApp (opsional)</label>
                <input id="pos-phone" name="no_whatsapp" class="bb-input mb-3" value="{{ old('no_whatsapp') }}" maxlength="20">

                <div id="pos-cart" class="mb-3">
                    <p class="section-sub" data-pos-empty>Belum ada produk dipilih.</p>
                </div>
                <div id="pos-items"></div>
                <div class="bb-summary-row total"><span>Total</span><span id="pos-total">Rp0</span></div>

                <label for="pos-payment" class="bb-form-label mt-3">Pembayaran</label>
                <select id="pos-payment" name="payment_method" class="bb-input mb-3" required>
                    <option value="cash">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
                <label for="pos-received" class="bb-form-label">Jumlah dibayar</label>
                <input id="pos-received" type="number" name="amount_received" class="bb-input" min="0" step="1" value="0" required>
                <div class="bb-summary-row mt-2"><span>Kembalian</span><span id="pos-change">Rp0</span></div>
                <button type="submit" class="btn-bb btn-bb-primary w-100 mt-3" data-loading-button>
                    Simpan Transaksi
                </button>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pos.js') }}"></script>
@endpush
