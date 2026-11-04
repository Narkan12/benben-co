@props([
    'name' => 'Nama Produk',
    'productId' => null,
    'slug' => '#',
    'image' => 'images/products/placeholder.jpg',
    'category' => 'brownies',
    'price' => 45000,
    'oldPrice' => null,
    'rating' => null,
    'stock' => 12,
    'badge' => null, // e.g. 'Best Seller', 'Baru'
])

@php
    $cartQuantity = $productId ? (int) session('cart.'.$productId.'.qty', 0) : 0;
    $stockRemaining = max(0, (int) $stock - $cartQuantity);
@endphp

<div class="bb-product-card"
     data-product-card
     data-product-id="{{ $productId }}"
     data-product-stock="{{ $stock }}"
     data-stock-remaining="{{ $stockRemaining }}"
     data-product-category="{{ $category }}"
     data-product-name="{{ $name }}"
     data-product-price="{{ $price }}">

    <div class="bb-product-media">
        <a href="{{ url('/products/' . $slug) }}">
            <img src="{{ route('media.show', ['path' => $image]) }}" alt="{{ $name }}" loading="lazy">
        </a>

        @if($badge)
            <span class="bb-badge">{{ $badge }}</span>
        @endif

        <button type="button" class="bb-wishlist-btn" aria-label="Tambah ke favorit">
            <i class="bi bi-heart"></i>
        </button>
    </div>

    <div class="bb-product-body">
        @if($rating !== null && $rating > 0)
            <div class="bb-product-rating">
                <i class="bi bi-star-fill"></i> {{ number_format($rating, 1) }}
            </div>
        @endif

        <a href="{{ url('/products/' . $slug) }}" class="bb-product-name">{{ $name }}</a>

        <span class="bb-product-stock {{ $stockRemaining <= 5 ? 'low' : '' }}"
              data-product-stock-display
              data-product-id="{{ $productId }}"
              aria-live="polite">
            @if($stockRemaining < 1)
                Stok habis untuk ditambahkan
            @else
                <i class="bi bi-box-seam"></i> Stok tersisa <span data-stock-count>{{ number_format($stockRemaining, 0, ',', '.') }}</span>
            @endif
        </span>

        <div class="bb-product-footer">
            <div class="bb-price">
                @if($oldPrice)
                    <small>Rp{{ number_format($oldPrice, 0, ',', '.') }}</small>
                @endif
                Rp{{ number_format($price, 0, ',', '.') }}
            </div>
            <div class="bb-product-actions">
                @if($productId)
                    <form method="POST" action="{{ route('cart.add') }}" data-cart-add-form>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $productId }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn-icon-circle" title="Tambah ke keranjang" aria-label="Tambah {{ $name }} ke keranjang" data-cart-add-button @disabled($stockRemaining < 1)>
                            <i class="bi bi-basket3"></i>
                        </button>
                        <small class="d-block text-success mt-1" data-cart-feedback aria-live="polite" hidden></small>
                    </form>
                @endif
                <a href="{{ url('/products/' . $slug) }}" class="btn-bb btn-bb-outline btn-bb-sm">Detail</a>
            </div>
        </div>
    </div>
</div>
