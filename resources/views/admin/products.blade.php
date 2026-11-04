@extends('layouts.admin')

@section('title', 'Kelola Produk — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Produk</h2>
        </div>
        <button type="button" class="btn-bb btn-bb-primary" data-bs-toggle="modal" data-bs-target="#productModal" data-add-product data-action="{{ route('admin.products.store') }}">
            <i class="bi bi-plus-lg"></i> Tambah Produk
        </button>
    </div>

    <form method="GET" class="bb-filter-bar">
        <input type="search" name="search" class="bb-input" value="{{ request('search') }}" placeholder="Cari produk..." style="max-width:260px;">
        <select name="category" class="bb-input" style="width:auto;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id_category }}" @selected(request('category') == $category->id_category)>{{ $category->nama_kategori }}</option>
            @endforeach
        </select>
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
                                    <a href="{{ route('product.detail', $product->slug) }}" title="Lihat produk"><i class="bi bi-eye"></i></a>
                                    <button type="button" title="Edit" data-bs-toggle="modal" data-bs-target="#productModal"
                                        data-edit-product
                                        data-action="{{ route('admin.products.update', $product->id_product) }}"
                                        data-name="{{ $product->nama }}" data-category="{{ $product->category_id }}"
                                        data-price="{{ $product->harga }}" data-stock="{{ $product->stok }}"
                                        data-status="{{ $product->status }}" data-description="{{ $product->deskripsi }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.products.delete', $product->id_product) }}" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"><i class="bi bi-trash3"></i></button>
                                    </form>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
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
                                <label class="bb-form-label">Foto Produk</label>
                                <div class="bb-upload-drop">
                                    <i class="bi bi-cloud-arrow-up" style="font-size:1.6rem;"></i>
                                    <input type="file" name="gambar" class="bb-input mt-2" accept="image/jpeg,image/png,image/webp">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="bb-form-label">Nama Produk</label>
                                <input type="text" name="nama" class="bb-input" placeholder="Nama produk" required maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label class="bb-form-label">Kategori</label>
                                <select name="category_id" class="bb-input" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}">{{ $category->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="bb-form-label">Harga</label>
                                <input type="number" name="harga" class="bb-input" placeholder="0" min="0" step="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="bb-form-label">Stok</label>
                                <input type="number" name="stok" class="bb-input" placeholder="0" min="0" step="1" required>
                            </div>
                            <div class="col-12">
                                <label class="bb-form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="bb-input" rows="3" placeholder="Deskripsi produk"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="bb-form-label">Status</label>
                                <select name="status" class="bb-input" required><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
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

@endsection
