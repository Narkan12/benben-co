@extends('layouts.admin')

@section('title', 'Stok Produk — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
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
                                <form method="POST" action="{{ route('admin.inventory.update', $product->id_product) }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="stok" class="bb-input" value="{{ $product->stok }}" min="0" step="1" required aria-label="Stok baru untuk {{ $product->nama }}" style="max-width:100px">
                                    <input type="text" name="keterangan" class="bb-input" placeholder="Catatan (opsional)" maxlength="255" aria-label="Catatan perubahan stok">
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
