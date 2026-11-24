@extends('layouts.admin')

@section('title', 'Stok Produk — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">Manajemen Stok</h2>
        </div>
    </div>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Produk</th><th>Stok Saat Ini</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->nama }}</td>
                            <td>{{ $product->stok === null ? 'Belum diketahui' : number_format($product->stok, 0, ',', '.').' pcs' }}</td>
                            <td>
                                @if($product->stok === null)
                                    <span class="bb-status bb-status-pending">Stok Belum Diketahui</span>
                                @elseif($product->stok === 0)
                                    <span class="bb-lowstock"><i class="bi bi-exclamation-triangle-fill"></i> Stok Habis</span>
                                @else
                                    <span class="bb-status bb-status-done">Stok Tersedia</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.inventory.update', $product->id_product) }}" class="d-flex flex-wrap gap-2 align-items-end">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="stock-{{ $product->id_product }}" class="bb-form-label mb-1">Stok baru (pcs)</label>
                                        <input id="stock-{{ $product->id_product }}" type="text" name="stok" class="bb-input" value="{{ $product->stok }}" inputmode="numeric" data-numeric-input required style="max-width:120px">
                                    </div>
                                    <div>
                                        <label for="stock-note-{{ $product->id_product }}" class="bb-form-label mb-1">Catatan perubahan (opsional)</label>
                                        <input id="stock-note-{{ $product->id_product }}" type="text" name="keterangan" class="bb-input" maxlength="255">
                                    </div>
                                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4"><x-pagination :currentPage="$products->currentPage()" :totalPages="$products->lastPage()" /></div>
    </div>

@endsection
