@extends('layouts.admin')

@section('title', 'Kelola Produk — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Produk</h2>
        </div>
        <button type="button" class="btn-bb btn-bb-primary" data-bs-toggle="modal" data-bs-target="#productModal" data-add-product data-action="{{ route('admin.products.store') }}">
            <i class="bi bi-plus-lg"></i> Tambah Produk
        </button>
    </div>

    <form method="GET" class="bb-filter-bar">
        <div class="bb-filter-field">
            <label for="admin-product-search" class="bb-form-label">Cari nama produk</label>
            <input id="admin-product-search" type="search" name="search" class="bb-input" value="{{ request('search') }}" placeholder="Nama produk" maxlength="100">
        </div>
        <div class="bb-filter-field">
            <label for="product-category-filter" class="bb-form-label">Kategori</label>
            <select id="product-category-filter" name="category" class="bb-input">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id_category }}" @selected(request('category') == $category->id_category)>{{ $category->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-bb btn-bb-outline btn-bb-sm" type="submit">Cari</button>
        @if(request()->hasAny(['search', 'category']))
            <a class="btn-bb btn-bb-outline btn-bb-sm" href="{{ route('admin.products') }}">Reset</a>
        @endif
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr><th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><img src="{{ route('media.show', ['path' => $product->gambar ?: 'images/logo.png']) }}" alt="{{ $product->nama }}" class="thumb"></td>
                            <td>{{ $product->nama }}</td>
                            <td>{{ $product->category?->nama_kategori ?? '—' }}</td>
                            <td>Rp{{ number_format($product->harga, 0, ',', '.') }}</td>
                            <td>{{ number_format($product->stok, 0, ',', '.') }} pcs</td>
                            <td>
                                <span class="bb-status {{ $product->status === 'active' ? 'bb-status-done' : 'bb-status-cancel' }}">{{ $product->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td>
                                <div class="bb-action-icons">
                                    <a href="{{ route('product.detail', $product->slug) }}" title="Lihat {{ $product->nama }}" aria-label="Lihat produk {{ $product->nama }}"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                    <button type="button" title="Edit {{ $product->nama }}" aria-label="Edit {{ $product->nama }}" data-bs-toggle="modal" data-bs-target="#productModal"
                                        data-edit-product
                                        data-action="{{ route('admin.products.update', $product->id_product) }}"
                                        data-name="{{ $product->nama }}" data-category="{{ $product->category_id }}"
                                        data-price="{{ $product->harga }}" data-stock="{{ $product->stok }}"
                                        data-status="{{ $product->status }}" data-description="{{ $product->deskripsi }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" title="Hapus" aria-label="Hapus {{ $product->nama }}"
                                        data-bs-toggle="modal" data-bs-target="#crud-delete-confirm-modal"
                                        data-crud-delete data-delete-action="{{ route('admin.products.delete', $product->id_product) }}"
                                        data-delete-name="{{ $product->nama }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-pagination :currentPage="$products->currentPage()" :totalPages="$products->lastPage()" />
        </div>
    </div>

    {{-- ADD/EDIT PRODUCT MODAL --}}
    <div class="modal fade bb-modal-bb" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bb-crud-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" data-product-form>
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-product-method>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="product-image" class="bb-form-label">Foto Produk (opsional)</label>
                                <div class="bb-upload-drop">
                                    <i class="bi bi-cloud-arrow-up" style="font-size:1.6rem;"></i>
                                    <input id="product-image" type="file" name="gambar" class="bb-input mt-2" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <small class="form-text">JPG, PNG, atau WEBP, maksimal 5 MB.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="product-name" class="bb-form-label">Nama Produk</label>
                                <input id="product-name" type="text" name="nama" class="bb-input" placeholder="Nama produk" required maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label for="product-category" class="bb-form-label">Kategori</label>
                                <select id="product-category" name="category_id" class="bb-input" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}">{{ $category->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="product-price" class="bb-form-label">Harga produk (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="product-price" type="text" name="harga" class="bb-input" placeholder="56.000" inputmode="numeric" data-numeric-input data-money-input required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="product-stock" class="bb-form-label">Stok (pcs)</label>
                                <input id="product-stock" type="text" name="stok" class="bb-input" placeholder="0" inputmode="numeric" data-numeric-input required>
                            </div>
                            <div class="col-12">
                                <label for="product-description" class="bb-form-label">Deskripsi produk</label>
                                <textarea id="product-description" name="deskripsi" class="bb-input" rows="3" placeholder="Deskripsi produk"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="product-status" class="bb-form-label">Status produk</label>
                                <select id="product-status" name="status" class="bb-input" required><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-bb btn-bb-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-bb btn-bb-primary">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-crud-delete-modal />

@endsection
