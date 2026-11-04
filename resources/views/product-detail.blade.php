@extends('layouts.app')

@section('title', $product['name'] . ' — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header" style="text-align:left;padding:24px 0;">
        <div class="container-bb">
            <div class="bb-breadcrumb">
                <a href="{{ url('/') }}">Beranda</a> / <a href="{{ url('/products') }}">Produk</a> / {{ $product['name'] }}
            </div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-5">
                {{-- GALLERY --}}
                <div class="col-lg-6">
                    <div class="bb-rounded-lg bb-shadow bb-product-lightbox" style="overflow:hidden;aspect-ratio:1/1;margin-bottom:16px;">
                        <img src="{{ route('media.show', ['path' => $product['image']]) }}" alt="{{ $product['name'] }}" data-lightbox
                             style="width:100%;height:100%;object-fit:cover;cursor:zoom-in;">
                    </div>
                </div>

                {{-- INFO --}}
                <div class="col-lg-6">
                    <span class="eyebrow">{{ $product['category'] }}</span>
                    <h1 class="section-title" style="margin-top:10px;">{{ $product['name'] }}</h1>

                    <div class="d-flex align-items-center gap-3 mt-2 mb-3">
                        @if($product['reviews'] > 0)
                            <span class="bb-review-stars bb-review-stars-compact" aria-label="Rating {{ number_format($product['rating'], 1) }} dari 5">
                                @for($star = 1; $star <= 5; $star++)
                                    <span>{{ $star <= round($product['rating']) ? '★' : '☆' }}</span>
                                @endfor
                            </span>
                            <span class="section-sub" style="font-size:.88rem;">
                                {{ number_format($product['rating'], 1) }} / 5 ({{ number_format($product['reviews']) }} ulasan)
                            </span>
                        @else
                            <span class="bb-review-stars bb-review-stars-compact" aria-label="Belum ada rating">
                                @for($star = 1; $star <= 5; $star++)<span>☆</span>@endfor
                            </span>
                            <span class="section-sub" style="font-size:.88rem;">Belum ada ulasan</span>
                        @endif
                    </div>

                    <div class="bb-price" style="font-size:1.7rem;margin-bottom:18px;">
                        @if($product['oldPrice'])
                            <small style="font-size:1rem;">Rp{{ number_format($product['oldPrice'], 0, ',', '.') }}</small>
                        @endif
                        Rp{{ number_format($product['price'], 0, ',', '.') }}
                    </div>

                    <p class="section-sub" style="max-width:100%;">{{ $product['description'] }}</p>

                    @php($stockAvailable = max(0, $product['stock'] - $product['cartQty']))
                    <div class="mb-3">
                        <span class="bb-product-stock {{ $stockAvailable <= 5 ? 'low' : '' }}"
                              data-product-stock-display
                              data-product-id="{{ $product['id'] }}"
                              aria-live="polite">
                            @if($stockAvailable < 1)
                                Stok habis untuk ditambahkan
                            @else
                                <i class="bi bi-box-seam"></i> Stok tersisa <span data-stock-count>{{ number_format($stockAvailable, 0, ',', '.') }}</span>
                            @endif
                        </span>
                    </div>

                    <form method="POST" action="{{ route('cart.add') }}" data-cart-add-form>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                        <div class="d-flex align-items-center gap-3 mb-2" data-detail-qty
                             data-product-id="{{ $product['id'] }}"
                             data-stock="{{ $product['stock'] }}"
                             data-cart-qty="{{ $product['cartQty'] }}">
                            <div class="bb-cart-qty">
                                <button type="button" data-qty-minus aria-label="Kurangi jumlah">−</button>
                                <input type="number" name="qty" value="1" min="1" max="{{ max(1, $stockAvailable) }}" data-qty-input required @disabled($stockAvailable < 1)>
                                <button type="button" data-qty-plus aria-label="Tambah jumlah" @disabled($stockAvailable < 2)>+</button>
                            </div>
                            <span class="section-sub" style="font-size:.85rem;">
                                Tersedia ditambahkan: <strong data-detail-available-count>{{ number_format($stockAvailable, 0, ',', '.') }}</strong> pcs
                            </span>
                        </div>
                        <small class="d-block section-sub mb-4" data-detail-after-count>
                            Setelah jumlah pilihan, stok tersisa {{ number_format(max(0, $stockAvailable - ($stockAvailable > 0 ? 1 : 0)), 0, ',', '.') }} pcs.
                        </small>

                        <div class="d-flex gap-3 flex-wrap">
                            <button type="submit" class="btn-bb btn-bb-outline" data-loading-button data-cart-add-button @disabled($stockAvailable < 1)>
                                <i class="bi bi-basket3"></i> Tambah ke Keranjang
                            </button>
                            <button type="submit" name="go_to_checkout" value="1" class="btn-bb btn-bb-primary" data-loading-button @disabled($stockAvailable < 1)>
                                Beli Sekarang <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" class="btn-icon-circle" title="Tambah ke favorit">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                        <small class="d-block text-success mt-2" data-cart-feedback aria-live="polite" hidden></small>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="bb-section-sm bb-product-reviews" id="reviews">
        <div class="container-bb">
            <div class="bb-product-reviews-heading">
                <div>
                    <span class="eyebrow">Ulasan pelanggan</span>
                    <h2 class="section-title">Cerita setelah mencicipi</h2>
                </div>
                <div class="bb-review-summary">
                    <span class="bb-review-stars" aria-label="Rating {{ number_format($product['rating'], 1) }} dari 5">
                        @for($star = 1; $star <= 5; $star++)
                            <span>{{ $star <= round($product['rating']) ? '★' : '☆' }}</span>
                        @endfor
                    </span>
                    <strong>{{ number_format($product['rating'], 1) }} / 5</strong>
                    <small>{{ number_format($product['reviews']) }} ulasan</small>
                </div>
            </div>

            <div class="bb-product-reviews-grid">
                <div class="bb-product-review-list">
                    <h3 class="bb-panel-title">Ulasan Pelanggan <small>({{ number_format($product['reviews']) }})</small></h3>
                    @forelse($reviews as $review)
                        <article class="bb-product-review-card">
                            <div class="bb-product-review-card-top">
                                <div class="bb-product-review-author">
                                    <span>{{ mb_substr($review->nama_customer, 0, 1) }}</span>
                                    <strong>{{ $review->nama_customer }}</strong>
                                </div>
                                <time datetime="{{ \Illuminate\Support\Carbon::parse($review->created_at)->toDateString() }}">
                                    {{ \Illuminate\Support\Carbon::parse($review->created_at)->translatedFormat('d M Y') }}
                                </time>
                            </div>
                            <div class="bb-review-stars" aria-label="Rating {{ $review->rating }} dari 5">
                                @for($star = 1; $star <= 5; $star++)
                                    <span>{{ $star <= $review->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p>{{ $review->komentar ?: 'Pelanggan memberi penilaian untuk produk ini.' }}</p>
                        </article>
                    @empty
                        <div class="bb-product-review-empty">
                            <span aria-hidden="true">☆</span>
                            <p>Belum ada ulasan untuk produk ini. Jadilah pelanggan pertama yang berbagi cerita.</p>
                        </div>
                    @endforelse
                </div>

                <div class="bb-product-review-form-card">
                    <h3 class="bb-panel-title">Tulis Ulasan</h3>
                    <p class="section-sub">Bagikan pengalamanmu dengan {{ $product['name'] }}.</p>
                    <form method="POST" action="{{ route('product.review', $product['id']) }}" class="bb-product-review-form">
                        @csrf
                        <div>
                            <label for="review-name" class="bb-form-label">Nama Anda</label>
                            <input id="review-name" name="nama_customer" class="bb-input" value="{{ old('nama_customer') }}" maxlength="100" required>
                            @error('nama_customer')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <fieldset>
                            <legend class="bb-form-label">Rating</legend>
                            <div class="bb-rating-input" role="radiogroup" aria-label="Pilih rating dari satu sampai lima bintang">
                                @for($star = 5; $star >= 1; $star--)
                                    <input type="radio" id="review-rating-{{ $star }}" name="rating" value="{{ $star }}"
                                           @checked((int) old('rating') === $star) required>
                                    <label for="review-rating-{{ $star }}" title="{{ $star }} bintang" aria-label="{{ $star }} bintang">★</label>
                                @endfor
                            </div>
                            @error('rating')<small class="text-danger">{{ $message }}</small>@enderror
                        </fieldset>
                        <div>
                            <label for="review-comment" class="bb-form-label">Komentar (opsional)</label>
                            <textarea id="review-comment" name="komentar" class="bb-input" rows="3" maxlength="2000"
                                      placeholder="Ceritakan pengalaman Anda dengan produk ini...">{{ old('komentar') }}</textarea>
                            @error('komentar')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <button type="submit" class="btn-bb btn-bb-primary w-100">Kirim Ulasan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- RELATED PRODUCTS --}}
    <section class="bb-section-sm bg-cream">
        <div class="container-bb">
            <div class="section-head">
                <span class="eyebrow">Rekomendasi</span>
                <h2 class="section-title">Produk Terkait</h2>
            </div>
            <div class="bb-related-grid">
                @forelse($related as $relatedProduct)
                    <x-product-card
                        :productId="$relatedProduct->id_product" :name="$relatedProduct->nama" :slug="$relatedProduct->slug" :image="$relatedProduct->gambar"
                        :category="$relatedProduct->category?->slug ?? ''" :price="$relatedProduct->harga" :oldPrice="null"
                        :rating="$relatedProduct->ratings_avg_rating" :stock="$relatedProduct->stok" :badge="null" />
                @empty
                    <p class="section-sub">Belum ada produk terkait.</p>
                @endforelse
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/cart.js') }}"></script>
@endpush
