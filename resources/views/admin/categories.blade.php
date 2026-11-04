@extends('layouts.admin')

@section('title', 'Kelola Kategori — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Kategori</h2>
        </div>
        <button type="button" class="btn-bb btn-bb-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-add-category data-action="{{ route('admin.categories.store') }}">
            <i class="bi bi-plus-lg"></i> Tambah Kategori
        </button>
    </div>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Icon</th><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td><span class="bb-cat-icon" style="width:40px;height:40px;margin:0;font-size:1rem;"><i class="bi bi-tags"></i></span></td>
                            <td>{{ $cat->nama_kategori }}</td>
                            <td>{{ $cat->products_count }} produk</td>
                            <td>
                                <div class="bb-action-icons">
                                    <button type="button" title="Edit" data-bs-toggle="modal" data-bs-target="#categoryModal" data-edit-category data-action="{{ route('admin.categories.update', $cat->id_category) }}" data-name="{{ $cat->nama_kategori }}" data-slug="{{ $cat->slug }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('admin.categories.delete', $cat->id_category) }}" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" @disabled($cat->products_count > 0)><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4"><x-pagination :currentPage="$categories->currentPage()" :totalPages="$categories->lastPage()" /></div>
    </div>

    <div class="modal fade bb-modal-bb" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.categories.store') }}" data-category-form>
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-category-method>
                    <div class="modal-body">
                        <div class="bb-input-group">
                            <label class="bb-form-label">Nama Kategori</label>
                            <input type="text" name="nama_kategori" class="bb-input" placeholder="Nama kategori" required maxlength="100">
                        </div>
                        <div class="bb-input-group">
                            <label class="bb-form-label">Slug (opsional)</label>
                            <input type="text" name="slug" class="bb-input" placeholder="dibuat-otomatis">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-bb btn-bb-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-bb btn-bb-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
