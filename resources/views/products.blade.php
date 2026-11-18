@extends('layouts.app')

@section('title', 'Produk — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Katalog</span>
            <h1 class="section-title mt-2">Semua Produk Kami</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Produk</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-4">

                {{-- SIDEBAR FILTER --}}
                <div class="col-lg-3">
                    <form method="GET" action="{{ route('products') }}" class="bb-filter-card">
                        <label for="product-search" class="form-label fw-semibold"><i class="bi bi-search me-1"></i> Cari Produk</label>
                        <input id="product-search" type="search" name="search" class="bb-input mb-3"
                               value="{{ request('search') }}" placeholder="Cheese Roll, brownies...">
                        <label for="product-category" class="form-label fw-semibold">Kategori</label>
                        <select id="product-category" name="category" class="bb-input">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                    {{ $category->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm w-100 mt-3">Terapkan Filter</button>
                        @if(request()->hasAny(['search', 'category']))
                            <a href="{{ route('products') }}" class="btn-bb btn-bb-outline btn-bb-sm w-100 mt-2">Hapus Filter</a>
                        @endif
                    </form>
                </div>

                {{-- PRODUCT GRID --}}
                <div class="col-lg-9">
                    <div class="bb-toolbar">
                        <span class="section-sub">Menampilkan {{ $products->total() }} produk</span>
                        <select class="bb-input" style="width:auto;" data-product-sort>
                            <option value="relevance">Urutkan: Relevan</option>
                            <option value="price-asc">Harga Terendah</option>
                            <option value="price-desc">Harga Tertinggi</option>
                            <option value="name-asc">Nama A-Z</option>
                        </select>
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
            </div>
        </div>
    </section>

@endsection
