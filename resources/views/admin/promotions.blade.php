@extends('layouts.admin')

@section('title', 'Kelola Promo — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Promo</h2>
        </div>
        <button type="button" class="btn-bb btn-bb-primary" data-bs-toggle="modal" data-bs-target="#promoModal" data-add-promotion data-action="{{ route('admin.promotions.store') }}">
            <i class="bi bi-plus-lg"></i> Tambah Promo
        </button>
    </div>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Nama Promo</th><th>Kode</th><th>Diskon</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($promos as $promo)
                        @php
                            $now = now()->toDateString();
                            $promoStatus = $promo->status !== 'active' ? 'Nonaktif' : ($now < $promo->tanggal_mulai ? 'Akan datang' : ($now > $promo->tanggal_berakhir ? 'Berakhir' : 'Aktif'));
                            $statusClass = $promoStatus === 'Aktif' ? 'bb-status-done' : ($promoStatus === 'Berakhir' || $promoStatus === 'Nonaktif' ? 'bb-status-cancel' : 'bb-status-pending');
                        @endphp
                        <tr>
                            <td>{{ $promo->nama_promo }}</td>
                            <td><code>{{ $promo->kode_promo }}</code></td>
                            <td>{{ $promo->jenis_diskon === 'persen' ? number_format($promo->nilai_diskon, 0).'%' : 'Rp'.number_format($promo->nilai_diskon, 0, ',', '.') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($promo->tanggal_mulai)->format('d M Y') }}–{{ \Illuminate\Support\Carbon::parse($promo->tanggal_berakhir)->format('d M Y') }}</td>
                            <td><span class="bb-status {{ $statusClass }}">{{ $promoStatus }}</span></td>
                            <td>
                                <div class="bb-action-icons">
                                    <button type="button" title="Edit" data-bs-toggle="modal" data-bs-target="#promoModal" data-edit-promotion
                                        data-action="{{ route('admin.promotions.update', $promo->id_promo) }}"
                                        data-name="{{ $promo->nama_promo }}" data-code="{{ $promo->kode_promo }}"
                                        data-type="{{ $promo->jenis_diskon }}" data-value="{{ $promo->nilai_diskon }}"
                                        data-start="{{ substr($promo->tanggal_mulai, 0, 10) }}"
                                        data-end="{{ substr($promo->tanggal_berakhir, 0, 10) }}" data-status="{{ $promo->status }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.promotions.delete', $promo->id_promo) }}" onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Belum ada promo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4"><x-pagination :currentPage="$promos->currentPage()" :totalPages="$promos->lastPage()" /></div>
    </div>

    <div class="modal fade bb-modal-bb" id="promoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Promo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form method="POST" action="{{ route('admin.promotions.store') }}" data-promotion-form>
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-promotion-method>
                    <div class="modal-body">
                        <div class="bb-input-group">
                            <label class="bb-form-label">Nama Promo</label>
                            <input type="text" name="nama_promo" class="bb-input" required maxlength="100">
                        </div>
                        <div class="bb-input-group">
                            <label class="bb-form-label">Kode Promo</label>
                            <input type="text" name="kode_promo" class="bb-input" required maxlength="50" pattern="[A-Za-z0-9_-]+">
                        </div>
                        <div class="row g-3">
                            <div class="col-6 bb-input-group">
                                <label class="bb-form-label">Jenis Diskon</label>
                                <select name="jenis_diskon" class="bb-input" required><option value="persen">Persen</option><option value="nominal">Nominal (Rp)</option></select>
                            </div>
                            <div class="col-6 bb-input-group">
                                <label class="bb-form-label">Nilai Diskon</label>
                                <input type="number" name="nilai_diskon" class="bb-input" min="1" step="1" required>
                            </div>
                            <div class="col-6 bb-input-group">
                                <label class="bb-form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="bb-input" required>
                            </div>
                            <div class="col-6 bb-input-group">
                                <label class="bb-form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_berakhir" class="bb-input" required>
                            </div>
                            <div class="col-12 bb-input-group">
                                <label class="bb-form-label">Status</label>
                                <select name="status" class="bb-input" required><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-bb btn-bb-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-bb btn-bb-primary">Simpan Promo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
