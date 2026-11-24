@extends('layouts.app')

@section('title', 'Produk — Bakery BenBen Co.')

@section('content')

    @php($showLiveCart = auth()->check() && auth()->user()->role === 'customer')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Katalog</span>
            <h1 class="section-title mt-2">Semua Produk Kami</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Produk</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <form method="GET" action="{{ route('products') }}" class="bb-catalog-filter">
                <div class="bb-filter-field bb-catalog-search-field">
                    <label for="product-search" class="form-label fw-semibold"><i class="bi bi-search me-1"></i> Cari Produk</label>
                    <input id="product-search" type="search" name="search" class="bb-input"
                           value="{{ request('search') }}" placeholder="Cheese Roll, brownies...">
                </div>
                <div class="bb-filter-field bb-catalog-category-field">
                    <label for="product-category" class="form-label fw-semibold">Kategori</label>
                    <select id="product-category" name="category" class="bb-input">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bb-catalog-filter-actions">
                    <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Terapkan Filter</button>
                    @if(request()->hasAny(['search', 'category']))
                        <a href="{{ route('products') }}" class="btn-bb btn-bb-outline btn-bb-sm">Hapus Filter</a>
                    @endif
                </div>
            </form>

            <div class="row g-4 align-items-start">
                {{-- PRODUCT GRID --}}
                <div class="{{ $showLiveCart ? 'col-lg-9' : 'col-12' }}">
                    <div class="bb-toolbar">
                        <span class="section-sub">Menampilkan {{ $products->total() }} produk</span>
                        <div class="bb-catalog-sort">
                            <label for="product-sort" class="form-label fw-semibold mb-0">Urutkan produk</label>
                            <select id="product-sort" class="bb-input" data-product-sort>
                                <option value="relevance">Urutkan: Relevan</option>
                                <option value="price-asc">Harga Terendah</option>
                                <option value="price-desc">Harga Tertinggi</option>
                                <option value="name-asc">Nama A-Z</option>
                            </select>
                        </div>
                    </div>

                    <div class="bb-products-grid">
                        @forelse($products as $product)
                            <x-product-card
                                :productId="$product->id_product" :name="$product->nama" :slug="$product->slug" :image="$product->gambar"
                                :category="$product->category?->slug ?? ''" :price="$product->harga" :oldPrice="null"
                                :rating="$product->ratings_avg_rating" :stock="$product->stok" :badge="null" />
                        @empty
                            <p class="section-sub">Produk tidak ditemukan.</p>
                        @endforelse
                    </div>

                    <div class="mt-5">
                        <x-pagination
                            :currentPage="$products->currentPage()"
                            :totalPages="$products->lastPage()"
                            :baseUrl="request()->url()" />
                    </div>
                </div>

                @if($showLiveCart)
                    <div class="col-lg-3">
                        <aside class="bb-panel bb-live-cart-card" data-live-cart data-summary-url="{{ route('cart.summary') }}">
                            <h2 class="bb-panel-title">Keranjang Belanja</h2>
                            <p class="section-sub mb-3"><span data-live-cart-count>Memuat...</span></p>
                            <div class="bb-live-cart-items" data-live-cart-items aria-live="polite">
                                <p class="section-sub mb-0">Memuat isi keranjang...</p>
                            </div>
                            <div class="bb-live-cart-subtotal">
                                <span>Subtotal</span>
                                <strong data-live-cart-subtotal>Rp0</strong>
                            </div>
                            <a href="{{ route('cart') }}" class="btn-bb btn-bb-primary w-100 mt-3">
                                Lihat Keranjang
                            </a>
                        </aside>
                    </div>
                @endif
            </div>
        </div>
    </section>

@endsection
